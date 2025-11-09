<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use EcclesiaCRM\Base\ConfigQuery;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\LocaleInfo;

use Slim\Views\PhpRenderer;

class VIEWSystemSettingsController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderSettings (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sidebar/');

        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'systemsettings.php', $this->argumentsSystemSettingsArray());
    }

    public function argumentsSystemSettingsArray ()
    {
        $saved = false;

        //Set the page title
        $sPageTitle = _("General Settings");

        $sRootDocument  = SystemURLs::getDocumentRoot();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'sPageTitle'    => $sPageTitle,
            'saved'         => $saved,
            'Mode'          => '',
            'categories'    => SystemConfig::getCategories()
        ];
        return $paramsArguments;
    }

    public function renderSettingsMode (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sidebar/');

        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $sMode = $args['mode'];

        return $renderer->render($response, 'systemsettings.php', $this->argumentsSystemSettingsModeArray($sMode));
    }
    
    public function argumentsSystemSettingsModeArray ($sMode)
    {
        $saved = false;

        //Set the page title
        $sPageTitle = _("General Settings");

        $sRootDocument  = SystemURLs::getDocumentRoot();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'sPageTitle'    => $sPageTitle,
            'Mode'          => $sMode,
            'categories'    => SystemConfig::getCategories()
        ];
        
        return $paramsArguments;
    }
    
}
