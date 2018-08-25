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

  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/UserProfileQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/UserProfile.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/UserProfileQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/UserProfile.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Map/UserProfileTableMap.php");
  
    // last we update the user settings
  $users = UserQuery::Create()->find();
  
  // we update all the accounts
  foreach ($users as $user) {
    $user->getWebDavKeyUUID();
  }

  echo SystemConfig::getValue('sLanguage');
  // upgrade languages
  switch (SystemConfig::getValue('sLanguage')) {
    case 'fr_FR':case 'fr_BE':case 'fr_CH':case 'fr_CA':
       $sql = "INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Utilisateur Administrateur'),
(2, 'Utilisateur Minimum')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);";
       $connection->exec($sql);
       echo "fr";
       break;
    case 'de_DE':
       $sql = "INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Administrator Benutzer'),
(2, 'Normal Benutzer ')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);";
       $connection->exec($sql);
       break;
    case 'es_ES':
       $sql = "INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Usuario  Administrador'),
(2, 'Usuario  Mínimo')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);";
       $connection->exec($sql);
       break;
  }
    
  $logger->info("End Delete the old unuseful files");
?>
