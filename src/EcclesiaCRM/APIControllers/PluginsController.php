<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/02/11
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\PluginQuery;

class PluginsController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function activate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->Id) )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);
            $plugin->setActiv(true);
            $plugin->save();

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function deactivate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->Id) )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);
            $plugin->setActiv(false);
            $plugin->save();

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function remove (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->Id) )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);

            if (!is_null($plugin)) {
                $plugin->delete();
            }
            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }
}
