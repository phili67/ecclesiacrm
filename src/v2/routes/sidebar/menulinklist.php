<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWMenuLinkListController;

$app->group('/menulinklist', function (RouteCollectorProxy $group) {
    $group->get('', VIEWMenuLinkListController::class . ':renderMenuLinkList');
    $group->get('/{personId:[0-9]+}', VIEWMenuLinkListController::class . ':renderMenuLinkListForPerson');
});
