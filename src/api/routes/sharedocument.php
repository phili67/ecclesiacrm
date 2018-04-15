<?php

/******************************************************************************
*
*  filename    : api/routes/sharedocument.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address, 
*                 groups, families, etc...
*
******************************************************************************/
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\NoteShareQuery;
use EcclesiaCRM\NoteShare;
use EcclesiaCRM\Person2group2roleP2g2r;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Emails\DocumentEmail;
use EcclesiaCRM\UserQuery;



// Routes sharedocument

$app->group('/sharedocument', function () {
  
    $this->get('/{query}',function($request,$response,$args) {
      $query = $args['query'];
      $resultsArray = [];
    
      $id = 1;
    
    //Person Search
      try {
        $searchLikeString = '%'.$query.'%';
        $people = PersonQuery::create()->
          filterByFirstName($searchLikeString, Criteria::LIKE)->
            _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
          limit(SystemConfig::getValue("bSearchIncludePersonsMax"))->find();
  

        if (!empty($people))
        {
          $data = [];
          $id++;
        
          foreach ($people as $person) {
            $elt = ['id'=>$id++,
                'text'=>$person->getFullName(),
                'personID'=>$person->getId()];
      
            array_push($data, $elt);
          }          
  
          if (!empty($data))
          {
            $dataPerson = ['children' => $data,
            'id' => 0,
            'text' => gettext('Persons')];
      
            $resultsArray = array ($dataPerson);
          }
        }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }
   
   // Family search   
     try {
          $families = FamilyQuery::create()
              ->filterByName("%$query%", Criteria::LIKE)
              ->limit(SystemConfig::getValue("bSearchIncludeFamiliesMax"))
              ->find();

          if (!empty($families))
          {
            $data = []; 
            $id++;  
            
            foreach ($families as $family)
            {                    
                $searchArray=[
                  "id" => $id++,
                  "text" => $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                  'familyID'=>$family->getId()
                ];
          
              array_push($data,$searchArray);
            }
            
            if (!empty($data))
            {
              $dataFamilies = ['children' => $data,
                'id' => 1,
                'text' => gettext('Families')];
      
              array_push($resultsArray, $dataFamilies);
            }
          }
        } catch (Exception $e) {
            $this->Logger->warn($e->getMessage());
        }

  // Group Search
      try {
          $groups = GroupQuery::create()
              ->filterByName("%$query%", Criteria::LIKE)
              ->limit(SystemConfig::getValue("bSearchIncludeGroupsMax"))
              ->withColumn('grp_Name', 'displayName')
              ->withColumn('grp_ID', 'id')
              ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/GroupView.php?GroupID=",Group.Id)', 'uri')
              ->select(['displayName', 'uri', 'id'])
              ->find();
        
          if (!empty($groups))
          { 
            $data = [];   
            $id++;
          
            foreach ($groups as $group) {
              $elt = ['id'=>$id++,
                'text'=>$group['displayName'],
                'groupID'=>$group['id']];
      
              array_push($data, $elt);
            }
  
            if (!empty($data))
            {
              $dataGroup = ['children' => $data,
                'id' => 2,
                'text' => gettext('Groups')];

              array_push($resultsArray, $dataGroup);
            }
          }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }
    
      return $response->withJson(array_filter($resultsArray));
  });
  
  $this->post('/getallperson',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    $result = [];
    
    if (isset ($params->noteId)) {
          $personShareQuery = NoteShareQuery::create()
            ->joinWithNote()
            ->findByNoteId($params->noteId);
            
          foreach ($personShareQuery as $noteShare) {
            $id = $noteShare->getSharePerId();
            $name = PersonQuery::Create()->findOneById ($id)->getFullName();
            
            $person = ['id' => $id,
                       'name' => (($noteShare->getRights() == 1)?gettext("[R ]"):gettext("[RW]"))."   ".$name];
                    
            array_push($result, $person);        
          }
    }
          
    return $response->withJson($result);                  
  });
  
  $this->post('/addperson',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->personID) && isset ($params->noteId) && isset ($params->currentPersonID) && isset ($params->notification) ) {
        $noteShare = NoteShareQuery::Create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);
        
        if ( empty($noteShare) && $params->currentPersonID != $params->personID) {
          $noteShare = new NoteShare();
          
          $noteShare->setSharePerId($params->personID);
          $noteShare->setNoteId($params->noteId);
          
          $noteShare->save();
          
          if (isset ($params->notification)) {
            $user = UserQuery::Create()->findOneByPersonId($params->personID);
          
            if ( !empty($user) ){
              $email = new DocumentEmail($user, gettext("You can visualize it in your account."));
              $email->send();
            }
          }
        }
    }
          
    return $response->withJson(['status' => "success"]);                  
  });
  
  $this->post('/addfamily',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->familyID) && isset ($params->noteId) && isset ($params->currentPersonID) && isset ($params->notification) ) {
       $members = FamilyQuery::Create()->findOneById($params->familyID)->getPeople();
       
       foreach ($members as $member) {   
          if ($member->getId() > 0) {   
            $noteShare = NoteShareQuery::Create()->filterBySharePerId($member->getId())->findOneByNoteId($params->noteId);
            
            if ( empty($noteShare) && $params->currentPersonID != $member->getId() ) {             
              $noteShare = new NoteShare();
          
              $noteShare->setSharePerId($member->getId());
              $noteShare->setNoteId($params->noteId);
          
              $noteShare->save();
              
              if (isset ($params->notification)) {
                $user = UserQuery::Create()->findOneByPersonId($member->getId());
          
                if ( !empty($user) ){
                  $email = new DocumentEmail($user, gettext("You can visualize it in your account."));
                  $email->send();
                }
              }
            }
          }
       }
    }
          
    return $response->withJson(['status' => "success"]);                  
  });

  $this->post('/addgroup',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->groupID) && isset ($params->noteId) && isset ($params->currentPersonID) && isset ($params->notification) ) {
       $members = GroupQuery::Create()->findOneById($params->groupID)->getPerson2group2roleP2g2rs();
       
       foreach ($members as $member) {   
          if ($member->getPersonId() > 0) {   
            $noteShare = NoteShareQuery::Create()->filterBySharePerId($member->getPersonId())->findOneByNoteId($params->noteId);
            
            if ( empty($noteShare) && $params->currentPersonID != $member->getPersonId() ) {             
              $noteShare = new NoteShare();
          
              $noteShare->setSharePerId($member->getPersonId());
              $noteShare->setNoteId($params->noteId);
          
              $noteShare->save();
              
              if (isset ($params->notification)) {
                $user = UserQuery::Create()->findOneByPersonId($member->getPersonId());
          
                if ( !empty($user) ){
                  $email = new DocumentEmail($user, gettext("You can visualize it in your account."));
                  $email->send();
                }
              }
            }
          }
       }
    }
          
    return $response->withJson(['status' => $params->groupID]);                  
  });

  $this->post('/deleteperson',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->personID) && isset ($params->noteId)) {
          $noteShare = NoteShareQuery::Create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);
          
          $noteShare->delete();
    }
    
    $noteShare = NoteShareQuery::Create()->findByNoteId($params->noteId);
          
    return $response->withJson(['status' => "success",'count' => $noteShare->count()]);                  
  });  
  
  $this->post('/setrights',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->personID) && isset ($params->noteId) && isset ($params->rightAccess) && $params->rightAccess > 0) {
          $noteShare = NoteShareQuery::Create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);
          
          $noteShare->setRights($params->rightAccess);
          $noteShare->save();
    }
              
    return $response->withJson(['status' => "success"]);                  
  });  

  
  $this->post('/cleardocument',function($request,$response,$args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->noteId)) {
          $noteShare = NoteShareQuery::Create()->findByNoteId($params->noteId);
          
          $noteShare->delete();
    }
          
    return $response->withJson(['status' => "success"]);                  
  });
  

});
