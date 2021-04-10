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

class SystemIssueController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function issues(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = json_decode($request->getBody());

        $SystemService = $this->container->get('SystemService');

        return $response->write($SystemService->reportIssue($input));
    }
}
