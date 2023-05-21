<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWGroupController;

$app->group('/group', function (RouteCollectorProxy $group) {
    $group->get('/list', VIEWGroupController::class . ':groupList' );
    $group->get('/{groupID:[0-9]+}/view', VIEWGroupController::class . ':groupView' );
    $group->get('/{groupId:[0-9]+}/badge/{useCart:[0-9]+}/{type}', VIEWGroupController::class . ':groupBadge' );

    $group->get('/editor/{groupId:[0-9]+}', VIEWGroupController::class . ':groupEdit' );
});
