<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarSystemSettingsController;

$app->group('/systemsettings', function (RouteCollectorProxy $group) {

    /*
     * @! save system settings
     * 
     */
    $group->post('/saveSettings', SidebarSystemSettingsController::class . ':saveSettings' );

});
