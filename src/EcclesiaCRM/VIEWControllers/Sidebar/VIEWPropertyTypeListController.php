<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWPropertyTypeListController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderPropertyTypeList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/sidebar/');

        if ( !( SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'propertytypelist.php', $this->argumentsPropertyTypeListArray());
    }

    public function argumentsPropertyTypeListArray ()
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
}

