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
 *                This code can't be incoprorated in another software without any authorizaion
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarVolunteerOpportunityController;

$app->group('/volunteeropportunity', function (RouteCollectorProxy $group) {

    /*
     * @! get all Volunteer Opportunities
     */
    $group->post('/', SidebarVolunteerOpportunityController::class . ':getAllVolunteerOpportunities');
    /*
     * @! delete volunteer opportunities by id
     * #! param: ref->int :: id
     */
    $group->post('/delete', SidebarVolunteerOpportunityController::class . ':deleteVolunteerOpportunity');
    /*
     * @! create volunteer oppportunities
     * #! param: ref->string :: Name
     * #! param: ref->string :: desc
     * #! param: ref->bool :: state
     */
    $group->post('/create', SidebarVolunteerOpportunityController::class . ':createVolunteerOpportunity');
    /*
     * @! set volunteer oppportunity by id
     * #! param: ref->int :: id
     * #! param: ref->string :: Name
     * #! param: ref->string :: desc
     * #! param: ref->bool :: state
     */
    $group->post('/set', SidebarVolunteerOpportunityController::class . ':setVolunteerOpportunity');
    /*
     * @! get volunteer oppportunity by id and return json
     * #! param: ref->int :: id
     */
    $group->post('/edit', SidebarVolunteerOpportunityController::class . ':editVolunteerOpportunity');
    /*
     * @! change parent's volunteer oppportunity
     * #! param: ref->int :: voldId
     * #! param: ref->int :: parentId
     */
    $group->post('/changeParent', SidebarVolunteerOpportunityController::class . ':changeParentVolunteerOpportunity');
    /*
     * @! change color of volunteer oppportunity
     * #! param: ref->int :: voldId
     * #! param: ref->int :: colId
     */
    $group->post('/changeColor', SidebarVolunteerOpportunityController::class . ':changeColorVolunteerOpportunity');
    /*
     * @! change icon of volunteer oppportunity
     * #! param: ref->int :: voldId
     * #! param: ref->int :: iconId
     */
    $group->post('/changeIcon', SidebarVolunteerOpportunityController::class . ':changeIconVolunteerOpportunity');

});
