<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentFileManagerController;

$app->group('/filemanager', function (RouteCollectorProxy $group) {

    $group->post('/{personID:[0-9]+}', DocumentFileManagerController::class . ':getAllFileNoteForPerson' );
    $group->get('/getFile/{personID:[0-9]+}/[{path:.*}]', DocumentFileManagerController::class . ':getRealFile' );
    $group->post('/getPreview', DocumentFileManagerController::class . ':getPreview' );
    $group->post('/changeFolder', DocumentFileManagerController::class . ':changeFolder' );
    $group->post('/folderBack', DocumentFileManagerController::class . ':folderBack' );
    $group->post('/deleteOneFolder', DocumentFileManagerController::class . ':deleteOneFolder' );
    $group->post('/deleteOneFile', DocumentFileManagerController::class . ':deleteOneFile' );
    $group->post('/deleteFiles', DocumentFileManagerController::class . ':deleteFiles' );
    $group->post('/movefiles', DocumentFileManagerController::class . ':movefiles' );
    $group->post('/newFolder', DocumentFileManagerController::class . ':newFolder' );
    $group->post('/rename', DocumentFileManagerController::class . ':renameFile' );
    $group->post('/uploadFile/{personID:[0-9]+}', DocumentFileManagerController::class . ':uploadFile' );
    $group->post('/getRealLink', DocumentFileManagerController::class . ':getRealLink' );
    $group->post('/setpathtopublicfolder', DocumentFileManagerController::class . ':setpathtopublicfolder' );

});
