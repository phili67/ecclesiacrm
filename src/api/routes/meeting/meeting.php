<?php

/*******************************************************************************
 *
 *  filename    : meeting.php
 *  last change : 2020-07-07
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2020 Philippe Logel all right reserved not MIT licence
 *                This code can't be include in another software
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

// Routes
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\MeetingController;


$app->group('/meeting', function (RouteCollectorProxy $group) {
    $group->get('/', MeetingController::class . ':getAllMettings');
    $group->get('/getLastMeeting', MeetingController::class . ':getLastMeeting');
    $group->post('/createMeetingRoom', MeetingController::class . ':createMeetingRoom');
    $group->post('/selectMeetingRoom', MeetingController::class . ':selectMeetingRoom');
    $group->delete('/deleteAllMeetingRooms', MeetingController::class . ':deleteAllMeetingRooms');
});
