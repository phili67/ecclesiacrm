<?php

use Slim\Http\Request;
use Slim\Http\Response;
use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\data\Countries;
use EcclesiaCRM\data\States;


$app->group('/public/data', function () {
    $this->get('/countries', 'getCountries');
    $this->get('/countries/', 'getCountries');
    $this->get('/countries/{countryCode}/states', 'getStates');
    $this->get('/countries/{countryCode}/states/', 'getStates');
});


function getCountries(Request $request, Response $response, array $args ) {
    return $response->withJson(Countries::getAll());
}

function getStates(Request $request, Response $response, array $args ) {
    $states = new States($args['countryCode']);
    return $response->withJson($states->getAll());
}