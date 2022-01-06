<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-06-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPersonListController;

$app->group('/personlist', function (RouteCollectorProxy $group) {
    $group->get('', VIEWPersonListController::class . ':renderPersonList' );
    $group->get('/', VIEWPersonListController::class . ':renderPersonList' );
    $group->get('/{mode}', VIEWPersonListController::class . ':renderPersonList' );
});
