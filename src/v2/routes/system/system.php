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
});


