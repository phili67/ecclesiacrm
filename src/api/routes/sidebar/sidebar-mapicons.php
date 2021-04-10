<?php
// Copyright 2021 Philippe Logel all right reserved
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarMapIconsController;

$app->group('/mapicons', function (RouteCollectorProxy $group) {

  $group->post('/getall', SidebarMapIconsController::class . ':getAllMapIcons' );
  $group->post('/checkOnlyPersonView', SidebarMapIconsController::class . ':checkOnlyPersonView' );
  $group->post('/setIconName', SidebarMapIconsController::class . ':setIconName' );
  $group->post('/removeIcon', SidebarMapIconsController::class . ':removeIcon' );

});


