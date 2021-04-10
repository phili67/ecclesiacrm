<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentDocumentController;

$app->group('/document', function (RouteCollectorProxy $group) {

    $group->post('/create', DocumentDocumentController::class . ':createDocument' );
    $group->post('/get', DocumentDocumentController::class . ':getDocument' );
    $group->post('/update', DocumentDocumentController::class . ':updateDocument' );
    $group->post('/delete', DocumentDocumentController::class . ':deleteDocument' );
    $group->post('/leave', DocumentDocumentController::class . ':leaveDocument' );

});




