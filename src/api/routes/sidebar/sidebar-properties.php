<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\PropertyTypeQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Record2propertyR2p;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;


$app->group('/properties', function() {
    
    $this->post('/propertytypelists', 'getAllPropertyTypes' );

    $this->post('/typelists/{type}', 'getAllProperties' );

    $this->post('/persons/assign', 'propertiesPersonsAssign' );
    $this->delete('/persons/unassign', 'propertiesPersonsUnAssign' );
    
    $this->post('/families/assign', 'propertiesFamiliesAssign' );
    $this->delete('/families/unassign', 'propertiesFamiliesUnAssign' );
    
    $this->post('/groups/assign', 'propertiesGroupsAssign' );
    $this->delete('/groups/unassign', 'propertiesGroupsUnAssign' );
    
    $this->post('/sundayschoolmenu/assign', 'propertiesSundayschoolMenuAssign' );
    $this->post('/sundayschoolmenu/unassign', 'propertiesSundayschoolMenuUnAssign' );
    
});

function getAllPropertyTypes (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  //Get the properties
    $ormPropertyTypes = PropertyTypeQuery::Create()
      ->leftJoinProperty()
      ->groupByPrtId()
      ->groupByPrtClass()
      ->groupByPrtName()
      ->find();
    
    $arr = $ormPropertyTypes->toArray();
    
    $res = "";
    $place = 0;
    
    $count = count($arr);
    
    foreach ($arr as $elt) {
      $new_elt = "{";
      foreach ($elt as $key => $value) {
        if ($key == 'PrtClass') {
          switch ($value) { case 'm': $value = _('Menu'); break; case 'p': $value = _('Person'); break; case 'f': $value = _('Family'); break; case 'g': $value =  _('Group'); break;}
          $new_elt .= "\""._($key)."\":"._(json_encode($value)).",";
        } else {
          $new_elt .= "\""._($key)."\":"._(json_encode($value)).",";
        }
      }
      
      $place++;
      
      if ($place == 1 && $count != 1) {
        $position = "first";
      } else if ($place == $count && $count != 1) {
        $position = "last";
      } else if ($count != 1){
        $position = "intermediate";
      } else {
        $position = "none";
      }
      
      $res .= $new_elt."\"place\":\"".$position."\",\"realplace\":\"".$place."\"},";
    }
    
    echo "{\"PropertyTypeLists\":[".substr($res, 0, -1)."]}"; 
}

function getAllProperties (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  //Get the properties
  $ormProperties = PropertyQuery::Create()
    ->leftJoinPropertyType()
    ->filterByProClass($args['type'])
    ->usePropertyTypeQuery()
      ->orderByPrtName()
    ->endUse()
    ->orderByProName()
    ->find();
    
    $arr = $ormProperties->toArray();
    
    $res = "";
    $place = 0;
    
    $count = count($arr);
    
    foreach ($arr as $elt) {
      $new_elt = "{";
      foreach ($elt as $key => $value) {
        $new_elt .= "\"".$key."\":".json_encode($value).",";
      }
      
      $place++;
      
      if ($place == 1 && $count != 1) {
        $position = "first";
      } else if ($place == $count && $count != 1) {
        $position = "last";
      } else if ($count != 1){
        $position = "intermediate";
      } else {
        $position = "none";
      }
      
      $res .= $new_elt."\"place\":\"".$position."\",\"realplace\":\"".$place."\"},";
    }
    
    echo "{\"PropertyLists\":[".substr($res, 0, -1)."]}"; 
}

