<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarSystemSettingsController;

$app->group('/systemsettings', function (RouteCollectorProxy $group) {

    /*
     * @! save system settings
     * #! param: ref->array  :: new_value
     * #! param: ref->array  :: type
     */
    $group->post('/saveSettings', SidebarSystemSettingsController::class . ':saveSettings' );

});
