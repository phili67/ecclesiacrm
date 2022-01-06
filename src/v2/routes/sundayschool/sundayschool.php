<?php

/*******************************************************************************
 *
 *  filename    : route : sundayschool.php
 *  last change : 2019-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWSundaySchoolController;


$app->group('/sundayschool', function (RouteCollectorProxy $group) {
    $group->get('', VIEWSundaySchoolController::class . ':sundayschoolDashboard' );
    $group->get('/', VIEWSundaySchoolController::class . ':sundayschoolDashboard' );
    $group->get('/dashboard', VIEWSundaySchoolController::class . ':sundayschoolDashboard' );
    $group->get('/{groupId:[0-9]+}/view', VIEWSundaySchoolController::class . ':sundayschoolView' );
    $group->get('/reports', VIEWSundaySchoolController::class . ':sundayschoolReports' );
    $group->post('/reports', VIEWSundaySchoolController::class . ':sundayschoolReports' );
});