function propertiesPersonsAssign (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  $data = $request->getParsedBody();
  $personId = empty($data['PersonId']) ? null : $data['PersonId'];
  $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];
  $propertyValue = empty($data['PropertyValue']) ? '' : $data['PropertyValue'];

  $person = PersonQuery::create()->findPk($personId);
  $property = PropertyQuery::create()->findPk($propertyId);
  if (!$person || !$property) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $personProperty = Record2propertyR2pQuery::create()
      ->filterByR2pRecordId($personId)
      ->filterByR2pProId($propertyId)
      ->findOne();

  if ($personProperty) {
      if (empty($property->getProPrompt()) || $personProperty->getR2pValue() == $propertyValue) {
          return $response->withJson(['success' => true, 'msg' => _('The property is already assigned.')]);
      }

      $personProperty->setR2pValue($propertyValue);
      if ($personProperty->save()) {
          return $response->withJson(['success' => true, 'msg' => _('The property is successfully assigned.')]);
      } else {
          return $response->withJson(['success' => false, 'msg' => _('The property could not be assigned.')]);
      }
  }

  $personProperty = new Record2propertyR2p();
  
  $personProperty->setR2pRecordId($personId);
  $personProperty->setR2pProId($propertyId);
  $personProperty->setR2pValue($propertyValue);
          
  if (!$personProperty->save()) {
      return $response->withJson(['success' => false, 'msg' => _('The property could not be assigned.')]);
  }

  $ormAssignedProperties = Record2propertyR2pQuery::Create()
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                      ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                      ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                      ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                      ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                      ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                      ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                      ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                      ->where(PropertyTableMap::COL_PRO_CLASS."='p'")
                      ->addAscendingOrderByColumn('ProName')
                      ->addAscendingOrderByColumn('ProTypeName')
                      ->findByR2pRecordId($personId);

  return $response->withJson(['success' => true, 'msg' => _('The property is successfully assigned.'), 'count' => $ormAssignedProperties->count()]);
}

function propertiesPersonsUnAssign (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }
  
  $data = $request->getParsedBody();
  $personId = empty($data['PersonId']) ? null : $data['PersonId'];
  $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];

  $personProperty = Record2propertyR2pQuery::create()
      ->filterByR2pRecordId($personId)
      ->_and()->filterByR2pProId($propertyId)
      ->findOne();        
  
  if ($personProperty == null) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $personProperty->delete();
  
  $ormAssignedProperties = Record2propertyR2pQuery::Create()
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                      ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                      ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                      ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                      ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                      ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                      ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                      ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                      ->where(PropertyTableMap::COL_PRO_CLASS."='p'")
                      ->addAscendingOrderByColumn('ProName')
                      ->addAscendingOrderByColumn('ProTypeName')
                      ->findByR2pRecordId($personId);
  
  return $response->withJson(['success' => true, 'msg' => _('The property is successfully unassigned.'), 'count' => $ormAssignedProperties->count()]);
}

function propertiesFamiliesAssign (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  $data = $request->getParsedBody();
  $familyId = empty($data['FamilyId']) ? null : $data['FamilyId'];
  $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];
  $propertyValue = empty($data['PropertyValue']) ? '' : $data['PropertyValue'];

  $family = FamilyQuery::create()->findPk($familyId);
  $property = PropertyQuery::create()->findPk($propertyId);
  if (!$family || !$property) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $familyProperty = Record2propertyR2pQuery::create()
      ->filterByR2pRecordId($familyId)
      ->filterByR2pProId($propertyId)
      ->findOne();

  if ($familyProperty) {
      if (empty($property->getProPrompt()) || $familyProperty->getR2pValue() == $propertyValue) {
          return $response->withJson(['success' => true, 'msg' => _('The property is already assigned.')]);
      }

      $familyProperty->setR2pValue($propertyValue);
      if ($familyProperty->save()) {
          return $response->withJson(['success' => true, 'msg' => _('The property is successfully assigned.')]);
      } else {
          return $response->withJson(['success' => false, 'msg' => _('The property could not be assigned.')]);
      }
  }

  $familyProperty = new Record2propertyR2p();
  
  $familyProperty->setR2pRecordId($familyId);
  $familyProperty->setR2pProId($propertyId);
  $familyProperty->setR2pValue($propertyValue);
  
  if (!$familyProperty->save()) {
      return $response->withJson(['success' => false, 'msg' => _('The property could not be assigned.')]);
  }

  return $response->withJson(['success' => true, 'msg' => _('The property is successfully assigned.')]);
}

function propertiesFamiliesUnAssign (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }
  
  $data = $request->getParsedBody();
  $familyId = empty($data['FamilyId']) ? null : $data['FamilyId'];
  $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];

  $familyProperty = Record2propertyR2pQuery::create()
      ->filterByR2pRecordId($familyId)
      ->_and()->filterByR2pProId($propertyId)
      ->findOne();        
  
  if ($familyProperty == null) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $familyProperty->delete();
  
  return $response->withJson(['success' => true, 'msg' => _('The property is successfully unassigned.')]);
}

