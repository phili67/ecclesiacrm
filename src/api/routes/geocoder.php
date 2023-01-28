<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\GeocoderController;

$app->group('/geocoder', function (RouteCollectorProxy $group) {

    /*
     * @! get address
     * #! param: ref->string :: address
     */
    $group->post('/address', GeocoderController::class . ':getGeoLocals' );
    /*
     * @! get address
     # #! param: ref->string :: address
     */
    $group->post('/address/', GeocoderController::class . ':getGeoLocals' );

});
