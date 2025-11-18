<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWVolunteerOpportunityController;

$app->group('/volunteeropportunity', function (RouteCollectorProxy $group) {
    $group->get('', VIEWVolunteerOpportunityController::class . ':renderVolunteerOpportunity' );
    $group->get('/', VIEWVolunteerOpportunityController::class . ':renderVolunteerOpportunity' );

    $group->get('/{volunteerID:[0-9]+}/view', VIEWVolunteerOpportunityController::class . ':volunteerView' );
    $group->post('/{volunteerID:[0-9]+}/view', VIEWVolunteerOpportunityController::class . ':volunteerView' );
});
