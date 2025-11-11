<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWVolunteerOpportunityEditorController;

$app->group('/volunteeropportunityeditor', function (RouteCollectorProxy $group) {
    $group->get('', VIEWVolunteerOpportunityEditorController::class . ':renderVolunteerOpportunityEditor' );
    $group->get('/', VIEWVolunteerOpportunityEditorController::class . ':renderVolunteerOpportunityEditor' );
});
