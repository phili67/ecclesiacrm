<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\KiosksController;

$app->group('/kiosks', function (RouteCollectorProxy $group) {

    $group->get('/', KiosksController::class . ':getKioskDevices' );
    $group->post('/allowRegistration', KiosksController::class . ':allowDeviceRegistration' );
    $group->post('/{kioskId:[0-9]+}/reloadKiosk', KiosksController::class . ':reloadKiosk' );
    $group->post('/{kioskId:[0-9]+}/identifyKiosk', KiosksController::class . ':identifyKiosk' );
    $group->post('/{kioskId:[0-9]+}/acceptKiosk', KiosksController::class . ':acceptKiosk' );
    $group->post('/{kioskId:[0-9]+}/setAssignment', KiosksController::class . ':setKioskAssignment' );
    $group->delete('/{kioskId:[0-9]+}', KiosksController::class . ':deleteKiosk' );

});
