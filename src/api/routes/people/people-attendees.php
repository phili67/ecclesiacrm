<?php
// Copyright 2018 Philippe Logel all right reserved

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PeopleAttendeesController;

$app->group('/attendees', function (RouteCollectorProxy $group) {

    /*
     * @! Returns event attendees for eventID
     * #! param: ref->int :: eventID
     */
    $group->get('/event/{eventID:[0-9]+}', PeopleAttendeesController::class . ':attendeesEvent' );

    /*
     * @! checkin a person ID for event ID
     * #! param: ref->int :: personID
     * #! param: ref->int :: eventID
     * #! param: ref->bool :: checked
     */
    $group->post('/checkin', PeopleAttendeesController::class . ':attendeesCheckIn' );
    /*
     * @! checkout a person ID for event ID
     * #! param: ref->int :: personID
     * #! param: ref->int :: eventID
     * #! param: ref->bool :: checked
     */
    $group->post('/checkout', PeopleAttendeesController::class . ':attendeesCheckOut' );
    /*
     * @! Add attendees to current Event or create one with the student groupID + rangeInhours (for the predefined eventTypeID : ie time day)
     * #! param: ref->int :: eventTypeID
     * #! param: ref->int :: groupID
     * #! param: ref->string :: rangeInHours
     */
    $group->post('/student', PeopleAttendeesController::class . ':attendeesStudent' );
    /*
     * @! delete Attendee for person ID in event ID
     * #! param: ref->int :: eventID
     * #! param: ref->int :: personID
     */
    $group->post('/delete', PeopleAttendeesController::class . ':attendeesDelete' );
    /*
     * @! delete all Attendees for event ID
     * #! param: ref->int :: eventID
     */
    $group->post('/deleteAll', PeopleAttendeesController::class . ':attendeesDeleteAll' );
    /*
     * @! check all Attendees for event ID
     * #! param: ref->int :: eventID
     * #! param: ref->int :: type (1: checkin only, 2: checkin+checkout if $eventAttent->getCheckinDate() )
     * isset ($requestValues->eventID) && isset($requestValues->)
     */
    $group->post('/checkAll', PeopleAttendeesController::class . ':attendeesCheckAll' );
    /*
     * @! uncheck all Attendees for event ID
     * #! param: ref->int :: eventID
     * #! param: ref->int :: type (1: un-checkin only, 2: un-checkin+un-checkout)
     * isset ($requestValues->eventID) && isset($requestValues->)
     */
    $group->post('/uncheckAll', PeopleAttendeesController::class . ':attendeesUncheckAll' );
    /*
     * @! Add attendees all the sunday groups with eventTypeID + rangeInhours at dateTime (for the predefined eventTypeID : ie time day)
     * #! param: ref->int :: eventTypeID
     * #! param: ref->string :: dateTime
     * #! param: ref->string :: rangeInHours
     */
    $group->post('/groups', PeopleAttendeesController::class . ':attendeesGroups' );
    /*
     * @! remove a person ID attendee from event ID
     * #! param: ref->int :: personID
     * #! param: ref->string :: eventID
     */
    $group->post('/deletePerson', PeopleAttendeesController::class . ':deleteAttendeesPerson' );
    /*
     * @! Add a person ID attendee to event ID (with the two possibilities iChildID | iAdultID)
     * #! param: ref->int :: iChildID
     * #! param: ref->int :: iAdultID
     * #! param: ref->string :: eventID
     */
    $group->post('/addPerson', PeopleAttendeesController::class . ':addAttendeesPerson' );
    /*
     * @! validate the event to close it definitely
     * #! param: ref->int :: eventID
     * #! param: ref->string :: noteText
     */
    $group->post('/validate', PeopleAttendeesController::class . ':validateAttendees' );
    /*
     * @! validate with checkout the event to close it definitely
     * #! param: ref->int :: eventID
     */
    $group->post('/checkoutValidate', PeopleAttendeesController::class . ':checkoutValidateAttendees' );
    /*
     * @! add free attendees to the event
     * #! param: ref->int :: eventID
     * #! param: ref->string :: fieldText
     * #! param: ref->int :: counts
     */
    $group->post('/addFreeAttendees', PeopleAttendeesController::class . ':addFreeAttendees' );

    /*
     * @! checkin or checkout a person in group ID in reference of the current event ($_SESSION['EventID'] or if the event is create in the same day)
     * #! param: ref->int :: groupID
     * #! param: ref->string :: personID
     */
    $group->post('/qrcodeCall', PeopleAttendeesController::class . ':qrcodeCallAttendees' );

});




