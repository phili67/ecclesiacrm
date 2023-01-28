<?php

/******************************************************************************
*
*  filename    : api/routes/sharedocument.php
*  last change : Copyright all right reserved 2021/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentShareController;

// Routes sharedocument
$app->group('/sharedocument', function (RouteCollectorProxy $group) {


    /*
     * @! get all shared persons for a noteID
     * #! param: ref->int :: noteId
     */
    $group->post('/getallperson', DocumentShareController::class . ':getAllShareForPerson' );
    /*
     * @! share a note to a personID from currentPersonID
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addperson', DocumentShareController::class . ':addPersonToShare' );
    /*
     * @! share a note to a familyID from currentPersonID
     * #! param: ref->int :: familyID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addfamily', DocumentShareController::class . ':addFamilyToShare' );
    /*
     * @! share a note to a groupID from currentPersonID
     * #! param: ref->int :: groupID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addgroup', DocumentShareController::class . ':addGroupToShare' );
    /*
     * @! remove a personID from a share note
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     */
    $group->post('/deleteperson', DocumentShareController::class . ':deletePersonFromShare' );
    /*
     * @! set right access to a note
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: rightAccess
     */
    $group->post('/setrights', DocumentShareController::class . ':setRightsForPerson' );
    /*
     * @! delete a note
     * #! param: ref->int :: noteId
     */
    $group->post('/cleardocument', DocumentShareController::class . ':clearDocument' );

});
