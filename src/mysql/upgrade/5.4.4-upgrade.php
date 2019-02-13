<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.4.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  function removeDirectory($path) {
    $files = glob($path . '/*');
    foreach ($files as $file) {
      is_dir($file) ? removeDirectory($file) : unlink($file);
    }
    rmdir($path);
    return;
  }

  $logger = LoggerUtils::getAppLogger();
  
  $logger->info("Start to delete : all unusefull files");

  unlink(SystemURLs::getDocumentRoot()."/GDPRDashboard.php");
  unlink(SystemURLs::getDocumentRoot()."/GDPRListExport.php");
  unlink(SystemURLs::getDocumentRoot()."/GDPRDataStructure.php");
  unlink(SystemURLs::getDocumentRoot()."/GDPRDataStructureExport.php");
  
  $logger->info("End of Reset :  all unusefull files");
?>
