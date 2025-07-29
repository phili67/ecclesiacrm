<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2025/07/28
//


namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;


class VIEWRestoreController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderRestore (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/backup/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'restore.php', $this->argumentsRestoreArray());
    }

    public function argumentsRestoreArray ()
    {
        $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => _('Restore Database')."/CRM",
            'encryptionMethod' => SystemConfig::getValue('sPGP')
        ];

        return $paramsArguments;
    }
}
