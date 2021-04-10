<?php

/******************************************************************************
*
*  filename    : api/routes/sharedocument.php
*  last change : Copyright all right reserved 2021/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\DocumentShareController;

// Routes sharedocument
$app->group('/sharedocument', function (RouteCollectorProxy $group) {

    $group->post('/getallperson', DocumentShareController::class . ':getAllShareForPerson' );
    $group->post('/addperson', DocumentShareController::class . ':addPersonToShare' );
    $group->post('/addfamily', DocumentShareController::class . ':addFamilyToShare' );
    $group->post('/addgroup', DocumentShareController::class . ':addGroupToShare' );
    $group->post('/deleteperson', DocumentShareController::class . ':deletePersonFromShare' );
    $group->post('/setrights', DocumentShareController::class . ':setRightsForPerson' );
    $group->post('/cleardocument', DocumentShareController::class . ':clearDocument' );

});
