<?php

$app->group('/timerjobs', function () {
    $this->post('/run', function () {
      if (!empty($this->SystemService)) {
        $this->SystemService->runTimerJobs();
      }
    });
});
