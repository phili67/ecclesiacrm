<?php

/*******************************************************************************
 *
 *  filename    : route/cartview.php
 *  last change : 2019-12-26
 *  description : manage the cartview
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWCartController;

$app->group('/cart', function (RouteCollectorProxy $group) {
    $group->get('/view', VIEWCartController::class . ':renderCarView');
    $group->post('/view', VIEWCartController::class . ':renderCarView');

    $group->get('/to/badge[/typeProblem/{flag:[0-9]+}]', VIEWCartController::class . ':renderCarToBadge');
    $group->post('/to/badge[/typeProblem/{flag:[0-9]+}]', VIEWCartController::class . ':renderCarToBadge');

    $group->get('/to/family', VIEWCartController::class . ':renderCarToFamily');
    $group->post('/to/family', VIEWCartController::class . ':renderCarToFamily');

});
