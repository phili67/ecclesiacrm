<?php

use Slim\Routing\RouteCollectorProxy;
use EcclesiaCRM\VIEWControllers\VIEWUserController;

$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('', VIEWUserController::class . ':renderUserList' );
    $group->get('/', VIEWUserController::class . ':renderUserList' );
    
    $group->get('/settings', VIEWUserController::class . ':renderUserSettings' );
    $group->post('/settings', VIEWUserController::class . ':renderUserSettings' );
});
