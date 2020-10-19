<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/kioskmanager', function () {
    $this->get('', 'renderKioskManager');
    $this->get('/', 'renderKioskManager');
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
