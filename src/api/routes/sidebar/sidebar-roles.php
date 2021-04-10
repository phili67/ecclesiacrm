<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarRolesController;

$app->group('/roles', function (RouteCollectorProxy $group) {

    $group->get('/all', SidebarRolesController::class . ':getAllRoles' );
    $group->post('/persons/assign', SidebarRolesController::class . ':rolePersonAssign' );

});
