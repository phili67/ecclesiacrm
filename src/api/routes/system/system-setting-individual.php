<?php

use Slim\Routing\RouteCollectorProxy;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EcclesiaCRM\APIControllers\SystemSettingsIndividualController;

$app->group('/settingsindividual', function (RouteCollectorProxy $group) {

    $group->post('/get2FA', SystemSettingsIndividualController::class . ':get2FA' );
    $group->post('/verify2FA', SystemSettingsIndividualController::class . ':verify2FA' );
    $group->post('/remove2FA', SystemSettingsIndividualController::class . ':remove2FA' );

});


