<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.7.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

/*
*
* Important : this update can only be done if you're login to the crm
*
*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemConfig;
  
  use EcclesiaCRM\GroupQuery;
  use EcclesiaCRM\GroupTypeQuery;
  use EcclesiaCRM\GroupType;
  use EcclesiaCRM\ListOptionQuery;
  use EcclesiaCRM\ListOption;
  use EcclesiaCRM\Map\ListOptionTableMap;
  
  use EcclesiaCRM\Record2propertyR2pQuery;
  use EcclesiaCRM\Map\Record2propertyR2pTableMap;
  use EcclesiaCRM\Map\PropertyTableMap;
  use EcclesiaCRM\Map\PropertyTypeTableMap;
  use EcclesiaCRM\Map\GroupTableMap;
  use Propel\Runtime\ActiveQuery\Criteria;
  
  
  use EcclesiaCRM\PropertyType;
  use EcclesiaCRM\PropertyQuery;
  use EcclesiaCRM\PropertyTypeQuery;
  use EcclesiaCRM\Record2propertyR2p;


  
/*function requireUserGroupMembership($allowedRoles = null)
{
    if ( isset($_SESSION['updateDataBase']) && $_SESSION['updateDataBase'] == true ) {// we don't have to interfer with this test
      return true;
    }
    
    if (!$allowedRoles) {
        throw new Exception('Role(s) must be defined for the function which you are trying to access.  End users should never see this error unless something went horribly wrong.');
    }
    if ($_SESSION[$allowedRoles] || SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isAddRecordsEnabled()) {  //most of the time the API endpoint will specify a single permitted role, or the user is an admin
        // new SessionUser::getUser()->isAddRecordsEnabled() : Philippe Logel
        return true;
    } elseif (is_array($allowedRoles)) {  //sometimes we might have an array of allowed roles.
        foreach ($allowedRoles as $role) {
            if ($_SESSION[$role]) {
                // The current allowed role is in the user's session variable
                return true;
            }
        }
    }

    //if we get to this point in the code, then the user is not authorized.
    throw new Exception('User is not authorized to access '.debug_backtrace()[1]['function'], 401);
}*/


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
  
  $groups = GroupQuery::Create()->find();
  
  foreach ($groups as $group) {
    if ($group->getType() == 4) {
      // in the case of a sunday group
      
    } else if ($group->getType() != 0) {
      // we're in the case of a normal group
      $groupType = new GroupType ();
      
      $groupType->setGroupId ($group->getId());
      $groupType->setListOptionId ($group->getType());
      
      $groupType->save();
      
      $groupNew = GroupQuery::Create()->findOneById($group->getId());
      $groupNew->setType(3);
      $groupNew->save();
    } else {
      // in the case of a type who is 0
      // we're in the case of a normal group
      $groupType = new GroupType ();
      
      $groupType->setGroupId ($group->getId());
      $groupType->setListOptionId (0);
      
      $groupType->save();
      
      $groupNew = GroupQuery::Create()->findOneById($group->getId());
      $groupNew->setType(3);
      $groupNew->save();
    }
  }
  
  //Get the Properties assigned to all the sunday Group
  $ormAssignedProperties = Record2propertyR2pQuery::Create()
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                      ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_RECORD_ID,GroupTableMap::COL_GRP_ID,Criteria::LEFT_JOIN)
                      ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                      ->addAsColumn("GroupId",GroupTableMap::COL_GRP_ID)
                      ->addAsColumn("GroupName",GroupTableMap::COL_GRP_NAME)
                      ->where(PropertyTableMap::COL_PRO_CLASS." = 'm' AND ".GroupTableMap::COL_GRP_TYPE." = '4' AND ". PropertyTypeTableMap::COL_PRT_NAME." = 'MENU'")
                      ->addAscendingOrderByColumn('ProName')
                      ->addAscendingOrderByColumn('groupName')
                      ->find();
                      
                      
    //echo $ormAssignedProperties;
      
      
    $name = '';
    foreach ($ormAssignedProperties as $ormAssignedProperty) {
      if ($ormAssignedProperty->getProName() != $name) {
        $name = $ormAssignedProperty->getProName();
        
        echo $name." ".$groupId."<br>";
        // we've to create a new 
        
        $list = ListOptionQuery::Create()->filterByOptionType('sunday_school')->findById (3);
        
        $numRows = $list->count();
        $newOptionSequence = $numRows + 1;
        
        echo "newOptionSequence : ".$newOptionSequence."<br>";
        
        
        // Get the new OptionID
        $listMax = ListOptionQuery::Create()
                        ->addAsColumn('MaxOptionID', 'MAX('.ListOptionTableMap::COL_LST_OPTIONID.')')
                        ->findOneById (3);
            
        // this ensure that the group list and sundaygroup list has ever an unique optionId.
        $max = $listMax->getMaxOptionID();
            
        $newOptionID = $max+1;
        
        echo "newOptionID : ".$newOptionID."<br>";
        
        // Insert into the appropriate options table
        $lst = new ListOption();
            
        $lst->setId(3);
        $lst->setOptionId($newOptionID);
        $lst->setOptionSequence($newOptionSequence);
        $lst->setOptionName($name);
        $lst->setOptionType('sunday_school');
            
        $lst->save();
      }
      
      $groupId = $ormAssignedProperty->getGroupId();
      
      
      // we're in the case of a normal group
      $groupType = new GroupType ();
      
      $groupType->setGroupId ($groupId);
      $groupType->setListOptionId ($newOptionID);
      
      $groupType->save();
      
    }
    
    
    //Get the sunday groups not assigned by properties
    $ormWithoutAssignedProperties = GroupQuery::Create()
                      ->addJoin(GroupTableMap::COL_GRP_ID,Record2propertyR2pTableMap::COL_R2P_RECORD_ID,Criteria::LEFT_JOIN)
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                      ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                      ->addAsColumn('PrtName',PropertyTypeTableMap::COL_PRT_NAME)
                      ->addAsColumn("GroupId",GroupTableMap::COL_GRP_ID)
                      ->addAsColumn("GroupName",GroupTableMap::COL_GRP_NAME)
                      ->where("((".Record2propertyR2pTableMap::COL_R2P_RECORD_ID." IS NULL) OR (".PropertyTypeTableMap::COL_PRT_NAME." != 'Menu')) AND ".GroupTableMap::COL_GRP_TYPE." = '4'")
                      ->addAscendingOrderByColumn('groupName')
                      ->find();
                      
    $name = '';
    foreach ($ormAssignedProperties as $ormAssignedProperty) {
      $name = $ormAssignedProperty->getProName();
      $groupId = $ormAssignedProperty->getGroupId();
      echo $name." ".$groupId."<br>";

      // we're in the case of a normal group
      $groupType = GroupTypeQuery::Create()->findOneByGroupId ($groupId);
      
      if ( is_null ($groupType)) {
        $groupType = new GroupType ();
      
        $groupType->setGroupId ($groupId);
        $groupType->setListOptionId (0);
      
        $groupType->save();
      }
      
    }
    
    // we clean the code !!!
    $properties = PropertyQuery::Create()->findByProClass('m');
    
    foreach ($properties as $property) {
      $recProps = Record2propertyR2pQuery::Create()->findByR2pProId ($ormAssignedProperty->getR2pProId());
      if(!is_null ($recProps)) {
        $recProps->delete();
      }
    
      if (!is_null ($property)) {
        $property->delete();
      }
    }
    
    
    $propertyType = PropertyTypeQuery::Create()->findOneByPrtClass('m');
    if (!is_null ($propertyType)) {
        $propertyType->delete();
    }
?>
