<?php

use Slim\Routing\RouteCollectorProxy;


$app->group('/timerjobs', function (RouteCollectorProxy $group) {
    $group->post('/run', function ($request, $response, $args) {
        $SystemService = $this->get('SystemService');
        if (!is_null($SystemService)) {
            $SystemService->runTimerJobs();
        }

        return $response->withJson(['status' => 'success']);
    });
});
