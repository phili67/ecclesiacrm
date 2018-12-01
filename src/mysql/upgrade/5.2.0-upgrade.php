<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.0.1-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\VolunteerOpportunityQuery;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old unuseful files");

  unlink(SystemURLs::getDocumentRoot()."/email/Dashboard.php");
  unlink(SystemURLs::getDocumentRoot()."/email/MailChimpMissingReport.php");
  unlink(SystemURLs::getDocumentRoot()."/email/MemberEmailExport.php");

  unlink(SystemURLs::getDocumentRoot()."/Include/StateDropDown.php");
  unlink(SystemURLs::getDocumentRoot()."/Include/CountryDropDown.php");
  
  $logger->info("End Delete the old unuseful files");
  
  $logger->info("End of Reset VolunteerOpportunityQuery");
?>
