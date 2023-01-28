<?php
// Copyright 2021 Philippe Logel all right reserved
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FinanceDonationFundController;

$app->group('/donationfunds', function (RouteCollectorProxy $group) {

    /*
     * @! get all donation funds
     */
    $group->post('/', FinanceDonationFundController::class . ':getAllDonationFunds' );
    /*
     * @! get all infos of donation fund to edit a donation fund
     * #! param: ref->int :: fundId
     */
    $group->post('/edit', FinanceDonationFundController::class . ':editDonationFund' );
    /*
     * @! set donation fund informations
     * #! param: ref->int :: fundId
     * #! param: ref->string :: Name
     * #! param: ref->string :: Description
     * #! param: ref->bool :: Activ
     */
    $group->post('/set', FinanceDonationFundController::class . ':setDonationFund' );
    /*
     * @! remove donation fund by fundId
     * #! param: ref->int :: fundId
     */
    $group->post('/delete', FinanceDonationFundController::class . ':deleteDonationFund' );
    /*
     * @! create donation fund
     * #! param: ref->string :: Name
     * #! param: ref->string :: Description
     * #! param: ref->bool :: Activ
     */
    $group->post('/create', FinanceDonationFundController::class . ':createDonationFund' );

});
