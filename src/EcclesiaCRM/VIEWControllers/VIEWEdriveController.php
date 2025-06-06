<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2023/05/22
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\RedirectUtils;


use Slim\Views\PhpRenderer;

class VIEWEdriveController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderDashbord (ServerRequest $request, Response $response, array $args): Response
    {
        $renderer = new PhpRenderer('templates/edrive/');        

        // Security: User must have finance permission or be the one who created this deposit
        if ( !(SessionUser::getUser()->isEDriveEnabled() )) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $res = $this->argumentsdashboardArray();
        

        return $renderer->render($response, 'dashboard.php', $res);
    }

    public function argumentsdashboardArray ()
    {        
        //Set the page title
        $sPageTitle = _('Edrive : Dashboard ');

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();
        $user           = SessionUser::getUser();
        $personId       = $user->getPersonId();

        $paramsArguments = [
            'sRootPath'          => SystemURLs::getRootPath(),
            'sRootDocument'      => $sRootDocument,
            'CSPNonce'           => $CSPNonce,
            'sPageTitle'         => $sPageTitle,
            'user'               => $user,
            'personId'           => $personId
        ];

        return $paramsArguments;
    }

}