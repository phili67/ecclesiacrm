<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/volunteeropportunityeditor', function () {
    $this->get('', 'renderVolunteerOpportunityEditor');
    $this->get('/', 'renderVolunteerOpportunityEditor');
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
