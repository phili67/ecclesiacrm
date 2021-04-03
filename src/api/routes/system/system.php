<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


use EcclesiaCRM\dto\SystemURLs;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$app->group('/system', function (RouteCollectorProxy $group) {
    $group->post('/csp-report', function (Request $request, Response $response, array $args) {
          $input = json_decode($request->getBody());
          $log  = json_encode($input, JSON_PRETTY_PRINT);

          $Logger = $this->get('Logger');
          $Logger->warn($log);

          return $response;
  });

    $group->post('/deletefile', function (Request $request, Response $response, array $args) {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->name) && isset($params->path) ) {
          if (unlink(SystemURLs::getDocumentRoot().$params->path.$params->name)) {
            return $response->withJson(['status' => "success"]);
          }
        }

        return $response->withJson(['status' => "failed"]);
  });
});
