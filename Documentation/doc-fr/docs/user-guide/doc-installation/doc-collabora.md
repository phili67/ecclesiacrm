# Édition en ligne des documents de l'eDrive (Collabora Online)

EcclesiaCRM peut ouvrir les documents Office de l'eDrive (docx, doc, odt, rtf, xlsx, xls, ods, csv, pptx, ppt, odp) dans une nouvelle fenêtre et enregistrer les modifications directement dans le fichier d'origine.

Le serveur **Collabora Online** (conteneur Docker) est un service indépendant. Le CRM joue le rôle d'hôte **WOPI** : Collabora lui demande les informations du fichier, son contenu, puis lui renvoie le document enregistré.

Dans cette documentation :

| Rôle | Nom de domaine d'exemple |
|---|---|
| CRM (hôte WOPI) | `crm.example.com` |
| Collabora Online | `office.example.com` |

Les deux noms doivent pointer vers le serveur, avoir un certificat TLS valide, et le CRM doit être en HTTPS.

```
Navigateur ──(1) clic « Éditer » ─▶ crm.example.com/api/collabora/edit  (JWT) ─▶ jeton WOPI
Navigateur ──(2) iframe ──────────▶ office.example.com  (Apache ─▶ conteneur 127.0.0.1:9980)
Collabora  ──(3) WOPI ────────────▶ crm.example.com/api/wopi/files/...  (jeton WOPI, sans session)
```

## 1. Prérequis

- Docker et le plugin Docker Compose.
- Apache 2.4 avec les modules `proxy`, `proxy_http`, `proxy_wstunnel`, `ssl`, `headers`.
- Environ 2 Go de RAM libres pour le conteneur.
- Les ports 80 et 443 ouverts. Le port 9980 de Collabora reste local (`127.0.0.1`).

```bash
a2enmod proxy proxy_http proxy_wstunnel ssl headers
```

## 2. Le conteneur Collabora

Créez `/var/www/collabora/docker-compose.yml` :

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
      # Hôte(s) WOPI autorisé(s) = le CRM (protocole + domaine + port). aliasgroup2, 3... pour plusieurs CRM
      - aliasgroup1=https://crm.example.com:443
      # Nom public de Collabora
      - server_name=office.example.com
      # Le TLS est géré par Apache
      - extra_params=--o:ssl.enable=false --o:ssl.termination=true
      - dictionaries=fr_FR en_GB
      # Console d'administration : /browser/dist/admin/admin.html
      - username=admin
      - password=CHOISISSEZ_UN_MOT_DE_PASSE_FORT
    # Le conteneur joint le CRM directement par l'hôte, sans passer par l'IP publique ni le routeur
    extra_hosts:
      - "crm.example.com:host-gateway"
    volumes:
      # Thème « EDrive Office » : nom et icône du CRM dans l'éditeur (facultatif, voir plus bas)
      - /chemin/du/CRM/src/skin/collabora/edrive:/usr/share/coolwsd/browser/dist/edrive:ro

    networks:
      default:
        # Adresse IP fixe du conteneur Collabora dans le sous-réseau défini ci-dessous
        ipv4_address: 172.18.0.2
        ipv4_address: 172.18.0.2

# Sous-réseau fixe : doit correspondre au "Require ip" des vhosts Apache du CRM
networks:
  default:
    ipam:
      config:
        # Sous-réseau fixe pour le conteneur Collabora
        - subnet: 172.18.0.0/16
