<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/propertytypelist', function () {
    $this->get('', 'renderPropertyTypeList');
    $this->get('/', 'renderPropertyTypeList');
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
