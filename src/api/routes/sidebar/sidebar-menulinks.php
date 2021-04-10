<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2018-07-11
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorizaion
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarMenuLinksController;

$app->group('/menulinks', function (RouteCollectorProxy $group) {

    $group->post('/{userId:[0-9]+}', SidebarMenuLinksController::class . ':getMenuLinksForUser');
    $group->post('/delete', SidebarMenuLinksController::class . ':deleteMenuLink');
    $group->post('/upaction', SidebarMenuLinksController::class . ':upMenuLink');
    $group->post('/downaction', SidebarMenuLinksController::class . ':downMenuLink');
    $group->post('/create', SidebarMenuLinksController::class . ':createMenuLink');
    $group->post('/set', SidebarMenuLinksController::class . ':setMenuLink');
    $group->post('/edit', SidebarMenuLinksController::class . ':editMenuLink');

});
