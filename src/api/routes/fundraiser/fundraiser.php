<?php

// Routes
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FundraiserController;

$app->group('/fundraiser', function (RouteCollectorProxy $group) {

    $group->post('/{FundRaiserID:[0-9]+}', FundraiserController::class . ':getAllFundraiserForID' );
    $group->post('/replicate', FundraiserController::class . ':replicateFundraiser' );

// donatedItem
    $group->post('/donatedItemSubmit', FundraiserController::class . ':donatedItemSubmitFundraiser' );
    $group->post('/donateditem/currentpicture', FundraiserController::class . ':donatedItemCurrentPicture' );
    $group->delete('/donateditem', FundraiserController::class . ':deleteDonatedItem' );
    $group->post('/donatedItem/submit/picture', FundraiserController::class . ':donatedItemSubmitPicture' );

    // FindFundRaiser.php
    $group->post('/findFundRaiser/{fundRaiserID:[0-9]+}/{startDate}/{endDate}', FundraiserController::class . ':findFundRaiser' );

// paddlenum
    $group->delete('/paddlenum', FundraiserController::class . ':deletePaddleNum' );
    $group->post('/paddlenum/list/{fundRaiserID:[0-9]+}', FundraiserController::class . ':getPaddleNumList' );
    $group->post('/paddlenum/add/donnors', FundraiserController::class . ':addDonnors' );

/*
 * @! Returns a list of all the persons who are in the cart
 */
    $group->get('/paddlenum/persons/all/{fundRaiserID:[0-9]+}', FundraiserController::class . ":getAllPersonsNum" );
/*
 * @! Returns a list of all the persons who are in the cart
 */
    $group->post('/paddlenum/add', FundraiserController::class . ':addPaddleNum' );

/*
 * @! Returns a list of all the persons who are in the cart
 */
    $group->post('/paddlenum/info', FundraiserController::class . ':paddleNumInfo' );

});


