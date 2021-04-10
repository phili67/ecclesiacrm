<?php
// Copyright 2021 Philippe Logel all right reserved
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinanceDonationFundController;

$app->group('/donationfunds', function (RouteCollectorProxy $group) {

    $group->post('/', FinanceDonationFundController::class . ':getAllDonationFunds' );
    $group->post('/edit', FinanceDonationFundController::class . ':editDonationFund' );
    $group->post('/set', FinanceDonationFundController::class . ':setDonationFund' );
    $group->post('/delete', FinanceDonationFundController::class . ':deleteDonationFund' );
    $group->post('/create', FinanceDonationFundController::class . ':createDonationFund' );

});
