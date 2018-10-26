<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.5.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\dto\SystemConfig;
  use EcclesiaCRM\UserQuery;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old unuseful files");

  unlink(SystemURLs::getDocumentRoot()."/api/routes/userprofile.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/session.php");
    
  $logger->info("End Delete the old unuseful files");
?>
