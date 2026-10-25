# Online Document Editing for eDrive (Collabora Online)

EcclesiaCRM can open eDrive Office documents (docx, doc, odt, rtf, xlsx, xls, ods, csv, pptx, ppt, odp) in a new window and save changes directly back to the original file.

The **Collabora Online** server (Docker container) is an independent service. The CRM acts as a **WOPI** host: Collabora requests file information and content from it, then sends the saved document back.

In this documentation:

| Role | Example Domain Name |
|---|---|
| CRM (WOPI host) | `crm.example.com` |
| Collabora Online | `office.example.com` |

Both domain names must point to the server, have a valid TLS certificate, and the CRM must use HTTPS.

```
Browser ──(1) Click "Edit" ──▶ crm.example.com/api/collabora/edit  (JWT) ──▶ WOPI token
Browser ──(2) iframe ─────────▶ office.example.com  (Apache ──▶ container 127.0.0.1:9980)
Collabora ─(3) WOPI ──────────▶ crm.example.com/api/wopi/files/...  (WOPI token, no session)
```

## 1. Prerequisites

- Docker and the Docker Compose plugin.
- Apache 2.4 with modules `proxy`, `proxy_http`, `proxy_wstunnel`, `ssl`, and `headers`.
- Approximately 2 GB of free RAM for the container.
- Ports 80 and 443 open. Collabora's port 9980 remains local (`127.0.0.1`).

```bash
a2enmod proxy proxy_http proxy_wstunnel ssl headers
```

## 2. The Collabora Container

Create `/var/www/collabora/docker-compose.yml`:

```yaml
services:
  collabora:
    image: collabora/code:latest
    container_name: collabora
    restart: unless-stopped
    cap_add:
      - MKNOD
      # - SYS_ADMIN
    ports:
      - "127.0.0.1:9980:9980"
    environment:
      # Allowed WOPI Host(s) = the CRM (protocol + domain + port). aliasgroup2, 3... for multiple CRMs
      - aliasgroup1=https://crm.example.com:443
      # Public domain name of Collabora
      - server_name=office.example.com
      # TLS is managed by Apache
      - extra_params=--o:ssl.enable=false --o:ssl.termination=true
      - dictionaries=fr_FR en_GB
      # Administration console: /browser/dist/admin/admin.html
      - username=admin
      - password=change-me-to-a-strong-password
    # The container reaches the CRM directly via the host, bypassing public IP and router
    extra_hosts:
      - "crm.example.com:host-gateway"
    volumes:
      # "EDrive Office" theme: CRM name and icon inside the editor (optional, see details below)
      - /path/to/CRM/src/skin/collabora/edrive:/usr/share/coolwsd/browser/dist/edrive:ro

    networks:
      default:
        # Fixed IP address for the Collabora container within the subnet defined below
        ipv4_address: 172.18.0.2

# Fixed subnet: must match the "Require ip" in the CRM Apache vhosts
networks:
  default:
    ipam:
      config:
        # Fixed subnet for the Collabora container
        - subnet: 172.18.0.0/16
```

```bash
cd /var/www/collabora
docker compose up -d
docker compose logs -f collabora
```

!!! warning "Do not invert `aliasgroup1` and `server_name`"
    - `aliasgroup1` is the **CRM** domain (allowed hosts whitelist).
    - `server_name` is the **Collabora** domain.

    If these two values are swapped, the editor will be sought on the CRM and opening will fail or redirect to `…/browser/…/index.php`.

!!! info "Message "Kit jail setup slow copying, cannot bind-mount""
    On a host where AppArmor restricts mounts (Ubuntu 24.04 and above), Collabora cannot perform a *bind-mount* to build its isolated environments (jails) and **copies** them instead: opening a document becomes slightly slower and Collabora's "About" page displays this warning. It has no impact on functionality.
    To remove it, you need to elevate permissions (`cap_add: SYS_ADMIN` and `security_opt: apparmor=unconfined` inside the container, or `kernel.apparmor_restrict_unprivileged_userns=0` on the host). These options **weaken container isolation** when opening user-provided documents and are not recommended in production.

!!! info "Why use `extra_hosts`?"
    Without this line, the container resolves `crm.example.com` to its public IP and downloads files through the router (NAT loopback). On some setups, speed drops to a few hundred KB/s: a 27 MB file takes over 30 seconds, causing Collabora to time out (`Timeout while requesting GET …/contents`) and resulting in very slow opening times. With `host-gateway`, requests remain internal to the server.
    If the Collabora server is hosted on a different machine than the CRM, replace `host-gateway` with the CRM server's private IP address.

