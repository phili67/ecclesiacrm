<?php

// Routes

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinanceDepositController;

$app->group('/deposits', function (RouteCollectorProxy $group) {

    /*
     * @! create a deposit type
     * #! param: ref->string :: depositType
     * #! param: ref->string :: depositComment
     * #! param: ref->string :: depositDate
     */
    $group->post('', FinanceDepositController::class . ':createDeposit' );
    /*
     * @! get All the deposits if you're a financial
     */
    $group->get('', FinanceDepositController::class . ':getAllDeposits' );
    /*
     * @! get information about one deposit
     * #! param: ref->int :: id (deposit id)
     */
    $group->get('/{id:[0-9]+}', FinanceDepositController::class . ':getOneDeposit' );
    /*
     * @! modify a deposit
     * #! param: ref->int :: id (deposit id)
     * #! param: ref->string :: depositType
     * #! param: ref->string :: depositComment
     * #! param: ref->string :: depositDate
     * #! param: ref->bool :: depositClosed
     */
    $group->post('/{id:[0-9]+}', FinanceDepositController::class . ':modifyOneDeposit' );
    /*
     * @! create an OFX deposit export
     * #! param: ref->int :: id (deposit id)
     */
    $group->get('/{id:[0-9]+}/ofx', FinanceDepositController::class . ':createDepositOFX' );
    /*
     * @! create a pdf deposit export
     * #! param: ref->int :: id (deposit id)
     */
    $group->get('/{id:[0-9]+}/pdf', FinanceDepositController::class . ':createDepositPDF' );
    /*
     * @! create a CSV deposit export
     * #! param: ref->int :: id (deposit id)
     */
    $group->get('/{id:[0-9]+}/csv', FinanceDepositController::class . ':createDepositCSV' );
    /*
     * @! delete deposit
     * #! param: ref->int :: id (deposit id)
     */
    $group->delete('/{id:[0-9]+}', FinanceDepositController::class . ':deleteDeposit' );
    /*
     * @! get all the pledges associated to the deposit
     * #! param: ref->int :: id (deposit id)
     */
    $group->get('/{id:[0-9]+}/pledges', FinanceDepositController::class . ':getAllPledgesForDeposit' );

});


