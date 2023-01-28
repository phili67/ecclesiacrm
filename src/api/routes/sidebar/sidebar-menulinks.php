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

    /*
     * @! get Menu link for user $args['userId']
     */
    $group->post('/{userId:[0-9]+}', SidebarMenuLinksController::class . ':getMenuLinksForUser');
    /*
     * @! delete Menu link by id
     * #! param: ref->int :: MenuLinkId
     */
    $group->post('/delete', SidebarMenuLinksController::class . ':deleteMenuLink');
    /*
     * @! move Menu link by id up
     * #! param: ref->int :: PersonID
     * #! param: ref->int :: MenuLinkId
     * #! param: ref->int :: MenuPlace
     */
    $group->post('/upaction', SidebarMenuLinksController::class . ':upMenuLink');
    /*
     * @! move Menu link by id down
     * #! param: ref->int :: PersonID
     * #! param: ref->int :: MenuLinkId
     * #! param: ref->int :: MenuPlace
     */
    $group->post('/downaction', SidebarMenuLinksController::class . ':downMenuLink');
    /*
     * @! create Menu link
     * #! param: ref->int :: PersonID
     * #! param: ref->str :: Name
     * #! param: ref->str :: URI
     */
    $group->post('/create', SidebarMenuLinksController::class . ':createMenuLink');
    /*
     * @! set Menu link uri and name by MenuLinkId
     * #! param: ref->int :: MenuLinkId
     * #! param: ref->str :: Name
     * #! param: ref->str :: URI
     */
    $group->post('/set', SidebarMenuLinksController::class . ':setMenuLink');
    /*
     * @! get al Menu link infos for MenuLinkId to edit the menu Link
     * #! param: ref->int :: MenuLinkId
     */
    $group->post('/edit', SidebarMenuLinksController::class . ':editMenuLink');

});
