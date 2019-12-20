<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/restore', function () {
    $this->get('', 'renderRestore');
    $this->get('/', 'renderRestore');
});

function renderRestore (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/backup/');

    if ( !( SessionUser::getUser()->isAdmin() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'restore.php', argumentsRestoreArray());
}

function argumentsRestoreArray ()
{
    $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
        'sRootDocument' => SystemURLs::getDocumentRoot(),
        'sPageTitle'  => _('Backup Database'),
        'hasGZIP' => SystemConfig::getBooleanValue('bGZIP'),
        'hasZIP' => SystemConfig::getBooleanValue('bZIP'),
        'encryptionMethod' => SystemConfig::getValue('sPGP')
    ];

    return $paramsArguments;
}
