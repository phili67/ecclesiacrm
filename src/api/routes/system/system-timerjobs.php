<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\TimerJobsController;

$app->group('/timerjobs', function (RouteCollectorProxy $group) {

    /*
     * @! get all running timer jobs
     */
    $group->post('/run', TimerJobsController::class . ':runTimerJobs' );

});
