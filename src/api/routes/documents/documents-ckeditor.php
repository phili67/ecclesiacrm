<?php
//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

// CKeditor APIs
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentCKEditorController;

$app->group('/ckeditor', function (RouteCollectorProxy $group) {

    $group->get('/{personId:[0-9]+}/templates', DocumentCKEditorController::class . ':templates' );
    $group->post('/alltemplates', DocumentCKEditorController::class . ':alltemplates' );
    $group->post('/deletetemplate', DocumentCKEditorController::class . ':deleteTemplate' );
    $group->post('/renametemplate', DocumentCKEditorController::class . ':renametemplate' );
    $group->post('/savetemplate', DocumentCKEditorController::class . ':saveTemplate' );
    $group->post('/saveAsWordFile', DocumentCKEditorController::class . ':saveAsWordFile' );

});




