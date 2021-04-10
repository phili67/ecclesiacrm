<?php

use Slim\Routing\RouteCollectorProxy;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EcclesiaCRM\APIControllers\SystemController;

$app->group('/system', function (RouteCollectorProxy $group) {

    $group->post('/csp-report', SystemController::class . ':cspReport' );
    $group->post('/deletefile', SystemController::class . ':deleteFile' );

});


