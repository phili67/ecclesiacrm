<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWCalendarController;

$app->group('/calendar', function (RouteCollectorProxy $group) {
    $group->get('', VIEWCalendarController::class . ':renderCalendar');
    $group->get('/', VIEWCalendarController::class . ':renderCalendar');
    $group->get('/events/list', VIEWCalendarController::class . ':renderCalendarEventsList');
});
