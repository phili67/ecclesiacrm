<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SessionUser;

class PublicRegisterController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerEcclesiaCRM(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

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

        return $response->withJson(['status' => 'success']);
    }

    public function systemregister(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (SessionUser::getUser()->isAdmin() && $_SESSION['isSoftwareRegisterTestPassed'] == false) {
            $isRegisterRequired = !SystemConfig::getBooleanValue('bRegistered');
            $_SESSION['isSoftwareRegisterTestPassed'] = true;
        } else {
            $isRegisterRequired = 0;
        }

        return $response->withJson(["Register" => $isRegisterRequired]);
    }

    public function getRegistredDatas(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $domainName = $_SERVER['HTTP_HOST'] . str_replace('api/register/getRegistredDatas', '', $_SERVER['REQUEST_URI']);
        $EcclesiaCRMURL = $protocol . $domainName;

        $sEmailMessage = gettext("Write a message Here !");

        return $response->withJson(
            ['ChurchName' => SystemConfig::getValue('sChurchName'),
                'InstalledVersion' => SystemService::getInstalledVersion(),
                'ChurchAddress' => SystemConfig::getValue('sChurchAddress'),
                'ChurchCity' => SystemConfig::getValue('sChurchCity'),
                'ChurchState' => SystemConfig::getValue('sChurchState'),
                'ChurchZip' => SystemConfig::getValue('sChurchZip'),
                'ChurchCountry' => SystemConfig::getValue('sChurchCountry'),
                'ChurchEmail' => SystemConfig::getValue('sChurchEmail'),
                'EcclesiaCRMURL' => $EcclesiaCRMURL,
                'EmailMessage' => htmlspecialchars($sEmailMessage)
            ]);
    }
}
