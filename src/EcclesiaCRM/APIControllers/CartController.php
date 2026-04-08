<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\PersonQuery;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\Group;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;

use Propel\Runtime\Propel;

class CartController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllPeopleInCart (ServerRequest $request, Response $response, array $args): Response {
        return $response->withJSON(['PeopleCart' =>  Cart::PeopleInCart(), 'FamiliesCart' => Cart::FamiliesInCart(), 'GroupsCart' => Cart::GroupsInCart()]);
    }

    public function cartIntersectPersons (ServerRequest $request, Response $response, array $args): Response {
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

    public function cartOperation (ServerRequest $request, Response $response, array $args): Response {
        if ( !( (SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isAddRecordsEnabled() ) && SessionUser::getUser()->isShowCartEnabled())) {
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

    public function emptyCartToGroup (ServerRequest $request, Response $response, array $args): Response {
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

    public function emptyCartToEvent (ServerRequest $request, Response $response, array $args): Response {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isAddRecordsEnabled() || SessionUser::getUser()->isShowCartEnabled() )) {
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

    public function emptyCartToNewGroup (ServerRequest $request, Response $response, array $args): Response {
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

    public function removeGroupFromCart(ServerRequest $request, Response $response, array $args): Response {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isShowCartEnabled())) {
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

    public function removeGroupsFromCart(ServerRequest $request, Response $response, array $args): Response {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isShowCartEnabled())) {
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

    public function addAllStudentsToCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function removeAllStudentsFromCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function removeStudentsGroupFromCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function addAllTeachersToCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function removeAllTeachersFromCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function removeTeachersGroupFromCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function deletePersonCart (ServerRequest $request, Response $response, array $args): Response {
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

    public function deactivatePersonCart (ServerRequest $request, Response $response, array $args): Response {
        if (!SessionUser::getUser()->isDeleteRecordsEnabled()) {
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

    public function removePersonCart (ServerRequest $request, Response $response, array $args): Response {

        $cartPayload = (object)$request->getParsedBody();

        $sEmailLink = [];
        $sPhoneLink = '';
        $sPhoneLinkSMS = '';
        $sMessage   = '';

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
                    WHERE per_DateDeactivated IS NULL AND per_ID NOT IN (
                        SELECT per_ID 
                        FROM person_per 
                        INNER JOIN record2property_r2p ON r2p_record_ID = per_ID 
                        INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email') 
                        AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

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
                        WHERE per_DateDeactivated IS NULL AND per_ID NOT IN (
                            SELECT per_ID FROM person_per 
                            INNER JOIN record2property_r2p ON r2p_record_ID = per_ID 
                            INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS') 
                            AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

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

            $returnedCart = $_SESSION['aPeopleCart'];
        }
        else
        {
            $sMessage = _('Your cart is empty');
            if(sizeof($_SESSION['aPeopleCart'])>0) {
                $returnedCart = $_SESSION['aPeopleCart'];
                $_SESSION['aPeopleCart'] = [];
                $sMessage = _('Your cart has been successfully emptied');
            }
        }

        return $response->withJson([
            'status' => "success",
            'message' =>$sMessage,
            'sEmailLink' => $sEmailLink,
            'sPhoneLink' => $sPhoneLink,
            'sPhoneLinkSMS' =>$sPhoneLinkSMS,
            'cartPeople' => $returnedCart,
            'PeopleCart' => Cart::PeopleInCart(),
            'FamiliesCart' => Cart::FamiliesInCart(),
            'GroupsCart' => Cart::GroupsInCart(),
            'currentPageName' => SessionUser::getCurrentPageName()
        ]);
    }

    public function addressBook (ServerRequest $request, Response $response, array $args): Response {
        $people = Cart::PeopleInCart();

        $output = '';

        foreach ($people as $personId) {
            $person = PersonQuery::create()->findOneById($personId);

            $output .= $person->getVCard();
        }

        $filename = "cart-export.vcf";

        $size = strlen($output);

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Length',$size)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Expires', '0');

        $response->getBody()->write($output);
        return $response;
    }


    public function addVolunteers (ServerRequest $request, Response $response, array $args): Response 
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isShowCartEnabled())) {
            return $response->withStatus(401);
        }

        $iCount = Cart::CountPeople();

        $cartPayload = (object)$request->getParsedBody();
        Cart::addVolunteers($cartPayload->VolID);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '._('records(s) successfully deleted from the selected volunteers.')
        ]); 
        
        return $response;
    }
    /*
     * @! Remove all volunteers members Ids from the cart
     * #! param: ref->int :: VolID (Id)
     */

    public function removeVolunteers (ServerRequest $request, Response $response, array $args): Response {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isShowCartEnabled())) {
            return $response->withStatus(401);
        }

        $iCount = Cart::CountPeople();

        $cartPayload = (object)$request->getParsedBody();
        Cart::RemoveVolunteers($cartPayload->VolID);
        return $response->withJson([
            'status' => "success",
            'message' => $iCount.' '._('records(s) successfully deleted from the selected volunteers.')
        ]);        
    }

    public function renderBadgePreview (ServerRequest $request, Response $response, array $args): Response
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isCreateDirectoryEnabled() || SessionUser::getUser()->isShowCartEnabled())) {
            return $response->withStatus(401);
        }

        $values = (object)$request->getParsedBody();

        if (!isset($values->mainTitle) || !isset($values->secondTitle) || !isset($values->thirdTitle)
            || !isset($values->title) || !isset($values->back) || !isset($values->labeltype)
            || !isset($values->labelfont) || !isset($values->labelfontsize)
            || !isset($values->imageName) || !isset($values->imagePosition)) {
            return $response->withJson(['success' => false]);
        }

        $people = Cart::PeopleInCart();
        if (count($people) == 0) {
            return $response->withJson(['success' => false, 'message' => _('Your cart is empty')]);
        }

        $person = PersonQuery::create()->findOneById($people[0]);
        if (is_null($person)) {
            return $response->withJson(['success' => false, 'message' => _('No person found for preview')]);
        }

        $mainTitle = htmlspecialchars(InputUtils::FilterString($values->mainTitle), ENT_QUOTES, 'UTF-8');
        $secondTitle = htmlspecialchars(InputUtils::FilterString($values->secondTitle), ENT_QUOTES, 'UTF-8');
        $thirdTitle = htmlspecialchars(InputUtils::FilterString($values->thirdTitle), ENT_QUOTES, 'UTF-8');
        $lastName = htmlspecialchars($person->getLastName(), ENT_QUOTES, 'UTF-8');
        $firstName = htmlspecialchars($person->getFirstName(), ENT_QUOTES, 'UTF-8');

        $titleColor = InputUtils::LegacyFilterInput($values->title, 'char', 16);
        $backColor = InputUtils::LegacyFilterInput($values->back, 'char', 16);
        $labelType = InputUtils::LegacyFilterInput($values->labeltype, 'char', 20);
        $imageName = InputUtils::LegacyFilterInput($values->imageName, 'char', 255);
        $imagePosition = InputUtils::LegacyFilterInput($values->imagePosition, 'char', 20);

        $labelTypeSizes = [
            'Tractor' => ['width' => 120.0, 'height' => 26.5],
            'Badge' => ['width' => 70.0, 'height' => 40.0],
            'Badge2' => ['width' => 77.0, 'height' => 48.0],
            '3670' => ['width' => 64.0, 'height' => 34.0],
            '5160' => ['width' => 66.675, 'height' => 25.4],
            '5161' => ['width' => 101.6, 'height' => 25.4],
            '5162' => ['width' => 100.807, 'height' => 34.0],
            '5163' => ['width' => 101.6, 'height' => 50.8],
            '5164' => ['width' => 4.0, 'height' => 3.33],
            '8600' => ['width' => 66.6, 'height' => 25.4],
            '74536' => ['width' => 102.0, 'height' => 76.0],
            'L7163' => ['width' => 99.1, 'height' => 38.1],
            'C32019' => ['width' => 85.0, 'height' => 54.0],
        ];

        $baseWidth = 420;
        $baseHeight = 260;
        $sizeDef = $labelTypeSizes[$labelType] ?? $labelTypeSizes['Tractor'];
        $ratio = $sizeDef['width'] / $sizeDef['height'];

        $renderWidth = $baseWidth;
        $renderHeight = (int)round($renderWidth / $ratio);
        if ($renderHeight > $baseHeight) {
            $renderHeight = $baseHeight;
            $renderWidth = (int)round($renderHeight * $ratio);
        }

        $sx = function ($v) use ($renderWidth, $baseWidth) {
            return (int)round(($v * $renderWidth) / $baseWidth);
        };
        $sy = function ($v) use ($renderHeight, $baseHeight) {
            return (int)round(($v * $renderHeight) / $baseHeight);
        };

        $imgHref = '';
        if (!empty($imageName)) {
            $safeImageName = basename($imageName);
            $imagePath = SystemURLs::getDocumentRoot() . '/Images/background/' . $safeImageName;

            if (file_exists($imagePath) && is_file($imagePath)) {
                $ext = strtolower(pathinfo($safeImageName, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $imgBlob = @file_get_contents($imagePath);
                if ($imgBlob !== false) {
                    $imgHref = 'data:' . $mime . ';base64,' . base64_encode($imgBlob);
                }
            }
        }

        $imageMarkup = '';
        if (!empty($imgHref)) {
            if ($imagePosition === 'Cover') {
                $imageMarkup = '<image href="' . $imgHref . '" x="0" y="0" width="' . $renderWidth . '" height="' . $renderHeight . '" preserveAspectRatio="xMidYMid slice" />';
            } else {
                $xPos = $sx(14);
                $yPos = $sy(24);
                $imgWidth = $sx(130);
                $imgHeight = $sy(90);
                $imageRatio = 'xMidYMid meet';
                $stripeWidth = max(1, (int)round($renderWidth * (14 / $sizeDef['width'])));

                if ($imagePosition === 'Left') {
                    $xPos = 0;
                    $yPos = 0;
                    $imgWidth = $stripeWidth;
                    $imgHeight = $renderHeight;
                    $imageRatio = 'none';
                } elseif ($imagePosition === 'Right') {
                    $xPos = max(0, $renderWidth - $stripeWidth);
                    $yPos = 0;
                    $imgWidth = $stripeWidth;
                    $imgHeight = $renderHeight;
                    $imageRatio = 'none';
                }
                if ($imagePosition === 'Center') {
                    $xPos = max(0, (int)round(($renderWidth - $imgWidth) / 2));
                }
                $imageMarkup = '<image href="' . $imgHref . '" x="' . $xPos . '" y="' . $yPos . '" width="' . $imgWidth . '" height="' . $imgHeight . '" preserveAspectRatio="' . $imageRatio . '" />';
            }
        }

        $radius = max(2, (int)round(18 * min($renderWidth / $baseWidth, $renderHeight / $baseHeight)));
        $cx = (int)round($renderWidth / 2);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $renderWidth . '" height="' . $renderHeight . '" viewBox="0 0 ' . $renderWidth . ' ' . $renderHeight . '">'
            . '<rect x="0" y="0" width="' . $renderWidth . '" height="' . $renderHeight . '" rx="' . $radius . '" ry="' . $radius . '" fill="' . $backColor . '" />'
            . $imageMarkup
            . '<text x="' . $cx . '" y="' . $sy(52) . '" text-anchor="middle" font-size="' . max(9, $sy(22)) . '" font-weight="700" fill="' . $titleColor . '" font-family="Arial, sans-serif">' . $mainTitle . '</text>'
            . '<text x="' . $cx . '" y="' . $sy(82) . '" text-anchor="middle" font-size="' . max(8, $sy(18)) . '" fill="' . $titleColor . '" font-family="Arial, sans-serif">' . $secondTitle . '</text>'
            . '<text x="' . $cx . '" y="' . $sy(108) . '" text-anchor="middle" font-size="' . max(8, $sy(16)) . '" fill="' . $titleColor . '" font-family="Arial, sans-serif">' . $thirdTitle . '</text>'
            . '<text x="' . $cx . '" y="' . $sy(180) . '" text-anchor="middle" font-size="' . max(10, $sy(28)) . '" font-weight="700" fill="' . $titleColor . '" font-family="Arial, sans-serif">' . $lastName . '</text>'
            . '<text x="' . $cx . '" y="' . $sy(214) . '" text-anchor="middle" font-size="' . max(9, $sy(24)) . '" fill="' . $titleColor . '" font-family="Arial, sans-serif">' . $firstName . '</text>'
            . '</svg>';

        return $response->withJson(['success' => true, 'imgData' => 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg)]);
    }
}
