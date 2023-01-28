<?php

/* Copyright Philippe Logel All right reserved */


use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinancePledgeController;

$app->group('/pledges', function (RouteCollectorProxy $group) {

    /*
     * @! Get Pledge details by groupKey
     * #! param: ref->string :: groupKey
     */
    $group->post('/detail', FinancePledgeController::class . ':pledgeDetail' );
    /*
     * @! Get Family pledges by famId
     * #! param: ref->int :: famId
     */
    $group->post('/family', FinancePledgeController::class . ':familyPledges' );
    /*
     * @! Delete Pledge by payment ID
     * #! param: ref->int :: paymentId
     */
    $group->post('/delete', FinancePledgeController::class . ':deletePledge' );

});

