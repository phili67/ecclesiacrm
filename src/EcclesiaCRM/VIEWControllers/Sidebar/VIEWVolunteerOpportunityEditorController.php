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

class VIEWVolunteerOpportunityEditorController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderVolunteerOpportunityEditor (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/sidebar/');

        if ( !( SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'volunteeropportunityeditor.php', $this->argumentsVolunteerOpportunityEditorArray());
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
}
