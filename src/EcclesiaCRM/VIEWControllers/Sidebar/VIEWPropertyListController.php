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
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PropertyTypeQuery;

use Slim\Views\PhpRenderer;

class VIEWPropertyListController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderPropertyList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/sidebar/');

        if ( !( SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        //Get the type to display
        $sType = $args['type'];

        //Based on the type, set the TypeName
        switch ($sType) {
            case 'p':
                $sTypeName = _('Person');
                break;

            case 'f':
                $sTypeName = _('Family');
                break;

            case 'g':
                $sTypeName = _('Group');
                break;

            default:
                RedirectUtils::Redirect('v2/dashboard');
                exit;
                break;
        }

        return $renderer->render($response, 'propertylist.php', $this->argumentsPropertyListArray($sType,$sTypeName));
    }

    public function argumentsPropertyListArray ($sType,$sTypeName)
    {
        //Set the page title
        $sPageTitle = _("Property List");

        $sRootDocument  = SystemURLs::getDocumentRoot();

        // We need the properties types
        $propertyTypes = PropertyTypeQuery::Create()
            ->filterByPrtClass($sType)
            ->find();


        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'CSPNonce'      => SystemURLs::getCSPNonce(),
            'sPageTitle'    => $sPageTitle,
            'propertyTypes' => $propertyTypes,
            'sType'         => $sType,
            'sTypeName'     => $sTypeName,
            'isMenuOption'  => SessionUser::getUser()->isMenuOptionsEnabled()
        ];
        return $paramsArguments;
    }
}
