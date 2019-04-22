<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2018-07-11
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemConfig;


use EcclesiaCRM\PastoralCare;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareType;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;

$app->group('/pastoralcare', function () {

  $this->post('/', 'getAllPastoralCare' );
  $this->post('/deletetype', 'deletePastoralCareType' );
  $this->post('/createtype', 'createPastoralCareType' );
  $this->post('/settype', 'setPastoralCareType');  
  $this->post('/edittype', 'editPastoralCareType' );
  
  $this->post('/add', 'addPastoralCare' );
  $this->post('/delete', 'deletePastoralCare');
  $this->post('/getinfo', 'getPastoralCareInfo' );
  $this->post('/modify', 'modifyPastoralCare' );

});

function getAllPastoralCare(Request $request, Response $response, array $args) {    
  if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
    return $response->withStatus(401);
  }

  return PastoralCareTypeQuery::Create()->find()->toJSON();
}

function deletePastoralCareType (Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();
  
  if (isset ($input->pastoralCareTypeId) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
    $pstCareType = PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId);
      
    if ($pstCareType != null) {
      $pstCareType->delete();
    }
    
    return $response->withJson(['status' => "success"]); 
    
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function createPastoralCareType(Request $request, Response $response, array $args) {    
  $input = (object)$request->getParsedBody();
  
  if (isset ($input->Visible) && isset ($input->Title) && isset ($input->Description) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
    $pstCareType = new PastoralCareType();
    
    $pstCareType->setVisible($input->Visible);
    $pstCareType->setTitle($input->Title);
    $pstCareType->setDesc($input->Description);
    
    $pstCareType->save();
    
    return $response->withJson(['status' => "success"]);
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function setPastoralCareType (Request $request, Response $response, array $args) {    
  $input = (object)$request->getParsedBody();
  
  if (isset ($input->pastoralCareTypeId) && isset ($input->Visible) 
    && isset ($input->Title) && isset ($input->Description) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
    $pstCareType = PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId);
    
    $pstCareType->setVisible($input->Visible);
    $pstCareType->setTitle($input->Title);
    $pstCareType->setDesc($input->Description);
    
    $pstCareType->save();
    
    return $response->withJson(['status' => "success"]);
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function editPastoralCareType (Request $request, Response $response, array $args) {    
  $input = (object)$request->getParsedBody();
  
  if (isset ($input->pastoralCareTypeId)  && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
    return PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId)->toJSON();
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function addPastoralCare (Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();
   
  if (isset ($input->typeID)  && isset ($input->personID) && isset ($input->currentPastorId) 
    && isset ($input->visibilityStatus) && isset ($input->noteText)
    && SessionUser::getUser()->isPastoralCareEnabled() ){
    $pstCare = new PastoralCare();
    
    $pstCare->setTypeId($input->typeID);

    $pstCare->setPersonId($input->personID);
    $pstCare->setPastorId($input->currentPastorId);
    
    $pastor = PersonQuery::Create()->findOneById ($input->currentPastorId);
    
    if ($pastor != null) {
      $pstCare->setPastorName($pastor->getFullName());
    }
    
    $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
    $pstCare->setDate($date->format('Y-m-d H:i:s'));
    
    $pstCare->setVisible($input->visibilityStatus);
    $pstCare->setText($input->noteText);

    $pstCare->save();
    
    return $response->withJson(['status' => "success"]); 
    
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function deletePastoralCare (Request $request, Response $response, array $args) {
   $input = (object)$request->getParsedBody();
   
  if (isset ($input->ID)  && SessionUser::getUser()->isPastoralCareEnabled() ){
    $pstCare = PastoralCareQuery::create()->findOneByID ($input->ID);
            
    if ($pstCare != null) {
      $pstCare->delete();
    }
    
    return $response->withJson(['status' => "success"]); 
    
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function getPastoralCareInfo (Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();
   
  if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled() ){
    $pstCare = PastoralCareQuery::create()->leftJoinWithPastoralCareType()->findOneByID ($input->ID);
    
    $typeDesc = $pstCare->getPastoralCareType()->getTitle().((!empty($pstCare->getPastoralCareType()->getDesc()))?" (".$pstCare->getPastoralCareType()->getDesc().")":"");
    
    return $response->withJson(["id"=> $pstCare->getId(),"typeid" => $pstCare->getTypeId(),"typedesc" => $typeDesc,"visible" => $pstCare->getVisible(),"text" => $pstCare->getText()]); 
    
  }   
  
  return $response->withJson(['status' => "failed"]);
}

function modifyPastoralCare (Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();
   
  if (isset ($input->ID) && isset ($input->typeID)  && isset ($input->personID) 
    && isset ($input->currentPastorId) 
    && isset ($input->visibilityStatus) && isset ($input->noteText)
    && SessionUser::getUser()->isPastoralCareEnabled() ){
    $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);
          
    $pstCare->setTypeId($input->typeID);

    $pstCare->setPersonId($input->personID);
    $pstCare->setPastorId($input->currentPastorId);
    
    $pastor = PersonQuery::Create()->findOneById ($input->currentPastorId);
    
    if ($pastor != null) {
      $pstCare->setPastorName($pastor->getFullName());
    }
    
    $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
    $pstCare->setDate($date->format('Y-m-d H:i:s'));
    
    $pstCare->setVisible($input->visibilityStatus);
    $pstCare->setText($input->noteText);

    $pstCare->save();
    
    return $response->withJson(['status' => "success"]); 
    
  }   
  
  return $response->withJson(['status' => "failed"]);
}