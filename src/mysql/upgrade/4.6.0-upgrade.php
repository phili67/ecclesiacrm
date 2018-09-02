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
  
  unlink(SystemURLs::getDocumentRoot()."/FamilyCustomFieldsRowOps.php");
  unlink(SystemURLs::getDocumentRoot()."/PersonCustomFieldsRowOps.php");
  
  $per_cus = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();
  
  $row = 1;
  foreach ($per_cus as $per) {
    $per->setCustomOrder($row++);
    $per->save();
  }
  
  $fam_cus = FamilyCustomMasterQuery::Create()->orderByCustomOrder()->find();
  
  $row = 1;
  foreach ($fam_cus as $fam) {
    $fam->setCustomOrder($row++);
    $fam->save();
  }
  
  // now we reorder all the menu links correctly
  $users = UserQuery::Create()->find();
  
  foreach ($users as $user) {
    $menuLinks = MenuLinkQuery::Create()->orderByOrder()->findByPersonId($user->getPersonId());
    
    $row = 0;
    foreach ($menuLinks as $menuLink) {
      $menuLink->setOrder($row++);
      $menuLink->save();
    }
  }
  
  $menuLinks = MenuLinkQuery::Create()->orderByOrder()->findByPersonId(null);
    
  $row = 0;
  foreach ($menuLinks as $menuLink) {
    $menuLink->setOrder($row++);
    $menuLink->save();
  }

    
  $logger->info("End Add the forgotten part");
?>
