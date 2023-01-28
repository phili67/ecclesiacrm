<?php

// Routes
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\FundraiserController;

$app->group('/fundraiser', function (RouteCollectorProxy $group) {

    /*
     * @! Get All fundraiser for FundRaiserID
     * #! param: ref->int :: FundRaiserID
     */
    $group->post('/{FundRaiserID:[0-9]+}', FundraiserController::class . ':getAllFundraiserForID' );
    /*
     * @! Duplicate fundraiser
     * #! param: ref->int :: DonatedItemID
     * #! param: ref->int :: count
     */
    $group->post('/replicate', FundraiserController::class . ':replicateFundraiser' );

// donatedItem
    /*
     * @! create or update DonateItem with params
     * #! param: ref->int :: currentFundraiser
     * #! param: ref->int :: currentDonatedItemID
     * #! param: ref->string :: Item
     * #! param: ref->int :: Multibuy
     * #! param: ref->int :: Donor
     * #! param: ref->string :: Title
     * #! param: ref->html :: Description
     * #! param: ref->float :: EstPrice
     * #! param: ref->float :: MaterialValue
     * #! param: ref->float :: MinimumPrice
     * #! param: ref->int :: Buyer
     * #! param: ref->float :: SellPrice
     * #! param: ref->string :: PictureURL
     */
    $group->post('/donatedItemSubmit', FundraiserController::class . ':donatedItemSubmitFundraiser' );
    /*
     * @! Return current url picture for the DonateItem ID
     * #! param: ref->int :: DonatedItemID
     */
    $group->post('/donateditem/currentpicture', FundraiserController::class . ':donatedItemCurrentPicture' );
    /*
     * @! Delete donatedItem with the params below
     * #! param: ref->int :: FundRaiserID
     * #! param: ref->int :: DonatedItemID
     */
    $group->delete('/donateditem', FundraiserController::class . ':deleteDonatedItem' );
    /*
     * @! Submit picture for the Donated Item Id
     * #! param: ref->int :: DonatedItemID
     * #! param: ref->string :: pathFile
     */
    $group->post('/donatedItem/submit/picture', FundraiserController::class . ':donatedItemSubmitPicture' );

    // FindFundRaiser.php
    /*
     * @! Find a fund raiser by Id and in range of dates
     * #! param: ref->int :: fundRaiserID
     * #! param: ref->string :: startDate
     * #! param: ref->string :: startDate
     */
    $group->post('/findFundRaiser/{fundRaiserID:[0-9]+}/{startDate}/{endDate}', FundraiserController::class . ':findFundRaiser' );

// paddlenum
    /*
     * @! delete PaddleNum
     * #! param: ref->int :: fundraiserID
     * #! param: ref->int :: pnID
     */
    $group->delete('/paddlenum', FundraiserController::class . ':deletePaddleNum' );
    /*
     * @! Get PaddleNum list by fundraiser ID
     * #! param: ref->int :: fundRaiserID
     */
    $group->post('/paddlenum/list/{fundRaiserID:[0-9]+}', FundraiserController::class . ':getPaddleNumList' );
    /*
     * @! Add all Donnors from the fundraiserID and create associated PaddleNums
     * #! param: ref->int :: fundraiserID
     */
    $group->post('/paddlenum/add/donnors', FundraiserController::class . ':addDonnors' );

    /*
     * @! Returns a list of all the persons who are in the cart
     * #! param: ref->int :: fundRaiserID
     */
    $group->get('/paddlenum/persons/all/{fundRaiserID:[0-9]+}', FundraiserController::class . ':getAllPersonsNum' );
    /*
     * @! Add PaddleNum
     * #! param: ref->int :: fundraiserID
     * #! param: ref->int :: PerID
     * #! param: ref->int :: PaddleNumID
     * #! param: ref->int :: Num
     */
    $group->post('/paddlenum/add', FundraiserController::class . ':addPaddleNum' );

/*
 * @! Get PaddleNum infos
 */
    /*
     * @! Returns a list of all the persons who are in the cart
     * #! param: ref->int :: fundraiserID
     * #! param: ref->int :: PerID
     * #! param: ref->int :: Num
     */
    $group->post('/paddlenum/info', FundraiserController::class . ':paddleNumInfo' );

});


