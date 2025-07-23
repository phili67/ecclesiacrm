<?php

/*******************************************************************************
 *
 *  filename    : route : system.php
 *  last change : 2019-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWSystemController;


$app->group('/system', function (RouteCollectorProxy $group) {
    $group->get('/integritycheck', VIEWSystemController::class . ':integritycheck' );
    $group->get('/infos', VIEWSystemController::class . ':infos' );

    $group->get('/report/list', VIEWSystemController::class . ':reportList' );
    $group->post('/report/list', VIEWSystemController::class . ':reportList' );

    $group->get('/option/manager/{mode}[/{ListID:[0-9]+}]', VIEWSystemController::class . ':optionManager' );
    $group->post('/option/manager/{mode}[/{ListID:[0-9]+}]', VIEWSystemController::class . ':optionManager' );

    /*
    * @! CanvassEditor
    * #! param: ref->string :: True/False (all optional)
    */
    $group->get('/convert/individual/address[/{all}]', VIEWSystemController::class . ':convertIndividualToAddress' );
    $group->post('/convert/individual/address[/{all}]', VIEWSystemController::class . ':convertIndividualToAddress' );

    /*
    * @! CSVExport
    * #! param: ref->string :: Source -> cart
    */
    $group->get('/csv/export[/{Source}]', VIEWSystemController::class . ':csvExport' );
    $group->post('/csv/export[/{Source}]', VIEWSystemController::class . ':csvExport' );


    /*
    * @! CSVExport
    * #! param: ref->string :: Source -> cart
    */
    $group->get('/event/attendance/{Action}/{Event:[0-9]+}/{Type}[/{Choice}]', VIEWSystemController::class . ':eventAttendance' );
    $group->post('/event/attendance/{Action}/{Event:[0-9]+}/{Type}[/{Choice}]', VIEWSystemController::class . ':eventAttendance' );   
    
    /*
    * @! email debug
    */
    $group->get('/email/debug', VIEWSystemController::class . ':renderEMailDebug');    
});


