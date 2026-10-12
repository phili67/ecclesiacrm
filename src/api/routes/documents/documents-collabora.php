<?php

//
//  Online edition of the eDrive documents with Collabora Online
//

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentCollaboraController;

$app->group('/collabora', function (RouteCollectorProxy $group) {

    /*
     * @! get the Collabora editor url and the WOPI access token of a file of the current folder
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     */
    $group->post('/edit', DocumentCollaboraController::class . ':edit' );
    /*
     * @! create an empty document (docx, xlsx, pptx, odg) in the current folder
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     * #! param: ref->string :: type
     */
    $group->post('/create', DocumentCollaboraController::class . ':create' );
    /*
     * @! list the previous versions of a document of the current folder
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     */
    $group->post('/versions', DocumentCollaboraController::class . ':versions' );
    /*
     * @! download a previous version of a document
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     * #! param: ref->string :: version
     */
    $group->post('/versions/download', DocumentCollaboraController::class . ':downloadVersion' );
    /*
     * @! restore a previous version of a document (the current content is kept as a version)
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     * #! param: ref->string :: version
     */
    $group->post('/versions/restore', DocumentCollaboraController::class . ':restoreVersion' );
    /*
     * @! delete a previous version of a document
     * #! param: ref->int :: personID
     * #! param: ref->string :: name
     * #! param: ref->string :: version
     */
    $group->post('/versions/delete', DocumentCollaboraController::class . ':deleteVersion' );

});
