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

// in practice you would require the composer loader if it was not already part of your framework or project
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\APIControllers', '\\'), array(__DIR__.'/../core/APIControllers', '/'), $className) . '.php';
});

use Plugins\APIControllers\MeetingController;

$app->group('/meeting', function (RouteCollectorProxy $group) {

    $group->get('/', MeetingController::class . ':getAllMettings' );
    $group->get('/getLastMeeting', MeetingController::class . ':getLastMeeting' );
    $group->post('/createMeetingRoom', MeetingController::class . ':createMeetingRoom' );
    $group->post('/selectMeetingRoom', MeetingController::class . ':selectMeetingRoom' );
    $group->delete('/deleteAllMeetingRooms', MeetingController::class . ':deleteAllMeetingRooms' );
    $group->post('/changeSettings', MeetingController::class . ':changeSettings' );

});
