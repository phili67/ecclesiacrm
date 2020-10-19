<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/pastoralcarelist', function () {
    $this->get('', 'renderPastoralCareList');
    $this->get('/', 'renderPastoralCareList');
});


function renderPastoralCareList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');

    if ( !(SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isPastoralCareEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'pastoralcarelist.php', argumentsPastoralCareListArray());
}

function argumentsPastoralCareListArray ()
{
    //Set the page title
    $sPageTitle = _("Pastoral Care Type Editor");

    $sRootDocument  = SystemURLs::getDocumentRoot();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
                       'sRootDocument' => $sRootDocument,
                       'sPageTitle'    => $sPageTitle,
                       'isPastoralCareEnabled' => ( (SessionUser::getUser()->isMenuOptionsEnabled() || $personId > 0 && $personId == SessionUser::getUser()->getPersonId())?1:0 )
                       ];
   return $paramsArguments;
}
