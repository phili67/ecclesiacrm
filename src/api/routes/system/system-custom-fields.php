<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemCustomFieldController;

$app->group('/system/custom-fields', function (RouteCollectorProxy $group) {

    $group->get('/person', SystemCustomFieldController::class . ':getPersonFieldsByType' );
    $group->get('/person/', SystemCustomFieldController::class . ':getPersonFieldsByType' );

});



