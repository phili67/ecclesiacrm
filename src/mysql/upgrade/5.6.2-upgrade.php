<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.6.1-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $logger = LoggerUtils::getAppLogger();
  
  $logger->info("Start to delete : all unusefull files");

  unlink(SystemURLs::getDocumentRoot()."/GroupPersonPropsFormEditor.php");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/email/MailChimp/publicfolder.js");
  
  $logger->info("End of delete :  all unusefull files");
?>
