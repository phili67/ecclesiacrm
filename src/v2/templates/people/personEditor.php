<?php
/*******************************************************************************
 *
 *  filename    : templates/personEditor.php
 *  last change : 2023-06-18
 * 
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-2005 Michael Wilt
 *                2023 Philippe Logel
 * 
 ******************************************************************************/

 use EcclesiaCRM\dto\SystemConfig;
 use EcclesiaCRM\Utils\InputUtils;
 use EcclesiaCRM\Utils\OutputUtils;
 use EcclesiaCRM\Utils\MiscUtils;
 use EcclesiaCRM\Utils\LoggerUtils;
 use EcclesiaCRM\Emails\NewPersonOrFamilyEmail;
 use EcclesiaCRM\PersonQuery;
 use EcclesiaCRM\Person;
 use EcclesiaCRM\dto\Photo;
 use EcclesiaCRM\dto\SystemURLs;
 use EcclesiaCRM\FamilyQuery;
 use EcclesiaCRM\Family;
 use EcclesiaCRM\ListOptionQuery;
 use EcclesiaCRM\PersonCustomQuery;
 use EcclesiaCRM\PersonCustom;
 use EcclesiaCRM\PersonCustomMasterQuery;
 use EcclesiaCRM\dto\StateDropDown;
 use EcclesiaCRM\dto\CountryDropDown;
 use EcclesiaCRM\utils\RedirectUtils;
 use EcclesiaCRM\SessionUser;
 use EcclesiaCRM\UserQuery;
 use EcclesiaCRM\dto\CanvassUtilities;
 
 use Propel\Runtime\Propel;


