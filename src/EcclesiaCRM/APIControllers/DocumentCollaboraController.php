<?php

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Service\CollaboraService;
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

        $file = $collabora->resolveUserFile($user, trim($currentPath, '/') . '/' . $fileName);
        if (is_null($file)) {
            return $this->error($response, _('Document not found'), 404);
        }

        [$path, $relativePath] = $file;

        // the file is replaced by a rename() : the folder must be writable too
        $canWrite = is_writable($path) && is_writable(dirname($path));

        $fileId = $collabora->getFileId(SessionUser::getId(), $relativePath);
        $url = $collabora->getEditorURL($fileName, $fileId, Bootstrapper::GetCurrentLocale()->getShortLocale());

        if (is_null($url)) {
            return $this->error($response, _('Document not found'), 404);
        }

        [$token, $ttl] = $collabora->createToken(SessionUser::getId(), SessionUser::getId(), $relativePath, $canWrite);

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

    /**
     * Absolute path of a file of the current folder of the connected user (versions management), null if it isn't usable
     */
    private function resolveOwnFile($params): ?string
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

        $file = (new CollaboraService())->resolveUserFile($user, trim($currentPath, '/') . '/' . $fileName);

        return is_null($file) ? null : $file[0];
    }

    public function versions(ServerRequest $request, Response $response, array $args): Response
    {
        $path = $this->resolveOwnFile((object)$request->getParsedBody());

        if (is_null($path)) {
            return $this->error($response, _('Document not found'), 404);
        }

        $versions = array_map(function ($version) {
            return [
                'id' => $version['id'],
                'date' => date(SystemConfig::getValue('sDateTimeFormat'), $version['timestamp']),
                'size' => MiscUtils::FileSizeConvert($version['size'])
            ];
        }, (new CollaboraService())->listVersions($path));

        return $response->withJson([
            'success' => true,
            'versions' => $versions,
            'currentDate' => date(SystemConfig::getValue('sDateTimeFormat'), filemtime($path)),
            'currentSize' => MiscUtils::FileSizeConvert(filesize($path))
        ]);
    }

    public function downloadVersion(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $path = $this->resolveOwnFile($params);
        $versionPath = (is_null($path) || !isset($params->version)) ? null
            : (new CollaboraService())->getVersionPath($path, (string)$params->version);

        if (is_null($versionPath)) {
            return $this->error($response, _('Document not found'), 404);
        }

        return $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="' . basename($path) . '"')
            ->withHeader('Cache-Control', 'no-store')
            ->withBody(new \Slim\Psr7\Stream(fopen($versionPath, 'rb')));
    }

    public function restoreVersion(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $path = $this->resolveOwnFile($params);

        if (is_null($path) || !isset($params->version)) {
            return $this->error($response, _('Document not found'), 404);
        }

        if (!(new CollaboraService())->restoreVersion($path, (string)$params->version)) {
            return $this->error($response, _('Unable to restore this version'), 400);
        }

        return $response->withJson(['success' => true]);
    }

    public function deleteVersion(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();
        $path = $this->resolveOwnFile($params);

        if (is_null($path) || !isset($params->version)
            || !(new CollaboraService())->deleteVersion($path, (string)$params->version)) {
            return $this->error($response, _('Document not found'), 404);
        }

        return $response->withJson(['success' => true]);
    }
}
