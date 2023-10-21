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

use EcclesiaCRM\dto\SystemURLs;

class SystemController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function cspReport (ServerRequest $request, Response $response, array $args): Response
    {
        $input = json_decode($request->getBody());
        $log  = json_encode($input, JSON_PRETTY_PRINT);

        $Logger = $this->container->get('Logger');
        $Logger->warn($log);

        return $response;
    }

    public function deleteFile (ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->name) && isset($params->path) ) {
            if (unlink(SystemURLs::getDocumentRoot().$params->path.$params->name)) {
                return $response->withJson(['status' => "success"]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }
}
