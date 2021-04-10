<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemBackupRestoreController;

// Routes

$app->group('/database', function (RouteCollectorProxy $group) {

    $group->post('/backup', SystemBackupRestoreController::class . ':backup');
    $group->post('/backupRemote', SystemBackupRestoreController::class . ':backupRemote');
    $group->post('/restore', SystemBackupRestoreController::class . ':restore' );
    $group->get('/download/{filename}', SystemBackupRestoreController::class . ':download' );
    $group->delete('/people/clear', SystemBackupRestoreController::class . ':clearPeopleTables' );

});
