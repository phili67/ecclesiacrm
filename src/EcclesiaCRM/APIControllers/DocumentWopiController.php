<?php

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Psr7\Stream;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\CollectionsinstancesQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Service\SharedDocumentService;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\DocumentSecurityUtils;
use EcclesiaCRM\WebDav\Utils\SabreUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Service\CollaboraService;

/**
 * WOPI host endpoints called by the Collabora Online server (no user session, no JWT) :
 * each request is authorized by the WOPI access token created in DocumentCollaboraController.
 */
class DocumentWopiController
{
    private $container;
    private CollaboraService $collabora;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->collabora = new CollaboraService();
    }

    private function authorize(ServerRequest $request, array $args): ?array
    {
        $token = (string)($request->getQueryParams()['access_token'] ?? '');

        return $this->collabora->validateToken($token, (string)$args['id']);
    }

    // CheckFileInfo : GET /wopi/files/{id}
    public function checkFileInfo(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->authorize($request, $args);

        if (is_null($file)) {
            return $response->withStatus(404);
        }

        $person = PersonQuery::create()->findPk($file['user_id']);
        $user = UserQuery::create()->findOneByPersonId($file['user_id']);
        $modified = filemtime($file['path']);
        $origin = parse_url($this->collabora->getWopiHostURL());
        $sharedAlias = $this->resolveSharedAlias($file);
        $canRename = $this->canRename($file, $sharedAlias);

        return $response->withJson([
            'BaseFileName' => $sharedAlias['name'] ?? $file['name'],
            'OwnerId' => (string)$file['owner_id'],
            'UserId' => (string)$file['user_id'],
            'UserFriendlyName' => is_null($person) ? 'User ' . $file['user_id'] : $person->getFullName(),
            'Size' => filesize($file['path']),
            'Version' => (string)$modified,
            'LastModifiedTime' => gmdate('Y-m-d\TH:i:s', $modified) . '.0000000Z',
            'UserCanWrite' => $file['can_write'],
            'UserCanRename' => $canRename,
            'SupportsRename' => true,
            'SupportsUpdate' => true,
            'IsAdminUser' => !is_null($user) && $user->isAdmin(),
            'UserCanNotWriteRelative' => !$canRename,
            'PostMessageOrigin' => $origin['scheme'] . '://' . $origin['host'] . (isset($origin['port']) ? ':' . $origin['port'] : '')
        ], null, JSON_UNESCAPED_UNICODE);
    }

    // RenameFile : POST /wopi/files/{id}, X-WOPI-Override: RENAME_FILE
    public function renameFile(ServerRequest $request, Response $response, array $args): Response
    {
        if ($request->getHeaderLine('X-WOPI-Override') !== 'RENAME_FILE') {
            return $response->withStatus(501);
        }

        $file = $this->authorize($request, $args);
        if (is_null($file)) {
            return $response->withStatus(404);
        }

        $sharedAlias = $this->resolveSharedAlias($file);
        if (!$this->canRename($file, $sharedAlias)) {
            return $response->withStatus(403);
        }

        $currentName = $sharedAlias['name'] ?? $file['name'];
        $newName = $this->normalizeRequestedName(
            $request->getHeaderLine('X-WOPI-RequestedName'),
            $currentName
        );
        if (is_null($newName)) {
            return $response->withStatus(400);
        }

        if ($newName === $currentName) {
            return $response->withJson(['Name' => $newName]);
        }

        $status = (int)$file['owner_id'] === (int)$file['user_id']
            ? $this->renameOwnedFile($file, $newName)
            : $this->renameSharedAlias($file, $sharedAlias, $newName);

        if ($status !== 200) {
            return $response->withStatus($status);
        }

        return $response->withJson(['Name' => $newName], null, JSON_UNESCAPED_UNICODE);
    }

    // GetFile : GET /wopi/files/{id}/contents
    public function getFile(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->authorize($request, $args);

        if (is_null($file)) {
            return $response->withStatus(404);
        }

        return $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Length', (string)filesize($file['path']))
            ->withHeader('X-WOPI-ItemVersion', (string)filemtime($file['path']))
            ->withBody(new Stream(fopen($file['path'], 'rb')));
    }

    // PutFile : POST /wopi/files/{id}/contents
    public function putFile(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->authorize($request, $args);

        if (is_null($file)) {
            return $response->withStatus(404);
        }

        if (!$file['can_write']) {
            return $response->withStatus(403);
        }

        // the temporary file stays in the same folder : the final rename() is atomic
        $tmpFile = dirname($file['path']) . '/.wopi.' . bin2hex(random_bytes(6)) . '.tmp';
        $out = @fopen($tmpFile, 'wb');

        if ($out === false) {
            return $response->withStatus(500);
        }

        $body = $request->getBody();
        while (!$body->eof()) {
            fwrite($out, $body->read(65536));
        }
        fclose($out);

        // we never replace a document by an empty file
        if (filesize($tmpFile) === 0) {
            unlink($tmpFile);
            return $response->withStatus(400);
        }

        $this->collabora->backupFile($file['path']);

        @chmod($tmpFile, fileperms($file['path']) & 0777);

        if (!rename($tmpFile, $file['path'])) {
            @unlink($tmpFile);
            return $response->withStatus(500);
        }

        clearstatcache(true, $file['path']);

        return $response->withJson(['LastModifiedTime' => gmdate('Y-m-d\TH:i:s', filemtime($file['path'])) . '.0000000Z']);
    }

    private function resolveSharedAlias(array $file): ?array
    {
        if ((int)$file['owner_id'] === (int)$file['user_id']) {
            return null;
        }

        $owner = UserQuery::create()->findOneByPersonId((int)$file['owner_id']);
        $guest = UserQuery::create()->findOneByPersonId((int)$file['user_id']);
        $relativePath = DocumentSecurityUtils::normalizeRelativePath((string)($file['relative_path'] ?? ''));
        if (is_null($owner) || is_null($guest) || is_null($relativePath)) {
            return null;
        }

        $ownerBasePath = rtrim($owner->getUserRootDir(), '/') . '/' . strtolower($owner->getUserName()) . '/';
        $ownerPath = $ownerBasePath . $relativePath;
        // WOPI stores the owner's source path; resolve the recipient's unique symbolic-link entry.
        $statement = Bootstrapper::GetPDO()->prepare(
            'SELECT ci.id
             FROM collectionsinstances ci
             INNER JOIN collections c ON c.id = ci.collections_id
             WHERE c.ownerId = :owner_id AND c.ownerPath = :owner_path AND ci.guestId = :guest_id'
        );
        $statement->execute([
            ':owner_id' => (int)$file['owner_id'],
            ':owner_path' => $ownerPath,
            ':guest_id' => (int)$file['user_id']
        ]);
        $shareIds = $statement->fetchAll(\PDO::FETCH_COLUMN);
        if (count($shareIds) !== 1) {
            return null;
        }

        $sharedDocument = (new SharedDocumentService())->resolve((int)$shareIds[0], (int)$file['user_id']);
        if (is_null($sharedDocument)
            || !$sharedDocument['canWrite']
            || realpath($sharedDocument['path']) !== realpath($file['path'])) {
            return null;
        }

        $share = $sharedDocument['share'];
        $guestPath = str_replace('\\', '/', (string)$share->getGuestpath());
        $guestDirectory = rtrim($guest->getUserDir(), '/') . '/';
        if (strpos($guestPath, $guestDirectory) !== 0) {
            return null;
        }

        $documentRoot = rtrim(SystemURLs::getDocumentRoot(), '/');
        $linkPath = $documentRoot . '/' . $guestPath;
        $guestRoot = realpath($documentRoot . '/' . rtrim($guest->getUserDir(), '/'));
        $linkParent = realpath(dirname($linkPath));
        $sourcePath = realpath($file['path']);
        if ($guestRoot === false
            || $linkParent === false
            || !$this->isPathWithinDirectory($linkParent, $guestRoot)
            || !is_link($linkPath)
            || $sourcePath === false
            || realpath($linkPath) !== $sourcePath) {
            return null;
        }

        $relativeAliasPath = DocumentSecurityUtils::normalizeRelativePath(substr($guestPath, strlen($guestDirectory)));
        if (is_null($relativeAliasPath)) {
            return null;
        }

        return [
            'share_id' => (int)$shareIds[0],
            'guest_user' => $guest,
            'guest_path' => $guestPath,
            'relative_path' => $relativeAliasPath,
            'link_path' => $linkPath,
            'name' => basename($guestPath),
            'can_write' => $sharedDocument['canWrite']
        ];
    }

    private function canRename(array $file, ?array $sharedAlias): bool
    {
        if (!$file['can_write']
            || !is_file($file['path'])
            || !is_writable($file['path'])
            || !is_writable(dirname($file['path']))) {
            return false;
        }

        if ((int)$file['owner_id'] === (int)$file['user_id']) {
            return true;
        }

        return !is_null($sharedAlias)
            && $sharedAlias['can_write']
            && is_writable(dirname($sharedAlias['link_path']));
    }

    private function normalizeRequestedName(string $requestedName, string $currentName): ?string
    {
        $requestedName = DocumentSecurityUtils::validateSimpleName($requestedName);
        $extension = pathinfo($currentName, PATHINFO_EXTENSION);
        if (is_null($requestedName) || $extension === '') {
            return null;
        }

        $requestedExtension = pathinfo($requestedName, PATHINFO_EXTENSION);
        if ($requestedExtension === '') {
            return $requestedName . '.' . $extension;
        }

        return strcasecmp($requestedExtension, $extension) === 0 ? $requestedName : null;
    }

    private function renameOwnedFile(array $file, string $newName): int
    {
        $owner = UserQuery::create()->findOneByPersonId((int)$file['owner_id']);
        if (is_null($owner)) {
            return 404;
        }

        $oldRelativePath = $file['relative_path'];
        $directory = dirname($oldRelativePath);
        $newRelativePath = ($directory === '.' ? '' : $directory . '/') . $newName;
        $newFile = $this->collabora->resolveUserFile($owner, $newRelativePath, false);
        if (is_null($newFile) || dirname($newFile[0]) !== dirname($file['path'])) {
            return 400;
        }

        $newPath = $newFile[0];
        if (file_exists($newPath) || is_link($newPath)) {
            return 409;
        }

        if (!rename($file['path'], $newPath)) {
            return 500;
        }

        $this->collabora->moveVersions($file['path'], $newPath);
        $this->collabora->updateTokensAfterRename((int)$file['owner_id'], $oldRelativePath, $newRelativePath);

        $username = $owner->getUserName();
        SabreUtils::moveSharedFileOrCollection(
            'principals/' . $username,
            'home/' . $username . '/' . $oldRelativePath,
            'home/' . $username . '/' . $newRelativePath
        );
        $this->updateNotesAfterRename(
            (int)$file['owner_id'],
            $username,
            $oldRelativePath,
            $newRelativePath
        );

        return 200;
    }

    private function renameSharedAlias(array $file, ?array $sharedAlias, string $newName): int
    {
        if (is_null($sharedAlias) || !$sharedAlias['can_write']) {
            return 403;
        }

        $oldGuestPath = $sharedAlias['guest_path'];
        $newGuestPath = dirname($oldGuestPath) . '/' . $newName;
        $newLinkPath = rtrim(SystemURLs::getDocumentRoot(), '/') . '/' . $newGuestPath;
        if (file_exists($newLinkPath) || is_link($newLinkPath)) {
            return 409;
        }

        $guest = $sharedAlias['guest_user'];
        $guestRootPath = rtrim($guest->getUserRootDir(), '/') . '/';
        if (strpos($oldGuestPath, $guestRootPath) !== 0) {
            return 403;
        }

        $oldDavRelativePath = substr($oldGuestPath, strlen($guestRootPath));
        $newDavRelativePath = dirname($oldDavRelativePath) . '/' . $newName;
        $oldDavPath = 'home/' . $oldDavRelativePath;
        $newDavPath = 'home/' . $newDavRelativePath;

        SabreUtils::moveSharedFileOrCollection(
            'principals/' . $guest->getUserName(),
            $oldDavPath,
            $newDavPath
        );

        $share = CollectionsinstancesQuery::create()->findOneById($sharedAlias['share_id']);
        $sourcePath = realpath($file['path']);
        if (is_null($share)
            || str_replace('\\', '/', (string)$share->getGuestpath()) !== $newGuestPath
            || !is_link($newLinkPath)
            || $sourcePath === false
            || realpath($newLinkPath) !== $sourcePath) {
            return 500;
        }

        $newRelativePath = dirname($sharedAlias['relative_path']) === '.'
            ? $newName
            : dirname($sharedAlias['relative_path']) . '/' . $newName;
        $this->updateNotesAfterRename(
            (int)$file['user_id'],
            $guest->getUserName(),
            $sharedAlias['relative_path'],
            $newRelativePath
        );

        return 200;
    }

    private function updateNotesAfterRename(int $personId, string $username, string $oldRelativePath, string $newRelativePath): void
    {
        $oldNotesPath = $username . '/' . $oldRelativePath;
        $newNotesPath = $username . '/' . $newRelativePath;
        $notes = NoteQuery::create()
            ->filterByPerId($personId)
            ->filterByText($oldNotesPath . '%', Criteria::LIKE)
            ->find();

        foreach ($notes as $note) {
            $oldText = $note->getText();
            if (strpos($oldText, $oldNotesPath) === 0) {
                $note->setText($newNotesPath . substr($oldText, strlen($oldNotesPath)));
                $note->setEntered($personId);
                $note->save();
            }
        }
    }

    private function isPathWithinDirectory(string $path, string $directory): bool
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);

        return $path === $directory || str_starts_with($path, $directory . DIRECTORY_SEPARATOR);
    }
}
