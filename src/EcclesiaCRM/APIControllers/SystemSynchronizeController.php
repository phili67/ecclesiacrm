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

use EcclesiaCRM\Service\SynchronizeService;

class SystemSynchronizeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function synchronize (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$cacheProvider->withExpires($response, 0);

        $dataFull = [];

        $SystemService = $this->container->get('SystemService');

        if ($SystemService->getSessionTimeout() < 10) {
            $dataTimeout = ['timeOut' => 1, 'availableTime' => $SystemService->getSessionTimeout()];
        } else {
            $dataTimeout = ['timeOut' => 0, 'availableTime' => $SystemService->getSessionTimeout()];
        }

        array_push($dataFull, $dataTimeout);

        if ($SystemService->getSessionTimeout() > 0) {
            $pageName = $request->getQueryParam("currentpagename", "");
            $DashboardValues = SynchronizeService::getValues($pageName);
            array_push($dataFull, $DashboardValues);
        }

        return $response->withJson($dataFull);
    }
}
