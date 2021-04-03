<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\Service\SynchronizeService;

$app->group('/synchronize', function (RouteCollectorProxy $group) {

/*
 * @! Returns the dashboard items in function of the current page name : for CRMJsom.js
 * #! param: page->string :: current page name
 */
    $group->get('/page', function (Request $request, Response $response, array $args) {
      //$cacheProvider->withExpires($response, 0);

      $dataFull = [];

      $SystemService = $this->get('SystemService');

      if ($SystemService->getSessionTimeout() < 10) {
        $dataTimeout = ['timeOut' => 1,'availableTime' =>  $SystemService->getSessionTimeout()];
      } else {
        $dataTimeout = ['timeOut' => 0,'availableTime' =>  $SystemService->getSessionTimeout()];
      }

      array_push ($dataFull,$dataTimeout);

      if ($SystemService->getSessionTimeout() > 0) {
          $pageName = $request->getQueryParam("currentpagename","");
          $DashboardValues = SynchronizeService::getValues($pageName);
          array_push ($dataFull,$DashboardValues);
      }

      return $response->withJson($dataFull);
    });
});
