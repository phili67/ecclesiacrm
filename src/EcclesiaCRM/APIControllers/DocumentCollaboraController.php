<?php

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\CollectionsinstancesQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\User;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Service\CollaboraService;
use EcclesiaCRM\Service\SharedDocumentService;
use EcclesiaCRM\Utils\DocumentSecurityUtils;

/**
 * Online document editing of the eDrive files with Collabora Online (user side, protected by the JWT)
 */
class DocumentCollaboraController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function error(Response $response, string $message, int $status = 400): Response
    {
        return $response->withJson(['success' => false, 'message' => $message], $status);
    }

    public function edit(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $collabora = new CollaboraService();

        if (!SessionUser::getUser()->isEDriveEnabled() || !isset($params->personID, $params->name)
            || SessionUser::getId() != $params->personID) {
            return $this->error($response, _('Forbidden'), 403);
        }

        $fileName = DocumentSecurityUtils::validateSimpleName((string)$params->name);
        $user = UserQuery::create()->findOneByPersonId(SessionUser::getId());

        if (is_null($fileName) || is_null($user) || !$collabora->isEditable($fileName)) {
            return $this->error($response, _('Document not found'), 404);
        }

        $currentPath = DocumentSecurityUtils::normalizeDirectoryPath($user->getCurrentpath());
        if (is_null($currentPath)) {
            return $this->error($response, _('Document not found'), 404);
        }

        $requestedRelativePath = DocumentSecurityUtils::normalizeRelativePath(
            trim($currentPath, '/') . '/' . $fileName
        );
        if (is_null($requestedRelativePath)) {
            return $this->error($response, _('Document not found'), 404);
        }

        $file = $collabora->resolveUserFile($user, $requestedRelativePath);
        $ownerId = SessionUser::getId();
        $title = $fileName;
        $sharedDocument = null;

        if (is_null($file)) {
            $sharedDocument = $this->resolveSharedAlias($user, $requestedRelativePath);
            if (is_null($sharedDocument)) {
                return $this->error($response, _('Document not found'), 404);
            }
            if (!$sharedDocument['canWrite']) {
                return $this->error($response, _('Forbidden'), 403);
            }

            $file = $collabora->resolveUserFile($sharedDocument['owner'], $sharedDocument['relativePath']);
            if (is_null($file) || $file[0] !== $sharedDocument['path']) {
                return $this->error($response, _('Document not found'), 404);
            }

            $ownerId = $sharedDocument['ownerId'];
            $title = $sharedDocument['name'];
        }

        [$path, $relativePath] = $file;

        // the file is replaced by a rename() : the folder must be writable too
        $canWrite = is_writable($path) && is_writable(dirname($path));
        if (!is_null($sharedDocument)) {
            $canWrite = $sharedDocument['canWrite'] && $canWrite;
            if (!$canWrite) {
                return $this->error($response, _('Forbidden'), 403);
            }
        }

        $fileId = $collabora->getFileId($ownerId, $relativePath);
        $url = $collabora->getEditorURL($title, $fileId, Bootstrapper::GetCurrentLocale()->getShortLocale());

        if (is_null($url)) {
            return $this->error($response, _('Document not found'), 404);
        }

        [$token, $ttl] = $collabora->createToken(SessionUser::getId(), $ownerId, $relativePath, $canWrite);

        return $response->withJson([
            'success' => true,
            'url' => $url,
            'access_token' => $token,
            'access_token_ttl' => $ttl,
            'title' => $title
        ]);
    }

    private function resolveSharedAlias(User $guest, string $relativePath): ?array
    {
        $relativePath = DocumentSecurityUtils::normalizeRelativePath($relativePath);
        if (is_null($relativePath)) {
            return null;
        }

        $guestPath = str_replace('\\', '/', rtrim($guest->getUserDir(), '/') . '/' . $relativePath);
        $linkPath = rtrim(SystemURLs::getDocumentRoot(), '/') . '/' . $guestPath;
        if (!is_link($linkPath)) {
            return null;
        }

        $share = CollectionsinstancesQuery::create()->findOneByGuestpath($guestPath);
        if (is_null($share) || (int)$share->getGuestid() !== SessionUser::getId()) {
            return null;
        }

        $sharedDocument = (new SharedDocumentService())->resolve((int)$share->getId(), SessionUser::getId());
        if (is_null($sharedDocument)) {
            return null;
        }

        $linkTarget = realpath($linkPath);
        $sourcePath = realpath($sharedDocument['path']);
        if ($linkTarget === false || $sourcePath === false || $linkTarget !== $sourcePath) {
            return null;
        }

        return $sharedDocument;
    }

    public function editShared(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        if (!SessionUser::getUser()->isEDriveEnabled() || !isset($params->shareId)) {
            return $this->error($response, _('Forbidden'), 403);
        }

        $collabora = new CollaboraService();
        $sharedDocument = (new SharedDocumentService())->resolve((int)$params->shareId, SessionUser::getId());
        if (is_null($sharedDocument) || !$sharedDocument['canWrite']) {
            return $this->error($response, _('Forbidden'), 403);
        }

        $fileName = $sharedDocument['name'];
        if (!$collabora->isEditable($fileName)) {
            return $this->error($response, _('Document not found'), 404);
        }

        $file = $collabora->resolveUserFile($sharedDocument['owner'], $sharedDocument['relativePath']);
        if (is_null($file) || $file[0] !== $sharedDocument['path']) {
            return $this->error($response, _('Document not found'), 404);
        }

        [$path, $relativePath] = $file;
        $canWrite = $sharedDocument['canWrite'] && is_writable($path) && is_writable(dirname($path));
        if (!$canWrite) {
            return $this->error($response, _('Forbidden'), 403);
        }

        $fileId = $collabora->getFileId($sharedDocument['ownerId'], $relativePath);
        $url = $collabora->getEditorURL($fileName, $fileId, Bootstrapper::GetCurrentLocale()->getShortLocale());
        if (is_null($url)) {
            return $this->error($response, _('Document not found'), 404);
        }

        [$token, $ttl] = $collabora->createToken(
            SessionUser::getId(),
            $sharedDocument['ownerId'],
            $relativePath,
            true
        );

        return $response->withJson([
            'success' => true,
            'url' => $url,
            'access_token' => $token,
            'access_token_ttl' => $ttl,
            'title' => $fileName
        ]);
    }

    public function create(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $collabora = new CollaboraService();

        if (!SessionUser::getUser()->isEDriveEnabled() || !isset($params->personID, $params->name, $params->type)
            || SessionUser::getId() != $params->personID) {
            return $this->error($response, _('Forbidden'), 403);
        }

        $type = strtolower((string)$params->type);
        $template = $collabora->getTemplatePath($type);
        $baseName = DocumentSecurityUtils::validateSimpleName(
            preg_replace('/\.(docx|xlsx|pptx|odg)$/i', '', trim((string)$params->name))
        );
        $user = UserQuery::create()->findOneByPersonId(SessionUser::getId());

        if (!$collabora->isConfigured() || is_null($template) || is_null($baseName) || is_null($user)) {
            return $this->error($response, _('Invalid name'), 400);
        }

        $currentPath = DocumentSecurityUtils::normalizeDirectoryPath($user->getCurrentpath());
        if (is_null($currentPath)) {
            return $this->error($response, _('Invalid name'), 400);
        }

        $fileName = $baseName . '.' . $type;
        $file = $collabora->resolveUserFile($user, trim($currentPath, '/') . '/' . $fileName, false);

        if (is_null($file) || !is_dir(dirname($file[0])) || !is_writable(dirname($file[0]))) {
            return $this->error($response, _('Invalid name'), 400);
        }

        if (file_exists($file[0]) || is_link($file[0])) {
            return $this->error($response, _('The file already exists'), 409);
        }

        if (!copy($template, $file[0])) {
            return $this->error($response, _('Unable to create the file'), 500);
        }

        @chmod($file[0], 0644);

        return $response->withJson(['success' => true, 'name' => $fileName]);
    }

    private function resolveVersionFile($params): ?array
    {
        if (!SessionUser::getUser()->isEDriveEnabled() || !isset($params->personID, $params->name)
            || SessionUser::getId() != $params->personID) {
            return null;
        }

        $fileName = DocumentSecurityUtils::validateSimpleName((string)$params->name);
        $user = UserQuery::create()->findOneByPersonId(SessionUser::getId());
        $currentPath = is_null($user) ? null : DocumentSecurityUtils::normalizeDirectoryPath($user->getCurrentpath());

        if (is_null($fileName) || is_null($currentPath)) {
            return null;
        }

        $relativePath = DocumentSecurityUtils::normalizeRelativePath(
            trim($currentPath, '/') . '/' . $fileName
        );
        if (is_null($relativePath)) {
            return null;
        }

        $collabora = new CollaboraService();
        $file = $collabora->resolveUserFile($user, $relativePath);
        if (!is_null($file)) {
            return [
                'path' => $file[0],
                'name' => $fileName,
                'canWrite' => is_writable($file[0]) && is_writable(dirname($file[0]))
            ];
        }

        $sharedDocument = $this->resolveSharedAlias($user, $relativePath);
        if (is_null($sharedDocument)) {
            return null;
        }

        $file = $collabora->resolveUserFile($sharedDocument['owner'], $sharedDocument['relativePath']);
        if (is_null($file) || $file[0] !== $sharedDocument['path']) {
            return null;
        }

        return [
            'path' => $file[0],
            'name' => $sharedDocument['name'],
            'canWrite' => $sharedDocument['canWrite']
                && is_writable($file[0])
                && is_writable(dirname($file[0]))
        ];
    }

    public function versions(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->resolveVersionFile((object)$request->getParsedBody());

        if (is_null($file) || !is_readable($file['path'])) {
            return $this->error($response, _('Document not found'), 404);
        }

        $versions = array_map(function ($version) {
            return [
                'id' => $version['id'],
                'date' => date(SystemConfig::getValue('sDateTimeFormat'), $version['timestamp']),
                'size' => MiscUtils::FileSizeConvert($version['size']),
                'author' => $version['author'] ?? ''
            ];
        }, (new CollaboraService())->listVersions($file['path']));

        return $response->withJson([
            'success' => true,
            'versions' => $versions,
            'currentDate' => date(SystemConfig::getValue('sDateTimeFormat'), filemtime($file['path'])),
            'currentSize' => MiscUtils::FileSizeConvert(filesize($file['path'])),
            'currentAuthor' => (new CollaboraService())->getCurrentAuthor($file['path']) ?? ''
        ]);
    }

    public function downloadVersion(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $file = $this->resolveVersionFile($params);
        $versionPath = (is_null($file) || !isset($params->version)) ? null
            : (new CollaboraService())->getVersionPath($file['path'], (string)$params->version);

        if (is_null($versionPath) || !is_readable($versionPath)) {
            return $this->error($response, _('Document not found'), 404);
        }

        return $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', "attachment; filename*=UTF-8''" . rawurlencode($file['name']))
            ->withHeader('Cache-Control', 'no-store')
            ->withBody(new \Slim\Psr7\Stream(fopen($versionPath, 'rb')));
    }

    public function restoreVersion(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $file = $this->resolveVersionFile($params);

        if (is_null($file) || !isset($params->version)) {
            return $this->error($response, _('Document not found'), 404);
        }
        if (!$file['canWrite']) {
            return $this->error($response, _('Forbidden'), 403);
        }

        if (!(new CollaboraService())->restoreVersion($file['path'], (string)$params->version)) {
            return $this->error($response, _('Unable to restore this version'), 400);
        }

        return $response->withJson(['success' => true]);
    }

    public function deleteVersion(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $file = $this->resolveVersionFile($params);

        if (is_null($file) || !isset($params->version)) {
            return $this->error($response, _('Document not found'), 404);
        }
        if (!$file['canWrite']) {
            return $this->error($response, _('Forbidden'), 403);
        }
        if (!(new CollaboraService())->deleteVersion($file['path'], (string)$params->version)) {
            return $this->error($response, _('Document not found'), 404);
        }

        return $response->withJson(['success' => true]);
    }
}
