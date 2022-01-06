<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-03-23
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWMeetingController;

$app->group('/meeting', function (RouteCollectorProxy $group) {
    $group->get('/dashboard', VIEWMeetingController::class . ':renderMeetingDashboard');
});
