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

unlink(SystemURLs::getDocumentRoot() . "/Images/jitsi_logo.png");

# mailchim is now a plugin, so we need to remove all the files related to mailchimp in the core of CRM
unlink(SystemURLs::getDocumentRoot() . "/api/routes/mailchimp.php");

unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/APIControllers/MailchimpController.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/Service/MailChimpService.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/Synchronize/MailchimpDashboardItem.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/VIEWControllers/VIEWMailchimpController.php");


MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/js/email/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/v2/routes/email/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/v2/templates/email/");


$logger->info("End of delete :  all unusefull files");
