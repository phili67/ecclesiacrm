<?php
// pour le debug on se met au bon endroit : https://192.168.151.205/mysql/upgrade/8.0.0-pre-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot() . "/skin/js/calendar/BingMapEvent.js");
unlink(SystemURLs::getDocumentRoot() . "/v2/templates/map/MapUsingGoogle.php");

MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/external/jquery.steps/");


$logger->info("End of delete :  all unusefull files");
