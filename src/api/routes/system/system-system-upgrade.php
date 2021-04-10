<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemUpgradeController;

$app->group('/systemupgrade', function (RouteCollectorProxy $group) {

    $group->get('/downloadlatestrelease', SystemUpgradeController::class . ':downloadlatestrelease' );
    $group->post('/doupgrade', SystemUpgradeController::class . ':doupgrade' );
    $group->post('/isUpdateRequired', SystemUpgradeController::class . ':isUpdateRequired' );

});


