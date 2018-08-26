<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.5.1-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\dto\SystemConfig;
  use EcclesiaCRM\UserQuery;
  use EcclesiaCRM\MenuLinkQuery;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old unuseful files");

  $menuLinks = MenuLinkQuery::Create()->findByPersonId(null);

  $place = 0;
    
  // we re-order the menu link
  foreach ($menuLinks as $menuLink) {
    $menuLink->setOrder($place++);
    $menuLink->save();
  }
  
  // last we update the user settings
  $users = UserQuery::Create()->find();
  
  foreach ($users as $user) {
    $menuLinks = MenuLinkQuery::Create()->findByPersonId($user->getPersonId());
    
    $place = 0;
    
    // we re-order the menu link
    foreach ($menuLinks as $menuLink) {
      $menuLink->setOrder($place++);
      $menuLink->save();
    }    
  }
    
  $logger->info("End Delete the old unuseful files");
?>
