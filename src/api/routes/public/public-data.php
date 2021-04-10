<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PublicDataController;

$app->group('/public/data', function (RouteCollectorProxy $group) {
    $group->get('/countries', PublicDataController::class . ':getCountries' );
    $group->get('/countries/', PublicDataController::class . ':getCountries' );
    $group->get('/countries/{countryCode}/states', PublicDataController::class . ':getStates' );
    $group->get('/countries/{countryCode}/states/', PublicDataController::class . ':getStates' );
});
