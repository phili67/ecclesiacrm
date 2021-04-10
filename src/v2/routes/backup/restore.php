<?php

/*******************************************************************************
 *
 *  filename    : routes/restore.php
 *  last change : 2019-11-21
 *  description : manage the restore
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/restore', function (RouteCollectorProxy $group) {
    $group->get('', 'renderRestore');
    $group->get('/', 'renderRestore');
});

function renderRestore (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/backup/');

    if ( !( SessionUser::getUser()->isAdmin() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'restore.php', argumentsRestoreArray());
}

function argumentsRestoreArray ()
{
    $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
        'sRootDocument' => SystemURLs::getDocumentRoot(),
        'sPageTitle'  => _('Restore Database'),
        'encryptionMethod' => SystemConfig::getValue('sPGP')
    ];

    return $paramsArguments;
}
