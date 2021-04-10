<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\TimerJobsController;

$app->group('/timerjobs', function (RouteCollectorProxy $group) {

    $group->post('/run', TimerJobsController::class . ':runTimerJobs' );

});
