<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWGroupController;

$app->group('/group', function (RouteCollectorProxy $group) {
    $group->get('/list', VIEWGroupController::class . ':groupList' );
    $group->get('/{groupID:[0-9]+}/view', VIEWGroupController::class . ':groupView' );
    $group->get('/{groupId:[0-9]+}/badge/{useCart:[0-9]+}/{type}', VIEWGroupController::class . ':groupBadge' );

    $group->get('/editor/{groupId:[0-9]+}', VIEWGroupController::class . ':groupEdit' );

    $group->get('/reports', VIEWGroupController::class . ':groupReport' );
    $group->post('/reports', VIEWGroupController::class . ':groupReport' );

    $group->get('/props/editor/{GroupID:[0-9]+}/{PersonID:[0-9]+}', VIEWGroupController::class . ':renderGroupPropsEditor');
    $group->post('/props/editor/{GroupID:[0-9]+}/{PersonID:[0-9]+}', VIEWGroupController::class . ':renderGroupPropsEditor');
    
    $group->get('/props/Form/editor/{GroupID:[0-9]+}', VIEWGroupController::class . ':renderGroupPropsFormEditor');
    $group->post('/props/Form/editor/{GroupID:[0-9]+}', VIEWGroupController::class . ':renderGroupPropsFormEditor');
    
});
