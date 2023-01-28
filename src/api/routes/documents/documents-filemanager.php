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

    /*
     * @! get All the files for personID user
     * #! param: ref->int :: personID
     */
    $group->post('/{personID:[0-9]+}', DocumentFileManagerController::class . ':getAllFileNoteForPerson' );
    /*
     * @! get real file
     * #! param: ref->int :: personID
     * #! param: ref->string :: path
     */
    $group->get('/getFile/{personID:[0-9]+}/[{path:.*}]', DocumentFileManagerController::class . ':getRealFile' );
    /*
     * @! get preview for file name
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     */
    $group->post('/getPreview', DocumentFileManagerController::class . ':getPreview' );
    /*
     * @! change to folder name for personID
     * #! param: ref->int :: personID
     * #! param: ref->string :: folder
     */
    $group->post('/changeFolder', DocumentFileManagerController::class . ':changeFolder' );
    /*
     * @! change to folder back
     * #! param: ref->int :: personID
     */
    $group->post('/folderBack', DocumentFileManagerController::class . ':folderBack' );
    /*
     * @! delete folder
     * #! param: ref->int :: personID
     * #! param: ref->string :: folder
     */
    $group->post('/deleteOneFolder', DocumentFileManagerController::class . ':deleteOneFolder' );
    /*
     * @! delete one file
     * #! param: ref->int :: personID
     * #! param: ref->string :: file
     */
    $group->post('/deleteOneFile', DocumentFileManagerController::class . ':deleteOneFile' );
    /*
     * @! delete files
     * #! param: ref->int :: personID
     * #! param: ref->string :: files
     */
    $group->post('/deleteFiles', DocumentFileManagerController::class . ':deleteFiles' );
    /*
     * @! move a file to another folder
     * #! param: ref->int :: personID
     * #! param: ref->string :: files
     * #! param: ref->string :: folder
     */
    $group->post('/movefiles', DocumentFileManagerController::class . ':movefiles' );
    /*
     * @! create new folder
     * #! param: ref->int :: personID
     * #! param: ref->string :: folder
     */
    $group->post('/newFolder', DocumentFileManagerController::class . ':newFolder' );
    /*
     * @! rename file
     * #! param: ref->int :: personID
     * #! param: ref->string :: oldName
     * #! param: ref->string :: newName
     * #! param: ref->string :: type
     */
    $group->post('/rename', DocumentFileManagerController::class . ':renameFile' );
    /*
     * @! upload file to current folder, everything is contained in $_FILES
     */
    $group->post('/uploadFile/{personID:[0-9]+}', DocumentFileManagerController::class . ':uploadFile' );
    /*
     * @! upload : get file to file path
     * #! param: ref->int :: personID
     * #! param: ref->string :: pathFile
     */
    $group->post('/getRealLink', DocumentFileManagerController::class . ':getRealLink' );
    /*
     * @! set current path to public folder
     */
    $group->post('/setpathtopublicfolder', DocumentFileManagerController::class . ':setpathtopublicfolder' );

});
