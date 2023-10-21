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

use EcclesiaCRM\data\Countries;
use EcclesiaCRM\data\States;

class PublicDataController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getCountries(ServerRequest $request, Response $response, array $args): Response
    {
        return $response->withJson(Countries::getAll());
    }

    public function getStates(ServerRequest $request, Response $response, array $args): Response
    {
        $states = new States($args['countryCode']);
        return $response->withJson($states->getAll());
    }
}
