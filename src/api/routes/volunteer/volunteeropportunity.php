<?php
/*******************************************************************************
 *
 *  filename    : volunteeropportunity.php
 *  last change : 2018-07-11
 *  description : volunteer opportunities
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\VolunteerOpportunityController;

$app->group('/volunteeropportunity', function (RouteCollectorProxy $group) {

    /*
     * @! get all Volunteer Opportunities
     */
    $group->post('/', VolunteerOpportunityController::class . ':getAllVolunteerOpportunities');
    /*
     * @! delete volunteer opportunities by id
     * #! param: ref->int :: id
     */
    $group->post('/delete', VolunteerOpportunityController::class . ':deleteVolunteerOpportunity');
    /*
     * @! create volunteer oppportunities
     * #! param: ref->string :: Name
     * #! param: ref->string :: desc
     * #! param: ref->bool :: state
     */
    $group->post('/create', VolunteerOpportunityController::class . ':createVolunteerOpportunity');
    /*
     * @! set volunteer oppportunity by id
     * #! param: ref->int :: id
     * #! param: ref->string :: Name
     * #! param: ref->string :: desc
     * #! param: ref->bool :: state
     */
    $group->post('/set', VolunteerOpportunityController::class . ':setVolunteerOpportunity');
    /*
     * @! get volunteer oppportunity by id and return json
     * #! param: ref->int :: id
     */
    $group->post('/edit', VolunteerOpportunityController::class . ':editVolunteerOpportunity');
    /*
     * @! change parent's volunteer oppportunity
     * #! param: ref->int :: voldId
     * #! param: ref->int :: parentId
     */
    $group->post('/changeParent', VolunteerOpportunityController::class . ':changeParentVolunteerOpportunity');
    /*
     * @! change color of volunteer oppportunity
     * #! param: ref->int :: voldId
     * #! param: ref->int :: colId
     */
    $group->post('/changeColor', VolunteerOpportunityController::class . ':changeColorVolunteerOpportunity');
    /*
     * @! change icon of volunteer oppportunity
     * #! param: ref->int :: voldId
     * #! param: ref->int :: iconId
     */
    $group->post('/changeIcon', VolunteerOpportunityController::class . ':changeIconVolunteerOpportunity');


    /*
     * @! get all persons in the volunteeroportunity volId
     * #! param: ref->int :: volId     
     */
    $group->get('/{volunteerID:[0-9]+}/members', VolunteerOpportunityController::class . ':getMembers');
});
