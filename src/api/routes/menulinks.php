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

use EcclesiaCRM\MenuLink;
use EcclesiaCRM\MenuLinkQuery;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;

$app->group('/menulinks', function () {

  $this->post('/{userId:[0-9]+}', function ($request, $response, $args) {
    if ($args['userId'] == 0 && !$_SESSION['user']->isAdmin()) {
            return $response->withStatus(401);
    }
    
    if ($args['userId'] == 0) {
      $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::ASC)->findByPersonId(null);
    } else {
      $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::ASC)->findByPersonId($args['userId']);
    }
    
    $arr = $menuLinks->toArray();
    
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
      
      $res .= $new_elt."\"place\":\"".$position."\"},";
    }
    
    echo "{\"MenuLinks\":[".substr($res, 0, -1)."]}"; 
  });
  
  $this->post('/delete', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->MenuLinkId) ){
      $menuLink = MenuLinkQuery::Create()->findOneById($input->MenuLinkId);
        
      if ($menuLink != null) {
        $menuLink->delete();
      }
      
      return $response->withJson(['success' => true]); 
      
    }   
    
    return $response->withJson(['success' => false]);
  });
  
  
  $this->post('/upaction', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset($input->PersonID) && isset ($input->MenuLinkId) && isset ($input->MenuPlace) ){
      if ($input->PersonID == 0) {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::DESC)->findByPersonId(null);
      } else {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::DESC)->findByPersonId($input->PersonID);
      }

    
      $find               = false;
      $first_find_order   = -1;
      $find_menu_link     = null;
    
      foreach ($menuLinks as $menuLink) {// get the last Order !!!
         if ($menuLink->getId() == $input->MenuLinkId) {
            $find             = true;
            $find_menu_link   = $menuLink;
            
            continue;
         }
         
         if ($find == true) {
            $temp_order = $menuLink->getOrder();

            $menuLink->setOrder($find_menu_link->getOrder());
            $find_menu_link->setOrder($temp_order);
            $menuLink->save();
            $find_menu_link->save();
            break;
         }
      }
         
      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });
  
  $this->post('/downaction', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset($input->PersonID) && isset ($input->MenuLinkId) && isset ($input->MenuPlace) ){
      if ($input->PersonID == 0) {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::ASC)->findByPersonId(null);
      } else {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::ASC)->findByPersonId($input->PersonID);
      }
    
      $find               = false;
      $first_find_order   = -1;
      $find_menu_link     = null;
    
      foreach ($menuLinks as $menuLink) {// get the last Order !!!
         if ($menuLink->getId() == $input->MenuLinkId) {
            $find             = true;
            $find_menu_link   = $menuLink;
            
            $coucou="toto";
            
            continue;
         }
         
         if ($find == true) {
            $temp_order = $menuLink->getOrder();

            $menuLink->setOrder($find_menu_link->getOrder());
            $find_menu_link->setOrder($temp_order);
            $menuLink->save();
            $find_menu_link->save();
            break;
         }
      }
         
      return $response->withJson(['success' => $coucou]);
    }
    
    return $response->withJson(['success' => false]);
  });  
  
  
  $this->post('/create', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->PersonID) && isset ($input->Name) && isset ($input->URI) ){
      if ($input->PersonID == 0) {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::DESC)->findByPersonId(null);
      } else {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::DESC)->findByPersonId($input->PersonID);
      }
    
      $place = 0;
    
      foreach ($menuLinks as $menuLink) {// get the last Order !!!
         $place = $menuLink->getOrder()+1;
         break;
      }
    
      $menuLink = new MenuLink();
      
      if ($input->PersonID == 0) {
        $menuLink->setPersonId(null);
      } else {
        $menuLink->setPersonId($input->PersonID);
      }
      $menuLink->setName($input->Name);
      $menuLink->setUri($input->URI);
      $menuLink->setOrder($place);
      
      $menuLink->save();
      
      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });  

  
  $this->post('/set', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->URI) 
      && isset ($input->MenuLinkId) && isset ($input->Name) ){
      
      $menuLink = MenuLinkQuery::Create()->findOneById($input->MenuLinkId);
      
      $menuLink->setName($input->Name);
      $menuLink->setUri($input->URI);
      
      $menuLink->save();
      
      return $response->withJson(['success' => true]);
    }   
    
    return $response->withJson(['success' => false]);
  });  
  
  $this->post('/edit', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->MenuLinkId) ){
      return MenuLinkQuery::Create()->findOneById($input->MenuLinkId)->toJSON();
    }   
    
    return $response->withJson(['success' => false]);
  });  
});