<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.6.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\ListOptionQuery;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\PersonCustomMasterQuery;
  use EcclesiaCRM\FamilyCustomMasterQuery;
  use EcclesiaCRM\UserQuery;
  use EcclesiaCRM\MenuLinkQuery;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Add the forgotten part");
  
  unlink(SystemURLs::getDocumentRoot()."/sundayschool/SundaySchoolLabel.php");
  unlink(SystemURLs::getDocumentRoot()."/Reports/PDFLabelSundaySchool.php");
    
  $logger->info("End Add the forgotten part");
?>
