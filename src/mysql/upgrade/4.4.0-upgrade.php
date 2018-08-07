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
      
    $pstCare->setTypeId(2);//getJoin

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
      
    $pstCare->setTypeId(3);//getJoin

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
      
    $pstCare->setTypeId(4);//getJoin

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
      
    $pstCare->setTypeId(5);//getJoin

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
  
  // upgrade languages
  switch (SystemConfig::getValue('sLanguage')) {
    case 'fr_FR':case 'fr_BE':case 'fr_CH':case 'fr_CA':
       $sql = "INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'Note pastorale classique', '', 1),
(2, 'Pourquoi êtes-vous venu à l\'église', '', 1),
(3, 'Pourquoi continuez-vous à venir ?', '', 1),
(4, 'Avez-vous une requêtes à nous faire ?', '', 1),
(5, 'Comment avez-vous entendu parler de l\'église ?', '', 1),
(6, 'Baptême', 'Formation', 0),
(7, 'Mariage', 'Formation', 0),
(8, 'Relation d\'aide', 'Thérapie et suivi', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);";
       $connection->exec($sql);
       break;
    case 'de_DE':
       $sql = "INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'klassischen Pastoral Notizen', '', 1),
(2, 'Warum sind Sie in unsere Kirche gekommen ?', '', 1),
(3, 'Warum kommen sie dann immer wieder her ?', '', 1),
(4, 'Haben Sie irgendwelche ein Wünsche ?', '', 1),
(5, 'Wie sind Sie auf uns gekommen ?', '', 1),
(6, 'Taufe', 'Ausbildung', 0),
(7, 'Hochzeit', 'Ausbildung', 0),
(8, 'Hilfeleistungen ', 'Therapie', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);";
       $connection->exec($sql);
       break;
    case 'es_ES':
       $sql = "INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'Nota Clásico Pastoral', '', 1),
(2, '¿Por qué vino a la iglesia?', '', 1),
(3, '¿Por qué sigue viniendo?', '', 1),
(4, '¿Tiene alguna sugerencia para nosotros?, '', 1),
(5, '¿Cómo se enteró de la iglesia?', '', 1),
(6, 'bautismo', 'Ausbildung', 0),
(7, 'boda', 'Ausbildung', 0),
(8, 'asistencia', 'Therapie', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);";
       $connection->exec($sql);
       break;
  }
  
  $logger->info("End of translate");
?>