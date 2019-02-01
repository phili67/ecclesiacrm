<?php
// Copyright 2018 Philippe Logel all right reserved
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\ListOptionIcon;


$app->group('/mapicons', function () {

  $this->post('/getall', function ($request, $response, $args) {
      $files = scandir('../skin/icons/markers');
      
      return $response->withJson(array_values(array_diff($files, array(".","..",'shadow'))));

  });
  
  $this->post('/checkOnlyPersonView', function ($request, $response, $args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->onlyPersonView) && isset ($params->lstID) && isset ($params->lstOptionID)) {    
      $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);
      
      if (!empty($icon)) {
        $icon->setOnlyVisiblePersonView($params->onlyPersonView);
        $icon->save();
      } else {
        $icon = new ListOptionIcon();
        
        $icon->setListId($params->lstID);
        $icon->setListOptionId($params->lstOptionID);
        $icon->setOnlyVisiblePersonView($params->onlyPersonView);
        $icon->save();
      }
      
      return $response->withJson(['status' => "success"]);
    }
    
    return $response->withJson(['status' => "failed"]);
  });
  
  
  $this->post('/setIconName', function ($request, $response, $args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->name) && isset ($params->lstID) && isset ($params->lstOptionID)) {
    
      $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);
      
      if (!empty($icon)) {
        $icon->setUrl($params->name);
        $icon->save();
      } else {
        $icon = new ListOptionIcon();
        
        $icon->setListId($params->lstID);
        $icon->setListOptionId($params->lstOptionID);
        $icon->setUrl($params->name);
        $icon->save();
      }
      
      return $response->withJson(['status' => "success"]);
    }
    
    return $response->withJson(['status' => "failed"]);
  });
  
  $this->post('/removeIcon', function ($request, $response, $args) {
    $params = (object)$request->getParsedBody();
          
    if (isset ($params->lstID) && isset ($params->lstOptionID)) {
    
      $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);
      
      if (!empty($icon)) {
        $icon->delete();
        
        return $response->withJson(['status' => "success"]);
      }
      
      return $response->withJson(['status' => "failed"]);
      
    }
    
    return $response->withJson(['status' => "failed"]);
  });
  

});
