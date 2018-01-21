<?php

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\SystemService;

$app->group('/register', function () {
    $this->post('', function ($request, $response, $args) {
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
    });
});
