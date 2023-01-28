<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarPropertiesController;


$app->group('/properties', function(RouteCollectorProxy $group) {

    /*
     * @! Assign property to a person
     * #! param: ref->int :: PersonId
     * #! param: ref->int :: PropertyId
     * #! param: ref->string :: PropertyValue
     */
    $group->post('/persons/assign', SidebarPropertiesController::class . ':propertiesPersonsAssign' );
    /*
     * @! Delete : un-assign property to a person
     * #! param: ref->int :: PersonId
     * #! param: ref->int :: PropertyId
     */
    $group->delete('/persons/unassign', SidebarPropertiesController::class . ':propertiesPersonsUnAssign' );

    /*
     * @! Assign property to a family
     * #! param: ref->int :: FamilyId
     * #! param: ref->int :: PropertyId
     * #! param: ref->string :: PropertyValue
     */
    $group->post('/families/assign', SidebarPropertiesController::class . ':propertiesFamiliesAssign' );
    /*
     * @! Delete : un-assign property to a family
     * #! param: ref->int :: FamilyId
     * #! param: ref->int :: PropertyId
     */
    $group->delete('/families/unassign', SidebarPropertiesController::class . ':propertiesFamiliesUnAssign' );

    /*
     * @! Assign property to a Group
     * #! param: ref->int :: GroupId
     * #! param: ref->int :: PropertyId
     * #! param: ref->string :: PropertyValue
     */
    $group->post('/groups/assign', SidebarPropertiesController::class . ':propertiesGroupsAssign' );
    /*
     * @! Delete : un-assign property to a group
     * #! param: ref->int :: GroupId
     * #! param: ref->int :: PropertyId
     */
    $group->delete('/groups/unassign', SidebarPropertiesController::class . ':propertiesGroupsUnAssign' );

    /*
     * @! get all propery types
     */
    $group->post('/propertytypelists', SidebarPropertiesController::class . ':getAllPropertyTypes' );
    /*
     * @! get all datas for a property type ID
     * #! param: ref->int :: typeId
     */
    $group->post('/propertytypelists/edit', SidebarPropertiesController::class . ':editPropertyType' );
    /*
     * @! set all datas for a property type ID
     * #! param: ref->int :: typeId
     * #! param: ref->string :: Name
     * #! param: ref->string :: Description
     */
    $group->post('/propertytypelists/set', SidebarPropertiesController::class . ':setPropertyType' );
    /*
     * @! create property type
     * #! param: ref->string :: Class
     * #! param: ref->string :: Name
     * #! param: ref->string :: Description
     */
    $group->post('/propertytypelists/create', SidebarPropertiesController::class . ':createPropertyType' );
    /*
     * @! delete property type
     * #! param: ref->id :: typeId
     */
    $group->post('/propertytypelists/delete', SidebarPropertiesController::class . ':deletePropertyType' );

    /*
     * @! get property datas for type Id
     * #! param: ref->id :: typeId
     */
    $group->post('/typelists/edit', SidebarPropertiesController::class . ':editProperty' );
    /*
     * @! get property datas for type Id
     * #! param: ref->int :: typeId
     * #! param: ref->string :: Name
     * #! param: ref->string :: Description
     * #! param: ref->string :: Prompt
     */
    $group->post('/typelists/set', SidebarPropertiesController::class . ':setProperty' );
    /*
     * @! delete property
     * #! param: ref->id :: typeId
     */
    $group->post('/typelists/delete', SidebarPropertiesController::class . ':deleteProperty' );
    /*
     * @! create property
     * #! param: ref->string :: Class
     * #! param: ref->string :: Name
     * #! param: ref->string :: Description
     * #! param: ref->string :: Prompt
     */
    $group->post('/typelists/create', SidebarPropertiesController::class . ':createProperty' );
    /*
     * @! get all properties
     */
    $group->post('/typelists/{type}', SidebarPropertiesController::class . ':getAllProperties' );

});
