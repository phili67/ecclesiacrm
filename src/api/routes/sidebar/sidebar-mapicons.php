<?php
// Copyright 2021 Philippe Logel all right reserved
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarMapIconsController;

$app->group('/mapicons', function (RouteCollectorProxy $group) {

    /*
     * @! get all map icons
     */
    $group->post('/getall', SidebarMapIconsController::class . ':getAllMapIcons' );
    /*
     * @! check only person view
     * #! param: ref->bool :: onlyPersonView
     * #! param: ref->int :: lstID
     * #! param: ref->int  :: lstOptionID
     */
    $group->post('/checkOnlyPersonView', SidebarMapIconsController::class . ':checkOnlyPersonView' );
    /*
     * @! set Icon By name
     * #! param: ref->str :: name
     * #! param: ref->int :: lstID
     * #! param: ref->int  :: lstOptionID
     */
    $group->post('/setIconName', SidebarMapIconsController::class . ':setIconName' );
    /*
     * @! remove icon
     * #! param: ref->int :: lstID
     * #! param: ref->int  :: lstOptionID
     */
    $group->post('/removeIcon', SidebarMapIconsController::class . ':removeIcon' );

});


