<?php

/*******************************************************************************
 *
 *  filename    : routes/restore.php
 *  last change : 2019-11-21
 *  description : manage the restore
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\BackupRestore\VIEWRestoreController;

$app->group('/restore', function (RouteCollectorProxy $group) {
    $group->get('', VIEWRestoreController::class . ':renderRestore');
    $group->get('/', VIEWRestoreController::class . ':renderRestore');
});
