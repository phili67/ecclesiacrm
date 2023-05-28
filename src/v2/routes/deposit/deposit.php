<?php

/*******************************************************************************
 *
 *  filename    : route/deposit.php
 *  last change : 2023-05-22
 *  description : manage the deposits
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWDepositController;

$app->group('/deposit', function (RouteCollectorProxy $group) {
    $group->get('/slipeditor', VIEWDepositController::class . ':renderDepositSlipEditor');
    $group->post('/slipeditor', VIEWDepositController::class . ':renderDepositSlipEditor');

    $group->get('/slipeditor/{DepositSlipID:[0-9]+}', VIEWDepositController::class . ':renderDepositSlipEditor');
    $group->post('/slipeditor/{DepositSlipID:[0-9]+}', VIEWDepositController::class . ':renderDepositSlipEditor');
});