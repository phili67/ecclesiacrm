<?php


//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWPastoralcareListController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderPastoralCareList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/sidebar/');

        if ( !(SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isPastoralCareEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'pastoralcarelist.php', $this->argumentsPastoralCareListArray());
    }

    public function argumentsPastoralCareListArray ()
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
}
