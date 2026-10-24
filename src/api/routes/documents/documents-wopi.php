<?php

//
//  WOPI host routes : only called by the Collabora Online server (see api/wopi-host.php)
//  There is no user session and no JWT, the WOPI access token is checked in each action.
//

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentWopiController;

$app->group('/wopi', function (RouteCollectorProxy $group) {

    /*
     * @! CheckFileInfo
     * #! param: ref->string :: id
     * #! param: ref->string :: access_token (query)
     */
    $group->get('/files/{id:[a-f0-9]{40}}', DocumentWopiController::class . ':checkFileInfo' );
    /*
     * @! RenameFile
     */
    $group->post('/files/{id:[a-f0-9]{40}}', DocumentWopiController::class . ':renameFile' );
    /*
     * @! GetFile
     */
    $group->get('/files/{id:[a-f0-9]{40}}/contents', DocumentWopiController::class . ':getFile' );
    /*
     * @! PutFile
     */
    $group->post('/files/{id:[a-f0-9]{40}}/contents', DocumentWopiController::class . ':putFile' );

});
