<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

// Routes

use EcclesiaCRM\SessionUser;

$app->group('/systemupgrade', function (RouteCollectorProxy $group) {
    $group->get('/downloadlatestrelease', function (Request $request, Response $response, array $args) {
        $SystemService = $this->get('SystemService');
        $upgradeFile = $SystemService->downloadLatestRelease();
        return $response->write(json_encode($upgradeFile));
    });

    $group->post('/doupgrade', function (Request $request, Response $response, $args) {
        $input = (object) $request->getParsedBody();

        $SystemService = $this->get('SystemService');
        $upgradeResult = $SystemService->doUpgrade($input->fullPath, $input->sha1);
        return $response->write(json_encode($upgradeResult));
    });

    $group->post('/isUpdateRequired', function (Request $request, Response $response, array $args) {
        if (SessionUser::getUser()->isAdmin() && $_SESSION['isSoftwareUpdateTestPassed'] == false) {
          $isUpdateRequired = $_SESSION['latestVersion'] != null && $_SESSION['latestVersion']['name'] != $_SESSION['sSoftwareInstalledVersion'];
          $_SESSION['isSoftwareUpdateTestPassed'] = true;
        } else {
          $isUpdateRequired = 0;
        }

        return $response->withJson(["Upgrade" => $isUpdateRequired,"latestVersion" => $_SESSION['latestVersion'], "installedVersion" => $_SESSION['sSoftwareInstalledVersion']]);
    });

});
