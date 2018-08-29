<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.5.2-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\ListOptionQuery;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Add the forgotten part");
  
  unlink(SystemURLs::getDocumentRoot()."/FamilyCustomFieldsRowOps.php");
    
  $logger->info("End Add the forgotten part");
?>
