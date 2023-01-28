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

    /*
     * @! create a document
     * #! param: ref->int :: personID
     * #! param: ref->int :: famID
     * #! param: ref->string :: type
     * #! param: ref->string :: text
     * #! param: ref->bool :: bPrivate
     */
    $group->post('/create', DocumentDocumentController::class . ':createDocument' );
    /*
     * @! get a document
     * #! param: ref->int :: docID
     * #! param: ref->int :: personID
     * #! param: ref->int :: famID
     */
    $group->post('/get', DocumentDocumentController::class . ':getDocument' );
    /*
     * @! update a document
     * #! param: ref->int :: docID
     * #! param: ref->string :: title
     * #! param: ref->string :: type
     * #! param: ref->string :: text
     * #! param: ref->bool :: bPrivate
     */
    $group->post('/update', DocumentDocumentController::class . ':updateDocument' );
    /*
     * @! delete a document
     * #! param: ref->int :: docID
     */
    $group->post('/delete', DocumentDocumentController::class . ':deleteDocument' );
    /*
     * @! leave a document (in case of a share document)
     * #! param: ref->int :: docID
     */
    $group->post('/leave', DocumentDocumentController::class . ':leaveDocument' );

});




