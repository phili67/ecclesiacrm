<?php

use Slim\Routing\RouteCollectorProxy;

Use EcclesiaCRM\VIEWControllers\VIEWSystemSettingsController;

$app->group('/systemsettings', function (RouteCollectorProxy $group) {
    $group->get('', VIEWSystemSettingsController::class . ':renderSettings');
    $group->get('/', VIEWSystemSettingsController::class . ':renderSettings');
    $group->post('', VIEWSystemSettingsController::class . ':renderSettings');
});