```

```bash
cd /var/www/collabora
docker compose up -d
docker compose logs -f collabora
```

!!! warning "Ne pas inverser `aliasgroup1` et `server_name`"
    - `aliasgroup1` est le domaine du **CRM** (liste blanche des hôtes autorisés).
    - `server_name` est le domaine de **Collabora**.

    Avec les deux valeurs inversées, l'éditeur est cherché sur le CRM et l'ouverture échoue ou renvoie vers `…/browser/…/index.php`.

!!! info "Message « Configuration du cloisonnement du Kit lente en copie, impossible de bind-mount »"
    Sur un hôte dont AppArmor restreint les montages (Ubuntu 24.04 et plus), Collabora ne peut pas faire de *bind-mount* pour construire ses environnements cloisonnés (jails) et les **copie** : l'ouverture d'un document est un peu plus lente et la page « À propos » de Collabora affiche cet avertissement. C'est sans conséquence fonctionnelle.
    Pour le supprimer, il faut élargir les droits (`cap_add: SYS_ADMIN` et `security_opt: apparmor=unconfined` dans le conteneur, ou `kernel.apparmor_restrict_unprivileged_userns=0` sur l'hôte). Ces options **affaiblissent l'isolation** d'un conteneur qui ouvre des documents fournis par les utilisateurs : elles sont déconseillées en production.

!!! info "Pourquoi `extra_hosts` ?"
    Sans cette ligne, le conteneur résout `crm.example.com` en IP publique et télécharge les documents via le routeur (retour par la box). Sur certaines installations, le débit tombe à quelques centaines de Ko/s : un fichier de 27 Mo met plus de 30 s, Collabora abandonne (`Timeout while requesting GET …/contents`) et l'ouverture est très lente. Avec `host-gateway`, la requête reste sur le serveur.
    Si le serveur Collabora est sur une autre machine que le CRM, remplacez `host-gateway` par l'IP privée du serveur CRM.

!!! info "Thème « EDrive Office » (nom et icône du CRM)"
    Le CRM ouvre l'éditeur avec le paramètre `theme=edrive`. Si le dossier `src/skin/collabora/edrive` du CRM est monté dans le conteneur (ligne `volumes` ci-dessus, à adapter : remplacez `/chemin/du/CRM` par le dossier d'installation de votre CRM, par exemple `/var/www/CRM`), la boîte « À propos » et le logo du document affichent **EDrive Office** et l'icône du CRM. Sans ce montage, Collabora garde son habillage par défaut. Si Collabora est sur une autre machine, copiez ce dossier sur celle-ci et adaptez le chemin de gauche. Après modification, recréez le conteneur (`docker compose up -d`) et videz le cache du navigateur.

## 3. Apache : site pour Collabora

Obtenez un certificat, par exemple avec Let's Encrypt :

```bash
certbot certonly --apache -d office.example.com
```

Créez `/etc/apache2/sites-available/office.example.com.conf` :

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

    # WebSocket de l'éditeur : « nocanon » conserve l'URL encodée (%3F, %2F...) telle quelle.
    # Ces règles doivent précéder le ProxyPass /cool générique.
    ProxyPass        /cool/ws ws://127.0.0.1:9980/cool/ws
    ProxyPassMatch   "/cool/(.*)/ws$" ws://127.0.0.1:9980/cool/$1/ws nocanon

    # Console d'administration (WebSocket) : avant /cool
    ProxyPass        /cool/adminws ws://127.0.0.1:9980/cool/adminws
    ProxyPassReverse /cool/adminws ws://127.0.0.1:9980/cool/adminws

    # Fichiers statiques, discovery, capabilities
    ProxyPass        /browser                http://127.0.0.1:9980/browser retry=0
    ProxyPassReverse /browser                http://127.0.0.1:9980/browser
    ProxyPass        /hosting/discovery      http://127.0.0.1:9980/hosting/discovery retry=0
    ProxyPassReverse /hosting/discovery      http://127.0.0.1:9980/hosting/discovery
    ProxyPass        /hosting/capabilities   http://127.0.0.1:9980/hosting/capabilities retry=0
    ProxyPassReverse /hosting/capabilities   http://127.0.0.1:9980/hosting/capabilities

    # Requêtes HTTP de l'éditeur
    ProxyPass        /cool http://127.0.0.1:9980/cool
    ProxyPassReverse /cool http://127.0.0.1:9980/cool
</VirtualHost>
```

```bash
a2ensite office.example.com
apachectl configtest && systemctl reload apache2
```

!!! warning "Pas de `RewriteRule` pour le WebSocket"
    Un `RewriteRule … [P,NE]` décode `%3F` en `?` : Collabora reçoit une URL erronée (`Unknown resource` dans ses logs) et affiche « Échec d'établissement de connexion par socket ». Apache 2.4.60+ refuse en plus ces URL (`AH10508: Unsafe URL with %3f`). Utilisez `ProxyPassMatch … nocanon` comme ci-dessus.