function propertiesGroupsAssign (Request $request, Response $response, array $args) {
  if ( !(SessionUser::getUser()->isMenuOptionsEnabled() || SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']) ) {
      return $response->withStatus(401);
  }

  $data = $request->getParsedBody();
  $groupId = empty($data['GroupId']) ? null : $data['GroupId'];
  $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];
  $propertyValue = empty($data['PropertyValue']) ? '' : $data['PropertyValue'];

  $group = GroupQuery::create()->findPk($groupId);
  $property = PropertyQuery::create()->findPk($propertyId);
  if (!$group || !$property) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $groupProperty = Record2propertyR2pQuery::create()
      ->filterByR2pRecordId($groupId)
      ->filterByR2pProId($propertyId)
      ->findOne();

  if ($groupProperty) {
      if (empty($property->getProPrompt()) || $groupProperty->getR2pValue() == $propertyValue) {
          return $response->withJson(['success' => true, 'msg' => _('The property is already assigned.')]);
      }

      $groupProperty->setR2pValue($propertyValue);
      if ($groupProperty->save()) {
          return $response->withJson(['success' => true, 'msg' => _('The property is successfully assigned.')]);
      } else {
          return $response->withJson(['success' => false, 'msg' => _('The property could not be assigned.')]);
      }
  }

  $groupProperty = new Record2propertyR2p();
  
  $groupProperty->setR2pProId($propertyId);
  $groupProperty->setR2pRecordId($groupId);
  $groupProperty->setR2pValue($propertyValue);
  
  if (!$groupProperty->save()) {
      return $response->withJson(['success' => false, 'msg' => _('The property could not be assigned.')]);
  }

  return $response->withJson(['success' => true, 'msg' => _('The property is successfully assigned.')]);
}

function propertiesGroupsUnAssign (Request $request, Response $response, array $args) {
  if ( !(SessionUser::getUser()->isMenuOptionsEnabled() || SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']) ) {
      return $response->withStatus(401);
  }
  
  $data = $request->getParsedBody();
  $GroupId = empty($data['GroupId']) ? null : $data['GroupId'];
  $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];

  $groupProperty = Record2propertyR2pQuery::create()
      ->filterByR2pRecordId($GroupId)
      ->_and()->filterByR2pProId($propertyId)
      ->findOne();        
  
  if ($groupProperty == null) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $groupProperty->delete();
  
  return $response->withJson(['success' => true, 'msg' => _('The property is successfully unassigned.')]);
}

function propertiesSundayschoolMenuAssign (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  $data = (object)$request->getParsedBody();
  
  $groupID = $data->groupID;
  $propertyID = $data->propertyID;
  $oldDropertyID = $data->oldDropertyID;

  $group = GroupQuery::create()->findPk($groupID);
  $property = PropertyQuery::create()->findPk($propertyID);
  if (!$group || !$property) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $groupProperty = Record2propertyR2pQuery::create()// we loop to find the good record
      ->filterByR2pRecordId($groupID)
      ->find();
      
  if ($groupProperty) { // we can delete the last property a sunday group menu is only affected to one group
      $groupProperty->delete();
  }

  $groupProperty = new Record2propertyR2p();
  
  $groupProperty->setR2pValue('Menu');
  $groupProperty->setR2pRecordId($groupID);
  $groupProperty->setR2pProId($propertyID);
  
  if (!$groupProperty->save()) {
      return $response->withJson(['success' => false, 'msg' => _('The menu could not be assigned.')]);
  }

  return $response->withJson(['success' => true, 'msg' => _('The menu is successfully assigned.')]);
}

function propertiesSundayschoolMenuUnAssign (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  $data = (object)$request->getParsedBody();
  
  $groupID = $data->groupID;
  $propertyID = $data->propertyID;
  $oldDropertyID = $data->oldDropertyID;

  $group = GroupQuery::create()->findPk($groupID);
  $property = PropertyQuery::create()->findPk($oldDropertyID);
  if (!$group || !$property) {
      return $response->withStatus(404, _('The record could not be found.'));
  }
  
  $groupProperty = Record2propertyR2pQuery::create()// we loop to find the good record
      ->filterByR2pRecordId($groupID)
      ->filterByR2pProId($oldDropertyID)
      ->findOne();
      
  if ($groupProperty) { // we can delete the last property a sunday group menu is only affected to one group
      $groupProperty->delete();
  }

  return $response->withJson(['success' => true, 'msg' => _('The menu is successfully unassigned.')]);
}