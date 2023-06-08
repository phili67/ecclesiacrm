<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWFundraiserController;

$app->group('/fundraiser', function (RouteCollectorProxy $group) {
    
    $group->get('/donatedItemEditor/{donatedItemID:[0-9]+}/{CurrentFundraiser:[0-9]+}', VIEWFundraiserController::class . ':renderDonatedItemEditor');
    $group->get('/find', VIEWFundraiserController::class . ':renderFindFundRaiser');
    $group->get('/paddlenum/list/{CurrentFundraiser:[0-9]+}', VIEWFundraiserController::class . ':renderPaddleNumList');

    $group->get('/editor[/{FundRaiserID:[0-9]+}[/{linkback}]]', VIEWFundraiserController::class . ':renderFundraiserEditor');
    $group->post('/editor[/{FundRaiserID:[0-9]+}[/{linkback}]]', VIEWFundraiserController::class . ':renderFundraiserEditor');
    
});

