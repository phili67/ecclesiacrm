<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarPropertiesController;


$app->group('/properties', function(RouteCollectorProxy $group) {

    $group->post('/persons/assign', SidebarPropertiesController::class . ':propertiesPersonsAssign' );
    $group->delete('/persons/unassign', SidebarPropertiesController::class . ':propertiesPersonsUnAssign' );

    $group->post('/families/assign', SidebarPropertiesController::class . ':propertiesFamiliesAssign' );
    $group->delete('/families/unassign', SidebarPropertiesController::class . ':propertiesFamiliesUnAssign' );

    $group->post('/groups/assign', SidebarPropertiesController::class . ':propertiesGroupsAssign' );
    $group->delete('/groups/unassign', SidebarPropertiesController::class . ':propertiesGroupsUnAssign' );

    $group->post('/propertytypelists', SidebarPropertiesController::class . ':getAllPropertyTypes' );
    $group->post('/propertytypelists/edit', SidebarPropertiesController::class . ':editPropertyType' );
    $group->post('/propertytypelists/set', SidebarPropertiesController::class . ':setPropertyType' );
    $group->post('/propertytypelists/create', SidebarPropertiesController::class . ':createPropertyType' );
    $group->post('/propertytypelists/delete', SidebarPropertiesController::class . ':deletePropertyType' );

    $group->post('/typelists/edit', SidebarPropertiesController::class . ':editProperty' );
    $group->post('/typelists/set', SidebarPropertiesController::class . ':setProperty' );
    $group->post('/typelists/delete', SidebarPropertiesController::class . ':deleteProperty' );
    $group->post('/typelists/create', SidebarPropertiesController::class . ':createProperty' );
    $group->post('/typelists/{type}', SidebarPropertiesController::class . ':getAllProperties' );

});
