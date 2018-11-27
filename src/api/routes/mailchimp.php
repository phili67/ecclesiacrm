<?php

/* Copyright Philippe Logel not MIT */

// Routes
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use Propel\Runtime\ActiveQuery\Criteria;

use PHPMailer\PHPMailer\PHPMailer;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\dto\ChurchMetaData;

use Slim\Http\Request;
use Slim\Http\Response;


$app->group('/mailchimp', function () {
    $this->get('/search/{query}','searchList');
    $this->get('/list/{listID}','oneList');
    $this->get('/lists','lists');
    $this->get('/listmembers/{listID}','listmembers');
    $this->post('/createlist', 'createList');
    $this->post('/modifylist', 'modifyList');
    $this->post('/deleteallsubscribers', 'deleteallsubscribers');
    $this->post('/deletelist', 'deleteList');
    
    $this->post('/campaign/actions/create', 'campaignCreate');
    $this->post('/campaign/actions/delete', 'campaignDelete');
    $this->post('/campaign/actions/send', 'campaignSend');
    $this->post('/campaign/actions/save', 'campaignSave');
    $this->get('/campaign/{campaignID}/content', 'campaignContent');
    
    $this->post('/status', 'statusList');
    $this->post('/suppress', 'suppress');
    $this->post('/addallnewsletterpersons', 'addallnewsletterpersons');
    $this->post('/addallpersons', 'addallpersons');
    $this->post('/addperson', 'addPerson');
    $this->post('/addfamily', 'addFamily');
    $this->post('/addgroup', 'addGroup');

    $this->post('/testConnection', 'testEmailConnectionMVC');
});

function searchList ($request,$response,$args) {
  $query = $args['query'];
  $resultsArray = [];

  $id = 1;
// all person in the CRM
  if ($query == '*') {
    $elt = ['id'=>$id++,
          'text'=>"*",
          'typeId' => 1
          ];
          
    $data = ['children' => [$elt],
        'id' => 0,
        'text' => gettext('All People')];

    array_push($resultsArray, $data);
  }
  
// add all person from the newsletter
  if (strpos("newsletter",$query) !== false) {
    $elt = ['id'=>$id++,
          'text'=>"newsletter",
          'typeId' => 2
          ];
          
    $data = ['children' => [$elt],
        'id' => 1,
        'text' => gettext('newsletter')];

    array_push($resultsArray, $data);
  }
  


// Person Search
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
        if ($person->getDateDeactivated() != null)
          continue;
        
        $elt = ['id'=>$id++,
            'text'=>$person->getFullName(),
            'personID'=>$person->getId()];

        array_push($data, $elt);
      }          

      if (!empty($data))
      {
        $dataPerson = ['children' => $data,
        'id' => 2,
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
           if ($family->getDateDeactivated() != null)
             continue;

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
            'id' => 3,
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
            'id' => 4,
            'text' => gettext('Groups')];

          array_push($resultsArray, $dataGroup);
        }
      }
  } catch (Exception $e) {
      $this->Logger->warn($e->getMessage());
  }

  return $response->withJson(array_filter($resultsArray));
}

function oneList (Request $request, Response $response, array $args) {
  if (!$_SESSION['user']->isMailChimpEnabled()) {
    return $response->withStatus(404);
  }

  $mailchimp = new MailChimpService();
  
  $list      = $mailchimp->getListFromListId ($args['listID']);
  $campaign  = $mailchimp->getCampaignsFromListId($args['listID']);
  
  return $response->withJSON(['MailChimpList' => $list,'MailChimpCampaign' => $campaign,'membersCount' => count($mailchimp->getListMembersFromListId($args['listID']))]);
}

function lists(Request $request, Response $response, array $args) {
  $mailchimp = new MailChimpService();
  
  $lists = $mailchimp->getLists();
  
  $campaigns = [];
  
  foreach ($lists as $list){
    $campaigns[] = $mailchimp->getCampaignsFromListId($list['id']);
  }
  
  return $response->withJSON(['MailChimpLists' => $mailchimp->getLists(),'MailChimpCampaigns' => $campaigns]);
}

function listmembers (Request $request, Response $response, array $args) {
  if (!$_SESSION['user']->isMailChimpEnabled()) {
    return $response->withStatus(404);
  }

  $mailchimp = new MailChimpService();
  
  return $response->withJSON(['MailChimpMembers' => $mailchimp->getListMembersFromListId($args['listID'])]);
}

function createList (Request $request, Response $response, array $args) {

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
}

function modifyList (Request $request, Response $response, array $args) {
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
}

function deleteallsubscribers(Request $request, Response $response, array $args) {
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
}

function deleteList (Request $request, Response $response, array $args) {
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
}

function campaignCreate (Request $request, Response $response, array $args) {
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
}

