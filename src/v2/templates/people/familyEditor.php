<?php
/*******************************************************************************
 *
 *  filename    : templates/familyEditor.php
 *  last change : 2023-06-18
 *
 *  http://www.ecclesiacrm.com/
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Philippe Logel 2017-12-13
 *
 ******************************************************************************/

 use EcclesiaCRM\dto\SystemConfig;
 use EcclesiaCRM\dto\CanvassUtilities;
 use EcclesiaCRM\dto\StateDropDown;
 use EcclesiaCRM\dto\CountryDropDown;
 use EcclesiaCRM\Utils\InputUtils;
 use EcclesiaCRM\Utils\OutputUtils;
 use EcclesiaCRM\Utils\MiscUtils;
 use EcclesiaCRM\Utils\RedirectUtils;
 use EcclesiaCRM\Utils\LoggerUtils;
 use EcclesiaCRM\SessionUser;
 use EcclesiaCRM\Bootstrapper;
 use EcclesiaCRM\Emails\NewPersonOrFamilyEmail;
 
 use EcclesiaCRM\Note;
 use EcclesiaCRM\FamilyQuery;
 use EcclesiaCRM\PersonQuery;
 use EcclesiaCRM\Person;
 use EcclesiaCRM\Family;
 use EcclesiaCRM\ListOptionQuery;
 use EcclesiaCRM\PersonCustom;
 use EcclesiaCRM\FamilyCustomMasterQuery;
 use EcclesiaCRM\FamilyCustom;
 use EcclesiaCRM\FamilyCustomQuery;
 use EcclesiaCRM\Record2propertyR2p;
 
 use Propel\Runtime\Propel;


