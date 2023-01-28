<?php

/* copyright 2018 Logel Philippe All right reserved */

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\UserRoleController;

$app->group('/userrole', function (RouteCollectorProxy $group) {

    /*
    * @! Add new role by name, global etc ...
    * param: ref->string :: name
    * param: ref->string :: global
    * param: ref->string :: userPerms,
    * param: ref->string :: userValues
    */
    $group->post('/add', UserRoleController::class . ':addUserRole' );
    /*
    * @! Get role by name, global etc ...
    * param: ref->int :: roleID
    */
    $group->post('/get', UserRoleController::class . ':getUserRole' );
    /*
    * @! Rename role id by name
    * param: ref->int :: roleID
    * param: ref->string :: name
    */
    $group->post('/rename', UserRoleController::class . ':renameUserRole' );
    /*
    * @! Get all user roles
    * param: ref->int :: roleID
    * param: ref->string :: name
    */
    $group->post('/getall', UserRoleController::class . ':getAllUserRoles' );
    /*
    * @! delete user role by id
    * param: ref->int :: roleID
    */
    $group->post('/delete', UserRoleController::class . ':deleteUserRole' );

});


