<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-03-23
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWPastoralCareController;

$app->group('/pastoralcare', function (RouteCollectorProxy $group) {
    $group->get('/person/{personId:[0-9]+}', VIEWPastoralCareController::class . ':renderPastoralCarePerson' );
    $group->get('/family/{familyId:[0-9]+}', VIEWPastoralCareController::class . ':renderPastoralCareFamily' );
    $group->get('/dashboard', VIEWPastoralCareController::class . ':renderPastoralCareDashboard' );
    $group->get('/membersList', VIEWPastoralCareController::class . ':renderPastoralCareMembersList' );
    $group->get('/listforuser/{UserID:[0-9]+}', VIEWPastoralCareController::class . ':renderPastoralCareListForUser' );
    $group->get('/person/print/{personId:[0-9]+}', VIEWPastoralCareController::class . ':renderPastoralCarePersonPrint' );
});
