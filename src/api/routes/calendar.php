<?php

use Slim\Http\Request;
use Slim\Http\Response;
use EcclesiaCRM\Service\CalendarService;


$app->group('/calendar', function () {

    $this->post('/getallevents', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        $calendarService = new CalendarService();
        return $response->withJson($calendarService->getEvents($params->start, $params->end));
    });
});