!!! info ""EDrive Office" Theme (CRM name and icon)"
    The CRM launches the editor using the parameter `theme=edrive`. If the CRM's `src/skin/collabora/edrive` folder is mounted inside the container (see `volumes` line above; adjust `/path/to/CRM` to your actual CRM installation path, e.g., `/var/www/CRM`), the "About" box and document icon will display **EDrive Office** alongside the CRM icon. Without this mount, Collabora uses its default theme. If Collabora runs on a different machine, copy this folder to that machine and adjust the host path accordingly. After modifying, recreate the container (`docker compose up -d`) and clear your browser cache.

## 3. Apache: Site Configuration for Collabora

Obtain a certificate, for instance using Let's Encrypt:

```bash
certbot certonly --apache -d office.example.com
```

Create `/etc/apache2/sites-available/office.example.com.conf`:

```apache
<VirtualHost *:80>
    ServerName office.example.com
    Redirect permanent / https://office.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName office.example.com

    SSLEngine On
    SSLCertificateFile    /etc/letsencrypt/live/office.example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/office.example.com/privkey.pem

    AllowEncodedSlashes NoDecode
    ProxyPreserveHost On
    ProxyTimeout 300

    # Editor WebSocket: "nocanon" preserves encoded URLs (%3F, %2F...) intact.
    # These rules must precede the generic ProxyPass /cool directive.
    ProxyPass        /cool/ws ws://127.0.0.1:9980/cool/ws
    ProxyPassMatch   "/cool/(.*)/ws$" ws://127.0.0.1:9980/cool/$1/ws nocanon

    # Admin Console (WebSocket): must be before /cool
    ProxyPass        /cool/adminws ws://127.0.0.1:9980/cool/adminws
    ProxyPassReverse /cool/adminws ws://127.0.0.1:9980/cool/adminws

    # Static files, discovery, capabilities
    ProxyPass        /browser                http://127.0.0.1:9980/browser retry=0
    ProxyPassReverse /browser                http://127.0.0.1:9980/browser
    ProxyPass        /hosting/discovery      http://127.0.0.1:9980/hosting/discovery retry=0
    ProxyPassReverse /hosting/discovery      http://127.0.0.1:9980/hosting/discovery
    ProxyPass        /hosting/capabilities   http://127.0.0.1:9980/hosting/capabilities retry=0
    ProxyPassReverse /hosting/capabilities   http://127.0.0.1:9980/hosting/capabilities

    # Editor HTTP requests
    ProxyPass        /cool http://127.0.0.1:9980/cool
    ProxyPassReverse /cool http://127.0.0.1:9980/cool
</VirtualHost>
```

```bash
a2ensite office.example.com
apachectl configtest && systemctl reload apache2
```

!!! warning "Do not use `RewriteRule` for WebSocket"
    A rule using `RewriteRule … [P,NE]` decodes `%3F` to `?`: Collabora receives a malformed URL (`Unknown resource` in logs) and displays "Socket connection failed". Apache 2.4.60+ explicitly rejects these URLs (`AH10508: Unsafe URL with %3f`). Use `ProxyPassMatch … nocanon` as shown above.

If using `mod_security` or `mod_evasive`, disable them for this virtual host (the editor makes frequent requests) and exclude `/api/wopi/` on the CRM from any rate limiting rules.

### Verification

```bash
# Should return XML containing urlsrc attributes starting with https://office.example.com/browser/...
curl -s https://office.example.com/hosting/discovery | head -5
```

No modifications are required on the CRM's Apache site configuration: `/api/wopi/` routes are handled directly by the API's `index.php`.

## 4. CRM Configuration

1. Update the database schema (CRM upgrade): the `wopi_tokens` table is created via `mysql/upgrade/9.0.0-upgrade.sql` (for fresh installs: `Install.sql`).
2. Go to **Administration ▸ Settings ▸ Integration**:

| Setting | Value |
|---|---|
| `sCollaboraURL` | `https://office.example.com` (empty = feature disabled) |
| `sCollaboraWopiURL` | Optional. Public URL of the CRM as seen by Collabora (default: request host) |
| `iCollaboraMaxVersions` | Maximum number of retained versions per document (default: 20, 0 = disabled) |

3. In eDrive, the **New Document** menu in the toolbar creates a blank document (Word `docx`, Excel `xlsx`, PowerPoint `pptx`, or Draw `odg`) in the current folder, then opens it in Collabora. Blank templates are located in `Include/collabora-templates/`.
4. In eDrive, an **Edit** button appears on supported files only when Collabora is reachable. Supported format discovery is cached for one hour (60 seconds on failure) in `tmp_attach/collabora_discovery.json`; delete this file to force a refresh.

## 5. Operation and Security

