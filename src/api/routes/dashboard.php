<?php

use Slim\Http\Request;
use Slim\Http\Response;
use EcclesiaCRM\Service\NewDashboardService;
use EcclesiaCRM\Service\SystemService;

$app->group('/dashboard', function () {
   $this->get('/page', function (Request $request,Response $response,array $args) {
      $dataFull = [];
  
      //
      //error_log("Les arguments sont coucou : ".print_r($p_args), 3, "/var/log/mes-erreurs.log");
      if ($this->SystemService->getSessionTimeout() < 10) {
        $dataTimeout = ['timeOut' => 1,'availableTime' =>  $this->SystemService->getSessionTimeout()];
      } else {
        $dataTimeout = ['timeOut' => 0,'availableTime' =>  $this->SystemService->getSessionTimeout()];
      }
  
      array_push ($dataFull,$dataTimeout);

      if ($this->SystemService->getSessionTimeout() > 0) {
          $pageName = $request->getQueryParam("currentpagename","");
          $DashboardValues = NewDashboardService::getValues($pageName);
          array_push ($dataFull,$DashboardValues);
      }
  
      return $response->withJson($dataFull);
    });
});
