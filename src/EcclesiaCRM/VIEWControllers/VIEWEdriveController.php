<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2023/05/22
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Service\CollaboraService;
use EcclesiaCRM\Service\SharedDocumentService;
use EcclesiaCRM\Utils\DocumentSecurityUtils;
use EcclesiaCRM\Utils\MiscUtils;

use Slim\Views\PhpRenderer;

class VIEWEdriveController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderDashbord (ServerRequest $request, Response $response, array $args): Response
    {
        $renderer = new PhpRenderer('templates/edrive/');        

        // Security: User must have finance permission or be the one who created this deposit
        if ( !(SessionUser::getUser()->isEDriveEnabled() )) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $res = $this->argumentsdashboardArray();
        

        return $renderer->render($response, 'dashboard.php', $res);
    }

    public function renderSharedWithMe (ServerRequest $request, Response $response, array $args): Response
    {
        return $this->renderShares($response, 'received');
    }

    public function renderSharedWithOthers (ServerRequest $request, Response $response, array $args): Response
    {
        return $this->renderShares($response, 'sent');
    }

    public function openSharedLocation (ServerRequest $request, Response $response, array $args): Response
    {
        $user = SessionUser::getUser();
        if (!$user->isEDriveEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $sharedDocument = (new SharedDocumentService())->resolve((int)$args['shareId'], SessionUser::getId());
        if (is_null($sharedDocument) || is_null($sharedDocument['navigationRelativePath'])) {
            return $response->withStatus(404);
        }

        $relativePath = $sharedDocument['navigationRelativePath'];
        $targetName = basename($relativePath);
        $targetRelativePath = dirname($relativePath);
        if ($targetRelativePath === '.') {
            $targetRelativePath = '';
        }

        $currentPath = DocumentSecurityUtils::normalizeDirectoryPath($targetRelativePath);
        if (is_null($currentPath)) {
            return $response->withStatus(404);
        }

        $targetDirectory = rtrim(SystemURLs::getDocumentRoot(), '/')
            . '/' . trim($user->getUserDir(), '/')
            . '/' . trim($currentPath, '/');
        $documentRoot = realpath(SystemURLs::getDocumentRoot());
        $realTargetDirectory = realpath($targetDirectory);
        if ($documentRoot === false || $realTargetDirectory === false
            || !is_dir($realTargetDirectory)
            || ($realTargetDirectory !== $documentRoot
                && strpos($realTargetDirectory, $documentRoot . DIRECTORY_SEPARATOR) !== 0)) {
            return $response->withStatus(404);
        }

        $user->setCurrentpath($currentPath);
        $user->save();
        $_SESSION['user'] = $user;

        return $response->withStatus(302)
            ->withHeader(
                'Location',
                SystemURLs::getRootPath() . '/v2/edrive/dashboard?select=' . rawurlencode($targetName)
            );
    }

    private function renderShares (Response $response, string $direction): Response
    {
        if (!SessionUser::getUser()->isEDriveEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $user = SessionUser::getUser();
        $personId = $user->getPersonId();
        $whereClause = $direction === 'received' ? 'ci.guestId = :personId' : 'c.ownerId = :personId';
        $query = '
            SELECT
                ci.id AS shareId,
                c.ownerPath AS ownerPath,
                ci.guestPath AS guestPath,
                ci.access AS accessLevel,
                ci.share_invitestatus AS inviteStatus,
                CONCAT_WS(\' \', NULLIF(owner.per_FirstName, \'\'), NULLIF(owner.per_LastName, \'\')) AS ownerName,
                CONCAT_WS(\' \', NULLIF(guest.per_FirstName, \'\'), NULLIF(guest.per_LastName, \'\')) AS guestName
            FROM collectionsinstances ci
            INNER JOIN collections c ON c.id = ci.collections_id
            INNER JOIN person_per owner ON owner.per_ID = c.ownerId
            INNER JOIN person_per guest ON guest.per_ID = ci.guestId
            WHERE ' . $whereClause . '
            ORDER BY c.ownerPath ASC, guest.per_LastName ASC, guest.per_FirstName ASC
        ';

        $statement = Bootstrapper::GetPDO()->prepare($query);
        $statement->execute(['personId' => $personId]);
        $shares = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $documentRoot = realpath(SystemURLs::getDocumentRoot());
        if ($documentRoot === false) {
            throw new \RuntimeException('The eDrive document root is not available.');
        }

        $shareRows = [];
        $collabora = new CollaboraService();

        foreach ($shares as $share) {
            $relativePath = $direction === 'received' ? $share['guestPath'] : $share['ownerPath'];
            $absolutePath = $documentRoot . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
            $realPath = realpath($absolutePath);

            if ($realPath !== false
                && $realPath !== $documentRoot
                && strpos($realPath, $documentRoot . DIRECTORY_SEPARATOR) !== 0) {
                $realPath = false;
            }

            $available = $realPath !== false;
            $readable = $available && is_readable($realPath);
            $isDirectory = $available && is_dir($realPath);
            $sizeBytes = $readable && is_file($realPath) ? filesize($realPath) : null;
            $modified = $readable ? filemtime($realPath) : false;
            $status = (int)$share['inviteStatus'];
            $access = (int)$share['accessLevel'];
            $name = basename(str_replace('\\', '/', $share['ownerPath']));
            $canEdit = $readable && !$isDirectory
                && ($direction === 'sent' || ($status === 2 && $access === 3))
                && is_writable($realPath) && is_writable(dirname($realPath))
                && $collabora->isEditable($name);
            $canDownload = $readable && !$isDirectory
                && ($direction === 'sent' || $status === 2);
            $navigationPath = str_replace('\\', '/', (string)$relativePath);
            $userDirectory = rtrim($user->getUserDir(), '/') . '/';
            $canNavigate = $available
                && ($direction === 'sent' || $status === 2)
                && strpos($navigationPath, $userDirectory) === 0;

            $shareRows[] = [
                'shareId' => (int)$share['shareId'],
                'name' => $name,
                'type' => !$available ? _('Unavailable') : ($isDirectory ? _('Folder') : _('File')),
                'icon' => !$available
                    ? SystemURLs::getRootPath() . '/Images/Icons/FILE.png'
                    : ($isDirectory ? SystemURLs::getRootPath() . '/Images/Icons/FOLDER.png' : MiscUtils::FileIcon($name)),
                'partner' => $direction === 'received' ? $share['ownerName'] : $share['guestName'],
                'accessLevel' => $access,
                'access' => $access === 2 ? _('Read only') : ($access === 3 ? _('Read and write') : _('Unknown')),
                'canPreview' => $available && ($direction === 'sent' || $status === 2),
                'canNavigate' => $canNavigate,
                'openUrl' => $canNavigate
                    ? SystemURLs::getRootPath() . '/v2/edrive/open-shared/' . (int)$share['shareId']
                    : null,
                'canEdit' => $canEdit,
                'canDownload' => $canDownload,
                'downloadUrl' => $canDownload
                    ? SystemURLs::getRootPath() . '/api/filemanager/getSharedFile/'
                        . (int)$share['shareId'] . '/' . rawurlencode($name) . '?download=1'
                    : null,
                'status' => match ($status) {
                    1 => _('No response'),
                    2 => _('Accepted'),
                    3 => _('Declined'),
                    4 => _('Invalid'),
                    default => _('Unknown')
                },
                'sizeBytes' => $sizeBytes === false ? null : $sizeBytes,
                'size' => $sizeBytes === false || $sizeBytes === null ? '—' : MiscUtils::FileSizeConvert($sizeBytes),
                'modified' => $modified === false ? null : $modified
            ];
        }

        $renderer = new PhpRenderer('templates/edrive/');
        return $renderer->render($response, 'shares.php', [
            'sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'CSPNonce' => SystemURLs::getCSPNonce(),
            'sPageTitle' => $direction === 'received' ? _('Shared with me') : _('Shared with others'),
            'direction' => $direction,
            'shareRows' => $shareRows
        ]);
    }

    public function argumentsdashboardArray ()
    {        
        //Set the page title
        $sPageTitle = _('Edrive : File manager');

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();
        $user           = SessionUser::getUser();
        $personId       = $user->getPersonId();

        $paramsArguments = [
            'sRootPath'          => SystemURLs::getRootPath(),
            'sRootDocument'      => $sRootDocument,
            'CSPNonce'           => $CSPNonce,
            'sPageTitle'         => '<i class="fas fa-folder-open mr-2 text-primary"></i>&nbsp;' . $sPageTitle,
            'user'               => $user,
            'personId'           => $personId
        ];

        return $paramsArguments;
    }

}