<?php

/* Copyright Philippe Logel All right reserved */


use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinancePledgeController;

$app->group('/pledges', function (RouteCollectorProxy $group) {

    $group->post('/detail', FinancePledgeController::class . ':pledgeDetail' );
    $group->post('/family', FinancePledgeController::class . ':familyPledges' );
    $group->post('/delete', FinancePledgeController::class . ':deletePledge' );

});

