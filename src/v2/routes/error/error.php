<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWErrorController;

$app->group('/error', function (RouteCollectorProxy $group) {
    $group->get('/404/{method}/{uri}', VIEWErrorController::class . ':render404Error');
});
