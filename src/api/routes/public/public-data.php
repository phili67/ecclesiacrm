<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\data\Countries;
use EcclesiaCRM\data\States;


$app->group('/public/data', function (RouteCollectorProxy $group) {
    $group->get('/countries', 'getCountries' );
    $group->get('/countries/', 'getCountries' );
    $group->get('/countries/{countryCode}/states', 'getStates' );
    $group->get('/countries/{countryCode}/states/', 'getStates' );
});


function getCountries(Request $request, Response $response, array $args ) {
    return $response->withJson(Countries::getAll());
}

function getStates(Request $request, Response $response, array $args ) {
    $states = new States($args['countryCode']);
    return $response->withJson($states->getAll());
}
