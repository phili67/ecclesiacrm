<?php 

// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.4.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemConfig;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\UserProfileQuery;
  use EcclesiaCRM\UserQuery;
  use EcclesiaCRM\PastoralCareQuery;
  use EcclesiaCRM\PastoralCare;

  use Propel\Runtime\ActiveQuery\Criteria;


  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $users = UserQuery::Create()->find();
  
  // we have to fix the theme to the default yellow value
  foreach($users as $user) {
    $user->setStyle("skin-yellow-light");
    $user->save();
  }
  
  // Now we move the old WhyCame to the new one
  // we can't use propel because the schema works without the WhyCame class
  $statement = $connection->prepare("SELECT * FROM `whycame_why` WHERE 1");
  $statement->execute();
  $wCames = $statement->fetchAll();
  
  
  foreach ($wCames as $wCame) {
    // why_join
    $pstCare = new PastoralCare();
      
    $pstCare->setTypeId(1);//getJoin

    $pstCare->setPersonId($wCame['why_per_ID']);
    $pstCare->setPastorId(1);// the administrator per default
      
    $pstCare->setPastorName("EcclesiaCRM Admin");
      
    $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
    $pstCare->setDate($date->format('Y-m-d H:i:s'));
      
    $pstCare->setVisible(true);
    $pstCare->setText("<p>".$wCame['why_join']."</p>");
  
    $pstCare->save();

    //why_come
    $pstCare = new PastoralCare();
      
    $pstCare->setTypeId(1);//getJoin

    $pstCare->setPersonId($wCame['why_per_ID']);
    $pstCare->setPastorId(1);// the administrator per default
      
    $pstCare->setPastorName("EcclesiaCRM Admin");
      
    $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
    $pstCare->setDate($date->format('Y-m-d H:i:s'));
      
    $pstCare->setVisible(true);
    $pstCare->setText("<p>".$wCame['why_come']."</p>");
  
    $pstCare->save();
    
    //why_suggest
    $pstCare = new PastoralCare();
      
    $pstCare->setTypeId(3);//getJoin

    $pstCare->setPersonId($wCame['why_per_ID']);
    $pstCare->setPastorId(1);// the administrator per default
      
    $pstCare->setPastorName("EcclesiaCRM Admin");
      
    $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
    $pstCare->setDate($date->format('Y-m-d H:i:s'));
      
    $pstCare->setVisible(true);
    $pstCare->setText("<p>".$wCame['why_suggest']."</p>");
  
    $pstCare->save(); 
    
    //why_hearofus
    $pstCare = new PastoralCare();
      
    $pstCare->setTypeId(4);//getJoin

    $pstCare->setPersonId($wCame['why_per_ID']);
    $pstCare->setPastorId(1);// the administrator per default
      
    $pstCare->setPastorName("EcclesiaCRM Admin");
      
    $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
    $pstCare->setDate($date->format('Y-m-d H:i:s'));
      
    $pstCare->setVisible(true);
    $pstCare->setText("<p>".$wCame['why_hearOfUs']."</p>");
  
    $pstCare->save();
    
    
  }
  
  // now we delete the unusefull files
  unlink(SystemURLs::getDocumentRoot()."/WhyCameEditor.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/WhyCame.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/WhyCameQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/WhyCame.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/WhyCameQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Map/WhyCameTableMap.php");
  
  // now we can drop the table
  $sqlEvents = "DROP TABLE IF EXISTS `whycame_why`";
  $connection->exec($sqlEvents);
  
  $logger->info("End of translate");
?>