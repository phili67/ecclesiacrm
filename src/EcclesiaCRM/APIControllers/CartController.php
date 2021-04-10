<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\Group;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;

use Propel\Runtime\Propel;

class CartController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllPeopleInCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->withJSON(['PeopleCart' =>  Cart::PeopleInCart(), 'FamiliesCart' => Cart::FamiliesInCart(), 'GroupsCart' => Cart::GroupsInCart()]);
    }

    public function cartIntersectPersons (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function cartOperation (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function emptyCartToGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function emptyCartToEvent (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function emptyCartToNewGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isAdmin() && !SessionUser::getUser()->isManageGroupsEnabled()) {
            return $response->withStatus(401);
        }

        $cartPayload = (object)$request->getParsedBody();
        $group = new Group();
        $group->setName($cartPayload->groupName);
        $group->save();

        Cart::EmptyToNewGroup($group->getId());

        return $response->write($group->toJSON());
    }

    public function removeGroupFromCart(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function removeGroupsFromCart(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function addAllStudentsToCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $iCount = Cart::CountPeople();

        $sundaySchoolService = $this->container->get('SundaySchoolService');

        $classes = $sundaySchoolService->getClassStats();

        foreach ($classes as $class) {
            Cart::AddStudents($class['id']);
        }

        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
        ]);
    }

    public function removeAllStudentsFromCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $iCount = Cart::CountPeople();

        $sundaySchoolService = $this->container->get('SundaySchoolService');

        $classes = $sundaySchoolService->getClassStats();

        foreach ($classes as $class) {
            Cart::RemoveStudents($class['id']);
        }

        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
        ]);
    }

    public function removeStudentsGroupFromCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function addAllTeachersToCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $iCount = Cart::CountPeople();

        $sundaySchoolService = $this->container->get('SundaySchoolService');
        $classes = $sundaySchoolService->getClassStats();

        foreach ($classes as $class) {
            Cart::AddTeachers($class['id']);
        }

        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
        ]);
    }

    public function removeAllTeachersFromCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $iCount = Cart::CountPeople();

        $sundaySchoolService = $this->container->get('SundaySchoolService');

        $classes = $sundaySchoolService->getClassStats();

        foreach ($classes as $class) {
            Cart::RemoveTeachers($class['id']);
        }

        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '._('records(s) successfully deleted from the selected Group.')
        ]);
    }

    public function removeTeachersGroupFromCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function deletePersonCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function deactivatePersonCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function removePersonCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $cartPayload = (object)$request->getParsedBody();

        $sEmailLink = [];
        $sPhoneLink = '';
        $sPhoneLinkSMS = '';

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
}
