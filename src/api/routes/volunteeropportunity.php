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

use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\VolunteerOpportunity;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;

$app->group('/volunteeropportunity', function () {

  $this->post('/', function ($request, $response, $args) {
    if ( !( $_SESSION['user']->isCanvasserEnabled() && $_SESSION['user']->isMenuOptionsEnabled() ) ) {
      return $response->withStatus(401);
    }
    
    $volunteerOpportunities = VolunteerOpportunityQuery::Create()->orderByOrder(Criteria::ASC)->find();
    
    $arr = $volunteerOpportunities->toArray();
    
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
    
    echo "{\"VolunteerOpportunities\":[".substr($res, 0, -1)."]}"; 
  });
  
  $this->post('/delete', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->id) && $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled() ){
      $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);
      $place = $vo->getOrder();
      
      if ( !is_null($vo) ) {
        $vo->delete();
      }
      
      $vos = VolunteerOpportunityQuery::Create()->find();
      $count = $vos->count();
      
      for ($i = $place+1;$i <= $count+1;$i++) {
        $vo = VolunteerOpportunityQuery::Create()->findOneByOrder($i);
        if ( !is_null($vo) ) {
          $vo->setOrder($i-1);
          $vo->save();
        }
      }
      
      return $response->withJson(['success' => $count]); 
      
    }   
    
    return $response->withJson(['success' => false]);
  });
  
  
  $this->post('/upaction', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->id) && isset ($input->place) && $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled() ){
      // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
      $firstVO = VolunteerOpportunityQuery::Create()->findOneByOrder($input->place - 1);
      $firstVO->setOrder($input->place)->save();
        
      $secondVO = VolunteerOpportunityQuery::Create()->findOneById($input->id);
      $secondVO->setOrder($input->place - 1)->save();

      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });
  
  $this->post('/downaction', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->id) && isset ($input->place) && $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled()  ){
      // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
      $firstVO = VolunteerOpportunityQuery::Create()->findOneByOrder($input->place + 1);
      $firstVO->setOrder($input->place)->save();
        
      $secondVO = VolunteerOpportunityQuery::Create()->findOneById($input->id);
      $secondVO->setOrder($input->place + 1)->save();
         
      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });  
  
  
  $this->post('/create', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if ( isset ($input->Name) && isset ($input->desc) && isset ($input->state) && $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled()  ){
      $volunteerOpportunities = VolunteerOpportunityQuery::Create()->orderByOrder(Criteria::DESC)->find();
    
      $place = 1;
    
      foreach ($volunteerOpportunities as $volunteerOpportunity) {// get the last Order !!!
         $place = $volunteerOpportunity->getOrder()+1;
         break;
      }
    
      $vo = new VolunteerOpportunity();
      
      $vo->setName($input->Name);
      $vo->setDescription($input->desc);
      $vo->setActive($input->state);
      $vo->setOrder($place);
      
      $vo->save();
      
      return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
  });  

  
  $this->post('/set', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->id) && isset ($input->Name) && isset ($input->desc) && isset ($input->state) && $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled()  ){
      
      $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);
      
      $vo->setName($input->Name);
      $vo->setDescription($input->desc);
      $vo->setActive($input->state);
      
      $vo->save();
      
      return $response->withJson(['success' => true]);
    }   
    
    return $response->withJson(['success' => false]);
  });  
  
  $this->post('/edit', function ($request, $response, $args) {    
    $input = (object)$request->getParsedBody();
    
    if (isset ($input->id) && $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled() ){
      return VolunteerOpportunityQuery::Create()->findOneById($input->id)->toJSON();
    }   
    
    return $response->withJson(['success' => false]);
  });  
});