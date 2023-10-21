<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWMenuLinkListController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderMenuLinkList (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sidebar/');

        return $renderer->render($response, 'menulinklist.php', $this->argumentsMenuLinkListArray());
    }

    public function renderMenuLinkListForPerson (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sidebar/');

        $personId = $args['personId'];

        if ( !($personId > 0 && $personId == SessionUser::getUser()->getPersonId()) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'menulinklist.php', $this->argumentsMenuLinkListArray($personId));
    }

    public function argumentsMenuLinkListArray ($personId=0)
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
}
