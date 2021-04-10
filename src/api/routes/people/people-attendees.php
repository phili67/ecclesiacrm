<?php
// Copyright 2018 Philippe Logel all right reserved

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PeopleAttendeesController;

$app->group('/attendees', function (RouteCollectorProxy $group) {

    $group->get('/event/{eventID:[0-9]+}', PeopleAttendeesController::class . ':attendeesEvent' );

    $group->post('/checkin', PeopleAttendeesController::class . ':attendeesCheckIn' );
    $group->post('/checkout', PeopleAttendeesController::class . ':attendeesCheckOut' );
    $group->post('/student', PeopleAttendeesController::class . ':attendeesStudent' );
    $group->post('/delete', PeopleAttendeesController::class . ':attendeesDelete' );
    $group->post('/deleteAll', PeopleAttendeesController::class . ':attendeesDeleteAll' );
    $group->post('/checkAll', PeopleAttendeesController::class . ':attendeesCheckAll' );
    $group->post('/uncheckAll', PeopleAttendeesController::class . ':attendeesUncheckAll' );
    $group->post('/groups', PeopleAttendeesController::class . ':attendeesGroups' );
    $group->post('/deletePerson', PeopleAttendeesController::class . ':deleteAttendeesPerson' );
    $group->post('/addPerson', PeopleAttendeesController::class . ':addAttendeesPerson' );
    $group->post('/validate', PeopleAttendeesController::class . ':validateAttendees' );
    $group->post('/addFreeAttendees', PeopleAttendeesController::class . ':addFreeAttendees' );

    $group->post('/qrcodeCall', PeopleAttendeesController::class . ':qrcodeCallAttendees' );

});




