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
                       'name' => (($noteShare->getRights() == 1)?gettext("[👀  ]"):gettext("[👀 ✐]"))."   ".$name];
                    
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
          
          if (isset ($params->notification)  && $params->notification) {
            $user = UserQuery::Create()->findOneByPersonId($params->personID);
          
            if ( !empty($user) ){
              $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
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
       $members = FamilyQuery::Create()->findOneById($params->familyID)->getActivatedPeople();
       
       foreach ($members as $member) {   
          if ($member->getId() > 0) {   
            $noteShare = NoteShareQuery::Create()->filterBySharePerId($member->getId())->findOneByNoteId($params->noteId);
            
            if ( empty($noteShare) && $params->currentPersonID != $member->getId() ) {             
              $noteShare = new NoteShare();
          
              $noteShare->setSharePerId($member->getId());
              $noteShare->setNoteId($params->noteId);
          
              $noteShare->save();
              
              if (isset ($params->notification)  && $params->notification) {
                $user = UserQuery::Create()->findOneByPersonId($member->getId());
          
                if ( !empty($user) ){
                  $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
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
              
              if (isset ($params->notification) && $params->notification) {
                $user = UserQuery::Create()->findOneByPersonId($member->getPersonId());
          
                if ( !empty($user) ){
                  $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
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
