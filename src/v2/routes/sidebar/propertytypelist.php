<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/propertytypelist', function (RouteCollectorProxy $group) {
    $group->get('', 'renderPropertyTypeList');
    $group->get('/', 'renderPropertyTypeList');
});


function renderPropertyTypeList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');

    if ( !( SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'propertytypelist.php', argumentsPropertyTypeListArray());
}

function argumentsPropertyTypeListArray ()
{
    //Set the page title
    $sPageTitle = _("Property Type List");

    $sRootDocument  = SystemURLs::getDocumentRoot();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
                       'sRootDocument' => $sRootDocument,
                       'CSPNonce'      => SystemURLs::getCSPNonce(),
                       'sPageTitle'    => $sPageTitle,
                       'isMenuOption' => SessionUser::getUser()->isMenuOptionsEnabled()
                       ];
   return $paramsArguments;
}
