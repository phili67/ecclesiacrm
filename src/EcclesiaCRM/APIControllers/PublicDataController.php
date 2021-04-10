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

use EcclesiaCRM\data\Countries;
use EcclesiaCRM\data\States;

class PublicDataController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getCountries(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $response->withJson(Countries::getAll());
    }

    public function getStates(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $states = new States($args['countryCode']);
        return $response->withJson($states->getAll());
    }
}
