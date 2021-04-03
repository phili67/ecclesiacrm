<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/volunteeropportunityeditor', function (RouteCollectorProxy $group) {
    $group->get('', 'renderVolunteerOpportunityEditor');
    $group->get('/', 'renderVolunteerOpportunityEditor');
});


function renderVolunteerOpportunityEditor (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');

    if ( !( SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'volunteeropportunityeditor.php', argumentsVolunteerOpportunityEditorArray());
}

function argumentsVolunteerOpportunityEditorArray ()
{
    //Set the page title
    $sPageTitle = _("Volunteer Opportunity Editor");

    $sRootDocument  = SystemURLs::getDocumentRoot();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
                       'sRootDocument' => $sRootDocument,
                       'sPageTitle'    => $sPageTitle,
                       'isVolunteerOpportunityEnabled' => SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()
                       ];
   return $paramsArguments;
}
