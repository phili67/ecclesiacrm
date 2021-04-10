<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemSynchronizeController;

$app->group('/synchronize', function (RouteCollectorProxy $group) {

/*
 * @! Returns the dashboard items in function of the current page name : for CRMJsom.js
 * #! param: page->string :: current page name
 */
    $group->get('/page', SystemSynchronizeController::class . ':synchronize' );

});


