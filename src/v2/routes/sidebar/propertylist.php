<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPropertyListController;

$app->group('/propertylist', function (RouteCollectorProxy $group) {
    $group->get('/{type}', VIEWPropertyListController::class . ':renderPropertyList');
});
