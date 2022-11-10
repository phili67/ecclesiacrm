<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SQLUtils;
use EcclesiaCRM\Utils\LoggerUtils;


use Slim\Views\PhpRenderer;

$app->group('/', function (RouteCollectorProxy $group) {

    $group->get('', function (Request $request, Response $response, array $args) {
        $renderer = new PhpRenderer('templates/');

        return $renderer->render($response, 'setup-steps.php', ['sRootPath' => SystemURLs::getRootPath()]);
    });

    $group->get('SystemIntegrityCheck', function (Request $request, Response $response, array $args) {
        $AppIntegrity = EcclesiaCRM\Service\AppIntegrityService::verifyApplicationIntegrity();
        return  $response->write($AppIntegrity['status']);
    });

    $group->get('SystemPrerequisiteCheck', function (Request $request, Response $response, array $args) {
        $required = EcclesiaCRM\Service\AppIntegrityService::getApplicationPrerequisites();
        return $response->withStatus(200)->withJson($required);
    });

    $group->post('checkDatabaseConnection', function (Request $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if (isset ($input->serverName) && isset ($input->dbName) && isset ($input->dbPort)  && isset ($input->user) && isset ($input->password) ){
            if ($input->dbPort == 3306) {
                $connection = new mysqli($input->serverName, $input->user, $input->password, $input->dbName, $input->dbPort);
            } else {
                $connection = new mysqli($input->serverName, $input->user, $input->password, $input->dbName);
            }
            if ($connection->connect_errno) {
                return $response->withStatus(401);
            }

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    });

    $group->post('', function (Request $request, Response $response, array $args) {

        $setupDate = $request->getParsedBody();
        $template = file_get_contents(SystemURLs::getDocumentRoot().'/Include/Config.php.example');

        $template = str_replace('||DB_SERVER_NAME||', $setupDate['DB_SERVER_NAME'], $template);
        $template = str_replace('||DB_SERVER_PORT||', $setupDate['DB_SERVER_PORT'], $template);
        $template = str_replace('||DB_NAME||', $setupDate['DB_NAME'], $template);
        $template = str_replace('||DB_USER||', $setupDate['DB_USER'], $template);
        $template = str_replace('||DB_PASSWORD||', $setupDate['DB_PASSWORD'], $template);
        $template = str_replace('||ROOT_PATH||', $setupDate['ROOT_PATH'], $template);
        $template = str_replace('||URL||', $setupDate['URL'], $template);

        $logger = LoggerUtils::getAppLogger();

        file_put_contents(SystemURLs::getDocumentRoot().'/Include/Config.php', $template);

        // now we can install the CRM
        $host = $setupDate['DB_SERVER_NAME'];
        $db   = $setupDate['DB_NAME'];
        $user = $setupDate['DB_USER'];
        $pass = $setupDate['DB_PASSWORD'];
        $port = $setupDate['DB_SERVER_PORT'];
        $charset = 'utf8';

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
        try {
            $pdo = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }

        $logger->info("Step1 : SystemService\n");

        $version = SystemService::getInstalledVersion();

        $date = (new DateTime())->format('Y-m-d H:i:s');

        SystemURLs::init($setupDate['ROOT_PATH'], $setupDate['URL'], dirname(dirname(dirname(__FILE__))));

        $logger->info("Step2 : Install.sql\n");

        $filename = SystemURLs::getDocumentRoot().'/mysql/install/Install.sql';
        $logger->info("filename sql : \n".  $filename);

        SQLUtils::sqlImport($filename, $pdo);

        // now we install the version
        $logger->info("Step3 : Version\n");


        $sql = "INSERT INTO `version_ver` (`ver_version`, `ver_update_start`, `ver_update_end`) VALUES ('".$version."', '".$date."', '".$date."');";

        $logger->info("Step4 : configuration\n". $sql);

        $pdo->query($sql);

        // and the info for the church Administration
        $sql = "INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(23, 'sDefaultCountry', '".addslashes($setupDate['sChurchCountry'])."'),
(39,'sLanguage', '".addslashes($setupDate['sLanguage'])."'),
(2057, 'bGDPR', '".addslashes($setupDate['bGDPR'])."'),
(1003, 'sChurchName', '".addslashes($setupDate['sChurchName'])."'),
(1004, 'sChurchAddress', '".addslashes($setupDate['sChurchAddress'])."'),
(1005, 'sChurchCity', '".addslashes($setupDate['sChurchCity'])."'),
(1007, 'sChurchZip', '".addslashes($setupDate['sChurchZip'])."'),
(1008, 'sChurchPhone', '".addslashes($setupDate['sChurchPhone'])."'),
(1009, 'sChurchEmail', '".addslashes($setupDate['sChurchEmail'])."'),"
//(1006, 'sChurchState', '".$connection->quote($setupDate['sChurchState'])."'),
            ."(1047, 'sChurchCountry', '".addslashes($setupDate['sChurchCountry'])."'),
(1025, 'sConfirmSigner', '".addslashes($setupDate['sConfirmSigner'])."'),
(2013, 'sChurchWebSite', '".addslashes($setupDate['sChurchWebSite'])."'),
(1016, 'sReminderSigner', '".addslashes($setupDate['sReminderSigner'])."'),
(1014, 'sTaxSigner', '".addslashes($setupDate['sTaxSigner'])."'),
(2014, 'sChurchFB', '".addslashes($setupDate['sChurchFB'])."'),
(2015, 'sChurchTwitter', '".addslashes($setupDate['sChurchTwitter'])."'),
(2056, 'sGdprDpoSigner', '".addslashes($setupDate['sGdprDpoSigner'])."'),
(2058, 'sGdprDpoSignerEmail', '".addslashes($setupDate['sGdprDpoSignerEmail'])."'),
(27, 'sSMTPHost', '".addslashes($setupDate['sSMTPHost'])."'),
(28, 'bSMTPAuth', '".addslashes($setupDate['bSMTPAuth'])."'),
(29, 'sSMTPUser', '".addslashes($setupDate['sSMTPUser'])."'),
(30, 'sSMTPPass', '".addslashes($setupDate['sSMTPPass'])."'),
(24, 'iSMTPTimeout', '".addslashes($setupDate['iSMTPTimeout'])."'),
(26, 'sToEmailAddress', '".addslashes($setupDate['sToEmailAddress'])."'),
(2045, 'bPHPMailerAutoTLS', '".addslashes($setupDate['bPHPMailerAutoTLS'])."'),
(2055, 'sTimeZone', '".addslashes($setupDate['sTimeZone'])."'),
(2046, 'sPHPMailerSMTPSecure', '".addslashes($setupDate['sPHPMailerSMTPSecure'])."') ON DUPLICATE KEY UPDATE cfg_id=VALUES(cfg_id)";

        $pdo->query($sql);


        // we install the language
        $logger->info("Step5 : language\n sql : ". $sql);

        $filename = SystemURLs::getDocumentRoot().'/mysql/install/languages/'.$setupDate['sLanguage'].'.sql';
        $logger->info("filename language : \n".  $filename );

        //if (file_exists($filename)) {
        SQLUtils::sqlImport($filename, $pdo);
        //}


        // dashboard plugins
        // we install the language
        $logger->info("Step6 : Dashboard plugins\n sql : ". $sql);

        $files = scandir(__DIR__ . "/../../Plugins/");

        foreach ($files as $file) {
            if (!in_array($file, [".", ".."])) {
                $filename = SystemURLs::getDocumentRoot().'/Plugins/' . $file . '/mysql/Install.sql';
                $logger->info("filename sql : \n".  $filename);

                SQLUtils::sqlImport($filename, $pdo);
            }
        }

        $logger->info("Setup : End");

        $pdo = null;

        return $response->withStatus(200);
    });
});