Si vous utilisez `mod_security` ou `mod_evasive`, désactivez-les pour ce site (l'éditeur envoie de nombreuses requêtes) et excluez aussi `/api/wopi/` du CRM de toute limitation de débit.

### Vérification

```bash
# doit renvoyer du XML dont les urlsrc commencent par https://office.example.com/browser/...
curl -s https://office.example.com/hosting/discovery | head -5
```

Aucune modification du site Apache du CRM n'est nécessaire : les routes `/api/wopi/` sont servies par l'`index.php` de l'API.

## 4. Configuration du CRM

1. Mettez à jour le schéma de base de données (mise à jour du CRM) : la table `wopi_tokens` est créée par `mysql/upgrade/9.0.0-upgrade.sql` (installation neuve : `Install.sql`).
2. Dans **Administration ▸ Réglages ▸ Intégration** :

| Réglage | Valeur |
|---|---|
| `sCollaboraURL` | `https://office.example.com` (vide = fonction désactivée) |
| `sCollaboraWopiURL` | Optionnel. URL publique du CRM vue par Collabora (par défaut : l'hôte de la requête) |
| `iCollaboraMaxVersions` | Nombre maximal de versions conservées par document (20 par défaut, 0 = aucune) |

3. Dans l'eDrive, le menu **Nouveau document** de la barre d'outils crée un document vide (Word `docx`, Excel `xlsx`, PowerPoint `pptx` ou Draw `odg`) dans le dossier courant, puis l'ouvre dans Collabora. Les modèles vides sont dans `Include/collabora-templates/`.
4. Dans l'eDrive, un bouton **Éditer** apparaît sur les fichiers pris en charge, uniquement si Collabora répond. La liste des formats est mise en cache une heure (60 s en cas d'échec) dans `tmp_attach/collabora_discovery.json` ; supprimez ce fichier pour la rafraîchir.

## 5. Fonctionnement et sécurité

- Le JWT de session n'est **jamais** transmis à Collabora. Le clic sur « Éditer » crée un **jeton WOPI** de 8 h, lié à un fichier et à un utilisateur, stocké dans `wopi_tokens`. Les jetons expirés sont supprimés à chaque création de jeton.
- Les routes `/api/wopi/*` n'utilisent ni session ni JWT : chaque appel est validé par le jeton. Le chemin du fichier est lu en base et revalidé, jamais fourni par le client.
- Seul le propriétaire d'un fichier peut l'éditer. Le document s'ouvre en lecture seule si le serveur web ne peut pas écrire dans le fichier ou son dossier.
- À chaque enregistrement, le fichier est remplacé de façon atomique (fichier temporaire puis `rename`). Un fichier vide est refusé.
- **Versions** : avant d'être remplacé, le contenu précédent est copié dans le dossier caché `.versions/` du même dossier (nom : `fichier.AAAAMMJJ-HHMMSS`, la date étant celle de modification du contenu). Au plus une version est créée toutes les 10 minutes, car Collabora enregistre automatiquement toutes les quelques dizaines de secondes. Le réglage `iCollaboraMaxVersions` (Intégration, 20 par défaut, 0 = aucune version) limite le nombre de versions conservées par fichier : les plus anciennes sont supprimées.
- Le bouton **Versions** (icône horloge) de la liste des fichiers affiche l'historique d'un document : on peut **télécharger**, **restaurer** ou **supprimer** une version. La restauration conserve le contenu actuel comme nouvelle version, elle peut donc être annulée. Fermez le document dans l'éditeur avant de restaurer, sinon Collabora réécrira son contenu à son prochain enregistrement.
- Collabora enregistre automatiquement et à la fermeture ; Ctrl+S force l'enregistrement.
- Pensez à protéger la console `/browser/dist/admin/admin.html` (mot de passe fort ou restriction d'IP).

Nettoyage des anciennes versions (exemple : plus de 90 jours) :

```bash
find /var/www/CRM/private/userdir -path '*/.versions/*' -mtime +90 -delete
```

## 6. Mise à jour du conteneur

```bash
cd /var/www/collabora
docker compose pull && docker compose up -d
docker image prune -f
rm -f /var/www/CRM/tmp_attach/collabora_discovery.json
```

## 7. Dépannage

| Symptôme | Cause probable | Solution |
|---|---|---|
| Pas de bouton « Éditer » | `sCollaboraURL` vide, Collabora injoignable, extension non prise en charge | Tester la discovery, supprimer `tmp_attach/collabora_discovery.json` |
| Redirection vers `…/browser/…/index.php` sur le domaine du CRM | `server_name` et `aliasgroup1` inversés | Voir l'avertissement du §2, puis `docker compose up -d --force-recreate` |
| « Unauthorized WOPI host » | `aliasgroup1` incorrect | Format exact `https://crm.example.com:443` |
| « Échec d'établissement de connexion par socket » | Proxy WebSocket mal configuré | Utiliser les règles `ProxyPass /cool/ws` et `ProxyPassMatch … nocanon` du §3 ; vérifier `a2enmod proxy_wstunnel` et `AH10508` dans `error.log` |
| Message « Configuration du cloisonnement du Kit lente en copie… » | Bind-mount impossible (AppArmor) | Sans gravité, voir la note du §2 avant d'élargir les droits |
| Chargement très lent, `Timeout while requesting GET …/contents` | Le conteneur télécharge le fichier via l'IP publique | Ajouter `extra_hosts` (§2) |
| Document ouvert en lecture seule | Fichier ou dossier non modifiable par `www-data` | Corriger les droits du dossier de l'eDrive |
| Erreur à l'enregistrement de gros fichiers | Limites de taille | `post_max_size`, `upload_max_filesize`, `LimitRequestBody` |
| Le conteneur ne joint pas le CRM | DNS, certificat, routage | Vérifier `extra_hosts` et le certificat du CRM |

Commandes utiles :

```bash
docker compose -f /var/www/collabora/docker-compose.yml logs -f collabora
tail -f /var/log/apache2/error.log
```

## 8. Environnement de développement (Docker)

La stack `docker/` peut faire tourner Collabora Online à côté du CRM pour le développement local. Collabora **ne fait pas partie** des images PHP : c'est un second serveur web (port 9980) qui tourne dans son propre service. Caddy termine le HTTPS devant lui, si bien que l'éditeur est servi sur `https://office.localhost:8443`. Cette configuration de développement est réservée aux tests et ne doit pas être utilisée en production.

1. Installez [mkcert](https://github.com/FiloSottile/mkcert) et créez le certificat pour `office.localhost` dans `docker/config/caddy/certs/` :

```bash
mkcert -install
cd docker/config/caddy/certs
mkcert -cert-file office.localhost.pem -key-file office.localhost-key.pem office.localhost
cp "$(mkcert -CAROOT)/rootCA.pem" rootCA.pem
```

2. Dans `docker/.env`, renseignez `COLLABORA_ADMIN_PASSWORD` (voir `sample.env`). `COLLABORA_HTTPS_PORT` vaut `8443` par défaut et `COLLABORA_SERVER_NAME` doit l'inclure (`office.localhost:8443`), sinon Collabora annonce des URL sans port.
3. Démarrez la stack :

```bash
cd docker
docker compose up -d database webserver caddy collabora
```

4. Dans **Administration ▸ Paramètres ▸ Intégration** :

| Paramètre | Valeur |
|---|---|
| `sCollaboraURL` | `https://office.localhost:8443` |
| `sCollaboraWopiURL` | `https://host.docker.internal:443` (le CRM vu depuis le conteneur Collabora) |

`office.localhost` correspond à la machine locale dans les navigateurs actuels : aucune modification du fichier hosts n'est nécessaire. Collabora rappelle le CRM en HTTPS sur le réseau Docker (`aliasgroup1=https://host.docker.internal:443`). Le `rootCA.pem` de mkcert est monté dans le conteneur Collabora et déclaré comme autorité de confiance via `ssl.ca_file_path` et `storage.ssl.ca_file_path` (dev uniquement). L'iframe de l'éditeur est autorisée sur la page du CRM via `net.frame_ancestors=https://localhost:*` (une seule origine, `extra_params` est découpé sur les espaces). Le certificat du CRM (`config/ssl/cert.pem`) doit inclure `host.docker.internal` dans son SAN, par exemple : `mkcert -cert-file config/ssl/cert.pem -key-file config/ssl/cert-key.pem localhost 127.0.0.1 ::1 host.docker.internal`.

La console d'administration est accessible sur `https://office.localhost:8443/browser/dist/admin/admin.html`, avec les valeurs `COLLABORA_ADMIN_USER` et `COLLABORA_ADMIN_PASSWORD`.

Dépannage (stack de développement) :

- `rootCA.pem` doit exister dans `docker/config/caddy/certs/` **avant** `docker compose up`, sinon Docker crée un dossier vide portant ce nom et le montage échoue silencieusement.
- Le conteneur `webserver` installe l'autorité mkcert (`update-ca-certificates`) à chaque démarrage : rien à refaire après une recréation. Si le bouton Edit est absent, supprimez `tmp_attach/collabora_discovery.json` (une découverte en échec est mise en cache brièvement) et testez la découverte depuis PHP.
- L'image Collabora n'a pas de shell : inspectez-la avec `docker compose logs collabora`. `net.frame_ancestors` est signalé comme obsolète dans les logs, mais c'est la seule forme acceptée via `extra_params`.
- Une fenêtre d'éditeur blanche avec une erreur `frame-ancestors` dans la console du navigateur signifie que l'origine du CRM n'est pas autorisée : vérifiez la valeur de `net.frame_ancestors`.
- Safari peut conserver un état périmé de la fenêtre de l'éditeur : rechargez eDrive (Cmd+Maj+R) ou redémarrez Safari après une modification de la configuration Collabora.
