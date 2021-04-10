<?php
// Copyright 2018 Philippe Logel all right reserved
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemGDRPController;

$app->group('/gdrp', function (RouteCollectorProxy $group) {

    $group->post('/', SystemGDRPController::class . ':getAllGdprNotes' );
    $group->post('/setComment', SystemGDRPController::class . ':setGdprComment' );
    $group->post('/removeperson', SystemGDRPController::class . ':removePersonGdpr' );
    $group->post('/removeallpersons', SystemGDRPController::class . ':removeAllPersonsGdpr' );
    $group->post('/removefamily', SystemGDRPController::class . ':removeFamilyGdpr' );
    $group->post('/removeallfamilies', SystemGDRPController::class . ':removeAllFamiliesGdpr' );

});


