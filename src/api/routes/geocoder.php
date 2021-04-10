<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\GeocoderController;

$app->group('/geocoder', function (RouteCollectorProxy $group) {
    $group->post('/address', GeocoderController::class . ':getGeoLocals' );
    $group->post('/address/', GeocoderController::class . ':getGeoLocals' );
});