// Security: User must have Add or Edit Records permission to use this form in those manners
// Clean error handling: (such as somebody typing an incorrect URL ?PersonID= manually)
if ($iPersonID > 0) {
    $person = PersonQuery::Create()
        ->findOneById($iPersonID);

    if (empty($person)) {
        RedirectUtils::Redirect('v2/dashboard');
        exit();
    }

    if ($person->getDateDeactivated() != null && !SessionUser::getUser()->isGdrpDpoEnabled()) {
        RedirectUtils::Redirect('members/404.php?type=Person');
    }

    if (!(
        SessionUser::getUser()->isEditRecordsEnabled() ||
        (SessionUser::getUser()->isEditSelfEnabled() && $iPersonID == SessionUser::getUser()->getPersonId()) ||
        (SessionUser::getUser()->isEditSelfEnabled() && $person->getFamId() == SessionUser::getUser()->getPerson()->getFamId())
    )
    ) {
        RedirectUtils::Redirect('v2/dashboard');
        exit;
    }
} elseif (!SessionUser::getUser()->isAddRecordsEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

// All the custom fields
$ormCustomFields = PersonCustomMasterQuery::Create()
    ->orderByCustomOrder()
    ->find();

// only the left custom fields
$ormLeftCustomFields = PersonCustomMasterQuery::Create()
    ->orderByCustomOrder()
    ->filterByCustomSide('left')
    ->find()->toArray();

// only the right custom fields
$ormRightCustomFields = PersonCustomMasterQuery::Create()
    ->orderByCustomOrder()
    ->filterByCustomSide('right')
    ->find()->toArray();

$numLeftCustomFields = count($ormLeftCustomFields);
$numRightCustomFields = count($ormRightCustomFields);

$maxCustomFields = max($numRightCustomFields, $numLeftCustomFields);

$numCustomFields = $numRightCustomFields + $numLeftCustomFields;

// Get the lists of canvassers
$canvassers = CanvassUtilities::CanvassGetCanvassers('Canvassers');
$braveCanvassers = CanvassUtilities::CanvassGetCanvassers('BraveCanvassers');


//Initialize the error flag
$bErrorFlag = false;
$sFirstNameError = '';

$sMiddleNameError = '';
$sLastNameError = '';
$sEmailError = '';
$sWorkEmailError = '';
$sBirthDateError = '';
$sBirthYearError = '';
$sFriendDateError = '';
$sMembershipDateError = '';
$aCustomErrors = [];

$fam_Country = '';

$bNoFormat_HomePhone = false;
$bNoFormat_WorkPhone = false;
$bNoFormat_CellPhone = false;
$bFacebookID = $sFacebookError = 0;
$sTwitter = $sTwitterError = 0;
$sLinkedIn = $sLinkedInError = 0;
$type_ID = 0;


//Is this the second pass?
if (isset($_POST['PersonSubmit']) || isset($_POST['PersonSubmitAndAdd'])) {
    //Get all the variables from the request object and assign them locally

    $sTitle = InputUtils::LegacyFilterInput($_POST['Title']);
    $sFirstName = InputUtils::FilterString($_POST['FirstName']);
    $sMiddleName = InputUtils::FilterString($_POST['MiddleName']);
    $sLastName = InputUtils::FilterString($_POST['LastName']);
    $sSuffix = InputUtils::FilterString($_POST['Suffix']);
    $iGender = InputUtils::LegacyFilterInput($_POST['Gender'], 'int');

    // Person address stuff is normally surpressed in favor of family address info
    $sAddress1 = '';
    $sAddress2 = '';

    $sCity = '';
    $sZip = '';
    $sCountry = '';
    if (array_key_exists('Address1', $_POST)) {
        $sAddress1 = InputUtils::FilterString($_POST['Address1']);
    }
    if (array_key_exists('Address2', $_POST)) {
        $sAddress2 = InputUtils::FilterString($_POST['Address2']);
    }
    if (array_key_exists('City', $_POST)) {
        $sCity = InputUtils::FilterString($_POST['City']);
    }
    if (array_key_exists('Zip', $_POST)) {
        $sZip = InputUtils::LegacyFilterInput($_POST['Zip']);
    }

    if (SessionUser::getUser()->isCanvasserEnabled()) { // Only take modifications to this field if the current user is a canvasser
        $bOkToCanvass = isset($_POST['OkToCanvass']);
        $iCanvasser = 0;
        if (array_key_exists('Canvasser', $_POST)) {
            $iCanvasser = InputUtils::LegacyFilterInput($_POST['Canvasser']);
        }
        if ((!$iCanvasser) && array_key_exists('BraveCanvasser', $_POST)) {
            $iCanvasser = InputUtils::LegacyFilterInput($_POST['BraveCanvasser']);
        }
        if (!$iCanvasser) {
            $iCanvasser = 0;
        }
    }

    // Person address stuff is normally surpressed in favor of family address info
    $sFamName = '';
    $sFamAddress1 = '';
    $sFamAddress2 = '';
    $sFamCity = '';
    $sFamZip = '';
    $sFamState = '';
    $sFamCountry = '';
    $bSendNewsLetter = isset($_POST['SendNewsLetter']);

    if ($bSendNewsLetter) {
        $bSendNewsLetterString = "TRUE";
    } else {
        $bSendNewsLetterString = "FALSE";
    }
    if ($bOkToCanvass) {
        $bOkToCanvassString = "TRUE";
    } else {
        $bOkToCanvassString = "FALSE";
    }
    if (array_key_exists('FamName', $_POST)) {
        $sFamName = InputUtils::FilterString($_POST['FamName']);
        if ($sFamName == "") {
            $sFamName = $sLastName;
        }
    }
    if (array_key_exists('FamAddress1', $_POST)) {
        $sFamAddress1 = InputUtils::FilterString($_POST['FamAddress1']);
    }
    if (array_key_exists('FamAddress2', $_POST)) {
        $sFamAddress2 = InputUtils::FilterString($_POST['FamAddress2']);
    }
    if (array_key_exists('FamCity', $_POST)) {
        $sFamCity = InputUtils::FilterString($_POST['FamCity']);
    }
    if (array_key_exists('FamZip', $_POST)) {
        $sFamZip = InputUtils::LegacyFilterInput($_POST['FamZip']);
    }
    if (array_key_exists('FamState', $_POST)) {
        $sFamState = InputUtils::FilterString($_POST['FamState']);
    }

    // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
    if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
        $sFamZip = strtoupper($sFamZip);
    }

    if (array_key_exists('FamCountry', $_POST)) {
        $sFamCountry = InputUtils::FilterString($_POST['FamCountry']);
    }

    $iFamily = InputUtils::LegacyFilterInput($_POST['Family'], 'int');
    $iFamilyRole = InputUtils::LegacyFilterInput($_POST['FamilyRole'], 'int');

    // Get their family's country in case person's country was not entered
    if ($iFamily > 0) {
        $fam = FamilyQuery::Create()->findOneById($iFamily);

        $fam_Country = $fam->getCountry();
    }

    $sCountryTest = MiscUtils::SelectWhichInfo($sCountry, $fam_Country, false);
    $sState = '';
    if ($sCountryTest == 'United States' || $sCountryTest == 'Canada') {
        if (array_key_exists('State', $_POST)) {
            $sState = InputUtils::FilterString($_POST['State']);
        }
    } else {
        if (array_key_exists('StateTextbox', $_POST)) {
            $sState = InputUtils::LegacyFilterInput($_POST['StateTextbox']);
        }
    }

    $sHomePhone = InputUtils::LegacyFilterInput($_POST['HomePhone']);
    $sWorkPhone = InputUtils::LegacyFilterInput($_POST['WorkPhone']);
    $sCellPhone = InputUtils::LegacyFilterInput($_POST['CellPhone']);
    $sEmail = InputUtils::LegacyFilterInput($_POST['Email']);
    $sWorkEmail = InputUtils::LegacyFilterInput($_POST['WorkEmail']);

    $sBirthDayDate = new DateTime(InputUtils::FilterDate($_POST['BirthDayDate']));

    $iBirthMonth = $sBirthDayDate->format('m');
    $iBirthDay = $sBirthDayDate->format('d');
    $iBirthYear = $sBirthDayDate->format('Y');

    $bHideAge = isset($_POST['HideAge']);
    // Philippe Logel
    $dFriendDate = InputUtils::FilterDate($_POST['FriendDate']);
    $dMembershipDate = InputUtils::FilterDate($_POST['MembershipDate']);
    $iClassification = InputUtils::LegacyFilterInput($_POST['Classification'], 'int');
    $iEnvelope = 0;
    if (array_key_exists('EnvID', $_POST)) {
        $iEnvelope = InputUtils::LegacyFilterInput($_POST['EnvID'], 'int');
    }

    if (array_key_exists('updateBirthYear', $_POST)) {
        $iupdateBirthYear = InputUtils::LegacyFilterInput($_POST['updateBirthYear'], 'int');
    }
    $iFacebook = InputUtils::FilterInt($_POST['Facebook']);
    $sTwitter = InputUtils::FilterString($_POST['Twitter']);
    $sLinkedIn = InputUtils::FilterString($_POST['LinkedIn']);

    $bNoFormat_HomePhone = isset($_POST['NoFormat_HomePhone']);
    $bNoFormat_WorkPhone = isset($_POST['NoFormat_WorkPhone']);
    $bNoFormat_CellPhone = isset($_POST['NoFormat_CellPhone']);

    //Validate the Last Name.  If family selected, but no last name, inherit from family.
    if (strlen($sLastName) < 1 && !SystemConfig::getValue('bAllowEmptyLastName')) {
        if ($iFamily < 1) {
            $sLastNameError = _('You must enter a Last Name if no Family is selected.');
            $bErrorFlag = true;
        } else {
            $fam = FamilyQuery::Create()->findOneById($iFamily);
            $sLastName = $fam->getName();
        }
    }
    // If they entered a full date, see if it's valid
    if (strlen($iBirthYear) > 0) {
        if ($iBirthYear == 0) { // If zero set to NULL
            $iBirthYear = null;
        } elseif ($iBirthYear > 2155 || $iBirthYear < 1901) {
            $sBirthYearError = _('Invalid Year: allowable values are 1901 to 2155');
            $bErrorFlag = true;
        } elseif ($iBirthMonth > 0 && $iBirthDay > 0) {
            if (!checkdate($iBirthMonth, $iBirthDay, $iBirthYear)) {
                $sBirthDateError = _('Invalid Birth Date.');
                $bErrorFlag = true;
            }
        }
    }

    // Validate Friend Date if one was entered
    if (strlen($dFriendDate) > 0) {
        $dateString = InputUtils::parseAndValidateDate($dFriendDate, $locale = 'US', $pasfut = 'past');
        if ($dateString === false) {
            $sFriendDateError = '<span style="color: red; ">'
                . _('Not a valid Friend Date') . '</span>';
            $bErrorFlag = true;
        } else {
            $dFriendDate = $dateString;
        }
    }
    // Validate Membership Date if one was entered
    if (strlen($dMembershipDate) > 0) {
        $dateString = InputUtils::parseAndValidateDate($dMembershipDate, $locale = 'US', $pasfut = 'past');
        if ($dateString === false) {
            $sMembershipDateError = '<span style="color: red; ">'
                . _('Not a valid Membership Date') . '</span>';
            $bErrorFlag = true;
        } else {
            $dMembershipDate = $dateString;
        }
    }

    // Validate Email
    if (strlen($sEmail) > 0) {
        if (MiscUtils::checkEmail($sEmail) == false) {
            $sEmailError = '<span style="color: red; ">'
                . _('Email is Not Valid') . '</span>';
            $bErrorFlag = true;
        } else {
            $sEmail = $sEmail;
        }
    }

    // Validate Work Email
    if (strlen($sWorkEmail) > 0) {
        if (MiscUtils::checkEmail($sWorkEmail) == false) {
            $sWorkEmailError = '<span style="color: red; ">'
                . _('Work Email is Not Valid') . '</span>';
            $bErrorFlag = true;
        } else {
            $sWorkEmail = $sWorkEmail;
        }
    }

    // Validate all the custom fields
    $aCustomData = [];

    foreach ($ormCustomFields as $rowCustomField) {
        if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
            $currentFieldData = InputUtils::LegacyFilterInput($_POST[$rowCustomField->getCustomField()]);

            $bErrorFlag |= !InputUtils::validateCustomField($rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $aCustomErrors);

            // assign processed value locally to $aPersonProps so we can use it to generate the form later
            $aCustomData[$rowCustomField->getCustomField()] = $currentFieldData;
        }
    }

    //If no errors, then let's update...
    if (!$bErrorFlag) {
        $sPhoneCountry = MiscUtils::SelectWhichInfo($sCountry, $fam_Country, false);

        if (!$bNoFormat_HomePhone) {
            $sHomePhone = MiscUtils::CollapsePhoneNumber($sHomePhone, $sPhoneCountry);
        }
        if (!$bNoFormat_WorkPhone) {
            $sWorkPhone = MiscUtils::CollapsePhoneNumber($sWorkPhone, $sPhoneCountry);
        }
        if (!$bNoFormat_CellPhone) {
            $sCellPhone = MiscUtils::CollapsePhoneNumber($sCellPhone, $sPhoneCountry);
        }

        //If no birth year, set to NULL
        if ((strlen($iBirthYear) != 4)) {
            $iBirthYear = 'NULL';
        } else {
            $iBirthYear = "$iBirthYear";
        }

        // New Family (add)
        // Family will be named by the Last Name of the Person
        if ($iFamily == -1) {
            $family = new Family();

            $family->setName($sFamName);
            $family->setAddress1($sFamAddress1);
            $family->setAddress2($sFamAddress2);
            $family->setCity($sFamCity);
            $family->setState($sFamState);
            $family->setZip($sFamZip);
            $family->setCountry($sFamCountry);
            $family->setHomePhone($sHomePhone);
            $family->setWorkPhone($sWorkPhone);
            $family->setCellPhone($sCellPhone);
            $family->setEmail($sEmail);
            $family->setDateEntered(date('YmdHis'));
            $family->setEnteredBy(SessionUser::getUser()->getPersonId());
            $family->save();

            $family->updateLanLng();
            $family->save();


            //Get the key back You use the same code in /v2/cart/view
            $iFamily = $family->getId();
        } else {// the Family still exist
            // in the case the family is changing we have to check if the old family has still a member.
            // maybe we have to deactivate the family
            $family = FamilyQuery::Create()
                ->findOneById($iFamily);

            // a member change to a new familly, but the name of the family, shouldn't be changed
            if (!is_null($family)) {//
                $family->setName($sFamName);
                $family->setAddress1($sFamAddress1);
                $family->setAddress2($sFamAddress2);
                $family->setCity($sFamCity);
                $family->setState($sFamState);
                $family->setZip($sFamZip);
                $family->setCountry($sFamCountry);
                $family->setHomePhone($sHomePhone);
                $family->setWorkPhone($sWorkPhone);
                $family->setCellPhone($sCellPhone);
                $family->setEmail($sEmail);
                $family->setDateEntered(date('YmdHis'));
                $family->setEnteredBy(SessionUser::getUser()->getPersonId());

                $family->updateLanLng();

                $family->save();

                $iFamily = $family->getId();
            }
        }

        if ($bHideAge) {
            $per_Flags = 1;
        } else {
            $per_Flags = 0;
        }

        // New Person (add)
        if ($iPersonID < 1) {
            $iEnvelope = 0;

            $person = new Person();
            $person->setTitle($sTitle);
            $person->setFirstName($sFirstName);
            $person->setMiddleName($sMiddleName);
            $person->setLastName($sLastName);
            $person->setSuffix($sSuffix);
            $person->setGender($iGender);
            $person->setAddress1($sAddress1);
            $person->setAddress2($sAddress2);
            $person->setCity($sCity);
            $person->setState($sState);
            $person->setZip($sZip);
            $person->setCountry($sCountry);
            $person->setHomePhone($sHomePhone);
            $person->setWorkPhone($sWorkPhone);
            $person->setCellPhone($sCellPhone);
            $person->setEmail($sEmail);
            $person->setWorkEmail($sWorkEmail);
            $person->setBirthMonth($iBirthMonth);
            $person->setBirthDay($iBirthDay);
            $person->setBirthYear($iBirthYear);
            $person->setSendNewsletter($bSendNewsLetterString);

            // bSendNewsLetterString : When you activated a single person the family is deactivated
            if ($bSendNewsLetterString == "TRUE" && $iFamily > 0 && !is_null($family)) {
                $family->setSendNewsletter("FALSE");
            }

            if (SessionUser::getUser()->isCanvasserEnabled() && !is_null($family)) {
                $family->setOkToCanvass($bOkToCanvassString);
                $family->setCanvasser($iCanvasser);
            }

            if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
                $person->setEnvelope($iEnvelope);
            }

            $person->setFamId($iFamily);
            $person->setFmrId($iFamilyRole);

            if (strlen($dMembershipDate) > 0) {
                $person->setMembershipDate($dMembershipDate);
            }

            $person->setClsId($iClassification);
            $person->setDateEntered(new DateTime());
            $person->setEnteredBy(SessionUser::getUser()->getPersonId());

            if (strlen($dFriendDate) > 0) {
                $person->setFriendDate($dFriendDate);
            }

            $person->setFlags($per_Flags);
            $person->setFacebookID($iFacebook);
            $person->setTwitter($sTwitter);
            $person->setLinkedIn($sLinkedIn);

            $person->save();

            $iPersonID = $person->getId();

            $bGetKeyBack = true;

            // Existing person (update)
        } else {
            // we change the email of the User if the user exists
            $user = UserQuery::Create()->findOneByPersonId($iPersonID);

            if (!is_null($user)) {
                $user->changePrincipalEmail($sEmail);
            }

            $person = PersonQuery::Create()
                ->findOneByID($iPersonID);

            $oldEmail = $person->getEmail();

            $person->setTitle($sTitle);
            $person->setFirstName($sFirstName);
            $person->setMiddleName($sMiddleName);
            $person->setLastName($sLastName);
            $person->setSuffix($sSuffix);
            $person->setGender($iGender);
            $person->setAddress1($sAddress1);
            $person->setAddress2($sAddress2);
            $person->setCity($sCity);
            $person->setState($sState);
            $person->setZip($sZip);
            $person->setCountry($sCountry);
            $person->setHomePhone($sHomePhone);
            $person->setWorkPhone($sWorkPhone);
            $person->setCellPhone($sCellPhone);
            $person->setNewEmail($oldEmail, $sEmail);
            $person->setWorkEmail($sWorkEmail);
            $person->setBirthMonth($iBirthMonth);
            $person->setBirthDay($iBirthDay);
            $person->setBirthYear($iBirthYear);
            $person->setSendNewsletter($bSendNewsLetterString);

            // bSendNewsLetterString : When you activated a single person the family is deactivated
            if ($bSendNewsLetterString == "TRUE" && !is_null($person->getFamily()) ) {
                $person->getFamily()->setSendNewsletter("FALSE");
            }

            if (SessionUser::getUser()->isCanvasserEnabled() && !is_null($person->getFamily()) ) {
                $person->getFamily()->setOkToCanvass($bOkToCanvassString);
                $person->getFamily()->setCanvasser($iCanvasser);
            }

            if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
                $person->setEnvelope($iEnvelope);
            }

            $person->setFamId($iFamily);
            $person->setFmrId($iFamilyRole);

            if (strlen($dMembershipDate) > 0) {
                $person->setMembershipDate($dMembershipDate);
            }

            $person->setClsId($iClassification);
            $person->setDateEntered(new DateTime());
            $person->setEnteredBy(SessionUser::getUser()->getPersonId());

            $person->setDateLastEdited(new DateTime());
            $person->setEditedBy(SessionUser::getUser()->getPersonId());

            if (strlen($dFriendDate) > 0) {
                $person->setFriendDate($dFriendDate);
            }

            $person->setFlags($per_Flags);
            $person->setFacebookID($iFacebook);
            $person->setTwitter($sTwitter);
            $person->setLinkedIn($sLinkedIn);

            $person->save();

            $bGetKeyBack = false;
        }

        $person = PersonQuery::create()->findOneByID($iPersonID);

        // the Part with note is no more useful :PL
        // If this is a new person, get the key back and insert a blank row into the person_custom table
        if ($bGetKeyBack) {
            $personCustom = new PersonCustom();

            $personCustom->setPerId($iPersonID);

            $personCustom->save();

            if (!empty(SystemConfig::getValue("sNewPersonNotificationRecipientIDs"))) {
                $person = PersonQuery::create()->findOneByID($iPersonID);
                $NotificationEmail = new NewPersonOrFamilyEmail($person);
                if (!$NotificationEmail->send()) {
                    LoggerUtils::getAppLogger()->warn($NotificationEmail->getError());
                }
            }
        }

        $photo = new Photo("Person", $iPersonID);
        $photo->refresh();

        // Update the custom person fields.
        if ($numCustomFields > 0) {
            $sSQL = '';
            foreach ($ormCustomFields as $rowCustomField) {
                if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
                    $currentFieldData = trim($aCustomData[$rowCustomField->getCustomField()]);
                    MiscUtils::sqlCustomField($sSQL, $rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $sPhoneCountry);
                }
            }

            // chop off the last 2 characters (comma and space) added in the last while loop iteration.
            if ($sSQL > '') {
                $sSQL = 'REPLACE INTO person_custom SET ' . $sSQL . ' per_ID = ' . $iPersonID;
                //Execute the SQL

                $connection = Propel::getConnection();

                $statement = $connection->prepare($sSQL);
                $statement->execute();
            }
        }

        if (isset($_POST['PersonSubmit']) && $iPersonID > 0) {
            //Send to the view of this person
            RedirectUtils::Redirect('v2/people/person/view/' . $iPersonID);
        } else {
            //Reload to editor to add another record
            RedirectUtils::Redirect('v2/people/person/editor');
        }
    }

    // Set the envelope in case the form failed.
    $per_Envelope = $iEnvelope;
} else {

    //FirstPass
    //Are we editing or adding?
    if ($iPersonID > 0) {
        //Editing....
        //Get all the data on this record
        $person = PersonQuery::create()
            ->leftJoinWithFamily()
            ->findOneById($iPersonID);

        $sTitle = $person->getTitle();
        $sFirstName = $person->getFirstName();
        $sMiddleName = $person->getMiddleName();
        $sLastName = $person->getLastName();
        $sSuffix = $person->getSuffix();
        $iGender = $person->getGender();
        $sAddress1 = $person->getAddress1();
        $sAddress2 = $person->getAddress2();
        $sCity = $person->getCity();
        $sState = $person->getState();
        $sZip = $person->getZip();
        $sCountry = $person->getCountry();
        $sHomePhone = $person->getHomePhone();
        $sWorkPhone = $person->getWorkPhone();
        $sCellPhone = $person->getCellPhone();
        $sEmail = $person->getEmail();
        $sWorkEmail = $person->getWorkEmail();
        $iBirthMonth = $person->getBirthMonth();
        $iBirthDay = $person->getBirthDay();
        $iBirthYear = $person->getBirthYear();
        $bHideAge = ($person->getFlags() & 1) != 0;
        $iOriginalFamily = $person->getFamId();
        $iFamily = $person->getFamId();
        $iFamilyRole = $person->getFmrId();
        $dMembershipDate = ($person->getMembershipDate() != null) ? $person->getMembershipDate()->format('Y-m-d') : "";
        $dFriendDate = ($person->getFriendDate() != null) ? $person->getFriendDate()->format('Y-m-d') : "";
        $iClassification = $person->getClsId();
        $iViewAgeFlag = $person->getFlags();
        $bSendNewsLetter = ($person->getSendNewsletter() == 'TRUE');

        $iFacebookID = $person->getFacebookID();
        $sTwitter = $person->getTwitter();
        $sLinkedIn = $person->getLinkedIn();

        $sPhoneCountry = MiscUtils::SelectWhichInfo($sCountry, $fam_Country, false);

        $sHomePhone = MiscUtils::ExpandPhoneNumber($sHomePhone, $sPhoneCountry, $bNoFormat_HomePhone);
        $sWorkPhone = MiscUtils::ExpandPhoneNumber($sWorkPhone, $sPhoneCountry, $bNoFormat_WorkPhone);
        $sCellPhone = MiscUtils::ExpandPhoneNumber($sCellPhone, $sPhoneCountry, $bNoFormat_CellPhone);

        //The following values are True booleans if the family record has a value for the
        //indicated field.  These are used to highlight field headers in red.
        if ($iFamily > 0) {
            $fam = FamilyQuery::Create()->findOneById($iFamily);

            if ( !is_null($fam) ) {
                $bFamilyAddress1 = strlen($fam->getAddress1());
                $bFamilyAddress2 = strlen($fam->getAddress2());
                $bFamilyCity = strlen($fam->getCity());
                $bFamilyState = strlen($fam->getState());
                $bFamilyZip = strlen($fam->getZip());
                $bFamilyCountry = strlen($fam->getCountry());
                $bFamilyHomePhone = strlen($fam->getHomePhone());
                $bFamilyWorkPhone = strlen($fam->getWorkPhone());
                $bFamilyCellPhone = strlen($fam->getCellPhone());
                $bFamilyEmail = strlen($fam->getEmail());

                $bOkToCanvass = ($fam->getOkToCanvass() == 'TRUE');
                $iCanvasser = $fam->getCanvasser();
            }
        }

        $bFacebookID = $iFacebookID != 0;
        $bTwitter = strlen($sTwitter);
        $bLinkedIn = strlen($sLinkedIn);

        $aCustomData = [];

        $aCustomData[] = $iPersonID;
        $aCustomData['per_ID'] = $iPersonID;

        foreach ($ormCustomFields as $ormCustomField) {
            $personCustom = PersonCustomQuery::Create()
                ->withcolumn($ormCustomField->getCustomField())
                ->findOneByPerId($iPersonID);

            if (!is_null($personCustom)) {
                $aCustomData[] = $personCustom->getVirtualColumn($ormCustomField->getCustomField());
                $aCustomData[$ormCustomField->getCustomField()] = $personCustom->getVirtualColumn($ormCustomField->getCustomField());
            }
        }
    } else {
        //Adding....
        //Set defaults
        $sTitle = '';
        $sFirstName = '';
        $sMiddleName = '';
        $sLastName = '';
        $sSuffix = '';
        $iGender = '';
        $sAddress1 = '';
        $sAddress2 = '';
        $sCity = SystemConfig::getValue('sDefaultCity');
        $sState = SystemConfig::getValue('sDefaultState');
        $sZip = '';
        $sCountry = SystemConfig::getValue('sDefaultCountry');
        $sHomePhone = '';
        $sWorkPhone = '';
        $sCellPhone = '';
        $sEmail = '';
        $sWorkEmail = '';
        $iBirthMonth = 0;
        $iBirthDay = 0;
        $iBirthYear = 0;
        $bHideAge = 0;
        $iOriginalFamily = 0;
        $iFamily = '0';
        $iFamilyRole = '0';
        $dMembershipDate = '';
        $dFriendDate = date('Y-m-d');
        $iClassification = '0';
        $iViewAgeFlag = 0;
        $sPhoneCountry = '';
        $bSendNewsLetter = false;
        $bSendNewsLetter = false;
        $bOkToCanvass = 1;
        $iCanvasser = -1;

        $iFacebookID = 0;
        $sTwitter = '';
        $sLinkedIn = '';


        $sHomePhone = '';
        $sWorkPhone = '';
        $sCellPhone = '';

        //The following values are True booleans if the family record has a value for the
        //indicated field.  These are used to highlight field headers in red.
        $bFamilyAddress1 = 0;
        $bFamilyAddress2 = 0;
        $bFamilyCity = 0;
        $bFamilyState = 0;
        $bFamilyZip = 0;
        $bFamilyCountry = 0;
        $bFamilyHomePhone = 0;
        $bFamilyWorkPhone = 0;
        $bFamilyCellPhone = 0;
        $bFamilyEmail = 0;
        $bHomeBound = false;
        $aCustomData = [];
    }
}

