<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PropertyType;
use EcclesiaCRM\Property;
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

    $this->post('/persons/assign', 'propertiesPersonsAssign' );
    $this->delete('/persons/unassign', 'propertiesPersonsUnAssign' );

    $this->post('/families/assign', 'propertiesFamiliesAssign' );
    $this->delete('/families/unassign', 'propertiesFamiliesUnAssign' );

    $this->post('/groups/assign', 'propertiesGroupsAssign' );
    $this->delete('/groups/unassign', 'propertiesGroupsUnAssign' );

    $this->post('/propertytypelists', 'getAllPropertyTypes' );
    $this->post('/propertytypelists/edit', 'editPropertyType' );
    $this->post('/propertytypelists/set', 'setPropertyType' );
    $this->post('/propertytypelists/create', 'createPropertyType' );
    $this->post('/propertytypelists/delete', 'deletePropertyType' );

    $this->post('/typelists/edit', 'editProperty' );
    $this->post('/typelists/set', 'setProperty' );
    $this->post('/typelists/delete', 'deleteProperty' );
    $this->post('/typelists/create', 'createProperty' );
    $this->post('/typelists/{type}', 'getAllProperties' );

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
      ->withColumn('COUNT(Property.pro_ID)', 'Properties')
      ->find();

    $arr = $ormPropertyTypes->toArray();

    $res = "";
    $place = 0;

    $count = count($arr);

    foreach ($arr as $elt) {
      $new_elt = "{";
      foreach ($elt as $key => $value) {
        if ($key == 'PrtClass') {
          switch ($value) { case 'p': $value = _('Person'); break; case 'f': $value = _('Family'); break; case 'g': $value =  _('Group'); break;}
          $new_elt .= "\"".$key."\":".json_encode($value).",";
        } else {
          $new_elt .= "\"".$key."\":".json_encode($value).",";
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

function editPropertyType (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

  //Get the properties
    $propertyType = PropertyTypeQuery::Create()
      ->findOneByPrtId($data['typeId']);

    return $response->withJson(['success' => true, 'prtType' => $propertyType->toArray()]);
}

function setPropertyType (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

  //Set the properties
    $propertyType = PropertyTypeQuery::Create()
      ->findOneByPrtId($data['typeId']);

    $propertyType->setPrtName ($data['Name']);
    $propertyType->setPrtDescription($data['Description']);

    $propertyType->save();

    return $response->withJson(['success' => true, 'prtType' => $propertyType->toArray()]);
}


function createPropertyType (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

  //Get the properties
    $propertyType = new PropertyType();

    $propertyType->setPrtClass ($data['Class']);
    $propertyType->setPrtName ($data['Name']);
    $propertyType->setPrtDescription($data['Description']);

    $propertyType->save();

    return $response->withJson(['success' => true, 'prtType' => $propertyType->toArray()]);
}


function deletePropertyType (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

  //Set the properties
    $propertyType = PropertyTypeQuery::Create()
      ->findOneByPrtId($data['typeId']);


   $properties = PropertyQuery::Create()->findByProPrtId ($data['typeId']);

  foreach ($properties as $property) {
      $recProps = Record2propertyR2pQuery::Create()->findByR2pProId ($property->getProId());
      if(!is_null ($recProps)) {
        $recProps->delete();
      }
  }

  if (!is_null ($properties)) {
      $properties->delete();
  }

  $propertyType->delete();

  return $response->withJson(['success' => true]);
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
        switch ($value) { case 'p': $value = _('Person'); break; case 'f': $value = _('Family'); break; case 'g': $value =  _('Group'); break;}
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

function editProperty (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }

  $data = $request->getParsedBody();


  //Get the properties
    $property = PropertyQuery::Create()
      ->findOneByProId($data['typeId']);

    $ormPropertyTypes = PropertyTypeQuery::Create()
                      ->filterByPrtClass($property->getProClass())
                      ->find();

    return $response->withJson(['success' => true, 'proType' => $property->toArray(), 'propertyTypes' => $ormPropertyTypes->toArray()]);
}

function setProperty (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

  //Set the properties
    $property = PropertyQuery::Create()
      ->findOneByProId($data['typeId']);

    $property->setProName ($data['Name']);
    $property->setProDescription($data['Description']);
    $property->setProPrompt($data['Prompt']);

    $property->save();

    return $response->withJson(['success' => true, 'proType' => $property->toArray()]);
}

function deleteProperty (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

  $property = PropertyQuery::Create()->findByProId ($data['typeId']);

  // we delete the correleted datas
  $recProps = Record2propertyR2pQuery::Create()->findByR2pProId ($data['typeId']);
  if(!is_null ($recProps)) {
    $recProps->delete();
  }

  if (!is_null ($property)) {
      $property->delete();
  }

  return $response->withJson(['success' => true]);
}

function createProperty (Request $request, Response $response, array $args) {
  if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
      return $response->withStatus(401);
  }


  $data = $request->getParsedBody();

    $propertyType = PropertyTypeQuery::Create()
                      ->filterByPrtClass($data['Class'])
                      ->findOne();

  //Create the properties
    $property = new Property();

    $property->setProPrtId($propertyType->getPrtId());
    $property->setProClass ($data['Class']);
    $property->setProName ($data['Name']);
    $property->setProDescription($data['Description']);
    $property->setProPrompt($data['Prompt']);

    $property->save();

    return $response->withJson(['success' => true, 'proType' => $property->toArray()]);
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
  if ( !(SessionUser::getUser()->isMenuOptionsEnabled() || SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']) ) {// use session variable for an current group manager
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
  if ( !(SessionUser::getUser()->isMenuOptionsEnabled() || SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']) ) {// use session variable for an current group manager
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
