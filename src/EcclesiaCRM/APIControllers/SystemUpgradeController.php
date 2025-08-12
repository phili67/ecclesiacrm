<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\SessionUser;

class SystemUpgradeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function downloadlatestrelease (ServerRequest $request, Response $response, array $args): Response
    {
        $SystemService = $this->container->get('SystemService');
        $upgradeFile = $SystemService->downloadLatestRelease();
        return $response->write(json_encode($upgradeFile));
    }

    public function doupgrade (ServerRequest $request, Response $response, array $args): Response {
        $input = (object) $request->getParsedBody();

        $SystemService = $this->container->get('SystemService');
        $upgradeResult = $SystemService->doUpgrade($input->fullPath, $input->sha1);
        return $response->write(json_encode($upgradeResult));
    }

    public function isUpdateRequired (ServerRequest $request, Response $response, array $args): Response {
        if (SessionUser::getUser()->isAdmin() && $_SESSION['isSoftwareUpdateTestPassed'] == false) {
            $SystemService = $this->container->get('SystemService');
            $isUpdateRequired = $SystemService->isUpdateRequired();
            $_SESSION['isSoftwareUpdateTestPassed'] = true;
        } else {
            $isUpdateRequired = 0;
        }

        return $response->withJson(["Upgrade" => $isUpdateRequired,"latestVersion" => $_SESSION['latestVersion'], "installedVersion" => $_SESSION['sSoftwareInstalledVersion']]);
    }
}
