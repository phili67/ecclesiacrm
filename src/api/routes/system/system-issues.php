<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Routes

$app->post('/issues', function (Request $request, Response $response, $args) {
    $input = json_decode($request->getBody());

    $SystemService = $this->get('SystemService');

    return $response->withJson($SystemService->reportIssue($input));
});
