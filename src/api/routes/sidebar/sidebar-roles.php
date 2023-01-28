<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarRolesController;

$app->group('/roles', function (RouteCollectorProxy $group) {

    /*
     * @! get all roles
     */
    $group->get('/all', SidebarRolesController::class . ':getAllRoles' );
    /*
     * @! get all roles
     * #! param: ref->string :: Description
     */
    $group->post('/persons/assign', SidebarRolesController::class . ':rolePersonAssign' );

});
