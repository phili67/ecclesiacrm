<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\User;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\DocumentSecurityUtils;
use Propel\Runtime\Propel;

/**
 * Collabora Online integration (WOPI host) for the eDrive.
 *
 * The files are never identified by a path coming from the client : the WOPI token
 * stores the owner and the relative path, validated when the token is created.
 */
class CollaboraService
{
    private const CACHE_TTL = 3600;
    private const FAIL_TTL = 60;
    private const TOKEN_TTL = 28800;
    private const BACKUP_INTERVAL = 600;
    // Collabora branding folder (skin/collabora/edrive), ignored by Collabora when it isn't mounted in the container
    private const BRANDING_THEME = 'edrive';

    private const EDITABLE_EXTENSIONS = [
        'docx', 'doc', 'odt', 'rtf',
        'xlsx', 'xls', 'ods', 'csv',
        'pptx', 'ppt', 'odp',
        'odg'
    ];

    // empty documents (generated with Collabora) used to create new files
    private const NEW_DOCUMENT_TYPES = ['docx', 'xlsx', 'pptx', 'odg'];

    private ?array $discoveryMap = null;

    private function getConnection()
    {
        return Propel::getConnection();
    }

    public function getCollaboraURL(): string
    {
        return rtrim(trim((string)SystemConfig::getValue('sCollaboraURL')), '/');
    }

    public function isConfigured(): bool
    {
        return $this->getCollaboraURL() !== '';
    }

    /**
     * Public URL of this CRM as seen by Collabora (without trailing slash)
     */
    public function getWopiHostURL(): string
    {
        $url = rtrim(trim((string)SystemConfig::getValue('sCollaboraWopiURL')), '/');

        return $url !== '' ? $url : SystemURLs::getHost();
    }

