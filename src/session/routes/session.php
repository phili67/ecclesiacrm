<?php

/*******************************************************************************
 *
 *  filename    : route/backup.php
 *  last change : 2019-11-21
 *  description : manage the backup
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/


use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWSessionController;

$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/login', VIEWSessionController::class . ':renderLogin');
    $group->post('/login', VIEWSessionController::class . ':renderLogin');
    
    $group->get('/Lock', VIEWSessionController::class . ':renderLoginLock');
    $group->post('/Lock', VIEWSessionController::class . ':renderLoginLock');
    
    $group->get('/login/username/{usr_name}', VIEWSessionController::class . ':renderLogin');
    $group->post('/login/username/{usr_name}', VIEWSessionController::class . ':renderLogin');
    
    $group->get('/login/timeout/{time}', VIEWSessionController::class . ':renderLogin');
    $group->post('/login/timeout/{time}', VIEWSessionController::class . ':renderLogin');
    

    $group->get('/logout', VIEWSessionController::class . ':renderLogout');
    $group->post('/logout', VIEWSessionController::class . ':renderLogout');
});