- The session JWT is **never** shared with Collabora. Clicking "Edit" generates an 8-hour **WOPI token** bound to a specific file and user, stored in `wopi_tokens`. Expired tokens are purged automatically whenever a new token is created.
- Routes under `/api/wopi/*` require neither a session nor a JWT; each call is authorized by the WOPI token. The file path is retrieved directly from the database and validated—it is never supplied by the client.
- Only the file owner can edit it. The document opens in read-only mode if the web server lacks write permissions to the file or its directory.
- On every save operation, the file is replaced atomically (via temporary file and `rename`). Empty files are rejected.
- **Versions**: Before being overwritten, previous file content is backed up into a hidden `.versions/` directory within the same folder (naming scheme: `file.YYYYMMDD-HHMMSS`, using the timestamp of content modification). At most one version is saved every 10 minutes to accommodate Collabora's automatic periodic saving. The `iCollaboraMaxVersions` setting (Integration settings, default 20, 0 = none) limits the number of stored versions per file, purging the oldest ones automatically.
- The **Versions** button (clock icon) in the file list shows a document's history, enabling you to **download**, **restore**, or **delete** a version. Restoring a file saves the current content as a new version first, making the restoration reversible. Close the document in the editor prior to restoring; otherwise, Collabora will overwrite the restored file on its next auto-save.
- Collabora saves automatically and upon closing; Ctrl+S forces an immediate save.
- Remember to protect the admin console `/browser/dist/admin/admin.html` (strong password or IP restriction).

To clean up old version backups (e.g., older than 90 days):

```bash
find /var/www/CRM/private/userdir -path '*/.versions/*' -mtime +90 -delete
```

## 6. Updating the Container

```bash
cd /var/www/collabora
docker compose pull && docker compose up -d
docker image prune -f
rm -f /var/www/CRM/tmp_attach/collabora_discovery.json
```

## 7. Troubleshooting

| Symptom | Probable Cause | Solution |
|---|---|---|
| Missing "Edit" button | `sCollaboraURL` is empty, Collabora is unreachable, or file extension is unsupported | Test discovery endpoint; remove `tmp_attach/collabora_discovery.json` |
| Redirected to `…/browser/…/index.php` on CRM domain | `server_name` and `aliasgroup1` are swapped | Refer to warning in §2, then run `docker compose up -d --force-recreate` |
| "Unauthorized WOPI host" | Incorrect `aliasgroup1` | Use exact format: `https://crm.example.com:443` |
| "Socket connection failed" | Misconfigured WebSocket proxy | Use `ProxyPass /cool/ws` and `ProxyPassMatch … nocanon` from §3; verify `a2enmod proxy_wstunnel` and check for `AH10508` in `error.log` |
| Warning "Kit jail setup slow copying..." | Cannot bind-mount (AppArmor) | Harmless; check note in §2 before elevating privileges |
| Very slow loading, `Timeout while requesting GET …/contents` | Container downloading file via public IP | Add `extra_hosts` directive (§2) |
| Document opens in read-only mode | File or folder not writable by `www-data` | Fix permissions on the eDrive folder |
| Error when saving large files | Size limit exceeded | Adjust `post_max_size`, `upload_max_filesize`, and `LimitRequestBody` |
| Container cannot reach CRM | DNS, TLS certificate, or routing issues | Verify `extra_hosts` configuration and CRM certificate validity |

Useful commands:

```bash
docker compose -f /var/www/collabora/docker-compose.yml logs -f collabora
tail -f /var/log/apache2/error.log
```

## 8. Development Environment (Docker)

The `docker/` stack can run Collabora Online next to the CRM for local development. Collabora is **not** part of the PHP images: it is a second web server (port 9980) and runs as its own service. Caddy terminates HTTPS in front of it, so the editor is served at `https://office.localhost:8443`. The development setup is for testing only and must not be used in production.

1. Install [mkcert](https://github.com/FiloSottile/mkcert) and create the certificate for `office.localhost` in `docker/config/caddy/certs/`:

```bash
mkcert -install
cd docker/config/caddy/certs
mkcert -cert-file office.localhost.pem -key-file office.localhost-key.pem office.localhost
```

2. In `docker/.env`, set `COLLABORA_ADMIN_PASSWORD` (see `sample.env`). `COLLABORA_HTTPS_PORT` defaults to `8443`.
3. Start the stack:

```bash
cd docker
docker compose up -d database webserver caddy collabora
```

4. In **Administration ▸ Settings ▸ Integration**:

| Setting | Value |
|---|---|
| `sCollaboraURL` | `https://office.localhost:8443` |
| `sCollaboraWopiURL` | `http://host.docker.internal:80` (the CRM as seen from the Collabora container) |

`office.localhost` resolves to the local machine in current browsers, so no hosts file change is needed. Collabora calls back the CRM over plain HTTP inside the Docker network (`ssl.enable=false`, `aliasgroup1=http://host.docker.internal:80`), so the CRM certificate is not checked in this setup.

The admin console is available at `https://office.localhost:8443/browser/dist/admin/admin.html`, with the `COLLABORA_ADMIN_USER` and `COLLABORA_ADMIN_PASSWORD` values.
