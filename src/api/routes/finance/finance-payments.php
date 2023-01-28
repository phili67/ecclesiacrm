<?php

// Routes
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinancePaymentController;

$app->group('/payments', function (RouteCollectorProxy $group) {

    /*
     * @! get payment for Id as JSON
     * #! param: ref->int :: Id
     */
    $group->get('/{id:[0-9]+}', FinancePaymentController::class . ':getPayment' );
    /*
    * @! Get submit or Payment
    */
    $group->post('/', FinancePaymentController::class . ':getSubmitOrPayement' );


    /*
     * @! Delete Payment par GroupKey
     * #! param: ref->string :: Groupkey
     */
    $group->delete('/byGroupKey', FinancePaymentController::class . ':deletePaymentByGroupKey' );


    /*
     * @! Get all payments for familyId
     * #! param: ref->int :: famId
     */
    $group->post('/family', FinancePaymentController::class . ':getAllPayementsForFamily' );
    /*
     * @! Get auto payment for the author ID
     * #! param: ref->int :: autID
     */
    $group->post('/info', FinancePaymentController::class . ':getAutoPaymentInfo' );

    // this can be used only as an admin or in finance in pledgeEditor
    /*
     * @! Get all payments for a family
     * #! param: ref->int :: famId
     */
    $group->post('/families', FinancePaymentController::class . ':getAllPayementsForFamilies' );
    /*
     * @! Delete paymentId for Family
     * #! param: ref->int :: famId
     * #! param: ref->int :: paymentId
     */
    $group->post('/delete', FinancePaymentController::class . ':deletePaymentForFamily' );
    /*
     * @! Delete auto payment
     * #! param: ref->int :: authID
     */
    $group->get('/delete/{authID:[0-9]+}', FinancePaymentController::class . ':deleteAutoPayment' );
    /*
     * @! Invalidate Pledge by Id
     * #! param: ref->int :: Id
     */
    $group->post('/invalidate', FinancePaymentController::class . ':invalidatePledge' );
    /*
     * @! Validate Pledge by Id
     * #! param: ref->int :: Id
     */
    $group->post('/validate', FinancePaymentController::class . ':validatePledge' );
    /*
     * @! Get depositSlip Charts in the View
     * #! param: ref->int :: depositSlipID
     */
    $group->post('/getchartsarrays', FinancePaymentController::class . ':getDepositSlipChartsArrays' );

});


