<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemBackupRestoreController;

// Routes

$app->group('/database', function (RouteCollectorProxy $group) {

    /*
    * @! backup crm (admin)
    * #! param: ref->int :: iArchiveType
    */
    $group->post('/backup', SystemBackupRestoreController::class . ':backup');
    /*
    * @! backup remotely to webdav server crm (admin)
    * #! param: ref->int :: iRemote
    * #! param: ref->int :: iArchiveType
    * #! param: ref->bool :: bEncryptBackup,
    * #! param: ref->string :: password
    */
    $group->post('/backupRemote', SystemBackupRestoreController::class . ':backupRemote');
    /*
    * @! Restore by file name and with post file (admin)
    * #! param: ref->string :: restoreFile
    */
    $group->post('/restore', SystemBackupRestoreController::class . ':restore' );
    /*
    * @! Download update (admin)
    * #! param: ref->string :: filename
    */
    $group->get('/download/{filename}', SystemBackupRestoreController::class . ':download' );
    /*
    * @! Clear all people from the database (admin)
    * #! param: ref->string :: filename
    */
    $group->delete('/people/clear', SystemBackupRestoreController::class . ':clearPeopleTables' );

});
