<?php
use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Group;
use EcclesiaCRM\dto\Cart;

$app->group('/cart', function () {
  
    $this->get('/',function($request,$response,$args) {
      return $response->withJSON(['PeopleCart' =>  $_SESSION['aPeopleCart']]);
    });

    $this->post('/', function ($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
            return $response->withStatus(401);
        }

          $cartPayload = (object)$request->getParsedBody();
          
          if ( isset ($cartPayload->Persons) && count($cartPayload->Persons) > 0 )
          {
            Cart::AddPersonArray($cartPayload->Persons);
          }
          elseif ( isset ($cartPayload->Family) )
          {
            Cart::AddFamily($cartPayload->Family);
          }
          elseif ( isset ($cartPayload->Group) )
          {
            Cart::AddGroup($cartPayload->Group);
          }
          elseif ( isset ($cartPayload->removeFamily) )
          {
            Cart::RemoveFamily($cartPayload->removeFamily);
          }
          elseif ( isset ($cartPayload->studentGroup) )
          {
            Cart::AddStudents($cartPayload->studentGroup);
          }
          elseif ( isset ($cartPayload->teacherGroup) )
          {
            Cart::AddTeachers($cartPayload->teacherGroup);
          }          
          else
          {
            throw new \Exception(gettext("POST to cart requires a Persons array, FamilyID, or GroupID"),500);
          }
          return $response->withJson(['status' => "success"]);
      });
      
    $this->post('/emptyToGroup', function($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
            return $response->withStatus(401);
        }

        $cartPayload = (object)$request->getParsedBody();
        Cart::EmptyToGroup($cartPayload->groupID, $cartPayload->groupRoleID);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '.gettext('records(s) successfully added to selected Group.')
        ]);
    });
    
    $this->post('/emptyToEvent', function($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
            return $response->withStatus(401);
        }

        $cartPayload = (object)$request->getParsedBody();
        Cart::EmptyToEvent($cartPayload->eventID);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '.gettext('records(s) successfully added to selected Group.')
        ]);
    });
    
    $this->post('/emptyToNewGroup', function($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin() && !$_SESSION['user']->isManageGroupsEnabled()) {
            return $response->withStatus(401);
        }
        
        $cartPayload = (object)$request->getParsedBody();
        $group = new Group();
        $group->setName($cartPayload->groupName);
        $group->save();
        
        Cart::EmptyToNewGroup($group->getId());
        
        echo $group->toJSON();
    });
    
    
    
    $this->post('/removeGroup', function($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $cartPayload = (object)$request->getParsedBody();
        Cart::RemoveGroup($cartPayload->Group);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '.gettext('records(s) successfully deleted from the selected Group.')
        ]);
    });
    
    $this->post('/removeStudentGroup', function($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $cartPayload = (object)$request->getParsedBody();
        Cart::RemoveStudents($cartPayload->Group);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '.gettext('records(s) successfully deleted from the selected Group.')
        ]);
    });
    
    $this->post('/removeTeacherGroup', function($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $cartPayload = (object)$request->getParsedBody();
        Cart::RemoveTeachers($cartPayload->Group);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '.gettext('records(s) successfully deleted from the selected Group.')
        ]);
    });
    
    

    
    $this->post('/delete', function($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
            return $response->withStatus(401);
        }
        
        $cartPayload = (object)$request->getParsedBody();
        if ( isset ($cartPayload->Persons) && count($cartPayload->Persons) > 0 )
        {
          Cart::DeletePersonArray($cartPayload->Persons);
        }
        else
        {
          $sMessage = gettext('Your cart is empty');
          if(sizeof($_SESSION['aPeopleCart'])>0) {
              Cart::DeletePersonArray ($_SESSION['aPeopleCart']);
              //$_SESSION['aPeopleCart'] = [];
          }
        }
        
        if (!empty($_SESSION['aPeopleCart'])) {
          $sMessage = gettext("You can't delete admin through the cart");
          $status = "failure";
        } else {
          $sMessage = gettext('Your cart and CRM has been successfully deleted');
          $status = "success";
        }
        
        return $response->withJson([
            'status' => $status,
            'message' => $sMessage
        ]);
    });

    /**
     * delete. This will empty the cart
     */
    $this->delete('/', function ($request, $response, $args) {
      
        $cartPayload = (object)$request->getParsedBody();
        if ( isset ($cartPayload->Persons) && count($cartPayload->Persons) > 0 )
        {
          Cart::RemovePersonArray($cartPayload->Persons);
        }
        else
        {
          $sMessage = gettext('Your cart is empty');
          if(sizeof($_SESSION['aPeopleCart'])>0) {
              $_SESSION['aPeopleCart'] = [];
              $sMessage = gettext('Your cart has been successfully emptied');
          }
        }
        return $response->withJson([
            'status' => "success",
            'message' =>$sMessage
        ]);

    });

});
