<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.6.3-upgrade.php
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

  $logger->info("Start to upgrade the roles");

  echo SystemConfig::getValue('sLanguage');
  // upgrade languages
  switch (SystemConfig::getValue('sLanguage')) {
    case 'fr_FR':case 'fr_BE':case 'fr_CH':case 'fr_CA':
       $sql = "INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Utilisateur Administrateur'),
(2, 'Utilisateur Minimum'),
(3, 'Utilisateur Max mais non Admin'),
(4, 'Utilisateur Max mais non DPO et non Suivi pastoral'),
(5, 'Utilisateur DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);";
       $connection->exec($sql);
       echo "fr";
       break;
    case 'de_DE':
       $sql = "INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Administrator Benutzer'),
(2, 'Normal Benutzer'),
(3, 'Max aber nicht Admin Benutzer'),
(4, 'Max aber nicht Dpo und nicht Pastoral Pflege Benutzer'),
(5, 'DPO Benutzer')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);";
       $connection->exec($sql);
       break;
    case 'es_ES':
       $sql = "INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Usuario Administrador'),
(2, 'Usuario Mínimo'),
(3, 'Usuario Maximo pero no Administrador'),
(4, 'Usuario Maximo pero no DPO e pero no Atención Pastoral'),
(5, 'Usuario DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);";
       $connection->exec($sql);
       break;
  }
    
  $logger->info("End to upgrade the roles");
?>
