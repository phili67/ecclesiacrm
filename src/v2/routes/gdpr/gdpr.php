<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWGdprController;

$app->group('/gdpr', function (RouteCollectorProxy $group) {
    $group->get('', VIEWGdprController::class . ':renderGdprDashboard' );
    $group->get('/', VIEWGdprController::class . ':renderGdprDashboard' );
    $group->get('/gdprdatastructure', VIEWGdprController::class . ':renderGdprDataStructure' );
});
