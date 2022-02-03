<?php
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/7.6.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot()."/Include/GetGroupArray.php");
unlink(SystemURLs::getDocumentRoot()."/RPCdummy.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/Reports/ChurchInfoReport.php");

unlink(SystemURLs::getDocumentRoot()."/ListEvents.php");
unlink(SystemURLs::getDocumentRoot()."/GetText.php");
unlink(SystemURLs::getDocumentRoot()."/skin/js/event/ListEvent.js");

unlink(SystemURLs::getDocumentRoot()."/EditEventAttendees.php");

MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/external/font-awesome/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/external/jquery-photo-uploader/");

unlink(SystemURLs::getDocumentRoot()."/Images/Bank.png");
unlink(SystemURLs::getDocumentRoot()."/Images/Group.png");
unlink(SystemURLs::getDocumentRoot()."/Images/Money.png");

$logger->info("End of delete :  all unusefull files");
?>
