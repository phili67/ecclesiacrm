<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;


class VIEWKioskManagerController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderKioskManager (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sidebar/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'kioskmanager.php', $this->argumentsKioskManagerArray());
    }

    public function argumentsKioskManagerArray ()
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

}
