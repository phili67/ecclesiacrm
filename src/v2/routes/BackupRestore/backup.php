<?php

/*******************************************************************************
 *
 *  filename    : route/backup.php
 *  last change : 2019-11-21
 *  description : manage the backup
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\BackupRestore\VIEWBackupController;

$app->group('/backup', function (RouteCollectorProxy $group) {
    $group->get('', VIEWBackupController::class . ':renderBackup');
    $group->get('/', VIEWBackupController::class . ':renderBackup');
});
