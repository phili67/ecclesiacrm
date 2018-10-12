<?php
// Routes
use EcclesiaCRM\Group;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\ListOption;
use EcclesiaCRM\ListOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\GroupManagerPerson;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Property;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Service\GroupService;




$app->group('/groups', function () {
    $this->get('/', function () {
        echo GroupQuery::create()->groupByName()->find()->toJSON();
    });
    
    $this->post('/groupproperties/{groupID:[0-9]+}', function ($request, $response, $args) {
      $ormAssignedProperties = Record2propertyR2pQuery::Create()
                            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                            ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                            ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                            ->where(PropertyTableMap::COL_PRO_CLASS."='g'")
                            ->addAscendingOrderByColumn('ProName')
                            ->addAscendingOrderByColumn('ProTypeName')
                            ->findByR2pRecordId($args['groupID']);

      return $ormAssignedProperties->toJSON();
    });
    
    $this->get('/search/{query}', function ($request, $response, $args) {
      $query = $args['query'];

      $searchLikeString = '%'.$query.'%';

        
      $groups = GroupQuery::create()
                ->filterByName($searchLikeString, Criteria::LIKE)
                //->orderByName()
                ->find();
            
            
      $return = [];        

      if (!empty($groups))
      { 
        $data = [];   
        $id++;
        
        foreach ($groups as $group) {                  
          $values['id'] = $id++;
          $values['objid'] = $group->getId();
          $values['text'] = $group->getName();
          $values['uri'] = SystemURLs::getRootPath()."/GroupView.php?GroupID=".$group->getId();
      
          array_push($return, $values);
    
          array_push($data, $elt);
        }
      }
      return $response->withJson($return);    
    });
    
    $this->post('/deleteAllManagers', function ($request, $response, $args) {
        $options = (object) $request->getParsedBody();
        
        if ( isset ($options->groupID) ) {
          $managers = GroupManagerPersonQuery::Create()->filterByGroupId($options->groupID)->find();
          
          if ($managers != null) {
            $managers->delete();
          }
          return $response->withJson(['status' => "success"]);        
        }
            
        return $response->withJson(['status' => "failed"]);        
    });
    
    $this->post('/deleteManager', function ($request, $response, $args) {
        $options = (object) $request->getParsedBody();
        
        if ( isset ($options->groupID) && isset ($options->personID) ) {
          $manager = GroupManagerPersonQuery::Create()->filterByPersonID($options->personID)->filterByGroupId($options->groupID)->findOne();
          
          if ($manager != null) {
            $manager->delete();
          }
          
          $managers = GroupManagerPersonQuery::Create()->filterByGroupId($options->groupID)->find();
          
          if ($managers->count()) {
            $data = [];
          
            foreach ($managers as $manager) {
          
             $elt = ['name'=> $manager->getPerson()->getFullName(),
                  'personID'=>$manager->getPerson()->getId()];
                
             array_push($data, $elt);
            
            }
          
            return $response->withJson($data);
          } else {
            return $response->withJson(['status' => "empty"]);
          }
        }
            
        return $response->withJson(['status' => "failed"]);        
    });
    $this->post('/getmanagers', function ($request, $response, $args) {
        $option = (object) $request->getParsedBody();
        
        if (isset ($option->groupID)) {
          $managers = GroupManagerPersonQuery::Create()->findByGroupId($option->groupID);
          
          if ($managers->count()) {
            $data = [];
          
            foreach ($managers as $manager) {
              if (!$manager->getPerson()->isDeactivated()) {
                 $elt = ['name'=> $manager->getPerson()->getFullName(),
                      'personID'=>$manager->getPerson()->getId()];
                
                 array_push($data, $elt);
              }            
            }
          
            return $response->withJson($data);
          } else {
            return $response->withJson(['status' => "empty"]);
          }
        }
            
        return $response->withJson(['status' => "failed"]);        
    });
    $this->post('/addManager', function ($request, $response, $args) {
        $options = (object)$request->getParsedBody();
        
        if (isset ($options->personID) && isset($options->groupID)) {
          $groupManager = new GroupManagerPerson();
        
          $groupManager->setPersonId($options->personID);
          $groupManager->setGroupId($options->groupID);
          
          $groupManager->save();
          
          return $response->withJson(['status' => "success".$options->groupID." ".$options->personID]);
        }
        
        return $response->withJson(['status' => "failed"]);
    });
    $this->get('/groupsInCart', function () {
        $groupsInCart = [];
        $groups = GroupQuery::create()->find();
        foreach ($groups as $group) {
            if ($group->checkAgainstCart()) {
                array_push($groupsInCart, $group->getId());
            }
        }
        echo json_encode(['groupsInCart' => $groupsInCart]);
    });
    $this->post('/', function ($request, $response, $args) {
        $groupSettings = (object) $request->getParsedBody();
        $group = new Group();
        if ($groupSettings->isSundaySchool) {
            $group->makeSundaySchool();
        }
        $group->setName($groupSettings->groupName);
        $group->save();
        echo $group->toJSON();
    });
    $this->post('/{groupID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findOneById($groupID);
        $group->setName($input->groupName);
        $group->setType($input->groupType);
        $group->setDescription($input->description);
        
        if ($input->groupType == 4) {
            $group->makeSundaySchool();
        }
        
        $group->save();
        
        echo $group->toJSON();
    });
    $this->get('/{groupID:[0-9]+}', function ($request, $response, $args) {
        echo GroupQuery::create()->findOneById($args['groupID'])->toJSON();
    });
    $this->get('/{groupID:[0-9]+}/cartStatus', function ($request, $response, $args) {
        echo GroupQuery::create()->findOneById($args['groupID'])->checkAgainstCart();
    });
    $this->delete('/{groupID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        GroupQuery::create()->findOneById($groupID)->delete();
        echo json_encode(['status'=>'success']);
    });
    $this->get('/{groupID:[0-9]+}/members', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->usePersonQuery()
              ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->endUse()
            ->findByGroupId($groupID);
        
            
        // we loop to find the information in the family to add adresses etc ... this is now unusefull, the address is created automatically        
        foreach ($members as $member)
        {
          $p = $member->getPerson();
          $fam = $p->getFamily();   
      
          // Philippe Logel : this is usefull when a person don't have a family : ie not an address
          if (!is_null($fam) 
            && !is_null($fam->getAddress1()) 
            && !is_null($fam->getAddress2())
            && !is_null($fam->getCity())
            && !is_null($fam->getState())
            && !is_null($fam->getZip())
            )
          {
            $p->setAddress1 ($fam->getAddress1());
            $p->setAddress2 ($fam->getAddress2());
      
            $p->setCity($fam->getCity());
            $p->setState($fam->getState());
            $p->setZip($fam->getZip());    
          }      
        }
        
        echo $members->toJSON();
    });
    
    $this->get('/{groupID:[0-9]+}/events', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->findByGroupId($groupID);
        echo $members->toJSON();
    });
    $this->delete('/{groupID:[0-9]+}/removeperson/{userID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $person = PersonQuery::create()->findPk($userID);
        $group = GroupQuery::create()->findPk($groupID);
        $groupRoleMemberships = $group->getPerson2group2roleP2g2rs();
                
        $groupService = new GroupService();
        
        foreach ($groupRoleMemberships as $groupRoleMembership) {
            if ($groupRoleMembership->getPersonId() == $person->getId()) {
                $groupService->removeUserFromGroup($groupID, $person->getId());
                //$groupRoleMembership->delete();
                $note = new Note();
                $note->setText(gettext("Deleted from group"). ": " . $group->getName());
                $note->setType("group");
                $note->setEntered($_SESSION['user']->getPersonId());
                $note->setPerId($person->getId());
                $note->save();
            }
        }
        echo json_encode(['success' => 'true']);
    });
    
    $this->post('/{groupID:[0-9]+}/addperson/{userID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $person = PersonQuery::create()->findPk($userID);
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findPk($groupID);
        $p2g2r = Person2group2roleP2g2rQuery::create()
          ->filterByGroupId($groupID)
          ->filterByPersonId($userID)
          ->findOneOrCreate();
        if($input->RoleID)
        {
          $p2g2r->setRoleId($input->RoleID);
        }
        else
        {
          $p2g2r->setRoleId($group->getDefaultRole());
        }
                
        $group->addPerson2group2roleP2g2r($p2g2r);
        $group->save();
        $note = new Note();
        $note->setText(gettext("Added to group"). ": " . $group->getName());
        $note->setType("group");
        $note->setEntered($_SESSION['user']->getPersonId());
        $note->setPerId($person->getId());
        $note->save();
        $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->filterByPersonId($input->PersonID)
            ->findByGroupId($GroupID);
        echo $members->toJSON();
    });
    $this->post('/{groupID:[0-9]+}/userRole/{userID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $roleID = $request->getParsedBody()['roleID'];
        $membership = EcclesiaCRM\Person2group2roleP2g2rQuery::create()->filterByGroupId($groupID)->filterByPersonId($userID)->findOne();
        $membership->setRoleId($roleID);
        $membership->save();
        echo $membership->toJSON();
    });
    $this->post('/{groupID:[0-9]+}/roles/{roleID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $roleID = $args['roleID'];
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findOneById($groupID);
        if (isset($input->groupRoleName)) {
            $groupRole = EcclesiaCRM\ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($roleID)->findOne();
            $groupRole->setOptionName($input->groupRoleName);
            $groupRole->save();
            return json_encode(['success' => true]);
        } elseif (isset($input->groupRoleOrder)) {
            $groupRole = EcclesiaCRM\ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($roleID)->findOne();
            $groupRole->setOptionSequence($input->groupRoleOrder);
            $groupRole->save();
            return json_encode(['success' => true]);
        }
        echo json_encode(['success' => false]);
    });
    $this->get('/{groupID:[0-9]+}/roles', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $group = GroupQuery::create()->findOneById($groupID);
        $roles = EcclesiaCRM\ListOptionQuery::create()->filterById($group->getRoleListId())->orderByOptionName()->find();
        echo $roles->toJSON();
    });
    $this->delete('/{groupID:[0-9]+}/roles/{roleID:[0-9]+}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $roleID = $args['roleID'];
        echo json_encode($this->GroupService->deleteGroupRole($groupID, $roleID));
    });
    $this->post('/{groupID:[0-9]+}/roles', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $roleName = $request->getParsedBody()['roleName'];
        echo $this->GroupService->addGroupRole($groupID, $roleName);
    });
    $this->post('/{groupID:[0-9]+}/defaultRole', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $roleID = $request->getParsedBody()['roleID'];
        $group = GroupQuery::create()->findPk($groupID);
        $group->setDefaultRole($roleID);
        $group->save();
        echo json_encode(['success' => true]);
    });
    $this->post('/{groupID:[0-9]+}/setGroupSpecificPropertyStatus', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $input = $request->getParsedBody();
        if ($input['GroupSpecificPropertyStatus']) {
            $this->GroupService->enableGroupSpecificProperties($groupID);
            echo json_encode(['status' => 'group specific properties enabled']);
        } else {
            $this->GroupService->disableGroupSpecificProperties($groupID);
            echo json_encode(['status' => 'group specific properties disabled']);
        }
    });
    $this->post('/{groupID:[0-9]+}/settings/active/{value}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $group = GroupQuery::create()->findOneById($groupID);
            if ($group != null) {
                $group->setActive($flag);
                $group->save();
            } else {
                return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid group id']);
            }
            return $response->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid status value']);
        }
    });
    $this->post('/{groupID:[0-9]+}/settings/email/export/{value}', function ($request, $response, $args) {
        $groupID = $args['groupID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $group = GroupQuery::create()->findOneById($groupID);
            if ($group != null) {
                $group->setIncludeInEmailExport($flag);
                $group->save();
            } else {
                return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid group id']);
            }
            return $response->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid export value']);
        }
    });
});