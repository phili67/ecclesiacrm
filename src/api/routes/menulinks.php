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
    if ($args['userId'] == 0 && !$_SESSION['user']->isMenuOptionsEnabled()) {
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
      
      $res .= $new_elt."\"place\":\"".$position."\",\"realplace\":\"".$place."\"},";
    }
    
    echo "{\"MenuLinks\":[".substr($res, 0, -1)."]}"; 
  });
  
  $this->post('/delete', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->MenuLinkId) && $_SESSION['user']->isMenuOptionsEnabled() ){
      $menuLink = MenuLinkQuery::Create()->findOneById($input->MenuLinkId);
      $place = $menuLink->getOrder();
      
      // we search all the links
      $menuLinks = MenuLinkQuery::Create()->findByPersonId($menuLink->getPersonId());
      $count = $menuLinks->count();

      if ($menuLink != null) {
        $menuLink->delete();
      }
      
      for ($i = $place+1;$i <= $count-1;$i++) {
        $menuLink = MenuLinkQuery::Create()->findOneByOrder($i);
        if (!is_null($menuLink)) {
          $menuLink->setOrder($i-1);
          $menuLink->save();
        }
      }
      
      return $response->withJson(['success' => true]); 
      
    }   
    
    return $response->withJson(['success' => false]);
  });
  
  
  $this->post('/upaction', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset($input->PersonID) && isset ($input->MenuLinkId) && isset ($input->MenuPlace) && $_SESSION['user']->isMenuOptionsEnabled() ){
      if ($input->PersonID == 0) {
        $personID = null;
      } else {
        $personID = $input->PersonID;
      }
      
      // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
      $firstMenu = MenuLinkQuery::Create()->filterByPersonId($personID)->findOneByOrder($input->MenuPlace - 1);
      $firstMenu->setOrder($input->MenuPlace)->save();
        
      $secondFamCus = MenuLinkQuery::Create()->filterByPersonId($personID)->findOneById($input->MenuLinkId);
      $secondFamCus->setOrder($input->MenuPlace - 1)->save();

      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });
  
  $this->post('/downaction', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset($input->PersonID) && isset ($input->MenuLinkId) && isset ($input->MenuPlace) && $_SESSION['user']->isMenuOptionsEnabled() ){
            if ($input->PersonID == 0) {
        $personID = null;
      } else {
        $personID = $input->PersonID;
      }
      
      // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
      $firstMenu = MenuLinkQuery::Create()->filterByPersonId($personID)->findOneByOrder($input->MenuPlace + 1);
      $firstMenu->setOrder($input->MenuPlace)->save();
        
      $secondFamCus = MenuLinkQuery::Create()->filterByPersonId($personID)->findOneById($input->MenuLinkId);
      $secondFamCus->setOrder($input->MenuPlace + 1)->save();
         
      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });  
  
  
  $this->post('/create', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->PersonID) && isset ($input->Name) && isset ($input->URI) && $_SESSION['user']->isMenuOptionsEnabled() ){
      if ($input->PersonID == 0) {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::DESC)->findByPersonId(null);
      } else {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::DESC)->findByPersonId($input->PersonID);
      }
    
      $place = 1;
    
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
    
    if (isset ($input->URI) && isset ($input->MenuLinkId) && isset ($input->Name) && $_SESSION['user']->isMenuOptionsEnabled() ){
      
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
    
    if (isset ($input->MenuLinkId) && $_SESSION['user']->isMenuOptionsEnabled() ){
      return MenuLinkQuery::Create()->findOneById($input->MenuLinkId)->toJSON();
    }   
    
    return $response->withJson(['success' => false]);
  });  
});