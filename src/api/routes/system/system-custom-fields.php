<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\PersonCustomMasterQuery;

$app->group('/system/custom-fields', function (RouteCollectorProxy $group) {
    $group->get('/person', 'getPersonFieldsByType' );
    $group->get('/person/', 'getPersonFieldsByType' );
});


/**
 * A method that does the work to handle getting an existing person custom fields by type.
 *
 * @param \Slim\Http\Request $p_request   The request.
 * @param \Slim\Http\Response $p_response The response.
 * @param array $p_args Arguments
 * @return \Slim\Http\Response The augmented response.
 */
function getPersonFieldsByType(Request $request, Response $response, array $p_args ) {
    $params = $request->getQueryParams();
    $typeId = $params['typeId'];

    $fields = PersonCustomMasterQuery::create()->filterByTypeId($typeId)->find();

    $keyValue = [];

    foreach ($fields as $field) {
        array_push($keyValue, ["id" => $field->getId() , "value" =>  $field->getCustomName()]);
    }

    return $response->withJson($keyValue);
}
