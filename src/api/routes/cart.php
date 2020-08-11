<?php

use EcclesiaCRM\Service\SundaySchoolService;
use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Group;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;

use Propel\Runtime\Propel;

$app->group('/cart', function () {

/*
 * @! Get all people in Cart
 */
    $this->get('/', 'getAllPeopleInCart' );
/*
 * @! Get user info by id
 * #! param: ref->array :: Persons id in array ref
 * #! param: id->int :: Family id
 * #! param: id->int :: Group id
 * #! param: id->int :: removeFamily id
 * #! param: id->int :: studentGroup id
 * #! param: id->int :: teacherGroup id
 */
    $this->post('/', 'cartOperation' );
    $this->post('/interectPerson', 'cartIntersectPersons' );
    $this->post('/emptyToGroup', 'emptyCartToGroup' );
    $this->post('/emptyToEvent', 'emptyCartToEvent' );
    $this->post('/emptyToNewGroup', 'emptyCartToNewGroup' );
    $this->post('/removeGroup', 'removeGroupFromCart' );
    $this->post('/removeGroups', 'removeGroupsFromCart' );
    $this->post('/removeStudentGroup', 'removeStudentsGroupFromCart' );
    $this->post('/removeTeacherGroup', 'removeTeachersGroupFromCart' );
    $this->post('/addAllStudents', 'addAllStudentsToCart' );
    $this->post('/addAllTeachers', 'addAllTeachersToCart' );
    $this->post('/removeAllStudents', 'removeAllStudentsFromCart' );
    $this->post('/removeAllTeachers', 'removeAllTeachersFromCart' );
    $this->post('/delete', 'deletePersonCart' );
    $this->post('/deactivate', 'deactivatePersonCart' );

/*
 * @! Remove all People in the Cart
 */
    $this->delete('/', 'removePersonCart' );

});

function getAllPeopleInCart (Request $request, Response $response, array $args) {
  return $response->withJSON(['PeopleCart' =>  Cart::PeopleInCart(), 'FamiliesCart' => Cart::FamiliesInCart(), 'GroupsCart' => Cart::GroupsInCart()]);
}

function cartIntersectPersons ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }

      $cartPayload = (object)$request->getParsedBody();

      if ( isset ($cartPayload->Persons) )
      {
        Cart::IntersectArrayWithPeopleCart($cartPayload->Persons);

        return $response->withJson(['status' => "success", "cart" => $_SESSION['aPeopleCart']]);
      }

      return $response->withJson(['status' => "failed"]);
}

function cartOperation ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
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
      elseif ( isset ($cartPayload->Families) )
      {
          foreach ($cartPayload->Families as $familyID) {
              Cart::AddFamily($familyID);
          }
      }
      elseif ( isset ($cartPayload->Group) )
      {
        Cart::AddGroup($cartPayload->Group);
      }
      elseif ( isset ($cartPayload->Groups) )
      {
          foreach ($cartPayload->Groups as $groupID) {
              Cart::AddGroup($groupID);
          }
      }
      elseif ( isset ($cartPayload->removeFamily) )
      {
        Cart::RemoveFamily($cartPayload->removeFamily);
      }
      elseif ( isset ($cartPayload->removeFamilies) )
      {
          foreach ($cartPayload->removeFamilies as $famID) {
              Cart::RemoveFamily($famID);
          }
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
        throw new \Exception(_("POST to cart requires a Persons array, FamilyID, or GroupID"),500);
      }
      return $response->withJson(['status' => "success", "cart" => $_SESSION['aPeopleCart']]);
  }

function emptyCartToGroup ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $cartPayload = (object)$request->getParsedBody();
    Cart::EmptyToGroup($cartPayload->groupID, $cartPayload->groupRoleID);
    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully added to selected Group.')
    ]);
}

function emptyCartToEvent ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $cartPayload = (object)$request->getParsedBody();
    Cart::EmptyToEvent($cartPayload->eventID);
    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully added to selected Group.')
    ]);
}

function emptyCartToNewGroup ($request, $response, $args) {
    if (!SessionUser::getUser()->isAdmin() && !SessionUser::getUser()->isManageGroupsEnabled()) {
        return $response->withStatus(401);
    }

    $cartPayload = (object)$request->getParsedBody();
    $group = new Group();
    $group->setName($cartPayload->groupName);
    $group->save();

    Cart::EmptyToNewGroup($group->getId());

    echo $group->toJSON();
}

function removeGroupFromCart($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $cartPayload = (object)$request->getParsedBody();
    Cart::RemoveGroup($cartPayload->Group);
    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}

function removeGroupsFromCart($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $cartPayload = (object)$request->getParsedBody();
    foreach ($cartPayload->Groups as $groupID) {
        Cart::RemoveGroup($groupID);
    }
    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}

function addAllStudentsToCart ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $sundaySchoolService = new SundaySchoolService();

    $classes = $sundaySchoolService->getClassStats();

    foreach ($classes as $class) {
        Cart::AddStudents($class['id']);
    }

    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}

function removeAllStudentsFromCart ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $sundaySchoolService = new SundaySchoolService();

    $classes = $sundaySchoolService->getClassStats();

    foreach ($classes as $class) {
        Cart::RemoveStudents($class['id']);
    }

    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}




function removeStudentsGroupFromCart ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $cartPayload = (object)$request->getParsedBody();
    Cart::RemoveStudents($cartPayload->Group);
    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}