// Security: User must have Add or Edit Records permission to use this form in those manners
// Clean error handling: (such as somebody typing an incorrect URL ?PersonID= manually)
if ($iFamilyID > 0) {
    if (!(SessionUser::getUser()->isEditRecordsEnabled() || (SessionUser::getUser()->isEditSelfEnabled() && ($iFamilyID == SessionUser::getUser()->getPerson()->getFamId())))) {
        RedirectUtils::Redirect('v2/dashboard');
        exit;
    }

    $family = FamilyQuery::Create()
        ->findOneById($iFamilyID);

    if (empty($family)) {
        RedirectUtils::Redirect('v2/dashboard');
        exit;
    }

    if ($family->getDateDeactivated() != null && !SessionUser::getUser()->isGdrpDpoEnabled()) {
        RedirectUtils::Redirect('members/404.php');
    }
} elseif (!SessionUser::getUser()->isAddRecordsEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$ormFamilyRoles = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(2);

$ormClassifications = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(1);

$familyQuickRoleIds = [
    'head' => 0,
    'spouse' => 0,
    'child' => 0,
];

foreach ($ormFamilyRoles as $ormFamilyRole) {
    $optionName = $ormFamilyRole->getOptionName();

    if ($optionName === _('Head of Household')) {
        $familyQuickRoleIds['head'] = $ormFamilyRole->getOptionId();
    } elseif ($optionName === _('Spouse')) {
        $familyQuickRoleIds['spouse'] = $ormFamilyRole->getOptionId();
    } elseif ($optionName === _('Child')) {
        $familyQuickRoleIds['child'] = $ormFamilyRole->getOptionId();
    }
}

// Get the lists of canvassers
$canvassers = CanvassUtilities::CanvassGetCanvassers('Canvassers');
$braveCanvassers = CanvassUtilities::CanvassGetCanvassers('BraveCanvassers');

// Get the list of custom person fields
$ormCustomFields = FamilyCustomMasterQuery::Create()
    ->orderByCustomOrder()
    ->find();

// only the left custom fields
$ormLeftCustomFields = FamilyCustomMasterQuery::Create()
    ->orderByCustomOrder()
    ->filterByCustomSide('left')
    ->find()->toArray();

// only the right custom fields
$ormRightCustomFields = FamilyCustomMasterQuery::Create()
    ->orderByCustomOrder()
    ->filterByCustomSide('right')
    ->find()->toArray();

$numLeftCustomFields = count($ormLeftCustomFields);
$numRightCustomFields = count($ormRightCustomFields);

$maxCustomFields = max($numRightCustomFields, $numLeftCustomFields);

$numCustomFields = $numRightCustomFields + $numLeftCustomFields;


// Get Field Security List Matrix
$securityListOptions = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(5);

$bErrorFlag = false;
$sNameError = '';
$sEmailError = '';
$sWeddingDateError = '';
$bWeddingDateUnknown = false;

$sName = '';

$UpdateBirthYear = 0;

$aFirstNameError = [];
$aBirthDateError = [];
$aperFlags = [];

//Is this the second pass?
if (isset($_POST['FamilySubmit']) || isset($_POST['FamilySubmitAndAdd'])) {
    //Assign everything locally
    $sName = InputUtils::FilterString($_POST['Name']);
    // Strip commas out of address fields because they are problematic when
    // exporting addresses to CSV file
    $sAddress1 = str_replace(',', '', InputUtils::FilterString($_POST['Address1']));
    $sAddress2 = str_replace(',', '', InputUtils::FilterString($_POST['Address2']));
    $sCity = InputUtils::FilterString($_POST['City']);
    $sZip = InputUtils::LegacyFilterInput($_POST['Zip']);

    // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
    if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
        $sZip = strtoupper($sZip);
    }

    $sCountry = InputUtils::FilterString($_POST['Country']);
    $iFamilyMemberRows = InputUtils::LegacyFilterInput($_POST['FamCount']);

    if ($sCountry == 'United States' || $sCountry == 'Canada' || $sCountry == '' || $sCountry != '') {
        $sState = InputUtils::FilterString($_POST['State']);
    } else {
        $sState = InputUtils::LegacyFilterInput($_POST['StateTextbox']);
    }

    $sHomePhone = InputUtils::LegacyFilterInput($_POST['HomePhone']);
    $sWorkPhone = InputUtils::LegacyFilterInput($_POST['WorkPhone']);
    $sCellPhone = InputUtils::LegacyFilterInput($_POST['CellPhone']);
    $sEmail = InputUtils::LegacyFilterInput($_POST['Email']);
    $bSendNewsLetter = isset($_POST['SendNewsLetter']);

    $nLatitude = 0.0;
    $nLongitude = 0.0;
    if (array_key_exists('Latitude', $_POST)) {
        $nLatitude = InputUtils::LegacyFilterInput($_POST['Latitude'], 'float');
    }
    if (array_key_exists('Longitude', $_POST)) {
        $nLongitude = InputUtils::LegacyFilterInput($_POST['Longitude'], 'float');
    }


    if (is_numeric($nLatitude)) {
        $nLatitude = "" . $nLatitude . "";
    } else {
        $nLatitude = 'NULL';
    }

    if (is_numeric($nLongitude)) {
        $nLongitude = "" . $nLongitude . "";
    } else {
        $nLongitude = 'NULL';
    }

    $nEnvelope = 0;
    if (array_key_exists('Envelope', $_POST)) {
        $nEnvelope = InputUtils::LegacyFilterInput($_POST['Envelope'], 'int');
    }

    if (is_numeric($nEnvelope)) { // Only integers are allowed as Envelope Numbers
        if (intval($nEnvelope) == floatval($nEnvelope)) {
            $nEnvelope = "" . intval($nEnvelope) . "";
        } else {
            $nEnvelope = "0";
        }
    } else {
        $nEnvelope = "0";
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

    $iPropertyID = 0;
    if (array_key_exists('PropertyID', $_POST)) {
        $iPropertyID = InputUtils::LegacyFilterInput($_POST['PropertyID'], 'int');
    }
    $bWeddingDateUnknown = isset($_POST['WeddingDateUnknown']);
    $dWeddingDate = $bWeddingDateUnknown ? '' : InputUtils::FilterDate($_POST['WeddingDate']);

    $bNoFormat_HomePhone = isset($_POST['NoFormat_HomePhone']);
    $bNoFormat_WorkPhone = isset($_POST['NoFormat_WorkPhone']);
    $bNoFormat_CellPhone = isset($_POST['NoFormat_CellPhone']);

    //Loop through the Family Member 'quick entry' form fields
    for ($iCount = 1; $iCount <= $iFamilyMemberRows; $iCount++) {
        // Assign everything to arrays
        $aFirstNames[$iCount] = InputUtils::FilterString($_POST['FirstName' . $iCount]);
        $aMiddleNames[$iCount] = InputUtils::FilterString($_POST['MiddleName' . $iCount]);
        $aLastNames[$iCount] = InputUtils::FilterString($_POST['LastName' . $iCount]);
        $aSuffix[$iCount] = InputUtils::LegacyFilterInput($_POST['Suffix' . $iCount]);
        $aRoles[$iCount] = InputUtils::LegacyFilterInput($_POST['Role' . $iCount], 'int');
        $aGenders[$iCount] = InputUtils::LegacyFilterInput($_POST['Gender' . $iCount], 'int');
        $aBirthDays[$iCount] = InputUtils::LegacyFilterInput($_POST['BirthDay' . $iCount], 'int');
        $aBirthMonths[$iCount] = InputUtils::LegacyFilterInput($_POST['BirthMonth' . $iCount], 'int');
        $aBirthYears[$iCount] = InputUtils::LegacyFilterInput($_POST['BirthYear' . $iCount], 'int');
        $aClassification[$iCount] = InputUtils::LegacyFilterInput($_POST['Classification' . $iCount], 'int');
        $aPersonIDs[$iCount] = InputUtils::LegacyFilterInput($_POST['PersonID' . $iCount], 'int');
        $aUpdateBirthYear[$iCount] = InputUtils::LegacyFilterInput($_POST['UpdateBirthYear'], 'int');

        // Make sure first names were entered if editing existing family
        if ($iFamilyID > 0) {
            if (strlen($aFirstNames[$iCount]) == 0) {
                $aFirstNameError[$iCount] = _('First name must be entered');
                $bErrorFlag = true;
            }
        }

        // Validate any family member birthdays
        if ((strlen($aFirstNames[$iCount]) > 0) && (strlen($aBirthYears[$iCount]) > 0)) {
            if (($aBirthYears[$iCount] > 2155) || ($aBirthYears[$iCount] < 1901)) {
                $aBirthDateError[$iCount] = _('Invalid Year: allowable values are 1901 to 2155');
                $bErrorFlag = true;
            } elseif ($aBirthMonths[$iCount] > 0 && $aBirthDays[$iCount] > 0) {
                if (!checkdate($aBirthMonths[$iCount], $aBirthDays[$iCount], $aBirthYears[$iCount])) {
                    $aBirthDateError[$iCount] = _('Invalid Birth Date.');
                    $bErrorFlag = true;
                }
            }
        }
    }

    //Did they enter a name?
    if (strlen($sName) < 1) {
        $sNameError = _('You must enter a name');
        $bErrorFlag = true;
    }

    // Validate Wedding Date if one was entered
    if ((strlen($dWeddingDate) > 0) && ($dWeddingDate != '')) {
        $dateString = InputUtils::parseAndValidateDate($dWeddingDate, Bootstrapper::getCurrentLocale()->getCountryCode(), $pasfut = 'past');
        if ($dateString === false) {
            $sWeddingDateError =  _('Not a valid Wedding Date');
            $bErrorFlag = true;
        } else {
            $dWeddingDate = "$dateString";
        }
    } else {
        $dWeddingDate = 'NULL';
    }

    // Validate Email
    if (strlen($sEmail) > 0) {
        if (MiscUtils::checkEmail($sEmail) == false) {
            $sEmailError =  _('Email is Not Valid');
            $bErrorFlag = true;
        } else {
            $sEmail = $sEmail;
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
        // Format the phone numbers before we store them
        if (!$bNoFormat_HomePhone) {
            $sHomePhone = MiscUtils::CollapsePhoneNumber($sHomePhone, $sCountry);
        }
        if (!$bNoFormat_WorkPhone) {
            $sWorkPhone = MiscUtils::CollapsePhoneNumber($sWorkPhone, $sCountry);
        }
        if (!$bNoFormat_CellPhone) {
            $sCellPhone = MiscUtils::CollapsePhoneNumber($sCellPhone, $sCountry);
        }

        //Write the base SQL depending on the Action
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
        if ($iFamilyID < 1) { // create a family
            $family = new Family();

            $family->setName($sName);
            $family->setAddress1($sAddress1);
            $family->setAddress2($sAddress2);
            $family->setCity($sCity);
            $family->setState($sState);
            $family->setZip($sZip);
            $family->setCountry($sCountry);
            $family->setHomePhone($sHomePhone);
            $family->setWorkPhone($sWorkPhone);
            $family->setCellPhone($sCellPhone);
            $family->setEmail($sEmail);
            if ($dWeddingDate !== "NULL") {// strangely it's a string wich contains sometimes "NULL"
                $family->setWeddingdate($dWeddingDate);
            }
            $family->setDateEntered(new DateTime());
            $family->setEnteredBy(SessionUser::getUser()->getPersonId());
            
            // bSendNewsLetterString : When you activate the family all members are deactivated
            if ($bSendNewsLetterString == "TRUE") {
                foreach ($family->getPeople() as $person) {
                    $person->setSendNewsletter("FALSE");
                }
            }

            if (SessionUser::getUser()->isCanvasserEnabled()) {
                $family->setOkToCanvass($bOkToCanvassString);
                $family->setCanvasser($iCanvasser);
            }

            $family->setLatitude($nLatitude);
            $family->setLongitude($nLongitude);
            $family->setEnvelope($nEnvelope);

            $family->updateLanLng();

            $family->save();

            $family->setSendNewsletter($bSendNewsLetterString);
            $family->save();

            $iFamilyID = $family->getId();

            $bGetKeyBack = true;
        } else {// edition family
            $family = FamilyQuery::Create()
                ->findOneByID($iFamilyID);

            $family->setName($sName);
            $family->setAddress1($sAddress1);
            $family->setAddress2($sAddress2);
            $family->setCity($sCity);
            $family->setState($sState);
            $family->setZip($sZip);
            $family->setCountry($sCountry);
            $family->setHomePhone($sHomePhone);
            $family->setWorkPhone($sWorkPhone);
            $family->setCellPhone($sCellPhone);
            $family->setEmail($sEmail);
            if ($dWeddingDate !== "NULL") {
                $family->setWeddingdate($dWeddingDate);
            }
            $family->setDateEntered(new DateTime());
            $family->setEnteredBy(SessionUser::getUser()->getPersonId());

            $family->setDateLastEdited(new DateTime());
            $family->setEditedBy(SessionUser::getUser()->getPersonId());

            $family->setSendNewsletter($bSendNewsLetterString);

            // head persons and spouse get the news letter in a family not the childrens
            // you can add them individually
            $familyMembersParents = array_merge($family->getHeadPeople(), $family->getSpousePeople());

            foreach ($familyMembersParents as $person) {
                $person->setSendNewsletter($bSendNewsLetterString);
            }

            if (SessionUser::getUser()->isCanvasserEnabled()) {
                $family->setOkToCanvass($bOkToCanvassString);
                $family->setCanvasser($iCanvasser);
            }

            $family->setLatitude($nLatitude);
            $family->setLongitude($nLongitude);
            $family->setEnvelope($nEnvelope);

            $family->updateLanLng();

            $family->save();

            $bGetKeyBack = false;
        }

        //If the user added a new record, we need to key back to the route to the FamilyView page
        if ($bGetKeyBack) {
            //Get the key back
            $familyCustom = new FamilyCustom();
            $familyCustom->setFamId($iFamilyID);
            $familyCustom->save();

            // Add property if assigned
            if ($iPropertyID) {
                $familyProperty = new Record2propertyR2p();

                $familyProperty->setR2pRecordId($iFamilyID);
                $familyProperty->setR2pProId($iPropertyID);

                $familyProperty->save();
            }

            //Run through the family member arrays...
            for ($iCount = 1; $iCount <= $iFamilyMemberRows; $iCount++) {
                if (strlen($aFirstNames[$iCount]) > 0) {
                    if (strlen($aBirthYears[$iCount]) < 4) {
                        $aBirthYears[$iCount] = 'NULL';
                    }

                    //If no last name is entered for a member, use the family name.
                    if (strlen($aLastNames[$iCount]) && $aLastNames[$iCount] != $sName) {
                        $sLastNameToEnter = $aLastNames[$iCount];
                    } else {
                        $sLastNameToEnter = $sName;
                    }

                    //RunKuery('LOCK TABLES person_per WRITE, person_custom WRITE');
                    $person = new Person();
                    $person->setFirstName($aFirstNames[$iCount]);
                    $person->setMiddleName($aMiddleNames[$iCount]);
                    $person->setLastName($sLastNameToEnter);
                    $person->setSuffix($aSuffix[$iCount]);
                    $person->setFamId($iFamilyID);
                    $person->setFmrId($aRoles[$iCount]);
                    $person->setDateEntered(date('YmdHis'));
                    $person->setEnteredBy(SessionUser::getUser()->getPersonId());
                    $person->setGender($aGenders[$iCount]);
                    $person->setBirthDay($aBirthDays[$iCount]);
                    $person->setBirthMonth($aBirthMonths[$iCount]);
                    $person->setBirthYear($aBirthYears[$iCount]);
                    $person->setClsId($aClassification[$iCount]);
                    $person->save();

                    $dbPersonId = $person->getID();

                    $note = new Note();
                    $note->setPerId($dbPersonId);
                    $note->setText(_('Created via Family'));
                    $note->setType('create');
                    $note->setEntered(SessionUser::getUser()->getPersonId());
                    $note->save();

                    $personCustom = new PersonCustom();
                    $personCustom->setPerId($dbPersonId);
                    $personCustom->save();

                    //RunKuery('UNLOCK TABLES');
                }
            }
            $family = FamilyQuery::create()->findPk($iFamilyID);
            $family->createTimeLineNote('create');
            $family->updateLanLng();

            if (!empty(SystemConfig::getValue("sNewPersonNotificationRecipientIDs"))) {
                $NotificationEmail = new NewPersonOrFamilyEmail($family);
                if (!$NotificationEmail->send()) {
                    LoggerUtils::getAppLogger()->warn($NotificationEmail->getError());
                }
            }
        } else {
            for ($iCount = 1; $iCount <= $iFamilyMemberRows; $iCount++) {
                if (strlen($aFirstNames[$iCount]) > 0) {
                    if (strlen($aBirthYears[$iCount]) < 4) {
                        $aBirthYears[$iCount] = 'NULL';
                    }

                    //If no last name is entered for a member, use the family name.
                    if (strlen($aLastNames[$iCount]) && $aLastNames[$iCount] != $sName) {
                        $sLastNameToEnter = $aLastNames[$iCount];
                    } else {
                        $sLastNameToEnter = $sName;
                    }
                    $sBirthYearScript = ($aUpdateBirthYear[$iCount] & 1) ? 'per_BirthYear=' . $aBirthYears[$iCount] . ', ' : '';
                    //RunKuery("LOCK TABLES person_per WRITE, person_custom WRITE");
                    $person = PersonQuery::Create()
                        ->findOneById($aPersonIDs[$iCount]);

                    $person->setFirstName($aFirstNames[$iCount]);
                    $person->setMiddleName($aMiddleNames[$iCount]);
                    $person->setLastName($aLastNames[$iCount]);
                    $person->setSuffix($aSuffix[$iCount]);
                    $person->setGender($aGenders[$iCount]);
                    $person->setFmrId($aRoles[$iCount]);
                    $person->setBirthMonth($aBirthMonths[$iCount]);
                    $person->setBirthDay($aBirthDays[$iCount]);
                    $person->setBirthYear($aBirthYears[$iCount]);
                    $person->setClsId($aClassification[$iCount]);
                    $person->setDateEntered(date('YmdHis'));
                    $person->setEnteredBy(SessionUser::getUser()->getPersonId());
                    $person->save();
                    //RunKuery("UNLOCK TABLES");

                    $note = new Note();
                    $note->setPerId($aPersonIDs[$iCount]);
                    $note->setText(_('Updated via Family'));
                    $note->setType('edit');
                    $note->setEntered(SessionUser::getUser()->getPersonId());
                    $note->save();
                }
            }
        }

        // Update the custom person fields.
        if ($numCustomFields > 0) {
            $sSQL = 'REPLACE INTO family_custom SET ';

            foreach ($ormCustomFields as $rowCustomField) {
                if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
                    $currentFieldData = trim($aCustomData[$rowCustomField->getCustomField()]);
                    MiscUtils::sqlCustomField($sSQL, $rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $sCountry);
                }
            }

            // chop off the last 2 characters (comma and space) added in the last while loop iteration.
            $sSQL = mb_substr($sSQL, 0, -2);

            $sSQL .= ', fam_ID = ' . $iFamilyID;

            $connection = Propel::getConnection();

            $statement = $connection->prepare($sSQL);
            $statement->execute();
        }

        //Which submit button did they press?
        if (isset($_POST['FamilySubmit'])) {
            //Send to the view of this person
            RedirectUtils::Redirect('v2/people/family/view/' . $iFamilyID);
        } else {
            //Reload to editor to add another record
            RedirectUtils::Redirect('v2/people/family/editor');
        }
    }
} else if (isset($_POST['FamilySubmitAndAddPerson']) and $iFamilyID > 0) {
    $quickAddRole = 0;
    if (isset($_POST['QuickAddFamilyRole'])) {
        $quickAddRole = InputUtils::LegacyFilterInput($_POST['QuickAddFamilyRole'], 'int');
    }

    $redirect = 'v2/people/person/editor/AddToFamily/'.$iFamilyID;
    if ($quickAddRole > 0) {
        $redirect .= '?FamilyRole='.$quickAddRole;
    }

    RedirectUtils::Redirect($redirect);
} else {
    //FirstPass
    //Are we editing or adding?
    if ($iFamilyID > 0) {
        //Editing....
        //Get the information on this family
        $family = FamilyQuery::Create()->findOneById($iFamilyID);
        $iFamilyID = $family->getId();
        $sName = $family->getName();
        $sAddress1 = $family->getAddress1();
        $sAddress2 = $family->getAddress2();
        $sCity = $family->getCity();
        $sState = $family->getState();
        $sZip = $family->getZip();
        $sCountry = $family->getCountry();
        $sHomePhone = $family->getHomePhone();
        $sWorkPhone = $family->getWorkPhone();
        $sCellPhone = $family->getCellPhone();
        $sEmail = $family->getEmail();
        $bSendNewsLetter = ($family->getSendNewsletter() == 'TRUE');
        $bOkToCanvass = ($family->getOkToCanvass() == 'TRUE');
        $iCanvasser = $family->getCanvasser();
        $dWeddingDate = ($family->getWeddingdate() != null) ? $family->getWeddingdate()->format('Y-m-d') : "";
        $bWeddingDateUnknown = $family->getWeddingdate() == null;
        $nLatitude = $family->getLatitude();
        $nLongitude = $family->getLongitude();

        // Expand the phone number
        $sHomePhone = MiscUtils::ExpandPhoneNumber($sHomePhone, $sCountry, $bNoFormat_HomePhone);
        $sWorkPhone = MiscUtils::ExpandPhoneNumber($sWorkPhone, $sCountry, $bNoFormat_WorkPhone);
        $sCellPhone = MiscUtils::ExpandPhoneNumber($sCellPhone, $sCountry, $bNoFormat_CellPhone);

        $famCustom = FamilyCustomQuery::Create()->findOneByFamId($iFamilyID);

        // get family with all the extra columns created
        $rawQry = FamilyCustomQuery::create();
        foreach ($ormCustomFields as $customfield) {
            $rawQry->withColumn($customfield->getCustomField());
        }

        if (!is_null($rawQry->findOneByFamId($iFamilyID))) {
            $aCustomData = $rawQry->findOneByFamId($iFamilyID)->toArray();
        }

        $aCustomErrors = [];

        if ($numCustomFields > 0) {
            foreach ($ormCustomFields as $rowCustomField) {
                $aCustomErrors[$rowCustomField->getCustomField()] = false;
            }
        }

        $persons = PersonQuery::Create()
            ->leftJoinWithFamily()
            ->orderByFmrId()
            ->filterByDateDeactivated(NULL)
            ->findByFamId($iFamilyID);

        $iCount = 0;
        $iFamilyMemberRows = 0;
        foreach ($persons as $person) {
            $iCount++;
            $iFamilyMemberRows++;
            $aFirstNames[$iCount] = $person->getFirstName();
            $aMiddleNames[$iCount] = $person->getMiddleName();
            $aLastNames[$iCount] = $person->getLastName();
            $aSuffix[$iCount] = $person->getSuffix();
            $aGenders[$iCount] = $person->getGender();
            $aRoles[$iCount] = $person->getFmrId();
            $aBirthMonths[$iCount] = $person->getBirthMonth();
            $aBirthDays[$iCount] = $person->getBirthDay();

            if ($person->getBirthYear() > 0) {
                $aBirthYears[$iCount] = $person->getBirthYear();
            } else {
                $aBirthYears[$iCount] = '';
            }

            $aClassification[$iCount] = $person->getClsId();
            $aPersonIDs[$iCount] = $person->getId();
            $aPerFlag[$iCount] = $person->getFlags();
        }
    } else {
        //Adding....
        //Set defaults
        $sCity = SystemConfig::getValue('sDefaultCity');
        $sCountry = SystemConfig::getValue('sDefaultCountry');
        $sState = SystemConfig::getValue('sDefaultState');
        $iClassification = '0';
        $iFamilyMemberRows = 1;
        $bOkToCanvass = 1;

        $iFamilyID = -1;
        $sName = '';
        $sAddress1 = '';
        $sAddress2 = '';
        $sZip = '';
        $sHomePhone = '';
        $bNoFormat_HomePhone = isset($_POST['NoFormat_HomePhone']);
        $sWorkPhone = '';
        $bNoFormat_WorkPhone = isset($_POST['NoFormat_WorkPhone']);
        $sCellPhone = '';
        $bNoFormat_CellPhone = isset($_POST['NoFormat_CellPhone']);
        $sEmail = '';
        $bSendNewsLetter = false;
        $iCanvasser = -1;
        $dWeddingDate = '';
        $bWeddingDateUnknown = false;
        $nLatitude = 0.0;
        $nLongitude = 0.0;

        //Loop through the Family Member 'quick entry' form fields
        for ($iCount = 1; $iCount <= $iFamilyMemberRows; $iCount++) {
            // Assign everything to arrays
            $aFirstNames[$iCount] = '';
            $aMiddleNames[$iCount] = '';
            $aLastNames[$iCount] = '';
            $aSuffix[$iCount] = '';
            $aRoles[$iCount] = 0;
            $aGenders[$iCount] = '';
            $aBirthDays[$iCount] = 0;
            $aBirthMonths[$iCount] = 0;
            $aBirthYears[$iCount] = '';
            $aClassification[$iCount] = 0;
            $aPersonIDs[$iCount] = 0;
            $aUpdateBirthYear[$iCount] = 0;
        }

        $aCustomData = [];
        $aCustomErrors = [];
        if ($numCustomFields > 0) {
            foreach ($ormCustomFields as $rowCustomField) {
                $aCustomData[$rowCustomField->getCustomField()] = '';
                $aCustomErrors[$rowCustomField->getCustomField()] = false;
            }
        }
    }
}

require $sRootDocument . '/Include/Header.php';
?>

<form method="post" action="<?= $sRootPath ?>/v2/people/family/editor<?= ($iFamilyID != -1)?("/".$iFamilyID):"" ?>">
    <input type="hidden" Name="iFamilyID" value="<?= $iFamilyID ?>">
    <input type="hidden" id="familyMemberCount" name="FamCount" value="<?= $iFamilyMemberRows ?>">
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between">
                <div class="pr-3">
                    <h2 class="h4 mb-1">
                        <i class="fas fa-home mr-2 text-primary"></i><?= $iFamilyID > 0 ? _('Edit family') : _('Create family') ?>
                    </h2>
                    <div class="text-muted small mb-0">
                        <?= _('Update the household details, contact information, members and custom fields, then save when everything is ready.') ?>
                    </div>
                </div>
                <div class="small text-muted mt-2 mt-md-0">
                    <?= _('You can complete each section independently before saving.') ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-outline card-info shadow-sm clearfix">
        <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h3 class="mb-1"><i class="fas fa-house-user mr-2 text-info"></i><?= _('Family Info') ?></h3>
                <div class="small text-muted"><?= _('Household name, address, geographic details and primary location information.') ?></div>
            </div>
            <div class="card-tools ml-auto text-right mt-2 mt-md-0">
                <input type="submit" class="btn btn-sm btn-primary" value="<?= _('Save') ?>" name="FamilySubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <div class="border rounded p-3 bg-light">
            <div class="form-group mb-0">
                <div class="row">
                    <div class="col-md-2">
                        <label><?= _('Family Name') ?>:</label>
                        <input type="text" Name="Name" id="FamilyName"
                               value="<?= htmlentities(stripslashes($sName), ENT_NOQUOTES, 'UTF-8') ?>" maxlength="48"
                               class="form-control form-control-sm">
                        <?php if ($sNameError) {
                            ?><span class="text-danger"><?= $sNameError ?></span><?php
                        } ?>
                    </div>
                    <div class="col-md-2">
                        <label><?= _('Address') ?> 1:</label>
                        <input type="text" Name="Address1"
                               value="<?= htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') ?>" size="50"
                               maxlength="250" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label><?= _('City') ?>:</label>
                        <input type="text" Name="City"
                               value="<?= htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') ?>" maxlength="50"
                               class="form-control form-control-sm">
                    </div>
                    <div <?= (SystemConfig::getValue('bStateUnusefull')) ? 'style="display: none;"' : 'class="form-group col-md-2"' ?>>
                        <label for="StatleTextBox"><?= _("State") ?>: </label>
                        <?php
                        $statesDD = new StateDropDown();
                        echo $statesDD->getDropDown($sState);
                        ?>
                    </div>                    
                    <div class="form-group col-md-2">
                        <label> <?= _('Country') ?>:</label>
                        <?= CountryDropDown::getDropDown($sCountry) ?>
                    </div>
                    <div class="form-group col-md-1">
                        <label><?= _('Zip') ?>:</label>
                        <input type="text" Name="Zip" class="form-control form-control-sm" <?php
                        // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
                        if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
                            echo 'style="text-transform:uppercase" ';
                        }
                        echo 'value="' . htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8') . '" '; ?>
                               maxlength="10" size="8">
                    </div>                    

                    <div class="col-md-2">
                        <label><?= _('Address') ?> 2:</label>
                        <input type="text" Name="Address2"
                               value="<?= htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') ?>" size="50"
                               maxlength="250" class="form-control form-control-sm">
                    </div>
                    <div <?= (SystemConfig::getValue('bStateUnusefull')) ? 'style="display: none;"' : 'class="form-group col-md-3"' ?>>
                        <label><?= _('None US/CND State') ?>:</label>
                        <input type="text" class="form-control form-control-sm" name="StateTextbox"
                               value="<?php if ($sCountry != 'United States' && $sCountry != 'Canada') {
                                   echo htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8');
                               } ?>" size="20" maxlength="30">
                    </div>
                </div>
                <?php if (!SystemConfig::getValue('bHideLatLon')) { /* Lat/Lon can be hidden - General Settings */
                    if (!$bHaveXML) { // No point entering if values will just be overwritten?>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label><?= _('Latitude') ?>:</label>
                                <input type="text" class="form-control form-control-sm" Name="Latitude"
                                       value="<?= $nLatitude ?>" size="30" maxlength="50">
                            </div>
                            <div class="form-group col-md-3">
                                <label><?= _('Longitude') ?>:</label>
                                <input type="text" class="form-control form-control-sm" Name="Longitude"
                                       value="<?= $nLongitude ?>" size="30" maxlength="50">
                            </div>
                        </div>
                        <?php
                    }
                } /* Lat/Lon can be hidden - General Settings */ ?>
            </div>
            </div>
        </div>
    </div>
    <script nonce="<?= $CSPNonce ?>">
        $(function() {
            $("#country-input").select2();
            $("#state-input").select2();
        });
    </script>
    <div class="card card-outline card-info shadow-sm clearfix">
        <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h3 class="mb-1"><i class="fas fa-address-book mr-2 text-info"></i><?= _('Contact Info') ?></h3>
                <div class="small text-muted"><?= _('Phone numbers, email address and newsletter preferences for the family.') ?></div>
            </div>
            <div class="card-tools ml-auto text-right mt-2 mt-md-0">
                <input type="submit" class="btn btn-sm btn-primary" value="<?= _('Save') ?>" name="FamilySubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <div class="border rounded p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div class="font-weight-bold"><?= _('Phone numbers') ?></div>
                    <div class="small text-muted"><?= _('Store the main family phone numbers.') ?></div>
                </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label><?= _('Home Phone') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div>
                        <input type="text" Name="HomePhone" value="<?= htmlentities(stripslashes($sHomePhone)) ?>"
                               size="30" maxlength="30" class="form-control form-control-sm"
                               data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormat') ?>"' data-mask>
                    </div>
                    <input type="checkbox" name="NoFormat_HomePhone"
                           value="1" <?= ($bNoFormat_HomePhone) ? ' checked' : '' ?>><?= _('Do not auto-format') ?>
                </div>
                <div class="form-group col-md-4">
                    <label><?= _('Work Phone') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div>
                        <input type="text" name="WorkPhone" value="<?= htmlentities(stripslashes($sWorkPhone)) ?>"
                               size="30" maxlength="30" class="form-control form-control-sm"
                               data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormatWithExt') ?>"'
                               data-mask/>
                    </div>
                    <input type="checkbox" name="NoFormat_WorkPhone"
                           value="1" <?= $bNoFormat_WorkPhone ? ' checked' : '' ?>><?= _('Do not auto-format') ?>
                </div>
                <div class="form-group col-md-4">
                    <label><?= _('Mobile Phone') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div>
                        <input type="text" name="CellPhone" value="<?= htmlentities(stripslashes($sCellPhone)) ?>"
                               size="30" maxlength="30" class="form-control form-control-sm"
                               data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormatCell') ?>"' data-mask>
                    </div>
                    <input type="checkbox" name="NoFormat_CellPhone"
                               value="1" <?= $bNoFormat_CellPhone ? ' checked' : '' ?>><?= _('Do not auto-format') ?>
                </div>
            </div>
            </div>
            <div class="border rounded p-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div class="font-weight-bold"><?= _('Email and newsletter') ?></div>
                    <div class="small text-muted"><?= _('Manage the shared family email and newsletter subscription.') ?></div>
                </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label><?= _('Email') ?>:</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="text" Name="Email" class="form-control form-control-sm"
                               value="<?= htmlentities(stripslashes($sEmail)) ?>" size="30" maxlength="100">
                               <span class="text-danger"><?=  $sEmailError ?></span>                            
                    </div>
                </div>
                <?php if (!SystemConfig::getValue('bHideFamilyNewsletter')) { /* Newsletter can be hidden - General Settings */ ?>
                    <div class="form-group col-md-4">
                        <br>
                        <label><?= _('Send Newsletter') ?>:</label>
                        <input type="checkbox" Name="SendNewsLetter"
                               value="1" <?= ($bSendNewsLetter) ? ' checked' : '' ?>>
                    </div>
                    <?php
                }
                ?>
            </div>
            </div>
        </div>
    </div>
    <div class="card card-outline card-info shadow-sm clearfix">
        <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h3 class="mb-1"><i class="fas fa-info-circle mr-2 text-info"></i><?= _('Other Info') ?></h3>
                <div class="small text-muted"><?= _('Wedding date, canvassing and other administrative details.') ?></div>
            </div>
            <div class="card-tools ml-auto text-right mt-2 mt-md-0">
                <input type="submit" class="btn btn-sm btn-primary" value="<?= _('Save') ?>" name="FamilySubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="card-body">
            <?php if (!SystemConfig::getValue('bHideWeddingDate')) { /* Wedding Date can be hidden - General Settings */
                if ($dWeddingDate == 'NULL') {
                    $dWeddingDate = '';
                } ?>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="weddingDateInput"><?= _('Wedding Date') ?>:</label>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                            <input type="text" class="form-control form-control-sm date-picker" Name="WeddingDate"
                                   value="<?= OutputUtils::change_date_for_place_holder($dWeddingDate) ?>" maxlength="10"
                                   id="weddingDateInput" size="15"
                                   placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>" <?= $bWeddingDateUnknown ? 'disabled' : '' ?>>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="weddingDateUnknown" name="WeddingDateUnknown" value="1" <?= $bWeddingDateUnknown ? 'checked' : '' ?>>
                            <label class="custom-control-label small" for="weddingDateUnknown"><?= _('Wedding date unknown') ?></label>
                        </div>
                        <?php if ($sWeddingDateError) { ?>
                            <span class="text-danger"><?= $sWeddingDateError ?></span>
                        <?php } ?>
                    </div>
                </div>

                <?php
            } /* Wedding date can be hidden - General Settings */ ?>
            <div class="row">
                <?php if (SessionUser::getUser()->isCanvasserEnabled()) { // Only show this field if the current user is a canvasser?>
                    <div class="form-group col-md-4">
                        <label><?= _('Ok To Canvass') ?>: </label>
                        <input type="checkbox" Name="OkToCanvass" value="1" <?= ($bOkToCanvass) ? ' checked ' : '' ?>>
                    </div>
                    <?php
                }

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
                ?>
            </div>
        </div>
    </div>
    <?php if (SystemConfig::getValue('bUseDonationEnvelopes')) { /* Donation envelopes can be hidden - General Settings */ ?>
        <div class="card card-outline card-info shadow-sm clearfix">
            <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h3 class="mb-1"><i class="fas fa-envelope-open-text mr-2 text-info"></i><?= _('Envelope Info') ?></h3>
                    <div class="small text-muted"><?= _('Donation envelope configuration for this family.') ?></div>
                </div>
                <div class="card-tools ml-auto text-right mt-2 mt-md-0">
                    <input type="submit" class="btn btn-sm btn-primary" value="<?= _('Save') ?>" name="FamilySubmit">
                </div>
            </div><!-- /.box-header -->
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label><?= _('Envelope Number') ?>:</label>
                           <input type="text" class="form-control form-control-sm"
                               Name="Envelope" <?= ($fam_Envelope) ? ' value="' . $fam_Envelope . '"' : '' ?> size="30"
                               maxlength="50">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ($numCustomFields > 0) {
        ?>
        <div class="card card-outline card-info shadow-sm clearfix">
            <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h3 class="mb-1"><i class="fas fa-sliders-h mr-2 text-info"></i><?= _('Custom Fields') ?></h3>
                    <div class="small text-muted"><?= _('Additional household information configured by your organization.') ?></div>
                </div>
                <div class="card-tools ml-auto text-right mt-2 mt-md-0">
                    <input type="submit" class="btn btn-sm btn-primary" value="<?= _('Save') ?>" name="FamilySubmit">
                </div>
            </div><!-- /.box-header -->
            <div class="card-body">
                <?php if ($numCustomFields > 0) {
                    for ($i = 0; $i < $maxCustomFields; $i++) {
                        echo '<div class="row">';

                        echo '  <div class="form-group col-md-6">';
                        if ($i < $numLeftCustomFields) {
                            $customField = $ormLeftCustomFields[$i];

                            if (OutputUtils::securityFilter($customField['CustomFieldSec'])) {
                                echo '<label>' . $customField['CustomName'] . '</label><br>';

                                if (!is_null($aCustomData) && array_key_exists($customField['CustomField'], $aCustomData)) {
                                    $currentFieldData = trim($aCustomData[$customField['CustomField']]);
                                } else {
                                    $currentFieldData = '';
                                }

                                if ($type_ID == 11) {// in the case of a phone number
                                    $custom_Special = $sCountry;
                                } else {
                                    $custom_Special = $customField['CustomSpecial'];
                                }

                                echo OutputUtils::formCustomField($customField['TypeId'], $customField['CustomField'], $currentFieldData, $custom_Special, !isset($_POST['PersonSubmit']));
                                if (isset($aCustomErrors[$customField['TypeId']])) { ?>
                                    <span class="text-danger"><?=  $aCustomErrors[$customField['TypeId']] ?></span>
                                <?php }
                            }
                        }
                        echo '  </div>';

                        echo '  <div class="form-group col-md-6">';
                        if ($i < $numRightCustomFields) {
                            $customField = $ormRightCustomFields[$i];

                            if (OutputUtils::securityFilter($customField['CustomFieldSec'])) {
                                echo '<label>' . $customField['CustomName'] . '</label><br>';

                                if (!is_null($aCustomData) && array_key_exists($customField['CustomField'], $aCustomData)) {
                                    $currentFieldData = trim($aCustomData[$customField['CustomField']]);
                                } else {
                                    $currentFieldData = '';
                                }

                                if ($type_ID == 11) {// in the case of a phone number
                                    $custom_Special = $sCountry;
                                } else {
                                    $custom_Special = $customField['CustomSpecial'];
                                }

                                echo OutputUtils::formCustomField($customField['TypeId'], $customField['CustomField'], $currentFieldData, $custom_Special, !isset($_POST['PersonSubmit']));
                                if (isset($aCustomErrors[$customField['TypeId']])) {?>
                                    <span class="text-danger"><?= $aCustomErrors[$customField['TypeId']] ?></span>
                                <?php }
                            }
                        }
                        echo '  </div>';

                        echo '</div>';

                    }
                } ?>
            </div>
        </div>
        <?php
    } ?>
    <div class="card card-outline card-info shadow-sm clearfix">
            <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h3 class="mb-1"><i class="fas fa-users mr-2 text-info"></i><?= _('Family Members') ?></h3>
                    <div class="small text-muted"><?= _('Create or update the people currently attached to this household.') ?></div>
                </div>
                <div class="card-tools ml-auto text-right mt-2 mt-md-0 d-flex flex-wrap align-items-center justify-content-end">
                    <span class="badge badge-primary mr-2 mb-2" id="familyMemberCountBadge"><i class="fas fa-users mr-1"></i><?= $iFamilyMemberRows . ' ' . _('Members') ?></span>
                    <input type="submit" class="btn btn-sm btn-primary mb-2" value="<?= _('Save') ?>" name="FamilySubmit">
                </div>
        </div><!-- /.box-header -->
        <div class="card-body">

            <?php if ($iFamilyMemberRows > 0) {
            ?>
                    <div class="row align-items-end mb-3">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div class="text-muted text-uppercase small"><?= _('Quick actions') ?></div>
                            <div class="font-weight-bold"><?= _('Build the household progressively') ?></div>
                            <div class="text-muted small"><?= $iFamilyID < 0 ? _('Start with one member, then add only the rows you need. All filled rows will create new person records when you save.') : _('Existing members stay editable here. New rows are ideal for adding more people without leaving the form.') ?></div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex flex-wrap justify-content-lg-end">
                                <button type="button" class="btn btn-sm btn-outline-primary mr-2 mb-2 add-family-member-row" data-role-id="0">
                                    <i class="fas fa-user-plus mr-1"></i><?= _('Add member row') ?>
                                </button>
                                <?php if ($familyQuickRoleIds['head'] > 0) { ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 add-family-member-row" data-role-id="<?= $familyQuickRoleIds['head'] ?>">
                                        <i class="fas fa-crown mr-1"></i><?= _('Add head') ?>
                                    </button>
                                <?php } ?>
                                <?php if ($familyQuickRoleIds['spouse'] > 0) { ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 add-family-member-row" data-role-id="<?= $familyQuickRoleIds['spouse'] ?>">
                                        <i class="fas fa-heart mr-1"></i><?= _('Add spouse') ?>
                                    </button>
                                <?php } ?>
                                <?php if ($familyQuickRoleIds['child'] > 0) { ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2 add-family-member-row" data-role-id="<?= $familyQuickRoleIds['child'] ?>">
                                        <i class="fas fa-child mr-1"></i><?= _('Add child') ?>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($iFamilyID > 0) { ?>
                        <div class="alert alert-light border py-2 px-3 small">
                            <i class="fas fa-info-circle text-info mr-1"></i><?= _('Rows linked to existing people stay locked for removal here. Use the family or person view for delete actions.') ?>
                        </div>
                    <?php } ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" width="100%">
                            <thead>
                            <tr class="thead-light" >
                                <th><?= _('#') ?></th>
                                <th><?= _('First Name') ?></th>
                                <th><?= _('Middle Name') ?></th>
                                <th><?= _('Last Name') ?></th>
                                <th><?= _('Suffix') ?></th>
                                <th><?= _('Gender') ?></th>
                                <th><?= _('Role') ?></th>
                                <th><?= _('Month') ?></th>
                                <th><?= _('Day') ?></th>
                                <th><?= _('Year') ?></th>
                                <th><?= _('Classification') ?></th>
                                <th class="text-center"><?= _('Action') ?></th>
                            </tr>
                            </thead>
                            <tbody id="familyMembersTableBody">
                            <?php
                            $numFamilyRoles = $ormFamilyRoles->count();

                            $c = 1;

                            foreach ($ormFamilyRoles as $rowFamilyRole) {
                                $aFamilyRoleNames[$c] = $rowFamilyRole->getOptionName();
                                $aFamilyRoleIDs[$c++] = $rowFamilyRole->getOptionId();
                            }

                            for ($iCount = 1; $iCount <= $iFamilyMemberRows; $iCount++) {
                                $isExistingPerson = !empty($aPersonIDs[$iCount]);
                                ?>
                                <tr class="family-member-row" data-member-index="<?= $iCount ?>">
                                    <input class="person-id-input" type="hidden" name="PersonID<?= $iCount ?>" value="<?= $aPersonIDs[$iCount] ?>">
                                    <td class="align-middle text-muted small font-weight-bold member-row-label"><?= $iCount ?></td>
                                    <td class="TextColumnFam">
                                        <input class="form-control form-control-sm" data-field-base="FirstName" name="FirstName<?= $iCount ?>" type="text"
                                               value="<?= $aFirstNames[$iCount] ?>" size="10">
                                        <?php if (array_key_exists($iCount, $aFirstNameError)) {?>
                                                <span class="text-danger"><?= $aFirstNameError[$iCount] ?></span>
                                            <?php } ?>
                                    </td>
                                    <td class="TextColumnFam">
                                        <input class="form-control form-control-sm" data-field-base="MiddleName" name="MiddleName<?= $iCount ?>" type="text"
                                               value="<?= $aMiddleNames[$iCount] ?>" size="10">
                                    </td>
                                    <td class="TextColumnFam">
                                        <input class="form-control form-control-sm family-member-last-name" data-field-base="LastName" name="LastName<?= $iCount ?>" type="text"
                                               value="<?= $aLastNames[$iCount] ?>" size="10">
                                    </td>
                                    <td class="TextColumnFam">
                                        <input class="form-control form-control-sm" data-field-base="Suffix" name="Suffix<?= $iCount ?>" type="text"
                                               value="<?= $aSuffix[$iCount] ?>" size="10">
                                    </td>
                                    <td class="TextColumnFam">
                                        <select class="form-control form-control-sm" data-field-base="Gender" name="Gender<?php echo $iCount ?>">
                                            <option value="0" <?php if ($aGenders[$iCount] == 0) {
                                                echo 'selected';
                                            } ?> ><?= _('Select Gender') ?></option>
                                            <option value="1" <?php if ($aGenders[$iCount] == 1) {
                                                echo 'selected';
                                            } ?> ><?= _('Male') ?></option>
                                            <option value="2" <?php if ($aGenders[$iCount] == 2) {
                                                echo 'selected';
                                            } ?> ><?= _('Female') ?></option>
                                        </select>
                                    </td>

                                    <td class="TextColumnFam">
                                        <select class="form-control form-control-sm family-member-role" data-field-base="Role" name="Role<?php echo $iCount ?>">
                                            <option value="0" <?php if ($aRoles[$iCount] == 0) {
                                                echo 'selected';
                                            } ?> ><?= _('Select Role') ?></option>
                                            <?php
                                            //Build the role select box
                                            for ($c = 1; $c <= $numFamilyRoles; $c++) {
                                                echo '<option value="' . $aFamilyRoleIDs[$c] . '"';
                                                if ($aRoles[$iCount] == $aFamilyRoleIDs[$c]) {
                                                    echo ' selected';
                                                }
                                                echo '>' . $aFamilyRoleNames[$c] . '</option>';
                                            } ?>
                                        </select>
                                    </td>
                                    <td class="TextColumnFam">
                                        <select class="form-control form-control-sm" data-field-base="BirthMonth" name="BirthMonth<?php echo $iCount ?>">
                                            <option value="0" <?php if ($aBirthMonths[$iCount] == 0) {
                                                echo 'selected';
                                            } ?>><?= _('Unknown') ?></option>
                                            <option value="01" <?php if ($aBirthMonths[$iCount] == 1) {
                                                echo 'selected';
                                            } ?>><?= _('January') ?></option>
                                            <option value="02" <?php if ($aBirthMonths[$iCount] == 2) {
                                                echo 'selected';
                                            } ?>><?= _('February') ?></option>
                                            <option value="03" <?php if ($aBirthMonths[$iCount] == 3) {
                                                echo 'selected';
                                            } ?>><?= _('March') ?></option>
                                            <option value="04" <?php if ($aBirthMonths[$iCount] == 4) {
                                                echo 'selected';
                                            } ?>><?= _('April') ?></option>
                                            <option value="05" <?php if ($aBirthMonths[$iCount] == 5) {
                                                echo 'selected';
                                            } ?>><?= _('May') ?></option>
                                            <option value="06" <?php if ($aBirthMonths[$iCount] == 6) {
                                                echo 'selected';
                                            } ?>><?= _('June') ?></option>
                                            <option value="07" <?php if ($aBirthMonths[$iCount] == 7) {
                                                echo 'selected';
                                            } ?>><?= _('July') ?></option>
                                            <option value="08" <?php if ($aBirthMonths[$iCount] == 8) {
                                                echo 'selected';
                                            } ?>><?= _('August') ?></option>
                                            <option value="09" <?php if ($aBirthMonths[$iCount] == 9) {
                                                echo 'selected';
                                            } ?>><?= _('September') ?></option>
                                            <option value="10" <?php if ($aBirthMonths[$iCount] == 10) {
                                                echo 'selected';
                                            } ?>><?= _('October') ?></option>
                                            <option value="11" <?php if ($aBirthMonths[$iCount] == 11) {
                                                echo 'selected';
                                            } ?>><?= _('November') ?></option>
                                            <option value="12" <?php if ($aBirthMonths[$iCount] == 12) {
                                                echo 'selected';
                                            } ?>><?= _('December') ?></option>
                                        </select>
                                    </td>
                                    <td class="TextColumnFam">
                                        <select class="form-control form-control-sm" data-field-base="BirthDay" name="BirthDay<?= $iCount ?>">
                                            <option value="0"><?= _('Unk') ?></option>
                                            <?php for ($x = 1; $x < 32; $x++) {
                                                if ($x < 10) {
                                                    $sDay = '0' . $x;
                                                } else {
                                                    $sDay = $x;
                                                } ?>
                                                <option
                                                    value="<?= $sDay ?>" <?= ($aBirthDays[$iCount] == $x) ? 'selected' : '' ?>><?= $x ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td class="TextColumnFam">
                                        <?php if (!array_key_exists($iCount, $aperFlags) || !$aperFlags[$iCount] || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                                            $UpdateBirthYear = 1; ?>
                                            <input class="form-control form-control-sm" data-field-base="BirthYear" name="BirthYear<?= $iCount ?>"
                                                   type="text" value="<?= $aBirthYears[$iCount] ?>" size="4"
                                                   maxlength="4">
                                            <?php
                                            if (array_key_exists($iCount, $aBirthDateError)) {
                                                ?>
                                                <span class="text-danger"><?= $aBirthDateError[$iCount] ?></span>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                        } else {
                                            $UpdateBirthYear = 0;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm"
                                                data-field-base="Classification" name="Classification<?php echo $iCount ?>">
                                            <option value="0" <?php if ($aClassification[$iCount] == 0) {
                                                echo 'selected';
                                            } ?>><?= _('Unassigned') ?></option>
                                            <option value="0" disabled>-----------------------</option>
                                            <?php
                                            foreach ($ormClassifications

                                            as $rowClassification) {
                                            ?>
                                            <option
                                                value="<?= $rowClassification->getOptionId() ?>"<?= ($aClassification[$iCount] == $rowClassification->getOptionId()) ? ' selected' : '' ?>>
                                                <?= $rowClassification->getOptionName() ?>&nbsp;
                                                <?php
                                                }
                                                ?>
                                        </select>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm <?= $isExistingPerson ? 'btn-outline-secondary' : 'btn-outline-danger' ?> remove-family-member-row" <?= $isExistingPerson ? 'disabled' : '' ?> title="<?= $isExistingPerson ? _('Existing members cannot be removed here') : _('Remove this row') ?>">
                                            <i class="fas <?= $isExistingPerson ? 'fa-lock' : 'fa-trash-alt' ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
        </div>
    </div>
<?php
}
?>

    <input type="hidden" name="UpdateBirthYear" value="<?= $UpdateBirthYear ?>">

    <div class="card card-outline card-secondary shadow-sm mt-3 mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="small text-muted mb-2 mb-md-0 pr-3">
                    <?= _('Review the sections above before saving the family record.') ?>
                </div>
                <div class="d-flex flex-wrap justify-content-end align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-1" name="FamilyCancel"
                    <?= ($iFamilyID > 0)?" onclick=\"javascript:document.location='". $sRootPath ."/v2/people/family/view/$iFamilyID';\"":" onclick=\"javascript:document.location='". $sRootPath ."/v2/familylist';\"" ?>>
                    <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                </button>

                <?php if (SessionUser::getUser()->isAddRecordsEnabled()) { ?>
                    <button type="submit" class="btn btn-sm btn-primary mr-2 mb-1" name="FamilySubmitAndAdd">
                        <i class="fas fa-home mr-1"></i><?= _('Save and Add Family') ?>
                    </button>
                    <select name="QuickAddFamilyRole" class="form-control form-control-sm mr-2 mb-1" style="max-width: 220px;">
                        <option value="0"><?= _('Detailed person form role') ?></option>
                        <?php foreach ($ormFamilyRoles as $ormFamilyRole) {
                            if ((int)$ormFamilyRole->getOptionId() > 0) { ?>
                                <option value="<?= $ormFamilyRole->getOptionId() ?>"><?= $ormFamilyRole->getOptionName() ?></option>
                            <?php }
                        } ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary mr-2 mb-1" name="FamilySubmitAndAddPerson">
                        <i class="fas fa-user-plus mr-1"></i><?= _('Save and Add Person To Family') ?>
                    </button>
                <?php } ?>

                <button type="submit" class="btn btn-sm btn-success mb-1" name="FamilySubmit">
                    <i class="fas fa-save mr-1"></i><?= _('Save') ?>
                </button>
                </div>
            </div>
        </div>
    </div>
</form>
<br>

<script nonce="<?= $CSPNonce ?>">
    $(function () {
        $("[data-mask]").inputmask();

        const familyMemberCountInput = $('#familyMemberCount');
        const familyMemberCountBadge = $('#familyMemberCountBadge');
        const familyMembersTableBody = $('#familyMembersTableBody');
        const familyNameInput = $('#FamilyName');

        function updateFamilyMemberNames() {
            const rows = familyMembersTableBody.find('tr.family-member-row');

            rows.each(function (index) {
                const rowIndex = index + 1;
                const row = $(this);

                row.attr('data-member-index', rowIndex);
                row.find('.member-row-label').text(rowIndex);
                row.find('.person-id-input').attr('name', 'PersonID' + rowIndex);

                row.find('[data-field-base]').each(function () {
                    const fieldBase = $(this).data('fieldBase');
                    $(this).attr('name', fieldBase + rowIndex);
                });
            });

            familyMemberCountInput.val(rows.length);
            familyMemberCountBadge.html('<i class="fas fa-users mr-1"></i>' + rows.length + ' <?= addslashes(_('Members')) ?>');
        }

        function resetMemberRow(row, roleId) {
            row.find('.person-id-input').val('0');
            row.find('input[type="text"]').val('');
            row.find('select').each(function () {
                $(this).val('0');
            });
            row.find('.text-danger').remove();

            if (roleId) {
                row.find('.family-member-role').val(String(roleId));
            }

            const familyName = familyNameInput.val().trim();
            if (familyName.length > 0) {
                row.find('.family-member-last-name').val(familyName);
            }

            row.find('.remove-family-member-row')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .prop('disabled', false)
                .attr('title', '<?= addslashes(_('Remove this row')) ?>')
                .html('<i class="fas fa-trash-alt"></i>');
        }

        $('.add-family-member-row').on('click', function () {
            const roleId = Number($(this).data('role-id')) || 0;
            const sourceRow = familyMembersTableBody.find('tr.family-member-row').last();
            const newRow = sourceRow.clone();

            resetMemberRow(newRow, roleId);
            familyMembersTableBody.append(newRow);
            updateFamilyMemberNames();
            newRow.find('[data-field-base="FirstName"]').trigger('focus');
        });

        $(document).on('click', '.remove-family-member-row', function () {
            const row = $(this).closest('tr.family-member-row');
            const rowCount = familyMembersTableBody.find('tr.family-member-row').length;
            const personId = Number(row.find('.person-id-input').val()) || 0;

            if (personId > 0 || rowCount === 1) {
                return;
            }

            row.remove();
            updateFamilyMemberNames();
        });

        familyNameInput.on('blur', function () {
            const familyName = $(this).val().trim();
            if (!familyName.length) {
                return;
            }

            familyMembersTableBody.find('.family-member-last-name').each(function () {
                if (!$(this).val().trim().length) {
                    $(this).val(familyName);
                }
            });
        });

        updateFamilyMemberNames();

        $('#weddingDateUnknown').on('change', function () {
            const dateInput = $('#weddingDateInput');
            const isUnknown = $(this).is(':checked');

            dateInput.prop('disabled', isUnknown);

            if (isUnknown) {
                dateInput.val('');
            }
        });
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


