<?php

// Routes
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinancePaymentController;

$app->group('/payments', function (RouteCollectorProxy $group) {

    $group->get('/{id:[0-9]+}', FinancePaymentController::class . ':getPayment' );

    $group->post('/', FinancePaymentController::class . ':getSubmitOrPayement' );

    $group->delete('/byGroupKey', FinancePaymentController::class . ':deletePaymentByGroupKey' );

    $group->post('/family', FinancePaymentController::class . ':getAllPayementsForFamily' );
    $group->post('/info', FinancePaymentController::class . ':getAutoPaymentInfo' );

    // this can be used only as an admin or in finance in pledgeEditor
    $group->post('/families', FinancePaymentController::class . ':getAllPayementsForFamilies' );
    $group->post('/delete', FinancePaymentController::class . ':deletePaymentForFamily' );
    $group->get('/delete/{authID:[0-9]+}', FinancePaymentController::class . ':deleteAutoPayment' );
    $group->post('/invalidate', FinancePaymentController::class . ':invalidatePledge' );
    $group->post('/validate', FinancePaymentController::class . ':validatePledge' );
    $group->post('/getchartsarrays', FinancePaymentController::class . ':getDepositSlipChartsArrays' );

});