    /**
     * The file extension is supported and the Collabora server is reachable
     */
    public function isEditable(string $fileName): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return in_array($ext, self::EDITABLE_EXTENSIONS, true) && isset($this->getDiscovery()[$ext]);
    }

    public function getEditorURL(string $fileName, string $fileId, string $lang = 'en'): ?string
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $urlsrc = $this->getDiscovery()[$ext] ?? null;

        if (is_null($urlsrc)) {
            return null;
        }

        $separator = (substr($urlsrc, -1) === '?' || substr($urlsrc, -1) === '&') ? '' : (strpos($urlsrc, '?') === false ? '?' : '&');
        $wopiSrc = $this->getWopiHostURL() . '/api/wopi/files/' . $fileId;

        return $urlsrc . $separator . 'WOPISrc=' . rawurlencode($wopiSrc) . '&lang=' . rawurlencode($lang) . '&theme=' . self::BRANDING_THEME;
    }

    /**
     * @return array [extension => urlsrc], empty when Collabora isn't reachable (cached on disk)
     */
    public function getDiscovery(): array
    {
        if (!is_null($this->discoveryMap)) {
            return $this->discoveryMap;
        }

        if (!$this->isConfigured()) {
            return $this->discoveryMap = [];
        }

        $cacheFile = SystemURLs::getDocumentRoot() . '/tmp_attach/collabora_discovery.json';

        if (is_file($cacheFile)) {
            $cache = json_decode((string)file_get_contents($cacheFile), true);

            if (is_array($cache) && ($cache['server'] ?? '') === $this->getCollaboraURL() && is_array($cache['map'] ?? null)) {
                $ttl = count($cache['map']) ? self::CACHE_TTL : self::FAIL_TTL;

                if (time() - filemtime($cacheFile) < $ttl) {
                    return $this->discoveryMap = $cache['map'];
                }
            }
        }

        $map = $this->fetchDiscovery();

        @file_put_contents($cacheFile, json_encode(['server' => $this->getCollaboraURL(), 'map' => $map]), LOCK_EX);

        return $this->discoveryMap = $map;
    }

    private function fetchDiscovery(): array
    {
        $context = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);
        $xml = @file_get_contents($this->getCollaboraURL() . '/hosting/discovery', false, $context);

        if (!$xml) {
            return [];
        }

        $discovery = @simplexml_load_string($xml);
        if (!$discovery || !isset($discovery->{'net-zone'})) {
            return [];
        }

        $map = [];
        foreach ($discovery->{'net-zone'}->app as $app) {
            foreach ($app->action as $action) {
                if ((string)$action['name'] === 'edit' && (string)$action['ext'] !== '') {
                    $map[(string)$action['ext']] = (string)$action['urlsrc'];
                }
            }
        }

        return $map;
    }

    /**
     * Absolute path (and normalized relative path) of a file of the user's eDrive, null if it isn't usable
     *
     * @param bool $mustExist false to resolve the path of a file which is going to be created
     * @return array{0:string,1:string}|null [absolute path, relative path in the user's eDrive]
     */
    public function resolveUserFile(User $user, string $relativePath, bool $mustExist = true): ?array
    {
        $relativePath = DocumentSecurityUtils::normalizeRelativePath($relativePath);

        if (is_null($relativePath) || $relativePath === '') {
            return null;
        }

        // the "public" folder of the eDrive is a symbolic link to the public directory of the user
        $segments = explode('/', $relativePath);
        if ($segments[0] === 'public' && count($segments) > 1) {
            $baseDirectory = realpath(SystemURLs::getDocumentRoot() . '/' . $user->getUserPublicDir());
            $subPath = implode('/', array_slice($segments, 1));
        } else {
            $baseDirectory = DocumentSecurityUtils::resolveDirectoryFromRelativeBase(
                SystemURLs::getDocumentRoot(),
                $user->getUserRootDir(),
                '/' . trim($user->getUserName(), '/')
            );
            $subPath = $relativePath;
        }

        if (!$baseDirectory) {
            return null;
        }

        $path = DocumentSecurityUtils::resolvePathWithinBase($baseDirectory, $subPath, $mustExist);

        if (is_null($path) || ($mustExist && !is_file($path))) {
            return null;
        }

        return [$path, $relativePath];
    }

    public function isNewDocumentType(string $type): bool
    {
        return in_array($type, self::NEW_DOCUMENT_TYPES, true);
    }

    public function getTemplatePath(string $type): ?string
    {
        if (!$this->isNewDocumentType($type)) {
            return null;
        }

        $path = SystemURLs::getDocumentRoot() . '/Include/collabora-templates/empty.' . $type;

        return is_file($path) ? $path : null;
    }

    public function getFileId(int $ownerId, string $relativePath): string
    {
        return sha1($ownerId . '|' . $relativePath);
    }

    /**
     * @return array{0:string,1:int} [token, expiration in milliseconds]
     */
    public function createToken(int $userId, int $ownerId, string $relativePath, bool $canWrite): array
    {
        $this->purgeExpired();

        $token = bin2hex(random_bytes(32));
        $expires = time() + self::TOKEN_TTL;

        $statement = $this->getConnection()->prepare(
            'INSERT INTO wopi_tokens (token, file_id, owner_id, user_id, rel_path, can_write, expires_at)
             VALUES (:token, :file_id, :owner_id, :user_id, :rel_path, :can_write, FROM_UNIXTIME(:expires))'
        );
        $statement->execute([
            ':token' => $token,
            ':file_id' => $this->getFileId($ownerId, $relativePath),
            ':owner_id' => $ownerId,
            ':user_id' => $userId,
            ':rel_path' => $relativePath,
            ':can_write' => (int)$canWrite,
            ':expires' => $expires
        ]);

        return [$token, $expires * 1000];
    }

    /**
     * Validate a WOPI access token for a file id
     *
     * @return array{user_id:int,owner_id:int,can_write:bool,path:string,name:string}|null
     */
    public function validateToken(string $token, string $fileId): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token) || !preg_match('/^[a-f0-9]{40}$/', $fileId)) {
            return null;
        }

        $statement = $this->getConnection()->prepare(
            'SELECT user_id, owner_id, rel_path, can_write FROM wopi_tokens
             WHERE token = :token AND file_id = :file_id AND expires_at > NOW()'
        );
        $statement->execute([':token' => $token, ':file_id' => $fileId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $owner = UserQuery::create()->findOneByPersonId((int)$row['owner_id']);
        if (is_null($owner)) {
            return null;
        }

        $file = $this->resolveUserFile($owner, $row['rel_path']);
        if (is_null($file)) {
            return null;
        }

        return [
            'user_id' => (int)$row['user_id'],
            'owner_id' => (int)$row['owner_id'],
            'can_write' => (bool)$row['can_write'],
            'path' => $file[0],
            'name' => basename($file[0])
        ];
    }

    public function purgeExpired(): void
    {
        $this->getConnection()->exec('DELETE FROM wopi_tokens WHERE expires_at < NOW()');
    }

    private function getVersionsDirectory(string $path): string
    {
        return dirname($path) . '/.versions';
    }

    /**
     * Versions of a file, the newest first. A version is stored as .versions/<file name>.<Ymd-His of its modification date>
     *
     * @return array<int, array{id:string,timestamp:int,size:int,path:string}>
     */
    public function listVersions(string $path): array
    {
        $directory = $this->getVersionsDirectory($path);
        $pattern = '/^' . preg_quote(basename($path), '/') . '\.(\d{8}-\d{6})$/';
        $versions = [];

        foreach (is_dir($directory) ? scandir($directory) : [] as $entry) {
            $date = preg_match($pattern, $entry, $matches) ? \DateTime::createFromFormat('Ymd-His', $matches[1]) : false;

            if ($date !== false && is_file($directory . '/' . $entry)) {
                $versions[] = [
                    'id' => $matches[1],
                    'timestamp' => $date->getTimestamp(),
                    'size' => filesize($directory . '/' . $entry),
                    'path' => $directory . '/' . $entry
                ];
            }
        }

        usort($versions, function ($a, $b) {
            return strcmp($b['id'], $a['id']);
        });

        return $versions;
    }

    public function getVersionPath(string $path, string $versionId): ?string
    {
        foreach ($this->listVersions($path) as $version) {
            if ($version['id'] === $versionId) {
                return $version['path'];
            }
        }

        return null;
    }

    /**
     * Keep the current content of the file in the hidden .versions folder before it is replaced.
     * Unless $force is set, at most one version is created every BACKUP_INTERVAL seconds.
     */
    public function backupFile(string $path, bool $force = false): void
    {
        $maxVersions = (int)SystemConfig::getValue('iCollaboraMaxVersions');

        if ($maxVersions <= 0 || !is_file($path)) {
            return;
        }

        $versions = $this->listVersions($path);

        if (!$force) {
            foreach ($versions as $version) {
                if (time() - filemtime($version['path']) < self::BACKUP_INTERVAL) {
                    return;
                }
            }
        }

        $directory = $this->getVersionsDirectory($path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $target = $directory . '/' . basename($path) . '.' . date('Ymd-His', filemtime($path));
        if (!file_exists($target)) {
            @copy($path, $target);
        }

        // only the newest versions are kept
        foreach (array_slice($this->listVersions($path), $maxVersions) as $version) {
            @unlink($version['path']);
        }
    }

    /**
     * Replace the file by one of its versions (the current content becomes a version too)
     */
    public function restoreVersion(string $path, string $versionId): bool
    {
        $versionPath = $this->getVersionPath($path, $versionId);

        if (is_null($versionPath) || !is_writable($path) || !is_writable(dirname($path))) {
            return false;
        }

        $tmpFile = dirname($path) . '/.wopi.' . bin2hex(random_bytes(6)) . '.tmp';

        if (!@copy($versionPath, $tmpFile)) {
            return false;
        }

        $this->backupFile($path, true);

        @chmod($tmpFile, fileperms($path) & 0777);

        if (!rename($tmpFile, $path)) {
            @unlink($tmpFile);
            return false;
        }

        clearstatcache(true, $path);

        return true;
    }

    public function deleteVersion(string $path, string $versionId): bool
    {
        $versionPath = $this->getVersionPath($path, $versionId);

        return !is_null($versionPath) && @unlink($versionPath);
    }

    /**
     * Remove all the versions of a file (to call once the file itself is deleted)
     */
    public function deleteAllVersions(string $path): void
    {
        foreach ($this->listVersions($path) as $version) {
            @unlink($version['path']);
        }

        $this->removeVersionsDirectoryIfEmpty($path);
    }

    /**
     * Make the versions of a file follow it when it is moved or renamed (to call once the file itself is moved)
     */
    public function moveVersions(string $oldPath, string $newPath): void
    {
        $versions = $this->listVersions($oldPath);

        if (!count($versions) || $this->getVersionsDirectory($oldPath) . '/' . basename($oldPath) === $this->getVersionsDirectory($newPath) . '/' . basename($newPath)) {
            return;
        }

        $directory = $this->getVersionsDirectory($newPath);
        if (!is_dir($directory) && !@mkdir($directory, 0775, true)) {
            return;
        }

        foreach ($versions as $version) {
            $target = $directory . '/' . basename($newPath) . '.' . $version['id'];

            if (!file_exists($target)) {
                @rename($version['path'], $target);
            } else {
                @unlink($version['path']);
            }
        }

        $this->removeVersionsDirectoryIfEmpty($oldPath);
    }

    private function removeVersionsDirectoryIfEmpty(string $path): void
    {
        $directory = $this->getVersionsDirectory($path);

        if (is_dir($directory) && count(scandir($directory)) === 2) {
            @rmdir($directory);
        }
    }
}
