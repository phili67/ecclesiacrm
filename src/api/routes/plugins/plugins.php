<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PluginsController;

$app->group('/plugins', function (RouteCollectorProxy $group) {

    $group->post('/activate', PluginsController::class . ':activate' );
    $group->post('/deactivate', PluginsController::class . ':deactivate' );
    $group->delete('/', PluginsController::class . ':remove' );
    $group->post('/add', PluginsController::class . ':add' );
    $group->post('/addDashboardPlaces', PluginsController::class . ':addDashboardPlaces' );
    $group->post('/removeFromDashboard', PluginsController::class . ':removeFromDashboard' );

});


