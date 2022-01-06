<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPropertyTypeListController;

$app->group('/propertytypelist', function (RouteCollectorProxy $group) {
    $group->get('', VIEWPropertyTypeListController::class . ':renderPropertyTypeList');
    $group->get('/', VIEWPropertyTypeListController::class . ':renderPropertyTypeList');
});
