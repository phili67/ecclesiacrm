<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PublicRegisterController;

$app->group('/register', function (RouteCollectorProxy $group) {

    $group->post('', PublicRegisterController::class . ':registerEcclesiaCRM' );
    $group->post('/isRegisterRequired', PublicRegisterController::class . ':systemregister');
    $group->post('/getRegistredDatas', PublicRegisterController::class . ':getRegistredDatas');

});
