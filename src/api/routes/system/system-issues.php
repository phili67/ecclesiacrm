<?php

// Routes
use EcclesiaCRM\APIControllers\SystemIssueController;

    /*
    * @! Sending an issue (public)
    * #! param: ref->int :: iArchiveType
    */
    $app->post('/issues', SystemIssueController::class . ':issues' );
