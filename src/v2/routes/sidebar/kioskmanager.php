<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/kioskmanager', function (RouteCollectorProxy $group) {
    $group->get('', 'renderKioskManager');
    $group->get('/', 'renderKioskManager');
});


function renderKioskManager (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');

    if ( !( SessionUser::getUser()->isAdmin() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'kioskmanager.php', argumentsKioskManagerArray());
}

function argumentsKioskManagerArray ()
{
    //Set the page title
    $sPageTitle = _("Kiosk Manager");

    $sRootDocument  = SystemURLs::getDocumentRoot();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
                       'sRootDocument' => $sRootDocument,
                       'sPageTitle'    => $sPageTitle
                        ];
   return $paramsArguments;
}
