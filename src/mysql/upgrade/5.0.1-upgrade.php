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

  unlink(SystemURLs::getDocumentRoot()."/api/routes/userprofile.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/session.php");
  
  $logger->info("End Delete the old unuseful files");
  
  $logger->info("Reset VolunteerOpportunityQuery");
  
  $vos = VolunteerOpportunityQuery::Create()->orderByOrder()->find();
    
  $row = 1;
  foreach ($vos as $vo) {
    $vo->setOrder($row++);
    $vo->save();
  }

  $logger->info("End of Reset VolunteerOpportunityQuery");
?>
