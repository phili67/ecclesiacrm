<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/fundlist', function (RouteCollectorProxy $group) {
    $group->get('', 'renderFundList');
    $group->get('/', 'renderFundList');
});


function renderFundList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');

    if ( !( SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'fundlist.php', argumentsFundListArray());
}

function argumentsFundListArray ()
{
    //Set the page title
    $sPageTitle = _("Donation Fund Editor");

    $sRootDocument  = SystemURLs::getDocumentRoot();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
                       'sRootDocument' => $sRootDocument,
                       'sPageTitle'    => $sPageTitle,
                       'isMenuOption' => SessionUser::getUser()->isMenuOptionsEnabled()
                       ];
   return $paramsArguments;
}
