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
     * #! param: ref->int :: currentPersonID
     * #! param: ref->array :: rows
     */
    $group->post('/getallpersonsabre', DocumentShareController::class . ':getAllShareForPersonSabre' );
    /*
     * @! share a note to a personID from currentPersonID 
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addperson', DocumentShareController::class . ':addPersonToShare' );
    /*
     * @! share a note to a personID from currentPersonID for sabre
     * #! param: ref->int :: currentPersonID
     * #! param: ref->int :: personToShareID
     * #! param: ref->array :: rows (all the rows)
     * #! param: ref->string :: access
     * #! param: ref->bool :: notification
     */
    $group->post('/addpersonsabre', DocumentShareController::class . ':addPersonSabreToShare' );    
    /*
     * @! share a file(s) to a familyID from currentPersonID 
     * #! param: ref->int :: currentPersonID
     * #! param: ref->array :: noteId
     * #! param: ref->int :: personToShareID
     * #! param: ref->bool :: notification
     * #! param: ref->string :: access
     */
    $group->post('/addfamily', DocumentShareController::class . ':addFamilyToShare' );
    /*
     * @! share a file(s) to a familyID for currentPersonID 
     * #! param: ref->int :: currentPersonID
     * #! param: ref->int :: familyToShareID
     * #! param: ref->array :: rows
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addfamilysabre', DocumentShareController::class . ':addFamilyToShareSabre' );
    /*
     * @! share a note to a groupID from currentPersonID 
     * #! param: ref->int :: groupID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addgroup', DocumentShareController::class . ':addGroupToShare' );
    /*
     * @! share a note to a groupID from currentPersonID 
     * #! param: ref->int :: currentPersonID
     * #! param: ref->int :: groupToShareID
     * #! param: ref->array :: rows
     * #! param: ref->int :: currentPersonID
     * #! param: ref->bool :: notification
     */
    $group->post('/addgroupsabre', DocumentShareController::class . ':addGroupToShareSabre' );
    /*
     * @! remove a personID from a share note 
     * #! param: ref->int :: personID
     * #! param: ref->array :: rows
     */
    $group->post('/deleteperson', DocumentShareController::class . ':deletePersonFromShare' );
    /*
     * @! remove a personID from a share note 
     * #! param: ref->string :: personPrincipal
     * #! param: ref->array :: rows
     * #! param: ref->int :: currentPersonID
     */
    $group->post('/deletepersonsabre', DocumentShareController::class . ':deletePersonSabreFromShare' );

    /*
     * @! set right access to a note 
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

    /*
     * @! cleardocument
     * #! param: ref->int :: personID
     * #! param: ref->int :: noteId
     * #! param: ref->int :: rightAccess
     
     */
    $group->post('/cleardocumentsabre', DocumentShareController::class . ':cleardocumentsabre' );

    /*
     * @! get all shared persons for all the selected rows (sabre)
     * #! param: ref->int :: currentPersonID
     * #! param: ref->array :: rows
     */
    $group->post('/getShareInfosSabre', DocumentShareController::class . ':getShareInfosSabre' );    
});
