<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemUpgradeController;

$app->group('/systemupgrade', function (RouteCollectorProxy $group) {

    /*
     * @! Download latest release
     */
    $group->get('/downloadlatestrelease', SystemUpgradeController::class . ':downloadlatestrelease' );
    /*
     * @! Do upgrade system to latest
     * #! param: ref->string :: fullPath
     * #! param: ref->string :: sha1
     */
    $group->post('/doupgrade', SystemUpgradeController::class . ':doupgrade' );
    /*
     * @! Test if update is required : return
     * "Upgrade" => $isUpdateRequired,"latestVersion" => $_SESSION['latestVersion'], "installedVersion" => $_SESSION['sSoftwareInstalledVersion']
     */
    $group->post('/isUpdateRequired', SystemUpgradeController::class . ':isUpdateRequired' );

});


