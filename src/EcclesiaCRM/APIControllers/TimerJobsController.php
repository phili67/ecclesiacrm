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


class TimerJobsController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function runTimerJobs (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $SystemService = $this->container->get('SystemService');
        if ( !is_null($SystemService) ) {
            $SystemService->runTimerJobs();

            return $response->withJson(['status' => 'success']);
        }

        return $response->withJson(['status' => 'failed']);
    }
}