function addAllTeachersToCart ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $sundaySchoolService = new SundaySchoolService();
    $classes = $sundaySchoolService->getClassStats();

    foreach ($classes as $class) {
        Cart::AddTeachers($class['id']);
    }

    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}

function removeAllTeachersFromCart ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $sundaySchoolService = new SundaySchoolService();

    $classes = $sundaySchoolService->getClassStats();

    foreach ($classes as $class) {
        Cart::RemoveTeachers($class['id']);
    }

    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}



function removeTeachersGroupFromCart ($request, $response, $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $iCount = Cart::CountPeople();

    $cartPayload = (object)$request->getParsedBody();
    Cart::RemoveTeachers($cartPayload->Group);
    return $response->withJson([
        'status' => "success",
        'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
    ]);
}

function deletePersonCart ($request, $response, $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }

    $cartPayload = (object)$request->getParsedBody();
    if ( isset ($cartPayload->Persons) && count($cartPayload->Persons) > 0 )
    {
      Cart::DeletePersonArray($cartPayload->Persons);
    }
    else
    {
      $sMessage = _('Your cart is empty');
      if(sizeof($_SESSION['aPeopleCart'])>0) {
          Cart::DeletePersonArray ($_SESSION['aPeopleCart']);
          //$_SESSION['aPeopleCart'] = [];
      }
    }

    if (!empty($_SESSION['aPeopleCart'])) {
      $sMessage = _("You can't delete admin through the cart");
      $status = "failure";
    } else {
      $sMessage = _('Your cart and CRM has been successfully deleted');
      $status = "success";
    }

    return $response->withJson([
        'status' => $status,
        'message' => $sMessage
    ]);
}

function deactivatePersonCart ($request, $response, $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }

    $cartPayload = (object)$request->getParsedBody();
    if ( isset ($cartPayload->Persons) && count($cartPayload->Persons) > 0 )
    {
      Cart::DeactivatePersonArray($cartPayload->Persons);
    }
    else
    {
      $sMessage = _('Your cart is empty');
      if(sizeof($_SESSION['aPeopleCart'])>0) {
          Cart::DeactivatePersonArray ($_SESSION['aPeopleCart']);
          //$_SESSION['aPeopleCart'] = [];
      }
    }

    if (!empty($_SESSION['aPeopleCart'])) {
      $sMessage = _("You can't deactivate admin through the cart");
      $status = "failure";
    } else {
      $sMessage = _('Your cart and CRM has been successfully deactivated');
      $status = "success";
    }

    return $response->withJson([
        'status' => $status,
        'message' => $sMessage
    ]);
}


function removePersonCart ($request, $response, $args) {

    $cartPayload = (object)$request->getParsedBody();

    $sEmailLink = [];
    $sPhoneLink = '';
    $sPhoneLinkSMS = '';

    $count = $cartPayload->Persons;

    if ( isset ($cartPayload->Persons) && count($cartPayload->Persons) > 0 )
    {
        Cart::RemovePersonArray($cartPayload->Persons);

        if (Cart::CountPeople() > 0) {
            $connection = Propel::getConnection();

            // we get the emails for the /v2/cart/view
            $sSQL = "SELECT per_Email, fam_Email
                        FROM person_per
                        LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
                        LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
                        LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
                    WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

            $statementEmails = $connection->prepare($sSQL);
            $statementEmails->execute();

            $sEmailLink = '';
            while ($row = $statementEmails->fetch( \PDO::FETCH_BOTH )) {
                $sEmail = MiscUtils::SelectWhichInfo($row['per_Email'], $row['fam_Email'], false);
                if ($sEmail) {
                    /* if ($sEmailLink) // Don't put delimiter before first email
                        $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
                    // Add email only if email address is not already in string
                    if (!stristr($sEmailLink, $sEmail)) {
                        $sEmailLink .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                    }
                }
            }

            $sEmailLink = mb_substr($sEmailLink, 0, -1);

            //Text Cart Link
            $sSQL = "SELECT per_CellPhone, fam_CellPhone
                            FROM person_per LEFT
                            JOIN family_fam ON person_per.per_fam_ID = family_fam.fam_ID
                        WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

            $statement = $connection->prepare($sSQL);
            $statement->execute();

            $sCommaDelimiter = ', ';

            while ($row = $statement->fetch( \PDO::FETCH_BOTH )) {
                $sPhone = MiscUtils::SelectWhichInfo($row['per_CellPhone'], $row['fam_CellPhone'], false);
                if ($sPhone) {
                    /* if ($sPhoneLink) // Don't put delimiter before first phone
                        $sPhoneLink .= $sCommaDelimiter;  */
                    // Add phone only if phone is not already in string
                    if (!stristr($sPhoneLink, $sPhone)) {
                        $sPhoneLink .= $sPhone.$sCommaDelimiter;
                        $sPhoneLinkSMS .= $sPhone.$sCommaDelimiter;
                    }
                }
            }

            $sPhoneLink = mb_substr($sPhoneLink, 0, -2);
        }
    }
    else
    {
      $sMessage = _('Your cart is empty');
      if(sizeof($_SESSION['aPeopleCart'])>0) {
          $_SESSION['aPeopleCart'] = [];
          $sMessage = _('Your cart has been successfully emptied');
      }
    }

    return $response->withJson([
        'status' => "success",
        'message' =>$sMessage,
        'sEmailLink' => $sEmailLink,
        'sPhoneLink' => $sPhoneLink,
        'sPhoneLinkSMS' =>$sPhoneLinkSMS
    ]);

}
