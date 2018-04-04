<?php

// Routes

$app->group('/systemupgrade', function () {
    $this->get('/downloadlatestrelease', function () {
        $upgradeFile = $this->SystemService->downloadLatestRelease();
        echo json_encode($upgradeFile);
    });

    $this->post('/doupgrade', function ($request, $response, $args) {
        $input = (object) $request->getParsedBody();
        $upgradeResult = $this->SystemService->doUpgrade($input->fullPath, $input->sha1);
        echo json_encode($upgradeResult);
    });
    
    $this->post('/isUpdateRequired', function ($request, $response, $args) {
        if ($_SESSION['user']->isAdmin() && $_SESSION['isSoftwareUpdateTestPassed'] == false) {
          $isUpdateRequired = $_SESSION['latestVersion'] != null && $_SESSION['latestVersion']['name'] != $_SESSION['sSoftwareInstalledVersion'];
          $_SESSION['isSoftwareUpdateTestPassed'] = true;        
        } else {
          $isUpdateRequired = 0;
        }
        
        echo json_encode(["Upgrade" => $isUpdateRequired,"latestVersion" => $_SESSION['latestVersion'], "installedVersion" => $_SESSION['sSoftwareInstalledVersion']]);
    });
    
});
