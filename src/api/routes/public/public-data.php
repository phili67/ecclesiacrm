<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PublicDataController;

$app->group('/public/data', function (RouteCollectorProxy $group) {

    /*
     * @! get all countries
     */
    $group->get('/countries', PublicDataController::class . ':getCountries' );
    /*
     * @! get all countries
     */
    $group->get('/countries/', PublicDataController::class . ':getCountries' );
    /*
     * @! Get all States
     * #! param: ref->int :: countryCode
     */
    $group->get('/countries/{countryCode}/states', PublicDataController::class . ':getStates' );
    /*
     * @! Get all States
     * #! param: ref->int :: countryCode
     */
    $group->get('/countries/{countryCode}/states/', PublicDataController::class . ':getStates' );

});
