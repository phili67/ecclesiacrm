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

    $group->get('/find', VIEWDepositController::class . ':renderDepositFind');

    $group->get('/manage/envelopes', VIEWDepositController::class . ':renderManageEnvelopes');
    $group->post('/manage/envelopes', VIEWDepositController::class . ':renderManageEnvelopes');

    $group->get('/financial/reports', VIEWDepositController::class . ':renderFinancialReports');

    $group->get('/financial/reports/NoRows/{ReportType}[/{year:[0-9]+}]', VIEWDepositController::class . ':renderFinancialReportsNoRows');

    $group->post('/financial/reports', VIEWDepositController::class . ':renderFinancialReports');

    $group->get('/tax/report', VIEWDepositController::class . ':renderTaxReport');
    $group->post('/tax/report', VIEWDepositController::class . ':renderTaxReport');

    $group->get('/electronic/payment/list', VIEWDepositController::class . ':renderElectronicPaymentList');
    
    $group->get('/auto/payment/clear/Account/{customerid:[0-9]+}', VIEWDepositController::class . ':renderAutoPaymentClearAccount');

    /*
    * @! AutoPaymentEditor
    * #! param: ref->int :: AuthID
    * #! param: ref->int :: FamilyID
    * #! param: ref->string :: linkBack 
    #
    # Important : the linkBack must be : v2-people-family-view-64 for v2/people/family/view/64
    */
    $group->get('/autopayment/editor/{AutID}/{FamilyID}/{linkBack}', VIEWDepositController::class . ':renderAutoPaymentEditor');
    $group->post('/autopayment/editor/{AutID}/{FamilyID}/{linkBack}', VIEWDepositController::class . ':renderAutoPaymentEditor');
});