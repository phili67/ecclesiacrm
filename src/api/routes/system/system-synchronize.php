<?php

use Slim\Http\Request;
use Slim\Http\Response;
use EcclesiaCRM\Service\Synchronize;

$app->group('/synchronize', function () {

/*
 * @! Returns the dashboard items in function of the current page name : for CRMJsom.js
 * #! param: page->string :: current page name
 */
    $this->get('/page', function ($request,$response,$args) {
      $dataFull = [];

      if ($this->SystemService->getSessionTimeout() < 10) {
        $dataTimeout = ['timeOut' => 1,'availableTime' =>  $this->SystemService->getSessionTimeout()];
      } else {
        $dataTimeout = ['timeOut' => 0,'availableTime' =>  $this->SystemService->getSessionTimeout()];
      }

      array_push ($dataFull,$dataTimeout);

      if ($this->SystemService->getSessionTimeout() > 0) {
          $pageName = $request->getQueryParam("currentpagename","");
          $DashboardValues = Synchronize::getValues($pageName);
          array_push ($dataFull,$DashboardValues);
      }

      return $response->withJson($dataFull);
    });
});
