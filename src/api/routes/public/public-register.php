<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SessionUser;

$app->group('/register', function (RouteCollectorProxy $group) {
    $group->post('', 'registerEcclesiaCRM' );
    $group->post('/isRegisterRequired', 'systemregister');
    $group->post('/getRegistredDatas', 'getRegistredDatas');
});

function registerEcclesiaCRM (Request $request, Response $response, array $args) {
    $input = (object) $request->getParsedBody();

    $headers = [];
    $headers[] = 'Content-type: application/json';

    $registrationData = new \stdClass();
    $registrationData->sName = SystemConfig::getValue('sChurchName');
    $registrationData->sAddress = SystemConfig::getValue('sChurchAddress');
    $registrationData->sCity = SystemConfig::getValue('sChurchCity');
    $registrationData->sState = SystemConfig::getValue('sChurchState');
    $registrationData->sZip = SystemConfig::getValue('sChurchZip');
    $registrationData->sCountry = SystemConfig::getValue('sChurchCountry');
    $registrationData->sEmail = SystemConfig::getValue('sChurchEmail');
    $registrationData->EcclesiaCRMURL = $input->EcclesiaCRMURL;
    $registrationData->Version = SystemService::getInstalledVersion();

    $registrationData->sComments = $input->emailmessage;
    $curlService = curl_init('https://www.ecclesiacrm.com/register.php');

    curl_setopt($curlService, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curlService, CURLOPT_POST, true);
    curl_setopt($curlService, CURLOPT_POSTFIELDS, json_encode($registrationData));
    curl_setopt($curlService, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlService, CURLOPT_CONNECTTIMEOUT, 1);

    $result = curl_exec($curlService);
    if ($result === false) {
        throw new \Exception('Unable to reach the registration server', 500);
    }

    // =Turn off the registration flag so the menu option is less obtrusive
    SystemConfig::setValue('bRegistered', '1');

    return $response->withJson(['status'=>'success']);
}

function systemregister (Request $request, Response $response, array $args) {
  if (SessionUser::getUser()->isAdmin() && $_SESSION['isSoftwareRegisterTestPassed'] == false) {
    $isRegisterRequired = !SystemConfig::getBooleanValue('bRegistered');
    $_SESSION['isSoftwareRegisterTestPassed'] = true;
  } else {
    $isRegisterRequired = 0;
  }

  return $response->withJson(["Register" => $isRegisterRequired]);
}

function getRegistredDatas (Request $request, Response $response, array $args) {
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
  $domainName = $_SERVER['HTTP_HOST'].str_replace('api/register/getRegistredDatas', '', $_SERVER['REQUEST_URI']);
  $EcclesiaCRMURL = $protocol.$domainName;

  return $response->withJson(
    ['ChurchName'       => SystemConfig::getValue('sChurchName'),
     'InstalledVersion' => SystemService::getInstalledVersion(),
     'ChurchAddress'    => SystemConfig::getValue('sChurchAddress'),
     'ChurchCity'       => SystemConfig::getValue('sChurchCity'),
     'ChurchState'      => SystemConfig::getValue('sChurchState'),
     'ChurchZip'        => SystemConfig::getValue('sChurchZip'),
     'ChurchCountry'    => SystemConfig::getValue('sChurchCountry'),
     'ChurchEmail'      => SystemConfig::getValue('sChurchEmail'),
     'EcclesiaCRMURL'   => $EcclesiaCRMURL,
     'EmailMessage'     => htmlspecialchars($sEmailMessage)]);
}

