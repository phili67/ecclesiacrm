<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPastoralcareListController;

$app->group('/pastoralcarelist', function (RouteCollectorProxy $group) {
    $group->get('', VIEWPastoralcareListController::class . ':renderPastoralCareList' );
    $group->get('/', VIEWPastoralcareListController::class . ':renderPastoralCareList' );
});