function campaignDelete (Request $request, Response $response, array $args) {
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
}

function campaignSend (Request $request, Response $response, array $args) {
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
}

function campaignSave (Request $request, Response $response, array $args) {
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
}

function campaignContent (Request $request, Response $response, array $args) {
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
}
    
function statusList (Request $request, Response $response, array $args) {
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
}
    
function suppress (Request $request, Response $response, array $args) {
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
}

function addallnewsletterpersons (Request $request, Response $response, array $args) {
  if (!$_SESSION['user']->isMailChimpEnabled()) {
    return $response->withStatus(404);
  }

  $input = (object)$request->getParsedBody();

  if ( isset ($input->list_id) ){
  
    // we get the MailChimp Service
    $mailchimp = new MailChimpService();

    if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->isEmailInMailChimp($person->getEmail()) == ''*/ ) {
      $persons = PersonQuery::Create()
          ->leftJoinFamily()
          ->addAsColumn('FamName',FamilyTableMap::COL_FAM_NAME)
          ->addAsColumn('FamEmail',FamilyTableMap::COL_FAM_EMAIL)
          ->filterByFmrId (SystemConfig::getValue("sDirRoleHead"))
          ->_or()->filterByFmrId (SystemConfig::getValue("sDirRoleSpouse"))
          ->useFamilyQuery()
            ->filterBySendNewsletter('TRUE')
          ->endUse()
          ->find();

      $resError = [];  
      foreach ($persons as $person) {
        
        if ($person->getEmail() != '') {
          $res = $mailchimp->postMember($input->list_id,32,$person->getFamName(),$person->getLastName(),$person->getEmail(),'subscribed');
      
          if ( array_key_exists ('title',$res) ) {
            $resError[] = $res;
          }
        }
      }
      
      if ( count($resError) > 0) {
        return $response->withJson(['success' => false, "error" => $resError]);
      } else {
        return $response->withJson(['success' => true, "result" => $res]);
      }
      
    }
  }
  
  return $response->withJson(['success' => false]);
}

function addallpersons(Request $request, Response $response, array $args) {
  if (!$_SESSION['user']->isMailChimpEnabled()) {
    return $response->withStatus(404);
  }

  $input = (object)$request->getParsedBody();

  if ( isset ($input->list_id) ){
  
    // we get the MailChimp Service
    $mailchimp = new MailChimpService();

    if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->isEmailInMailChimp($person->getEmail()) == ''*/ ) {
      $persons = PersonQuery::create()
        ->filterByEmail(null, Criteria::NOT_EQUAL)
        ->_or()->filterByWorkEmail(null, Criteria::NOT_EQUAL)
        ->find();

      $resError = [];  
      foreach ($persons as $person) {
        $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),'subscribed');
      
        if ( array_key_exists ('title',$res) ) {
          $resError[] = $res;
        }
      }
      
      if ( count($resError) > 0) {
        return $response->withJson(['success' => false, "error" => $resError]);
      } else {
        return $response->withJson(['success' => true, "result" => $res]);
      }
      
    }
  }
  
  return $response->withJson(['success' => false]);
}

function addPerson(Request $request, Response $response, array $args) {
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
}

function addFamily (Request $request, Response $response, array $args) {
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
}

function addGroup (Request $request, Response $response, array $args) {
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
}

function testEmailConnectionMVC(Request $request, Response $response, array $args)
{
    $mailer = new PHPMailer();
    $message = "";
    if (!empty(SystemConfig::getValue("sSMTPHost")) && !empty(ChurchMetaData::getChurchEmail())) {
        $mailer->IsSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = intval(SystemConfig::getValue("iSMTPTimeout"));
        $mailer->Host = SystemConfig::getValue("sSMTPHost");
        if (SystemConfig::getBooleanValue("bSMTPAuth")) {
            $mailer->SMTPAuth = true;
            $result = "<b>SMTP Auth Used</b></br></br>";
            $mailer->Username = SystemConfig::getValue("sSMTPUser");
            $mailer->Password = SystemConfig::getValue("sSMTPPass");
        }
        $mailer->SMTPDebug = 3;
        $mailer->Subject = "Test SMTP Email";
        $mailer->setFrom(ChurchMetaData::getChurchEmail());
        $mailer->addAddress(ChurchMetaData::getChurchEmail());
        $mailer->Body = "test email";
        $mailer->Debugoutput = "html";
    } else {
        $message = gettext("SMTP Host is not setup, please visit the settings page");
    }
    
    if (empty($message)) {  
        ob_start();      
        $mailer->send();
        $result .= ob_get_clean();
        return $response->withJson(['success' => true,"result" => $result]);
    } else {
        return $response->withJson(['success' => false,"error" => $message]);
    } 
}
