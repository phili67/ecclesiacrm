<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/menulinklist', function () {
    $this->get('', 'renderMenuLinkList');
    $this->get('/{personId:[0-9]+}', 'renderMenuLinkListForPerson');
});

function renderMenuLinkList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');
    
    $personId = 0;

    return $renderer->render($response, 'menulinklist.php', argumentsMl($personId));
}

function renderMenuLinkListForPerson (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sidebar/');
    
    $personId = $args['personId'];

    if ( !($personId > 0 && $personId == SessionUser::getUser()->getPersonId()) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }
    
    return $renderer->render($response, 'menulinklist.php', argumentsMl($personId));
}

function argumentsMl ($personId)
{
    //Set the page title
    $sPageTitle = _("Custom Menus List");

    if ($personId > 0) {// we are in the case of Personal Links
      $sPageTitle .= " "._("For")." : ".SessionUser::getUser()->getFullName();
    }

    $sRootDocument  = SystemURLs::getDocumentRoot();
          
    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
                       'sRootDocument' => $sRootDocument,
                       'sPageTitle'    => $sPageTitle, 
                       'personId'      => $personId,
                       'addCustomLink' => ( (SessionUser::getUser()->isMenuOptionsEnabled() || $personId > 0 && $personId == SessionUser::getUser()->getPersonId())?1:0 )
                       ];   
   return $paramsArguments;
}