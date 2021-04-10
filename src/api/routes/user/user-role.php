<?php

/* copyright 2018 Logel Philippe All right reserved */

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\UserRoleController;

$app->group('/userrole', function (RouteCollectorProxy $group) {

    $group->post('/add', UserRoleController::class . ':addUserRole' );
    $group->post('/get', UserRoleController::class . ':getUserRole' );
    $group->post('/rename', UserRoleController::class . ':renameUserRole' );
    $group->post('/getall', UserRoleController::class . ':getAllUserRoles' );
    $group->post('/delete', UserRoleController::class . ':deleteUserRole' );

});


