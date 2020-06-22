<?php
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/6.1.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot()."/KioskManager.php");
unlink(SystemURLs::getDocumentRoot()."/v2/templates/sundayschool/sundayschoolbadge.php");
unlink(SystemURLs::getDocumentRoot()."/Reports/PDFBadgeSundaySchool.php");

$logger->info("End of delete :  all unusefull files");
?>
