<?php
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/7.6.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot()."/Include/GetGroupArray.php");
unlink(SystemURLs::getDocumentRoot()."/RPCdummy.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/Reports/ChurchInfoReport.php");
unlink(SystemURLs::getDocumentRoot()."/ListEvents.php");
unlink(SystemURLs::getDocumentRoot()."/GetText.php");
unlink(SystemURLs::getDocumentRoot()."/skin/js/event/ListEvent.js");

$logger->info("End of delete :  all unusefull files");
?>
