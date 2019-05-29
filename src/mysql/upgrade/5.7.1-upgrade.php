<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.7.1-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $logger = LoggerUtils::getAppLogger();
  
  $logger->info("Start to delete : all unusefull files");

  unlink(SystemURLs::getDocumentRoot()."/Include/CanvassUtilities.php");
  unlink(SystemURLs::getDocumentRoot()."/Include/ReportFunctions.php");
  unlink(SystemURLs::getDocumentRoot()."/Include/EnvelopeFunctions.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/Dashboard/ClassificationDashboardItem.php.php");
  unlink(SystemURLs::getDocumentRoot()."/MemberRoleChange.php");
  unlink(SystemURLs::getDocumentRoot()."/PersonToGroup.php");
  
  $logger->info("End of delete :  all unusefull files");
?>
