<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2022-01-06
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPeopleController;

$app->group('/people', function (RouteCollectorProxy $group) {
    $group->get('/dashboard', VIEWPeopleController::class . ':peopleDashboard' );
    $group->get('/list/{mode}', VIEWPeopleController::class . ':peopleList' );
    $group->get('/list/{mode}/{gender}/{familyRole}/{classification}', VIEWPeopleController::class . ':peopleList' );
});
