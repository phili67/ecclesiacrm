<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2022-01-06
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software without authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWFamilyListController;

$app->group('/familylist', function (RouteCollectorProxy $group) {
    $group->get('', VIEWFamilyListController::class . ':renderFamilyList' );
    $group->get('/', VIEWFamilyListController::class . ':renderFamilyList' );
    $group->get('/{mode}', VIEWFamilyListController::class . ':renderFamilyList' );
});
