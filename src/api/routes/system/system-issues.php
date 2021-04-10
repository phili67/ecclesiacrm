<?php

// Routes
use EcclesiaCRM\APIControllers\SystemIssueController;

$app->post('/issues', SystemIssueController::class . ':issues' );
