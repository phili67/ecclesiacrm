<?php

use Slim\Routing\RouteCollectorProxy;

Use EcclesiaCRM\VIEWControllers\VIEWFundListController;

$app->group('/fundlist', function (RouteCollectorProxy $group) {
    $group->get('', VIEWFundListController::class . ':renderFundList');
    $group->get('/', VIEWFundListController::class . ':renderFundList');
});
