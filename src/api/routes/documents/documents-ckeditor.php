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

    /*
     * @! get all templates
     * #! param: ref->personId :: int (in URL)
     */
    $group->get('/{personId:[0-9]+}/templates', DocumentCKEditorController::class . ':templates' );
    /*
     * @! get all templates
     * #! param: ref->id   :: personID
     */
    $group->post('/alltemplates', DocumentCKEditorController::class . ':alltemplates' );
    /*
     * @! delete template
     * #! param: ref->int :: templateID
     */
    $group->post('/deletetemplate', DocumentCKEditorController::class . ':deleteTemplate' );
    /*
     * @! rename template
     * #! param: ref->int :: templateID
     * #! param: ref->string :: title
     * #! param: ref->string :: desc
     */
    $group->post('/renametemplate', DocumentCKEditorController::class . ':renametemplate' );
    /*
     * @! save template
     * #! param: ref->int :: personID
     * #! param: ref->string :: title
     * #! param: ref->string :: desc
     * #! param: ref->string :: text
     */
    $group->post('/savetemplate', DocumentCKEditorController::class . ':saveTemplate' );
    /*
     * @! save template as word file
     * #! param: ref->int :: personID
     * #! param: ref->string :: title
     * #! param: ref->string :: text
     */
    $group->post('/saveAsWordFile', DocumentCKEditorController::class . ':saveAsWordFile' );

});




