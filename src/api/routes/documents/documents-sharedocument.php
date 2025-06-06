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
     * @! get all shared persons for a noteID (unusefull)
     * #! param: ref->int :: noteId
     */
    $group->post('/getallperson', DocumentShareController::class . ':getAllShareForPerson' );
    /*
     * @! get all shared persons for all the selected rows (sabre)
     * #! param: ref->array :: rows
     */
    $group->post('/getallpersonsabre', DocumentShareController::class . ':getAllShareForPersonSabre' );
    /*
     * @! share a note to a personID from currentPersonID (deprecated)
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addperson', DocumentShareController::class . ':addPersonToShare' );
    /*
     * @! share a note to a personID from currentPersonID for sabre
     * #! param: ref->int :: personID
     * #! param: ref->int :: currentPersonID
     * #! param: ref->array :: all the rows
     */
    $group->post('/addpersonsabre', DocumentShareController::class . ':addPersonSabreToShare' );    
    /*
     * @! share a note to a familyID from currentPersonID 
     * #! param: ref->int :: familyID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addfamily', DocumentShareController::class . ':addFamilyToShare' );
    /*
     * @! share a note to a groupID from currentPersonID (deprecated)
     * #! param: ref->int :: groupID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addgroup', DocumentShareController::class . ':addGroupToShare' );
    /*
     * @! remove a personID from a share note (deprecated)
     * #! param: ref->int :: personID
     * #! param: ref->array :: rows
     */
    $group->post('/deleteperson', DocumentShareController::class . ':deletePersonFromShare' );
    /*
     * @! remove a personID from a share note (deprecated)
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     */
    $group->post('/deletepersonsabre', DocumentShareController::class . ':deletePersonSabreFromShare' );

    /*
     * @! set right access to a note (deprecated)
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: rightAccess
     */
    $group->post('/setrights', DocumentShareController::class . ':setRightsForPerson' );
    /*
     * @! set right access to a note (sabre)
     * #! param: ref->string :: currentPersonID : principal/admin
     * #! param: ref->int :: personID
     * #! param: ref->array :: rows (the lines)
     * #! param: ref->int :: rightAccess
     */
    $group->post('/setrightssabre', DocumentShareController::class . ':setRightsSabreForPerson' );
    /*
     * @! delete a note
     * #! param: ref->int :: noteId
     */
    $group->post('/cleardocument', DocumentShareController::class . ':clearDocument' );

});
