<?php
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/6.0.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot()."/BackupDatabase.php");
unlink(SystemURLs::getDocumentRoot()."/RestoreDatabase.php");
unlink(SystemURLs::getDocumentRoot()."/CartView.php");

unlink(SystemURLs::getDocumentRoot()."/PrintPastoralCare.php");

unlink(SystemURLs::getDocumentRoot()."/v2/templates/people/pastoralcare.php");
unlink(SystemURLs::getDocumentRoot()."/skin/js/people/PastoralCare.js");

unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/Service/DashboardService.php");

$logger->info("End of delete :  all unusefull files");
?>
