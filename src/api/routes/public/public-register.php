<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PublicRegisterController;

$app->group('/register', function (RouteCollectorProxy $group) {

    /*
     * @! register EcclesiaCRM (Admin role)
     * #! param: ref->string :: EcclesiaCRMURL
     * #! param: ref->string :: emailmessage
     */
    $group->post('', PublicRegisterController::class . ':registerEcclesiaCRM' );
    /*
     * @! Test if the software is registred
     */
    $group->post('/isRegisterRequired', PublicRegisterController::class . ':systemregister');
    /*
     * @! Get registred datas if the software is registred (ChurchName, InstalledVersion...)
     */
    $group->post('/getRegistredDatas', PublicRegisterController::class . ':getRegistredDatas');

});
