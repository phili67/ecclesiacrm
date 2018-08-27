<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.5.2-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\ListOptionQuery;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Add the forgotten part");
  
  $list = ListOptionQuery::Create()->findOneByOptionName('bAdmin');
  
  if (is_null($list)) {    
    $sql = "INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_OptionName`) VALUES
    (4, 1, 1, 'True / False'),
    (4, 2, 2, 'Date'),
    (4, 3, 3, 'Text Field (50 char)'),
    (4, 4, 4, 'Text Field (100 char)'),
    (4, 5, 5, 'Text Field (Long)'),
    (4, 6, 6, 'Year'),
    (4, 7, 7, 'Season'),
    (4, 8, 8, 'Number'),
    (4, 9, 9, 'Person from Group'),
    (4, 10, 10, 'Money'),
    (4, 11, 11, 'Phone Number'),
    (4, 12, 12, 'Custom Drop-Down List'),
    (5, 1, 1, 'bAll'),
    (5, 2, 2, 'bAdmin'),
    (5, 3, 3, 'bAddRecords'),
    (5, 4, 4, 'bEditRecords'),
    (5, 5, 5, 'bDeleteRecords'),
    (5, 6, 6, 'bMenuOptions'),
    (5, 7, 7, 'bManageGroups'),
    (5, 8, 8, 'bFinance'),
    (5, 9, 9, 'bNotes'),
    (5, 10, 10, 'bCommunication'),
    (5, 11, 11, 'bCanvasser'),
    (10, 1, 1, 'Teacher'),
    (10, 2, 2, 'Student'),
    (11, 1, 1, 'Member'),
    (12, 1, 1, 'Teacher'),
    (12, 2, 2, 'Student')
  ON DUPLICATE KEY UPDATE lst_OptionName=VALUES(lst_OptionName);";
     
    $connection->exec($sql); 
  }
    
  $logger->info("End Add the forgotten part");
?>
