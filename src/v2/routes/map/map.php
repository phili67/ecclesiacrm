<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWMapController;

$app->group('/map', function (RouteCollectorProxy $group) {
    $group->get('/{GroupID}', VIEWMapController::class . ':renderMap');
});
