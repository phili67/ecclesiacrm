<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\KiosksController;

$app->group('/kiosks', function (RouteCollectorProxy $group) {

    /*
     * @! Get all Kiosk devices
     * #! param: ref->string :: address
     */
    $group->get('/', KiosksController::class . ':getKioskDevices' );
    /*
     * @! Allow a Kiosk registration
     */
    $group->post('/allowRegistration', KiosksController::class . ':allowDeviceRegistration' );
    /*
     * @! Reload kiosk for kioskId
     * #! param: ref->int :: kioskId
     */
    $group->post('/{kioskId:[0-9]+}/reloadKiosk', KiosksController::class . ':reloadKiosk' );
    /*
     * @! Identify Kiosk by id
     * #! param: ref->int :: kioskId
     */
    $group->post('/{kioskId:[0-9]+}/identifyKiosk', KiosksController::class . ':identifyKiosk' );
    /*
     * @! Accept Kiosk by id
     * #! param: ref->int :: kioskId
     */
    $group->post('/{kioskId:[0-9]+}/acceptKiosk', KiosksController::class . ':acceptKiosk' );
    /*
     * @! Set Kiosk assignement
     * #! param: ref->int :: kioskId
     */
    $group->post('/{kioskId:[0-9]+}/setAssignment', KiosksController::class . ':setKioskAssignment' );
    /*
     * @! Delete kiosk by id
     * #! param: ref->int :: kioskId
     */
    $group->delete('/{kioskId:[0-9]+}', KiosksController::class . ':deleteKiosk' );

});
