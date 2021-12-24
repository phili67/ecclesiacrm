<?php

// Users APIs
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\UserUsersController;

$app->group('/users', function (RouteCollectorProxy $group) {

    $group->post('/{userId:[0-9]+}/password/reset', UserUsersController::class . ':passwordReset' );
    $group->post('/applyrole' , UserUsersController::class . ':applyRole' );
    $group->post('/webdavKey' , UserUsersController::class . ':webDavKey' );
    $group->post('/lockunlock', UserUsersController::class . ':lockUnlock' );
    $group->post('/showsince', UserUsersController::class . ':showSince' );
    $group->post('/showto', UserUsersController::class . ':showTo' );
    $group->post('/{userId:[0-9]+}/login/reset', UserUsersController::class . ':loginReset' );
    $group->delete('/{userId:[0-9]+}', UserUsersController::class . ':deleteUser' );
    $group->post('/2fa/remove', UserUsersController::class . ':userstwofaremove' );
    $group->post('/2fa/pending', UserUsersController::class . ':userstwofapending' );

});
