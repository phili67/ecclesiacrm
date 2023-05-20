<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWCalendarController;

$app->group('/calendar', function (RouteCollectorProxy $group) {
    $group->get('', VIEWCalendarController::class . ':renderCalendar');
    $group->get('/', VIEWCalendarController::class . ':renderCalendar');
    $group->get('/events/list', VIEWCalendarController::class . ':renderCalendarEventsList');
    $group->get('/events/Attendees/Edit', VIEWCalendarController::class . ':renderCalendarEventAttendeesEdit');
    $group->post('/events/Attendees/Edit', VIEWCalendarController::class . ':renderCalendarEventAttendeesEdit');

    $group->get('/events/checkin', VIEWCalendarController::class . ':renderCalendarEventCheckin');
    $group->post('/events/checkin', VIEWCalendarController::class . ':renderCalendarEventCheckin');
});
