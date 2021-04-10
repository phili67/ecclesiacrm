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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\SessionUser;

class SystemUpgradeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function downloadlatestrelease (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $SystemService = $this->container->get('SystemService');
        $upgradeFile = $SystemService->downloadLatestRelease();
        return $response->write(json_encode($upgradeFile));
    }

    public function doupgrade (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object) $request->getParsedBody();

        $SystemService = $this->container->get('SystemService');
        $upgradeResult = $SystemService->doUpgrade($input->fullPath, $input->sha1);
        return $response->write(json_encode($upgradeResult));
    }

    public function isUpdateRequired (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (SessionUser::getUser()->isAdmin() && $_SESSION['isSoftwareUpdateTestPassed'] == false) {
            $isUpdateRequired = $_SESSION['latestVersion'] != null && $_SESSION['latestVersion']['name'] != $_SESSION['sSoftwareInstalledVersion'];
            $_SESSION['isSoftwareUpdateTestPassed'] = true;
        } else {
            $isUpdateRequired = 0;
        }

        return $response->withJson(["Upgrade" => $isUpdateRequired,"latestVersion" => $_SESSION['latestVersion'], "installedVersion" => $_SESSION['sSoftwareInstalledVersion']]);
    }
}