if ($iBirthDay != 0 and $iBirthMonth != 0 and $iBirthYear) {
    $sBirthDayDate = $iBirthDay . "-" . $iBirthMonth . "-" . $iBirthYear;
} else {
    $sBirthDayDate = '';
}

//Get Classifications for the drop-down
// Get Field Security List Matrix
$ormClassifications = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(1);

//Get Families for the drop-down
if (SessionUser::getUser()->isGdrpDpoEnabled()) {// only GDRP Pdo can see the super deactivated members
    $ormFamilies = FamilyQuery::Create()
        ->orderByName()
        ->find();
} else {
    $ormFamilies = FamilyQuery::Create()
        ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
        ->orderByName()
        ->find();
}

//Get Family Roles for the drop-down
$ormFamilyRoles = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(2);


$bShowAddress = false;
if ($iFamily == 0 && $iFamilyID != -1) {
    $iFamily = $iFamilyID;
}

$sFamName = '';

if ($iFamily != 0) {
    $bShowAddress = true;
    $theFamily = FamilyQuery::Create()
        ->findOneById($iFamily);

    if (!is_null($theFamily)) {
        $sFamName = $theFamily->getName();
        $sAddress1 = $theFamily->getAddress1();
        $sAddress2 = $theFamily->getAddress2();
        $sCity = $theFamily->getCity();
        $sState = $theFamily->getState();
        $sCountry = $theFamily->getCountry();
        $sZip = $theFamily->getZip();
    }
}

