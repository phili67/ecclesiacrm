<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarGeneralRolesController;

$app->group('/generalrole', function (RouteCollectorProxy $group) {

    /*
     * @! get all general roles
     * #! param: ref->str :: mode 'famroles' 'classes' 'grptypes' 'grptypesSundSchool' 'famcustom' 'groupcustom' ('grproles' dead code)
     */
    $group->get('/all/{mode}', SidebarGeneralRolesController::class . ':getAllGeneralRoles' );// this is to finish !!! All in JS
    /*
     * @! set gerneral role for the family, classification, etc ...
     * #! param: ref->str :: mode 'famroles' 'classes' 'grptypes' 'grptypesSundSchool' 'famcustom' 'groupcustom' ('grproles' dead code)
     * #! param: ref->int :: Order
     * #! param: id->int  :: ID as id
     * #! param: res->str :: Action 'up' 'down'
     */
    $group->post('/action', SidebarGeneralRolesController::class . ':generalRoleAssign' );

});


