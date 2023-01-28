<?php

// Users APIs
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\UserUsersController;

$app->group('/users', function (RouteCollectorProxy $group) {

    /*
    * @! Reset password to random one
    */
    $group->post('/{userId:[0-9]+}/password/reset', UserUsersController::class . ':passwordReset' );
    /*
    * @! Apply role ID to user ID
    * param: ref->int :: userID
    * param: ref->int :: roleID
    */
    $group->post('/applyrole' , UserUsersController::class . ':applyRole' );
    /*
    * @! Get webdav Key for user ID
    * param: ref->int :: userID
    */
    $group->post('/webdavKey' , UserUsersController::class . ':webDavKey' );
    /*
    * @! Take account control (admin)
    * param: ref->int :: userID
    */
    $group->post('/controlAccount', UserUsersController::class . ':controlAccount' );
    /*
    * @! Exit account control (admin)
    * param: ref->int :: userID
    */
    $group->post('/exitControlAccount', UserUsersController::class . ':exitControlAccount' );
    /*
    * @! Lock/unlock account (admin)
    * param: ref->int :: userID
    */
    $group->post('/lockunlock', UserUsersController::class . ':lockUnlock' );
    /*
    * @! Show since (every user)
    * param: ref->string :: date
    */
    $group->post('/showsince', UserUsersController::class . ':showSince' );
    /*
    * @! Show to (every user)
    * param: ref->string :: date
    */
    $group->post('/showto', UserUsersController::class . ':showTo' );
    /*
    * @! Reset login count to setFailedLogins(0) (Admin)
    * param: ref->int :: userId
    */
    $group->post('/{userId:[0-9]+}/login/reset', UserUsersController::class . ':loginReset' );
    /*
    * @! Delete user account (Admin)
    * param: ref->int :: userId
    */
    $group->delete('/{userId:[0-9]+}', UserUsersController::class . ':deleteUser' );
    /*
    * @! Remove 2FA code (Admin)
    * param: ref->int :: userID
    */
    $group->post('/2fa/remove', UserUsersController::class . ':userstwofaremove' );
    /*
    * @! pending 2FA code (Admin)
    * param: ref->int :: userID
    */
    $group->post('/2fa/pending', UserUsersController::class . ':userstwofapending' );

});
