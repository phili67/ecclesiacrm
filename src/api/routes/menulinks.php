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

use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\MenuLinks;
use EcclesiaCRM\MenuLinksQuery;
use EcclesiaCRM\PersonQuery;

$app->group('/menulinks', function () {

  $this->post('/{userId:[0-9]+}', function ($request, $response, $args) {
    if ($args['userId'] == 0 && !$_SESSION['user']->isAdmin()) {
            return $response->withStatus(401);
    }
    
    if ($args['userId'] == 0) {
      return MenuLinksQuery::Create()->findByPersonId(null)->toJSON();
    } else {
      return MenuLinksQuery::Create()->findByPersonId($args['userId'])->toJSON();
    }
  });
  
  $this->post('/delete', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->MenuLinksId) ){
      $menuLink = MenuLinksQuery::Create()->findOneById($input->MenuLinksId);
        
      if ($menuLink != null) {
        $menuLink->delete();
      }
      
      return $response->withJson(['status' => "success"]); 
      
    }   
    
    return $response->withJson(['status' => "failed"]);
  });
  
  
  $this->post('/create', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->PersonID) && isset ($input->Name) && isset ($input->URI) ){
      $menuLink = new MenuLinks();
      
      $menuLink->setPersonId($input->PersonID);
      $menuLink->setName($input->Name);
      $menuLink->setUri($input->URI);
      
      $menuLink->save();
      
      return $response->withJson(['status' => "success"]);
    }   
    
    return $response->withJson(['status' => "failed"]);
  });  

  
  $this->post('/set', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->URI) 
      && isset ($input->MenuLinksId) && isset ($input->Name) && $_SESSION['user']->isPastoralCareEnabled() ){
      
      $menuLink = MenuLinksQuery::Create()->findOneById($input->MenuLinksId);
      
      $menuLink->setName($input->Name);
      $menuLink->setUri($input->URI);
      
      $menuLink->save();
      
      return $response->withJson(['status' => "success"]);
    }   
    
    return $response->withJson(['status' => "failed"]);
  });  
  
  $this->post('/edit', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->MenuLinksId) ){
      return MenuLinksQuery::Create()->findOneById($input->MenuLinksId)->toJSON();
    }   
    
    return $response->withJson(['status' => "failed"]);
  });  
});