require $sRootDocument . '/Include/Header.php';

?>
<form method="post" action="<?= $sRootPath ?>/v2/people/person/editor<?= ($iPersonID != -1)?("/".$iPersonID):"" ?>" name="PersonEditor">
    <div class="alert alert-info alert-dismissable">
        <i class="fas fa-info"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong><span
                style="color: red;"><?= _('Red text') ?></span></strong> <?= _('indicates items inherited from the associated family record.') ?>
    </div>
    <?php if ($bErrorFlag) {
        ?>
        <div class="alert alert-danger alert-dismissable">
            <i class="fas fa-ban"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <?= _('Invalid fields or selections. Changes not saved! Please correct and try again!') ?>
        </div>
        <?php
    } ?>
    <div class="card card-info clearfix">
        <div class="card-header border-1">
            <h3 class="card-title"><?= _('Personal Info') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2">
                        <label><?= _('Gender') ?>:</label>
                        <select name="Gender" class="form-control form-control-sm">
                            <option value="0"><?= _('Select Gender') ?></option>
                            <option value="0" disabled>-----------------------</option>
                            <option value="1" <?= ($iGender == 1) ? 'selected' : '' ?>><?= _('Male') ?></option>
                            <option value="2" <?= ($iGender == 2) ? 'selected' : '' ?>><?= _('Female') ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="Title"><?= _('Title') ?>:</label>
                        <input type="text" name="Title" id="Title"
                               value="<?= htmlentities(stripslashes($sTitle), ENT_NOQUOTES, 'UTF-8') ?>"
                               class= "form-control form-control-sm" placeholder="<?= _('Mr., Mrs., Dr., Rev.') ?>">
                    </div>
                </div>
                <p/>
                <div class="row">
                    <div class="col-md-4">
                        <label for="FirstName"><?= _('First Name') ?>:</label>
                        <input type="text" name="FirstName" id="FirstName"
                               value="<?= htmlentities(stripslashes($sFirstName), ENT_NOQUOTES, 'UTF-8') ?>"
                               class= "form-control form-control-sm">
                        <?php if ($sFirstNameError) {
                            ?><br><font
                                color="red"><?= $sFirstNameError ?></font><?php
                        } ?>
                    </div>

                    <div class="col-md-2">
                        <label for="MiddleName"><?= _('Middle Name') ?>:</label>
                        <input type="text" name="MiddleName" id="MiddleName"
                               value="<?= htmlentities(stripslashes($sMiddleName), ENT_NOQUOTES, 'UTF-8') ?>"
                               class= "form-control form-control-sm">
                        <?php if ($sMiddleNameError) {
                            ?><br><font
                                color="red"><?= $sMiddleNameError ?></font><?php
                        } ?>
                    </div>

                    <div class="col-md-4">
                        <label for="LastName"><?= _('Last Name') ?>:</label>
                        <input type="text" name="LastName" id="LastName"
                               value="<?= htmlentities(stripslashes($sLastName), ENT_NOQUOTES, 'UTF-8') ?>"
                               class= "form-control form-control-sm">
                        <?php if ($sLastNameError) {
                            ?><br><font
                                color="red"><?= $sLastNameError ?></font><?php
                        } ?>
                    </div>

                    <div class="col-md-1">
                        <label for="Suffix"><?= _('Suffix') ?>:</label>
                        <input type="text" name="Suffix" id="Suffix"
                               value="<?= htmlentities(stripslashes($sSuffix), ENT_NOQUOTES, 'UTF-8') ?>"
                               placeholder="<?= _('Jr., Sr., III') ?>" class= "form-control form-control-sm">
                    </div>
                </div>
                <p/>
                <div class="row">
                    <div class="col-md-2">
                        <label><?= _('Birthday Date') ?>:</label>
                        <input type="text" name="BirthDayDate" class=" form-control  form-control-sm date-picker" value="<?= OutputUtils::change_date_for_place_holder($sBirthDayDate) ?>" maxlength="10" id="sel2" size="10" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                    </div>
                    <div class="col-md-2">
                        <label><?= _('Hide Age') ?></label><br/>
                        <input type="checkbox" name="HideAge" value="1" <?= ($bHideAge) ? ' checked' : '' ?>/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-info clearfix">
        <div class="card-header with-border">
            <h3 class="card-title"><?= _("Person or Family Info") ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <div class="form-group col-md-3">
                <label><?= _("Person or Family Role") ?>:</label>
                <select name="FamilyRole" class="form-control form-control-sm">
                    <option value="0"><?= _("Unassigned") ?></option>
                    <option value="0" disabled>-----------------------</option>
                    <?php
                    foreach ($ormFamilyRoles

                    as $ormFamilyRole) {
                    ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>"
                        <?= ($iFamilyRole == $ormFamilyRole->getOptionId()) ? ' selected' : '' ?>><?= $ormFamilyRole->getOptionName() ?>
                        &nbsp;
                        <?php
                        }
                        ?>
                </select>
            </div>

            <div class="form-group col-md-9"
                <?= (!SessionUser::getUser()->isEditRecordsEnabled()) ? 'style="display: none;"' : '' ?>>
                <label><?= _('Person or Family address'); ?>:</label>
                <select name="Family" size="8" class= "form-control form-control-sm" id="optionFamily">
                    <option value="0" selected><?= _('Unassigned') ?></option>
                    <option value="-1"><?= _("Create a new Address or A new family (using last name)") ?></option>
                    <option value="0" disabled>-----------------------</option>
                    <?php
                    foreach ($ormFamilies

                    as $ormFamily) {
                    ?>
                    <option value="<?= $ormFamily->getId() ?>"
                        <?= ($iFamily == $ormFamily->getId() || $iFamilyID != -1 && $iFamilyID == $ormFamily->getId()) ? ' selected' : '' ?>><?= $ormFamily->getName() ?>
                        &nbsp;<?= MiscUtils::FormatAddressLine($ormFamily->getAddress1(), $ormFamily->getCity(), $ormFamily->getState()) ?>
                        <?php
                        }
                        ?>
                </select>
            </div>

            <!-- start of the new code PL -->
            <div id="familyAddress">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-header with-border">
                                <h3 class="card-title"><?= _('Person or Family Address') ?></h3>
                            </div>
                        </div><!-- /.box-header -->
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?= _('Person Name') ?> <?= _('or') ?> <?= _('Family Name') ?>:</label>
                            <input type="text" id="FamName" name="FamName"
                                   value="<?= htmlentities(stripslashes($sFamName), ENT_NOQUOTES, 'UTF-8') ?>" size="50"
                                   maxlength="250" class= "form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <b><?= _('A person could have a different name as his family.<br>• In this case set the Family Name in this field.<br>• In the other case, leave this field blank.') ?></b>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?= _('Address') ?> 1:</label>
                            <input type="text" id="FamAddress1" name="FamAddress1"
                                   value="<?= htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') ?>"
                                   size="50" maxlength="250" class= "form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label><?= _('Address') ?> 2:</label>
                            <input type="text" id="FamAddress2" name="FamAddress2"
                                   value="<?= htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') ?>"
                                   size="50" maxlength="250" class= "form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label><?= _('City') ?>:</label>
                            <input type="text" id="FamCity" name="FamCity"
                                   value="<?= htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') ?>"
                                   maxlength="50" class= "form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div
                            <?= (SystemConfig::getValue('bStateUnusefull')) ? "style=\"display: none;\"" : "class=\"form-group col-md-3\" " ?>>
                            <label for="StatleTextBox"><?= _('State') ?>: </label><br>
                            <?php
                            $statesDDF = new StateDropDown();
                            echo $statesDDF->getDropDown($sState, "FamState");
                            ?>
                        </div>
                        <div
                            <?= (SystemConfig::getValue('bStateUnusefull')) ? 'style="display: none;"' : 'class="form-group col-md-3" ' ?>>
                            <label><?= _('None US/CND State') ?>:</label>
                            <input type="text" class= "form-control form-control-sm" id="FamStateTextbox" name="FamStateTextbox"
                                   value="<?php if ($sCountry != 'United States' && $sCountry != 'Canada') {
                                       echo htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8');
                                   } ?>" size="20" maxlength="30">
                        </div>
                        <div class="form-group col-md-3">
                            <label><?= _('Zip') ?>:</label>
                            <input type="text" id="FamZip" name="FamZip" class= "form-control form-control-sm" <?php
                            // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
                            if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
                                echo 'style="text-transform:uppercase" ';
                            }
                            echo 'value="' . htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8') . '" '; ?>
                                   maxlength="10" size="8">
                        </div>
                        <div class="form-group col-md-3">
                            <label> <?= _('Country') ?>:</label><br>
                            <?php
                            $countriesDDF = new CountryDropDown();
                            echo $countriesDDF->getDropDown($sCountry, "FamCountry");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end of the new code PL -->

            <!-- canvasser -->
            <div class="row">
                <?php if (SessionUser::getUser()->isCanvasserEnabled() && !empty($person) && !is_null($person->getFamily()) && $person->getFamily()->getPeople()->count() == 1) { // Only show this field if the current user is a canvasser?>
                    <div class="form-group col-md-4">
                        <label><?= _('Ok To Canvass') ?>: </label>
                        <input type="checkbox" Name="OkToCanvass" value="1" <?= ($bOkToCanvass) ? ' checked ' : '' ?>>
                    </div>
                    <?php


                    if (!is_null($canvassers) && $canvassers->count() > 0) {
                        ?>
                        <div class="form-group col-md-4">
                            <label><?= _('Assign a Canvasser') ?>:</label>
                            <select name='Canvasser' class= "form-control form-control-sm">
                                <option value="0"><?= _('None selected') ?></option>
                                <?php // Display all canvassers
                                foreach ($canvassers as $canvasser) {
                                    ?>
                                    <option
                                        value="<?= $canvasser->getId() ?>" <?= ($canvasser->getId() == $iCanvasser) ? ' selected' : '' ?>>
                                        <?= $canvasser->getFirstName() . ' ' . $canvasser->getLastName() ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                    }

                    if (!is_null($braveCanvassers) && $braveCanvassers->count() > 0) {
                        ?>
                        <div class="form-group col-md-4">
                            <label><?= _('Assign a Brave Canvasser') ?>: </label>

                            <select name='BraveCanvasser' class= "form-control form-control-sm">
                                <option value="0"><?= _('None selected') ?></option>
                                <?php // Display all canvassers
                                foreach ($braveCanvassers as $braveCanvasser) {
                                    ?>
                                    <option
                                        value="<?= $braveCanvasser->getId() ?>" <?= ($braveCanvasser->getId() == $iCanvasser) ? ' selected' : '' ?>>
                                        <?= $braveCanvasser->getFirstName() . ' ' . $braveCanvasser->getLastName() ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

            <!-- canvasser -->
        </div>
    </div>
    <div class="card card-info clearfix">
        <div class="card-header with-border">
            <h3 class="card-title"><?= _('Contact Info') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <div id="personAddress">
                <?php
                if (!SystemConfig::getValue('bHidePersonAddress')) { // Person Address can be hidden - General Settings : dead code now
                    ?>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-4">
                                <label>
                                    <?php
                                    if ($bFamilyAddress1) {
                                    ?>
                                    <span style="color: red;">
                          <?php
                          }
                          ?>

                          <?= _('Address') . ' 1:' ?>

                          <?php
                          if ($bFamilyAddress1) {
                          ?>
                            </span>
                                <?php
                                }
                                ?>
                                </label>
                                <input type="text" name="Address1"
                                       value="<?= htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') ?>"
                                       size="30" maxlength="50" class= "form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label>
                                    <?php
                                    if ($bFamilyAddress2) {
                                    ?>
                                    <span style="color: red;">
                            <?php
                            }
                            ?>

                            <?= _('Address') . ' 2:' ?>

                            <?php
                            if ($bFamilyAddress2) {
                            ?>
                               </span>
                                <?php
                                }
                                ?>
                                </label>
                                <input type="text" name="Address2"
                                       value="<?= htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') ?>"
                                       size="30" maxlength="50" class= "form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label>
                                    <?php
                                    if ($bFamilyCity) {
                                    ?>
                                    <span style="color: red;">
                          <?php
                          }
                          ?>

                          <?= _('City') . ':' ?>

                          <?php
                          if ($bFamilyCity) {
                          ?>
                            </span>
                                <?php
                                }
                                ?>
                                </label>
                                <input type="text" name="City"
                                       value="<?= htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') ?>"
                                       class= "form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <p/>
                    <div class="row">
                        <div class="form-group col-md-2">
                            <label for="StatleTextBox">
                                <?php
                                if ($bFamilyState) {
                                ?>
                                <span style="color: red;">
                        <?php
                        }
                        ?>

                        <?= _('State') . ':' ?>

                        <?php
                        if ($bFamilyState) {
                        ?>
                            </span>
                            <?php
                            }
                            ?>
                            </label>
                            <?php
                            $statesDD = new StateDropDown();
                            echo $statesDD->getDropDown($sState);
                            ?>
                        </div>
                        <div class="form-group col-md-2">
                            <label><?= _('None US/CND State') ?>:</label>
                            <input type="text" name="StateTextbox"
                                   value="<?php if ($sPhoneCountry != 'United States' && $sPhoneCountry != 'Canada') {
                                       echo htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8');
                                   } ?>"
                                   size="20" maxlength="30" class= "form-control form-control-sm">
                        </div>

                        <div class="form-group col-md-1">
                            <label for="Zip">
                                <?php
                                if ($bFamilyZip) {
                                ?>
                                <span style="color: red;">
                        <?php
                        }
                        ?>

                        <?= _('Zip') . ':' ?>

                        <?php
                        if ($bFamilyZip) {
                        ?>
                            </span>
                            <?php
                            }
                            ?>
                            </label>
                            <input type="text" name="Zip" class= "form-control form-control-sm"
                                <?php
                                // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
                                if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
                                    echo 'style="text-transform:uppercase" ';
                                }

                                echo 'value="' . htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8') . '" '; ?>
                                   maxlength="10" size="8">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="Zip">
                                <?php
                                if ($bFamilyCountry) {
                                ?>
                                <span style="color: red;">
                        <?php
                        }
                        ?>

                        <?= _('Country') . ':' ?>

                        <?php
                        if ($bFamilyCountry) {
                        ?>
                            </span>
                            <?php
                            }
                            ?>
                            </label>
                            <?php
                            $countriesDD = new CountryDropDown();
                            echo $countriesDD->getDropDown($sCountry);
                            ?>
                        </div>
                    </div>
                    <p/>
                    <?php
                } else { // put the current values in hidden controls so they are not lost if hiding the person-specific info?>
                    <input type="hidden" name="Address1"
                           value="<?= htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <input type="hidden" name="Address2"
                           value="<?= htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <input type="hidden" name="City"
                           value="<?= htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <input type="hidden" name="State"
                           value="<?= htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <input type="hidden" name="StateTextbox"
                           value="<?= htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <input type="hidden" name="Zip"
                           value="<?= htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <input type="hidden" name="Country"
                           value="<?= htmlentities(stripslashes($sCountry), ENT_NOQUOTES, 'UTF-8') ?>"></input>
                    <?php
                } ?>
            </div>
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="HomePhone">
                        <?php
                        if ($bFamilyHomePhone) {
                            ?>
                            <span style="color: red;"><?= _('Home Phone') ?>:</span>
                            <?php
                        } else {
                            ?>
                            <?= _('Home Phone') ?>:
                            <?php
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input type="text" name="HomePhone"
                                   value="<?= htmlentities(stripslashes($sHomePhone), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                                   maxlength="30" class= "form-control form-control-sm"
                                   data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormat') ?>"' data-mask>
                        </div>
                        <br>
                        <input type="checkbox" name="NoFormat_HomePhone" value="1"
                            <?= ($bNoFormat_HomePhone) ? ' checked' : '' ?>><?= _('Do not auto-format') ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="WorkPhone">
                        <?php
                        if ($bFamilyWorkPhone) {
                            ?>
                            <span style="color: red;"><?= _('Work Phone') ?>:</span>
                            <?php
                        } else {
                            ?>
                            <?= _('Work Phone') ?>:
                            <?php
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input type="text" name="WorkPhone"
                                   value="<?= htmlentities(stripslashes($sWorkPhone), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                                   maxlength="30" class= "form-control form-control-sm"
                                   data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormatWithExt') ?>"'
                                   data-mask/>
                        </div>
                        <br>
                        <input type="checkbox" name="NoFormat_WorkPhone" value="1"
                            <?= ($bNoFormat_WorkPhone) ? ' checked' : '' ?>><?= _('Do not auto-format') ?>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <label for="CellPhone">
                        <?php
                        if ($bFamilyCellPhone) {
                            ?>
                            <span style="color: red;"><?= _('Mobile Phone') ?>:</span>
                            <?php
                        } else {
                            ?>
                            <?= _('Mobile Phone') ?>:
                            <?php
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input type="text" name="CellPhone"
                                   value="<?= htmlentities(stripslashes($sCellPhone), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                                   maxlength="30" class= "form-control form-control-sm"
                                   data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormatCell') ?>"' data-mask>
                        </div>
                        <br><input type="checkbox" name="NoFormat_CellPhone" value="1"
                            <?= ($bNoFormat_CellPhone) ? ' checked' : '' ?>><?= _('Do not auto-format') ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label><?= _('Send Newsletter') ?>:</label>
                        </div>
                        <div class="form-group col-md-4">
                            <input type="checkbox" Name="SendNewsLetter" value="1"
                                <?= ($bSendNewsLetter) ? ' checked' : '' ?> style="margin-top:10px">
                        </div>
                    </div>
                </div>
            </div>
            <p/>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="Email">
                        <?php
                        if ($bFamilyEmail) {
                            ?>
                            <span style="color: red;"><?= _('Email') ?>:</span>
                            <?php
                        } else {
                            ?>
                            <?= _('Email') ?>:
                            <?php
                        }
                        ?>
                    </label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"> <i class="far fa-envelope"></i></span>
                        </div>
                        <input type="text" name="Email"
                               value="<?= htmlentities(stripslashes($sEmail), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class= "form-control form-control-sm">
                        <?php
                        if ($sEmailError) {
                            ?>
                            <font color="red"><?= $sEmailError ?></font>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="WorkEmail"><?= _('Work / Other Email') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="far fa-envelope"></i></span>
                        </div>
                        <input type="text" name="WorkEmail"
                               value="<?= htmlentities(stripslashes($sWorkEmail), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class= "form-control form-control-sm">
                        <?php
                        if ($sWorkEmailError) {
                            ?>
                            <font color="red"><?= $sWorkEmailError ?></font>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="FacebookID">
                        <?php
                        if ($bFacebookID) {
                            ?>
                            <span style="color: red;"><?= _('Facebook') ?> ID:</span>
                            <?php
                        } else {
                            ?>
                            <?= _('Facebook') ?> ID:
                            <?php
                        }
                        ?>
                    </label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                        </div>
                        <input type="text" name="Facebook"
                               value="<?= htmlentities(stripslashes($iFacebookID), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class= "form-control form-control-sm">
                        <?php
                        if ($sFacebookError) {
                            ?>
                            <font color="red"><?= $sFacebookError ?></font>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="Twitter"><?= _('Twitter') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                        </div>
                        <input type="text" name="Twitter"
                               value="<?= htmlentities(stripslashes($sTwitter), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class= "form-control form-control-sm">
                        <?php
                        if ($sTwitterError) {
                            ?>
                            <font color="red"><?= $sTwitterError ?></font>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="LinkedIn"><?= _('LinkedIn') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                        </div>
                        <input type="text" name="LinkedIn"
                               value="<?= htmlentities(stripslashes($sLinkedIn), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class= "form-control form-control-sm">
                        <?php
                        if ($sLinkedInError) {
                            ?>
                            <font color="red"><?= $sLinkedInError ?></font>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-info clearfix">
        <div class="card-header with-border">
            <h3 class="card-title"><?= _('Membership Info') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-3 col-lg-3">
                    <label><?= _('Classification') ?>:</label>
                    <select name="Classification" class= "form-control form-control-sm">
                        <option value="0"><?= _('Unassigned') ?></option>
                        <option value="0" disabled>-----------------------</option>

                        <?php
                        foreach ($ormClassifications

                        as $ormClassification) {
                        ?>
                        <option value="<?= $ormClassification->getOptionId() ?>"
                            <?= ($iClassification == $ormClassification->getOptionId()) ? ' selected' : '' ?>><?= $ormClassification->getOptionName() ?>
                            &nbsp;
                            <?php
                            }
                            ?>
                    </select>
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    <label ><?= _('Membership Date') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <!-- Philippe Logel -->
                        <input type="text" name="MembershipDate" class=" form-control  form-control-sm date-picker"
                               value="<?= OutputUtils::change_date_for_place_holder($dMembershipDate) ?>" maxlength="10"
                               id="sel1" size="11"
                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                        <?php
                        if ($sMembershipDateError) {
                            ?>
                            <font color="red"><?= $sMembershipDateError ?></font>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
                if (!SystemConfig::getBooleanValue('bHideFriendDate')) { /* Friend Date can be hidden - General Settings */
                    ?>
                    <div class="form-group col-md-3 col-lg-3">
                        <label ><?= _('Friend Date') ?>:</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                            <!-- Philippe Logel -->
                            <input type="text" name="FriendDate" class=" form-control  form-control-sm date-picker"
                                   value="<?= OutputUtils::change_date_for_place_holder($dFriendDate) ?>" maxlength="10"
                                   id="sel2" size="10"
                                   placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                            <?php
                            if ($sFriendDateError) {
                                ?>
                                <font color="red"><?= $sFriendDateError ?></font>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    if ($numCustomFields > 0) {
        ?>
        <div class="card card-info clearfix">
            <div class="card-header with-border">
                <h3 class="card-title"><?= _('Custom Fields') ?></h3>
                <div class="pull-right">
                    <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
                </div>
            </div><!-- /.box-header -->
            <div class="card-body">
                <?php
                if ($numCustomFields > 0) {
                    for ($i = 0; $i < $maxCustomFields; $i++) {
                        ?>
                        <div class="row">

                            <div class="form-group col-md-6">
                                <?php
                                if ($i < $numLeftCustomFields) {
                                    $customField = $ormLeftCustomFields[$i];

                                    if (OutputUtils::securityFilter($customField['CustomFieldSec'])) {
                                        ?>
                                        <label><?= $customField['CustomName'] ?></label>
                                        <br>
                                        <?php

                                        if (array_key_exists($customField['CustomField'], $aCustomData)) {
                                            $currentFieldData = trim($aCustomData[$customField['CustomField']]);
                                        } else {
                                            $currentFieldData = '';
                                        }

                                        if ($type_ID == 11) {// in the case of a phone number
                                            $custom_Special = $sPhoneCountry;
                                        } else {
                                            $custom_Special = $customField['CustomSpecial'];
                                        }

                                        OutputUtils::formCustomField($customField['TypeId'], $customField['CustomField'], $currentFieldData, $custom_Special, !isset($_POST['PersonSubmit']));
                                        if (isset($aCustomErrors[$customField['TypeId']])) {
                                            ?>
                                            <span
                                                style="color: red; "><?= $aCustomErrors[$customField['TypeId']] ?></span>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </div>

                            <div class="form-group col-md-6">
                                <?php
                                if ($i < $numRightCustomFields) {
                                    $customField = $ormRightCustomFields[$i];

                                    if (OutputUtils::securityFilter($customField['CustomFieldSec'])) {
                                        ?>
                                        <label><?= $customField['CustomName'] ?></label><br>
                                        <?php
                                        if (array_key_exists($customField['CustomField'], $aCustomData)) {
                                            $currentFieldData = trim($aCustomData[$customField['CustomField']]);
                                        } else {
                                            $currentFieldData = '';
                                        }

                                        if ($type_ID == 11) {// in the case of a phone number
                                            $custom_Special = $sPhoneCountry;
                                        } else {
                                            $custom_Special = $customField['CustomSpecial'];
                                        }

                                        OutputUtils::formCustomField($customField['TypeId'], $customField['CustomField'], $currentFieldData, $custom_Special, !isset($_POST['PersonSubmit']));

                                        if (isset($aCustomErrors[$customField['TypeId']])) {
                                            ?>
                                            <span
                                                style="color: red; "><?= $aCustomErrors[$customField['TypeId']] ?></span>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
    <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
    <?php
    if (SessionUser::getUser()->isAddRecordsEnabled()) {
        ?>
        <input type="submit" class="btn btn-success" value="<?= _('Save and Add') ?>" name="PersonSubmitAndAdd">
        <?php
    }
    ?>
    <input type="button" class="btn btn-default" value="<?= _('Cancel') ?>" name="PersonCancel"
           onclick="javascript:document.location='v2/people/list/person';">
</form>
<br/>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.bShowAddress = <?= ($bShowAddress) ? 'true' : 'false' ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/PersonEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


