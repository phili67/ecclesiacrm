<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemCustomFieldController;

$app->group('/system/custom-fields', function (RouteCollectorProxy $group) {

    /*
    * @! Get person field type (public)
    * #! param: ref->int :: typeId
    */
    $group->get('/person', SystemCustomFieldController::class . ':getPersonFieldsByType' );
    /*
    * @! Get person field type (public)
    * #! param: ref->int :: typeId
    */
    $group->get('/person/', SystemCustomFieldController::class . ':getPersonFieldsByType' );

});



