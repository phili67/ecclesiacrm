<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWKioskManagerController;

$app->group('/kioskmanager', function (RouteCollectorProxy $group) {
    $group->get('', VIEWKioskManagerController::class . ':renderKioskManager');
    $group->get('/', VIEWKioskManagerController::class . ':renderKioskManager');
});
