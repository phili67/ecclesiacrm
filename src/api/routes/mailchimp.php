<?php

/* Copyright Philippe Logel not MIT */

// Routes
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Person2group2roleP2g2rQuery;

$app->group('/mailchimp', function () {
    $this->get('/list/{listID}',function($request,$response,$args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $mailchimp = new MailChimpService();
      
      $list      = $mailchimp->getListFromListId ($args['listID']);
      $campaign  = $mailchimp->getCampaignsFromListId($args['listID']);
      
      return $response->withJSON(['MailChimpList' => $list,'MailChimpCampaign' => $campaign,'membersCount' => count($mailchimp->getListMembersFromListId($args['listID']))]);
    });

    $this->get('/lists',function($request,$response,$args) {
      $mailchimp = new MailChimpService();
      
      $lists = $mailchimp->getLists();
      
      $campaigns = [];
      
      foreach ($lists as $list){
        $campaigns[] = $mailchimp->getCampaignsFromListId($list['id']);
      }
      
      return $response->withJSON(['MailChimpLists' => $mailchimp->getLists(),'MailChimpCampaigns' => $campaigns]);
    });

    $this->get('/listmembers/{listID}',function($request,$response,$args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $mailchimp = new MailChimpService();
      
      return $response->withJSON(['MailChimpMembers' => $mailchimp->getListMembersFromListId($args['listID'])]);
    });
    
    $this->post('/createlist', function ($request, $response, $args) {
    
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->ListTitle) && isset ($input->Subject) && isset ($input->PermissionReminder) && isset ($input->ArchiveBars) && isset ($input->Status) ){
        $mailchimp = new MailChimpService();
      
        if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
           $res = $mailchimp->createList($input->ListTitle, $input->Subject, $input->PermissionReminder, $input->ArchiveBars, $input->Status);
         
           if ( !array_key_exists ('title',$res) ) {
             return $response->withJson(['success' => true, "result" => $res]);
           } else {
             return $response->withJson(['success' => false, "error" => $res]);
           }
        }
      }
      
      return $response->withJson(['success' => false,"res" => $res]);
    });
    
    $this->post('/modifylist', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->list_id) && isset ($input->name) && isset ($input->subject) ){
         $mailchimp = new MailChimpService();
      
         if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
           $res = $mailchimp->changeListName($input->list_id, $input->name, $input->subject);
           
           if ( !array_key_exists ('title',$res) ) {
             return $response->withJson(['success' => true, "result" => $res]);
           } else {
             return $response->withJson(['success' => false, "error" => $res]);
           }
         }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/deleteallsubscribers', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->list_id) ){
         $mailchimp = new MailChimpService();
      
         if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
           $res = $mailchimp->deleteAllMembers($input->list_id);
           
           if ( !array_key_exists ('title',$res) ) {
             return $response->withJson(['success' => true, "result" => $res]);
           } else {
             return $response->withJson(['success' => false, "error" => $res]);
           }
         }
      }
      
      return $response->withJson(['success' => false]);
    });
    

    $this->post('/deletelist', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->list_id) ){
         $mailchimp = new MailChimpService();
      
         if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
           $res = $mailchimp->deleteList($input->list_id);
           
           if ( !array_key_exists ('title',$res) ) {
             return $response->withJson(['success' => true, "result" => $res]);
           } else {
             return $response->withJson(['success' => false, "error" => $res]);
           }
         }
      }
      
      return $response->withJson(['success' => false]);
    });
    
    $this->post('/campaign/actions/create', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->list_id) && isset ($input->subject) && isset ($input->title) && isset ($input->htmlBody) ){
         $mailchimp = new MailChimpService();
      
         if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
           $res = $mailchimp->createCampaign($input->list_id, $input->subject, $input->title, $input->htmlBody);
           
           if ( !array_key_exists ('title',$res) ) {
             return $response->withJson(['success' => true, "result" => $res]);
           } else {
             return $response->withJson(['success' => false, "error" => $res]);
           }
         }
      }
      
      return $response->withJson(['success' => false]);
    });
    
    

    $this->post('/campaign/actions/delete', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }
      
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->campaign_id) ){
      
        $mailchimp = new MailChimpService();
      
        $res = $mailchimp->deleteCampaign ($input->campaign_id);
      
        if ( !array_key_exists ('title',$res) ) {
          return $response->withJson(['success' => true,'content' => $res]);
        } else {
          return $response->withJson(['success' => false, "error" => $res]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });    
    $this->post('/campaign/actions/send', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }
      
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->campaign_id) ){
      
        $mailchimp = new MailChimpService();
      
        $res = $mailchimp->sendCampaign ($input->campaign_id);
      
        if ( !array_key_exists ('title',$res) ) {
          return $response->withJson(['success' => true,'content' => $realContent]);
        } else {
          return $response->withJson(['success' => false, "error" => $res]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/campaign/actions/save', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }
      
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->campaign_id) && isset ($input->subject) && isset ($input->content) ){
        $mailchimp = new MailChimpService();
      
        $res1 = $mailchimp->setCampaignContent ($input->campaign_id,$input->content);
        $res2 = $mailchimp->setCampaignMailSubject ($input->campaign_id,$input->subject);
      
        if ( !array_key_exists ('title',$res1) && !array_key_exists ('title',$res2) ) {
          return $response->withJson(['success' => true,'content' => $res1,'subject' => $res2]);
        } else {
          return $response->withJson(['success' => false, "error1" => $res1, "error2" => $res2]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });
    
    
    $this->get('/campaign/{campaignID}/content', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $mailchimp = new MailChimpService();
      
      $campaignContent = $mailchimp->getCampaignContent ($args['campaignID']);
      
      // Be careFull this can change with a new MailChimp api
      $realContent = explode("            <center>\n                <br/>\n                <br/>\n",$campaignContent['html'])[0];
      
      if ( !empty($campaignContent['html']) ) {
        return $response->withJson(['success' => true,'content' => $realContent]);
      }
      
      return $response->withJson(['success' => false]);
    });
    
    
    
    
    $this->post('/status', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->status) && isset ($input->list_id) && isset ($input->email) ){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        
        $res = $mailchimp->updateMember($input->list_id,"","",$input->email,$input->status);
        
        if ( !array_key_exists ('title',$res) ) {
          return $response->withJson(['success' => true, "result" => $res]);
        } else {
          return $response->withJson(['success' => false, "error" => $res]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });
    
    $this->post('/suppress', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->list_id) && isset ($input->email) ){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        
        $res = $mailchimp->deleteMember($input->list_id,$input->email);
        
        if ( !array_key_exists ('title',$res) ) {
          return $response->withJson(['success' => true, "result" => $res]);
        } else {
          return $response->withJson(['success' => false, "error" => $res]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/addperson', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->personID) && isset ($input->list_id) ){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        $person = PersonQuery::create()->findPk($input->personID);
        
        if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->isEmailInMailChimp($person->getEmail()) == ''*/ ) {
          $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),'subscribed');
          
          if ( !array_key_exists ('title',$res) ) {
            return $response->withJson(['success' => true, "result" => $res]);
          } else {
             return $response->withJson(['success' => false, "error" => $res]);
          }
        }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/addfamily', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->familyID) && isset ($input->list_id) ){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        
        if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
          $family = FamilyQuery::create()->findPk($input->familyID);
          $persons = $family->getPeople();
         
          // all person from the family should be deactivated too
          $res = [];
          foreach ($persons as $person) {
            $res[] = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),'subscribed');
          }
          
          return $response->withJson(['success' => true, "result" => $res]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/addgroup', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMailChimpEnabled()) {
        return $response->withStatus(404);
      }

      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->groupID) && isset ($input->list_id) ){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        
        if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
          $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
              ->joinWithPerson()
              ->usePersonQuery()
                ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
              ->endUse()
              ->findByGroupId($input->groupID);
        
            
          // all person from the family should be deactivated too
          $res = [];
          foreach ($members as $member) {
            $res[] = $mailchimp->postMember($input->list_id,32,$member->getPerson()->getFirstName(),$member->getPerson()->getLastName(),$member->getPerson()->getEmail(),'subscribed');
          }
          
          return $response->withJson(['success' => true, "result" => $res]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });
});
