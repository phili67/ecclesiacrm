<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2022-01-06
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPeopleController;

$app->group('/people', function (RouteCollectorProxy $group) {
    $group->get('/dashboard', VIEWPeopleController::class . ':peopleDashboard' );
    $group->get('/list/{mode}', VIEWPeopleController::class . ':peopleList' );
    $group->get('/list/{mode}/{gender}/{familyRole}/{classification}', VIEWPeopleController::class . ':peopleList' );

    $group->get('/person/editor[/{personId:[0-9]+}]', VIEWPeopleController::class . ':personEditor' );
    $group->post('/person/editor[/{personId:[0-9]+}]', VIEWPeopleController::class . ':personEditor' );

    $group->get('/person/editor/AddToFamily/{FamilyID:[0-9]+}', VIEWPeopleController::class . ':personEditor' );
    $group->post('/person/editor/AddToFamily/{FamilyID:[0-9]+}', VIEWPeopleController::class . ':personEditor' );


    $group->get('/person/view/{personId:[0-9]+}[/{mode}]', VIEWPeopleController::class . ':personview' );
    $group->get('/person/print/{personId:[0-9]+}', VIEWPeopleController::class . ':personPrint' );
    
    $group->get('/family/editor[/{famId:[0-9]+}]', VIEWPeopleController::class . ':familyEditor' );
    $group->post('/family/editor[/{famId:[0-9]+}]', VIEWPeopleController::class . ':familyEditor' );

    $group->get('/family/view/{famId:[0-9]+}', VIEWPeopleController::class . ':familyview' );

    $group->get('/UpdateAllLatLon', VIEWPeopleController::class . ':UpdateAllLatLon' );

    $group->get('/geopage', VIEWPeopleController::class . ':geopage' );
    $group->post('/geopage', VIEWPeopleController::class . ':geopage' );


    $group->get('/directory/report', VIEWPeopleController::class . ':directoryreport' );
    $group->post('/directory/report', VIEWPeopleController::class . ':directoryreport' );


    $group->get('/directory/report/{cartdir}', VIEWPeopleController::class . ':directoryreport' );
    $group->post('/directory/report/{cartdir}', VIEWPeopleController::class . ':directoryreport' );

    $group->get('/LettersAndLabels', VIEWPeopleController::class . ':lettersandlabels' );
    $group->post('/LettersAndLabels', VIEWPeopleController::class . ':lettersandlabels' );

    $group->get('/ReminderReport', VIEWPeopleController::class . ':reminderreport' );
    $group->post('/ReminderReport', VIEWPeopleController::class . ':reminderreport' );

    $group->get('/person/customfield/editor', VIEWPeopleController::class . ':personCustomFieldEditor' );
    $group->post('/person/customfield/editor', VIEWPeopleController::class . ':personCustomFieldEditor' );

    $group->get('/family/customfield/editor', VIEWPeopleController::class . ':familyCustomFieldEditor' );
    $group->post('/family/customfield/editor', VIEWPeopleController::class . ':familyCustomFieldEditor' );

    /*
    * @! CanvassEditor
    * #! param: ref->int :: FamilyID
    * #! param: ref->int :: FYID
    * #! param: ref->string :: linkBack 
    #
    # Important : the linkBack must be : v2-people-family-view-64 for v2/people/family/view/64
    */
    $group->get('/canvass/editor/{FamilyID}/{FYID}/{linkBack}[/{CanvassID}]', VIEWPeopleController::class . ':canvassEditor' );
    $group->post('/canvass/editor/{FamilyID}/{FYID}/{linkBack}[/{CanvassID}]', VIEWPeopleController::class . ':canvassEditor' );


    $group->get('/canvass/automation', VIEWPeopleController::class . ':canvassAutomation' );
    $group->post('/canvass/automation', VIEWPeopleController::class . ':canvassAutomation' );

});
