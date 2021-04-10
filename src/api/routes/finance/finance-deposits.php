<?php

// Routes

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinanceDepositController;

$app->group('/deposits', function (RouteCollectorProxy $group) {

    $group->post('', FinanceDepositController::class . ':createDeposit');
    $group->get('', FinanceDepositController::class . ':getAllDeposits');
    $group->get('/{id:[0-9]+}', FinanceDepositController::class . ':getOneDeposit');
    $group->post('/{id:[0-9]+}', FinanceDepositController::class . ':modifyOneDeposit');
    $group->get('/{id:[0-9]+}/ofx', FinanceDepositController::class . ':createDepositOFX');
    $group->get('/{id:[0-9]+}/pdf', FinanceDepositController::class . ':createDepositPDF');
    $group->get('/{id:[0-9]+}/csv', FinanceDepositController::class . ':createDepositCSV');
    $group->delete('/{id:[0-9]+}', FinanceDepositController::class . ':deleteDeposit');
    $group->get('/{id:[0-9]+}/pledges', FinanceDepositController::class . ':getAllPledgesForDeposit');

});


