<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;


use EcclesiaCRM\SessionUser;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class SystemIssueController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function issues(ServerRequest $request, Response $response, array $args): Response
    {
        $input = json_decode($request->getBody());

        $SystemService = $this->container->get('SystemService');

        return $response->write($SystemService->reportIssue($input));
    }
}
