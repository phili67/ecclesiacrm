<?php

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SQLUtils;
use EcclesiaCRM\Version;

use Slim\Views\PhpRenderer;

$app->group('/', function () {
    $this->get('', function ($request, $response, $args) {
        $renderer = new PhpRenderer('templates/');

        return $renderer->render($response, 'setup-steps.php', ['sRootPath' => SystemURLs::getRootPath()]);
    });

    $this->get('SystemIntegrityCheck', function ($request, $response, $args) {
        $AppIntegrity = EcclesiaCRM\Service\AppIntegrityService::verifyApplicationIntegrity();
        echo $AppIntegrity['status'];
    });

    $this->get('SystemPrerequisiteCheck', function ($request, $response, $args) {
        $required = EcclesiaCRM\Service\AppIntegrityService::getApplicationPrerequisites();
        return $response->withStatus(200)->withJson($required);
    });

    $this->post('', function ($request, $response, $args) {
        $setupDate = $request->getParsedBody();
        $template = file_get_contents(SystemURLs::getDocumentRoot().'/Include/Config.php.example');

        $template = str_replace('||DB_SERVER_NAME||', $setupDate['DB_SERVER_NAME'], $template);
        $template = str_replace('||DB_NAME||', $setupDate['DB_NAME'], $template);
        $template = str_replace('||DB_USER||', $setupDate['DB_USER'], $template);
        $template = str_replace('||DB_PASSWORD||', $setupDate['DB_PASSWORD'], $template);
        $template = str_replace('||ROOT_PATH||', $setupDate['ROOT_PATH'], $template);
        $template = str_replace('||URL||', $setupDate['URL'], $template);
                
        
        file_put_contents(SystemURLs::getDocumentRoot().'/Include/Config.php', $template);

        // now we can install the CRM
        $connection = new mysqli($setupDate['DB_SERVER_NAME'], $setupDate['DB_USER'], $setupDate['DB_PASSWORD'], $setupDate['DB_NAME']);
        $connection->set_charset("utf8");
        
        $systemService = new SystemService();
        $version = SystemService::getInstalledVersion();
        
        $date = (new DateTime())->format('Y-m-d H:i:s');
        
        SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/mysql/install/Install.sql', $connection);
        
        // we install the language
        $filename = SystemURLs::getDocumentRoot().'/mysql/install/languages/'.$setupDate['sLanguage'].'.sql';

        if (file_exists($filename)) {
          SQLUtils::sqlImport($filename, $connection);
        }
        
        // now we install the version
        $sql = "INSERT INTO `version_ver` (`ver_version`, `ver_update_start`, `ver_update_end`) VALUES ('".$version."', '".$date."', '".$date."');";
        
        $connection->query($sql);
        
        // and the info for the church Administration
        $sql = "INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES 
(39,'sLanguage', '".$connection->real_escape_string($setupDate['sLanguage'])."'),
(2057, 'bGDPR', '".$connection->real_escape_string($setupDate['bGDPR'])."'),
(1003, 'sChurchName', '".$connection->real_escape_string($setupDate['sChurchName'])."'),
(1004, 'sChurchAddress', '".$connection->real_escape_string($setupDate['sChurchAddress'])."'),
(1005, 'sChurchCity', '".$connection->real_escape_string($setupDate['sChurchCity'])."'),
(1007, 'sChurchZip', '".$connection->real_escape_string($setupDate['sChurchZip'])."'),
(1008, 'sChurchPhone', '".$connection->real_escape_string($setupDate['sChurchPhone'])."'),
(1009, 'sChurchEmail', '".$connection->real_escape_string($setupDate['sChurchEmail'])."'),"
//(1006, 'sChurchState', '".$connection->real_escape_string($setupDate['sChurchState'])."'),
."(1047, 'sChurchCountry', '".$connection->real_escape_string($setupDate['sChurchCountry'])."'),
(1025, 'sConfirmSigner', '".$connection->real_escape_string($setupDate['sConfirmSigner'])."'),
(2013, 'sChurchWebSite', '".$connection->real_escape_string($setupDate['sChurchWebSite'])."'),
(1016, 'sReminderSigner', '".$connection->real_escape_string($setupDate['sReminderSigner'])."'),
(1014, 'sTaxSigner', '".$connection->real_escape_string($setupDate['sTaxSigner'])."'),
(2014, 'sChurchFB', '".$connection->real_escape_string($setupDate['sChurchFB'])."'),
(2015, 'sChurchTwitter', '".$connection->real_escape_string($setupDate['sChurchTwitter'])."'),
(2056, 'sGdprDpoSigner', '".$connection->real_escape_string($setupDate['sGdprDpoSigner'])."'),
(2058, 'sGdprDpoSignerEmail', '".$connection->real_escape_string($setupDate['sGdprDpoSignerEmail'])."'),
(27, 'sSMTPHost', '".$connection->real_escape_string($setupDate['sSMTPHost'])."'),
(28, 'bSMTPAuth', '".$connection->real_escape_string($setupDate['bSMTPAuth'])."'),
(29, 'sSMTPUser', '".$connection->real_escape_string($setupDate['sSMTPUser'])."'),
(30, 'sSMTPPass', '".$connection->real_escape_string($setupDate['sSMTPPass'])."'),
(24, 'iSMTPTimeout', '".$connection->real_escape_string($setupDate['iSMTPTimeout'])."'),
(26, 'sToEmailAddress', '".$connection->real_escape_string($setupDate['sToEmailAddress'])."'),
(2045, 'bPHPMailerAutoTLS', '".$connection->real_escape_string($setupDate['bPHPMailerAutoTLS'])."'),
(2055, 'sTimeZone', '".$connection->real_escape_string($setupDate['sTimeZone'])."'),
(2046, 'sPHPMailerSMTPSecure', '".$connection->real_escape_string($setupDate['sPHPMailerSMTPSecure'])."') ON DUPLICATE KEY UPDATE cfg_id=VALUES(cfg_id)";

        $connection->query($sql);
        
        $connection->close();

        return $response->withStatus(200);
    });
});