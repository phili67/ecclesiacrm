<?php


use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Record2propertyR2p;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;



$app->group('/properties', function() {

    $this->post('/persons/assign', function($request, $response, $args) {
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            return $response->withStatus(401);
        }
 
        $data = $request->getParsedBody();
        $personId = empty($data['PersonId']) ? null : $data['PersonId'];
        $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];
        $propertyValue = empty($data['PropertyValue']) ? '' : $data['PropertyValue'];

        $person = PersonQuery::create()->findPk($personId);
        $property = PropertyQuery::create()->findPk($propertyId);
        if (!$person || !$property) {
            return $response->withStatus(404, gettext('The record could not be found.'));
        }
        
        $personProperty = Record2propertyR2pQuery::create()
            ->filterByR2pRecordId($personId)
            ->filterByR2pProId($propertyId)
            ->findOne();

        if ($personProperty) {
            if (empty($property->getProPrompt()) || $personProperty->getR2pValue() == $propertyValue) {
                return $response->withJson(['success' => true, 'msg' => gettext('The property is already assigned.')]);
            }

            $personProperty->setR2pValue($propertyValue);
            if ($personProperty->save()) {
                return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully assigned.')]);
            } else {
                return $response->withJson(['success' => false, 'msg' => gettext('The property could not be assigned.')]);
            }
        }

        $personProperty = new Record2propertyR2p();
        
        $personProperty->setR2pRecordId($personId);
        $personProperty->setR2pProId($propertyId);
        $personProperty->setR2pValue($propertyValue);
                
        if (!$personProperty->save()) {
            return $response->withJson(['success' => false, 'msg' => gettext('The property could not be assigned.')]);
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

        return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully assigned.'), 'count' => $ormAssignedProperties->count()]);
    });
    
    
    $this->delete('/persons/unassign', function($request, $response, $args) {
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
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
            return $response->withStatus(404, gettext('The record could not be found.'));
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
        
        return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully unassigned.'), 'count' => $ormAssignedProperties->count()]);
    });
    
    $this->post('/families/assign', function($request, $response, $args) {
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            return $response->withStatus(401);
        }
 
        $data = $request->getParsedBody();
        $familyId = empty($data['FamilyId']) ? null : $data['FamilyId'];
        $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];
        $propertyValue = empty($data['PropertyValue']) ? '' : $data['PropertyValue'];

        $family = FamilyQuery::create()->findPk($familyId);
        $property = PropertyQuery::create()->findPk($propertyId);
        if (!$family || !$property) {
            return $response->withStatus(404, gettext('The record could not be found.'));
        }
        
        $familyProperty = Record2propertyR2pQuery::create()
            ->filterByR2pRecordId($familyId)
            ->filterByR2pProId($propertyId)
            ->findOne();

        if ($familyProperty) {
            if (empty($property->getProPrompt()) || $familyProperty->getR2pValue() == $propertyValue) {
                return $response->withJson(['success' => true, 'msg' => gettext('The property is already assigned.')]);
            }

            $familyProperty->setR2pValue($propertyValue);
            if ($familyProperty->save()) {
                return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully assigned.')]);
            } else {
                return $response->withJson(['success' => false, 'msg' => gettext('The property could not be assigned.')]);
            }
        }

        $familyProperty = new Record2propertyR2p();
        
        $familyProperty->setR2pRecordId($familyId);
        $familyProperty->setR2pProId($propertyId);
        $familyProperty->setR2pValue($propertyValue);
        
        if (!$familyProperty->save()) {
            return $response->withJson(['success' => false, 'msg' => gettext('The property could not be assigned.')]);
        }

        return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully assigned.')]);
    });
    
    
    $this->delete('/families/unassign', function($request, $response, $args) {
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
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
            return $response->withStatus(404, gettext('The record could not be found.'));
        }
        
        $familyProperty->delete();
        
        return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully unassigned.')]);
    });
    
    $this->post('/groups/assign', function($request, $response, $args) {
        if ( !($_SESSION['user']->isMenuOptionsEnabled() || $_SESSION['user']->isManageGroupsEnabled() || $_SESSION['bManageGroups']) ) {
            return $response->withStatus(401);
        }
 
        $data = $request->getParsedBody();
        $groupId = empty($data['GroupId']) ? null : $data['GroupId'];
        $propertyId = empty($data['PropertyId']) ? null : $data['PropertyId'];
        $propertyValue = empty($data['PropertyValue']) ? '' : $data['PropertyValue'];

        $group = GroupQuery::create()->findPk($groupId);
        $property = PropertyQuery::create()->findPk($propertyId);
        if (!$group || !$property) {
            return $response->withStatus(404, gettext('The record could not be found.'));
        }
        
        $groupProperty = Record2propertyR2pQuery::create()
            ->filterByR2pRecordId($groupId)
            ->filterByR2pProId($propertyId)
            ->findOne();

        if ($groupProperty) {
            if (empty($property->getProPrompt()) || $groupProperty->getR2pValue() == $propertyValue) {
                return $response->withJson(['success' => true, 'msg' => gettext('The property is already assigned.')]);
            }

            $groupProperty->setR2pValue($propertyValue);
            if ($groupProperty->save()) {
                return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully assigned.')]);
            } else {
                return $response->withJson(['success' => false, 'msg' => gettext('The property could not be assigned.')]);
            }
        }

        $groupProperty = new Record2propertyR2p();
        
        $groupProperty->setR2pProId($propertyId);
        $groupProperty->setR2pRecordId($groupId);
        $groupProperty->setR2pValue($propertyValue);
        
        if (!$groupProperty->save()) {
            return $response->withJson(['success' => false, 'msg' => gettext('The property could not be assigned.')]);
        }

        return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully assigned.')]);
    });
    
    
    $this->delete('/groups/unassign', function($request, $response, $args) {
        if ( !($_SESSION['user']->isMenuOptionsEnabled() || $_SESSION['user']->isManageGroupsEnabled() || $_SESSION['bManageGroups']) ) {
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
            return $response->withStatus(404, gettext('The record could not be found.'));
        }
        
        $groupProperty->delete();
        
        return $response->withJson(['success' => true, 'msg' => gettext('The property is successfully unassigned.')]);
    });
    
    $this->post('/sundayschoolmenu/assign', function($request, $response, $args) {
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            return $response->withStatus(401);
        }
 
        $data = (object)$request->getParsedBody();
        
        $groupID = $data->groupID;
        $propertyID = $data->propertyID;
        $oldDropertyID = $data->oldDropertyID;

        $group = GroupQuery::create()->findPk($groupID);
        $property = PropertyQuery::create()->findPk($propertyID);
        if (!$group || !$property) {
            return $response->withStatus(404, gettext('The record could not be found.'));
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
            return $response->withJson(['success' => false, 'msg' => gettext('The menu could not be assigned.')]);
        }

        return $response->withJson(['success' => true, 'msg' => gettext('The menu is successfully assigned.')]);
    });
    
    $this->post('/sundayschoolmenu/unassign', function($request, $response, $args) {
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            return $response->withStatus(401);
        }
 
        $data = (object)$request->getParsedBody();
        
        $groupID = $data->groupID;
        $propertyID = $data->propertyID;
        $oldDropertyID = $data->oldDropertyID;

        $group = GroupQuery::create()->findPk($groupID);
        $property = PropertyQuery::create()->findPk($oldDropertyID);
        if (!$group || !$property) {
            return $response->withStatus(404, gettext('The record could not be found.'));
        }
        
        $groupProperty = Record2propertyR2pQuery::create()// we loop to find the good record
            ->filterByR2pRecordId($groupID)
            ->filterByR2pProId($oldDropertyID)
            ->findOne();
            
        if ($groupProperty) { // we can delete the last property a sunday group menu is only affected to one group
            $groupProperty->delete();
        }

        return $response->withJson(['success' => true, 'msg' => gettext('The menu is successfully unassigned.')]);
    });
});