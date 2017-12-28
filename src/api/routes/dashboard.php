<?php

use Slim\Http\Request;
use Slim\Http\Response;
use EcclesiaCRM\Service\NewDashboardService;
use EcclesiaCRM\Service\SystemService;

$app->group('/dashboard', function () {
   $this->get('/page', 'getDashboard');
});

function getDashboard(Request $request, Response $response, array $p_args ) {
  $dataFull = [];
  if (SystemService::getSessionTimeout() < 10) {
	  $dataTimeout = ['timeOut' => 1,'availableTime' =>  SystemService::getSessionTimeout()];
	} else {
		$dataTimeout = ['timeOut' => 0,'availableTime' =>  SystemService::getSessionTimeout()];
	}
	
  array_push ($dataFull,$dataTimeout);

  if (SystemService::getSessionTimeout() > 0) {
      $pageName = $request->getQueryParam("currentpagename","");
      $DashboardValues = NewDashboardService::getValues($pageName);
      array_push ($dataFull,$DashboardValues);
  } 
  
  return $response->withJson($dataFull);
}