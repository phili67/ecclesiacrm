<?php
/*******************************************************************************
 *
 *  filename    : PersonEditor.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-2005 Michael Wilt
 *                2017 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';
require 'Include/CountryDropDown.php';
require 'Include/StateDropDown.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Note;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
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

//Set the page title
$sPageTitle = _('Person Editor');

//Get the PersonID out of the querystring
if (array_key_exists('PersonID', $_GET)) {
    $iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');
} else {
    $iPersonID = 0;
}

$sPreviousPage = '';
if (array_key_exists('previousPage', $_GET)) {
    $sPreviousPage = InputUtils::LegacyFilterInput($_GET['previousPage']);
}

// Security: User must have Add or Edit Records permission to use this form in those manners
// Clean error handling: (such as somebody typing an incorrect URL ?PersonID= manually)
if ($iPersonID > 0) {
    $person = PersonQuery::Create()
        ->findOneById($iPersonID);
        
    if (empty($person)) {
        Redirect('Menu.php');
        exit();
    }
    
    if ($person->getDateDeactivated() != null && !$_SESSION['user']->isGdrpDpoEnabled()) {
      Redirect('members/404.php?type=Person');
    }

    if (!(
        $_SESSION['user']->isEditRecordsEnabled() ||
        ($_SESSION['user']->isEditSelfEnabled() && $iPersonID == $_SESSION['user']->getPersonId()) ||
        ($_SESSION['user']->isEditSelfEnabled() && $person->getFamId() == $_SESSION['user']->getPerson()->getFamId())
    )
    ) {
        Redirect('Menu.php');
        exit;
    }
} elseif (!$_SESSION['user']->isAddRecordsEnabled()) {
    Redirect('Menu.php');
    exit;
}

$ormCustomFields = PersonCustomMasterQuery::Create()
                     ->orderByCustomOrder()
                     ->find();
                     

$numCustomFields = count($ormCustomFields);

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
    
    // Person address stuff is normally surpressed in favor of family address info
    $sFamAddress1 = '';
    $sFamAddress2 = '';
    $sFamCity = '';
    $sFamZip = '';
    $sFamState = '';
    $sFamCountry = '';
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

    $sCountryTest = SelectWhichInfo($sCountry, $fam_Country, false);
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
    $iBirthMonth = InputUtils::LegacyFilterInput($_POST['BirthMonth'], 'int');
    $iBirthDay = InputUtils::LegacyFilterInput($_POST['BirthDay'], 'int');
    $iBirthYear = InputUtils::LegacyFilterInput($_POST['BirthYear'], 'int');

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

    //Adjust variables as needed
    if ($iFamily == 0) {
        $iFamilyRole = 0;
    }

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
        $dateString = parseAndValidateDate($dFriendDate, $locale = 'US', $pasfut = 'past');
        if ($dateString === false) {
            $sFriendDateError = '<span style="color: red; ">'
                ._('Not a valid Friend Date').'</span>';
            $bErrorFlag = true;
        } else {
            $dFriendDate = $dateString;
        }
    }

    // Validate Membership Date if one was entered
    if (strlen($dMembershipDate) > 0) {
        $dateString = parseAndValidateDate($dMembershipDate, $locale = 'US', $pasfut = 'past');
        if ($dateString === false) {
            $sMembershipDateError = '<span style="color: red; ">'
                ._('Not a valid Membership Date').'</span>';
            $bErrorFlag = true;
        } else {
            $dMembershipDate = $dateString;
        }
    }

    // Validate Email
    if (strlen($sEmail) > 0) {
        if (checkEmail($sEmail) == false) {
            $sEmailError = '<span style="color: red; ">'
                ._('Email is Not Valid').'</span>';
            $bErrorFlag = true;
        } else {
            $sEmail = $sEmail;
        }
    }

    // Validate Work Email
    if (strlen($sWorkEmail) > 0) {
        if (checkEmail($sWorkEmail) == false) {
            $sWorkEmailError = '<span style="color: red; ">'
                ._('Work Email is Not Valid').'</span>';
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
            
            echo $rowCustomField->getCustomField()." ".$currentFieldData;

            $bErrorFlag |= !validateCustomField($rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $aCustomErrors);

            // assign processed value locally to $aPersonProps so we can use it to generate the form later
            $aCustomData[$rowCustomField->getCustomField()] = $currentFieldData;
        }      
    }

    //If no errors, then let's update...
    if (!$bErrorFlag) {
        $sPhoneCountry = SelectWhichInfo($sCountry, $fam_Country, false);

        if (!$bNoFormat_HomePhone) {
            $sHomePhone = CollapsePhoneNumber($sHomePhone, $sPhoneCountry);
        }
        if (!$bNoFormat_WorkPhone) {
            $sWorkPhone = CollapsePhoneNumber($sWorkPhone, $sPhoneCountry);
        }
        if (!$bNoFormat_CellPhone) {
            $sCellPhone = CollapsePhoneNumber($sCellPhone, $sPhoneCountry);
        }

        //If no birth year, set to NULL
        if ((strlen($iBirthYear) != 4)) {
            $iBirthYear = 'NULL';
        } else {
            $iBirthYear = "$iBirthYear";
        }

        // New Family (add)
        // Family will be named by the Last Name.
        if ($iFamily == -1) {
            $family = new Family();
            
            $family->setName($sLastName);
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
            $family->setEnteredBy($_SESSION['user']->getPersonId());
            
            $family->save();
            
            //Get the key back You use the same code in CartView.php
            $iFamily = $family->getId();            
        } else {// the Family exist
            $family = FamilyQuery::Create()
                  ->findOneById($iFamily);
            
            $family->setName($sLastName);
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
            $family->setEnteredBy($_SESSION['user']->getPersonId());
            
            $family->save();
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
            
            if ($_SESSION['user']->isFinanceEnabled()) {
                $person->setEnvelope($iEnvelope);
            }
            
            $person->setFamId($iFamily);
            $person->setFmrId($iFamilyRole);
            
            if (strlen($dMembershipDate) > 0) {
                $person->setMembershipDate($dMembershipDate);
            }
            
            $person->setClsId($iClassification);
            $person->setDateEntered(new DateTime());
            $person->setEnteredBy($_SESSION['user']->getPersonId());
            
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
            $person = PersonQuery::Create()
                ->findOneByID($iPersonID);
                
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
            
            if ($_SESSION['user']->isFinanceEnabled()) {
                $person->setEnvelope($iEnvelope);
            }
            
            $person->setFamId($iFamily);
            $person->setFmrId($iFamilyRole);
            
            if (strlen($dMembershipDate) > 0) {
                $person->setMembershipDate($dMembershipDate);
            }
            
            $person->setClsId($iClassification);
            $person->setDateEntered(new DateTime());
            $person->setEnteredBy($_SESSION['user']->getPersonId());
            
            $person->setDateLastEdited(new DateTime());
            $person->setEditedBy($_SESSION['user']->getPersonId());
            
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
                    $logger->warn($NotificationEmail->getError());
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
                    sqlCustomField($sSQL, $rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $sPhoneCountry);
                    
                    echo $rowCustomField->getCustomField()." ".$currentFieldData;
              }
            }
            
            // chop off the last 2 characters (comma and space) added in the last while loop iteration.
            if ($sSQL > '') {
                $sSQL = 'REPLACE INTO person_custom SET '.$sSQL.' per_ID = '.$iPersonID;
                //Execute the SQL
                RunQuery($sSQL);
            }
        }

        // Check for redirection to another page after saving information: (ie. PersonEditor.php?previousPage=prev.php?a=1;b=2;c=3)
        if ($sPreviousPage != '') {
            $sPreviousPage = str_replace(';', '&', $sPreviousPage);
            Redirect($sPreviousPage.$iPersonID);
        } elseif (isset($_POST['PersonSubmit'])) {
            //Send to the view of this person
            Redirect('PersonView.php?PersonID='.$iPersonID);
        } else {
            //Reload to editor to add another record
            Redirect('PersonEditor.php');
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
        $dMembershipDate = ($person->getMembershipDate() != null)?$person->getMembershipDate()->format('Y-m-d'):"";
        $dFriendDate = ($person->getFriendDate() != null)?$person->getFriendDate()->format('Y-m-d'):"";
        $iClassification = $person->getClsId();
        $iViewAgeFlag = $person->getFlags();
        
        $iFacebookID = $person->getFacebookID();
        $sTwitter = $person->getTwitter();
        $sLinkedIn = $person->getLinkedIn();

        $sPhoneCountry = SelectWhichInfo($sCountry, $fam_Country, false);

        $sHomePhone = ExpandPhoneNumber($sHomePhone, $sPhoneCountry, $bNoFormat_HomePhone);
        $sWorkPhone = ExpandPhoneNumber($sWorkPhone, $sPhoneCountry, $bNoFormat_WorkPhone);
        $sCellPhone = ExpandPhoneNumber($sCellPhone, $sPhoneCountry, $bNoFormat_CellPhone);

        //The following values are True booleans if the family record has a value for the
        //indicated field.  These are used to highlight field headers in red.
        $bFamilyAddress1 = strlen($fam_Address1);
        $bFamilyAddress2 = strlen($fam_Address2);
        $bFamilyCity = strlen($fam_City);
        $bFamilyState = strlen($fam_State);
        $bFamilyZip = strlen($fam_Zip);
        $bFamilyCountry = strlen($fam_Country);
        $bFamilyHomePhone = strlen($fam_HomePhone);
        $bFamilyWorkPhone = strlen($fam_WorkPhone);
        $bFamilyCellPhone = strlen($fam_CellPhone);
        $bFamilyEmail = strlen($fam_Email);

        $bFacebookID = $iFacebookID != 0;
        $bTwitter =  strlen($sTwitter);
        $bLinkedIn = strlen($sLinkedIn);

        $aCustomData = [];        
        
        $aCustomData[] = $iPersonID;
        $aCustomData['per_ID'] = $iPersonID;
                
        foreach ($ormCustomFields as $ormCustomField) {
          //echo $ormCustomField->getCustomField();
          $personCustom = PersonCustomQuery::Create()
                          ->withcolumn($ormCustomField->getCustomField())
                          ->findOneByPerId($iPersonID);
                          
        if (!is_null($personCustom)) {
            $aCustomData[] = $personCustom->getVirtualColumn($ormCustomField->getCustomField());
            $aCustomData[$ormCustomField->getCustomField()] = $personCustom->getVirtualColumn($ormCustomField->getCustomField());
          }
        }
        
         
        //print_r($aCustomData);        
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

//Get Classifications for the drop-down
// Get Field Security List Matrix
$ormClassifications = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(1);

//Get Families for the drop-down
if ($_SESSION['user']->isGdrpDpoEnabled()) {// only GDRP Pdo can see the super deactivated members
   $ormFamilies = FamilyQuery::Create()
                  ->orderByName()
                  ->find();
} else {
   $ormFamilies = FamilyQuery::Create()
                  ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
                  ->orderByName()
                  ->find();
}

//Get Family Roles for the drop-down
$ormFamilyRoles = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(2);

require 'Include/Header.php';

if ($iFamily != 0) {
  $theFamily = FamilyQuery::Create()
                  ->findOneById($iFamily);
                  
  $sAddress1 = $theFamily->getAddress1();
  $sAddress2 = $theFamily->getAddress2();
  $sCity     = $theFamily->getCity();
  $sState    = $theFamily->getState();
  $sCountry  = $theFamily->getCountry();
  $sZip      = $theFamily->getZip();
}

?>
<form method="post" action="PersonEditor.php?PersonID=<?= $iPersonID ?>" name="PersonEditor">
    <div class="alert alert-info alert-dismissable">
        <i class="fa fa-info"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong><span
                style="color: red;"><?= _('Red text') ?></span></strong> <?php echo _('indicates items inherited from the associated family record.'); ?>
    </div>
    <?php if ($bErrorFlag) {
    ?>
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <?= _('Invalid fields or selections. Changes not saved! Please correct and try again!') ?>
        </div>
    <?php
} ?>
    <div class="box box-info clearfix">
        <div class="box-header with-border">
            <h3 class="box-title"><?= _('Personal Info') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="box-body">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2">
                        <label><?= _('Gender') ?>:</label>
                        <select name="Gender" class="form-control input-sm">
                            <option value="0"><?= _('Select Gender') ?></option>
                            <option value="0" disabled>-----------------------</option>
                            <option value="1" <?php if ($iGender == 1) {
        echo 'selected';
    } ?>><?= _('Male') ?></option>
                            <option value="2" <?php if ($iGender == 2) {
        echo 'selected';
    } ?>><?= _('Female') ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="Title"><?= _('Title') ?>:</label>
                        <input type="text" name="Title" id="Title"
                               value="<?= htmlentities(stripslashes($sTitle), ENT_NOQUOTES, 'UTF-8') ?>"
                               class="form-control" placeholder="<?= _('Mr., Mrs., Dr., Rev.') ?>">
                    </div>
                </div>
                <p/>
                <div class="row">
                    <div class="col-md-4">
                        <label for="FirstName"><?= _('First Name') ?>:</label>
                        <input type="text" name="FirstName" id="FirstName"
                               value="<?= htmlentities(stripslashes($sFirstName), ENT_NOQUOTES, 'UTF-8') ?>"
                               class="form-control">
                        <?php if ($sFirstNameError) {
        ?><br><font
                            color="red"><?php echo $sFirstNameError ?></font><?php
    } ?>
                    </div>

                    <div class="col-md-2">
                        <label for="MiddleName"><?= _('Middle Name') ?>:</label>
                        <input type="text" name="MiddleName" id="MiddleName"
                               value="<?= htmlentities(stripslashes($sMiddleName), ENT_NOQUOTES, 'UTF-8') ?>"
                               class="form-control">
                        <?php if ($sMiddleNameError) {
        ?><br><font
                            color="red"><?php echo $sMiddleNameError ?></font><?php
    } ?>
                    </div>

                    <div class="col-md-4">
                        <label for="LastName"><?= _('Last Name') ?>:</label>
                        <input type="text" name="LastName" id="LastName"
                               value="<?= htmlentities(stripslashes($sLastName), ENT_NOQUOTES, 'UTF-8') ?>"
                               class="form-control">
                        <?php if ($sLastNameError) {
        ?><br><font
                            color="red"><?php echo $sLastNameError ?></font><?php
    } ?>
                    </div>

                    <div class="col-md-1">
                        <label for="Suffix"><?= _('Suffix') ?>:</label>
                        <input type="text" name="Suffix" id="Suffix"
                               value="<?= htmlentities(stripslashes($sSuffix), ENT_NOQUOTES, 'UTF-8') ?>"
                               placeholder="<?= _('Jr., Sr., III') ?>" class="form-control">
                    </div>
                </div>
                <p/>
                <div class="row">
                    <div class="col-md-2">
                        <label><?= _('Birth Month') ?>:</label>
                        <select name="BirthMonth" class="form-control input-sm">
                            <option value="0" <?php if ($iBirthMonth == 0) {
        echo 'selected';
    } ?>><?= _('Select Month') ?></option>
                            <option value="01" <?php if ($iBirthMonth == 1) {
        echo 'selected';
    } ?>><?= _('January') ?></option>
                            <option value="02" <?php if ($iBirthMonth == 2) {
        echo 'selected';
    } ?>><?= _('February') ?></option>
                            <option value="03" <?php if ($iBirthMonth == 3) {
        echo 'selected';
    } ?>><?= _('March') ?></option>
                            <option value="04" <?php if ($iBirthMonth == 4) {
        echo 'selected';
    } ?>><?= _('April') ?></option>
                            <option value="05" <?php if ($iBirthMonth == 5) {
        echo 'selected';
    } ?>><?= _('May') ?></option>
                            <option value="06" <?php if ($iBirthMonth == 6) {
        echo 'selected';
    } ?>><?= _('June') ?></option>
                            <option value="07" <?php if ($iBirthMonth == 7) {
        echo 'selected';
    } ?>><?= _('July') ?></option>
                            <option value="08" <?php if ($iBirthMonth == 8) {
        echo 'selected';
    } ?>><?= _('August') ?></option>
                            <option value="09" <?php if ($iBirthMonth == 9) {
        echo 'selected';
    } ?>><?= _('September') ?></option>
                            <option value="10" <?php if ($iBirthMonth == 10) {
        echo 'selected';
    } ?>><?= _('October') ?></option>
                            <option value="11" <?php if ($iBirthMonth == 11) {
        echo 'selected';
    } ?>><?= _('November') ?></option>
                            <option value="12" <?php if ($iBirthMonth == 12) {
        echo 'selected';
    } ?>><?= _('December') ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label><?= _('Birth Day') ?>:</label>
                        <select name="BirthDay" class="form-control input-sm">
                            <option value="0"><?= _('Select Day') ?></option>
                            <?php for ($x = 1; $x < 32; $x++) {
        if ($x < 10) {
            $sDay = '0'.$x;
        } else {
            $sDay = $x;
        } ?>
                                <option value="<?= $sDay ?>" <?php if ($iBirthDay == $x) {
            echo 'selected';
        } ?>><?= $x ?></option>
                            <?php
    } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label><?= _('Birth Year') ?>:</label>
                        <input type="text" name="BirthYear" value="<?php echo $iBirthYear ?>" maxlength="4" size="5"
                               placeholder="yyyy" class="form-control input-sm">
                        <?php if ($sBirthYearError) {
        ?><font color="red"><br><?php echo $sBirthYearError ?>
                            </font><?php
    } ?>
                        <?php if ($sBirthDateError) {
        ?><font
                            color="red"><?php echo $sBirthDateError ?></font><?php
    } ?>
                    </div>
                    <div class="col-md-2">
                        <label><?= _('Hide Age') ?></label><br/>
                        <input type="checkbox" name="HideAge" value="1" <?php if ($bHideAge) {
        echo ' checked';
    } ?> />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-info clearfix">
        <div class="box-header with-border">
            <h3 class="box-title"><?= _("Person or Family Info") ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="box-body">
            <div class="form-group col-md-3">
                <label><?= _("Person or Family Role") ?>:</label>
                <select name="FamilyRole" class="form-control input-sm">
                    <option value="0"><?= _("Unassigned") ?></option>
                    <option value="0" disabled>-----------------------</option>
                    <?php 
                        foreach ($ormFamilyRoles as $ormFamilyRole) {
                            echo '<option value="'.$ormFamilyRole->getOptionId().'"';
                            if ($iFamilyRole == $ormFamilyRole->getOptionId()) {
                                echo ' selected';
                            }
                            echo '>'.$ormFamilyRole->getOptionName().'&nbsp;';
                        }
                    
                     ?>
                </select>
            </div>

            <div class="form-group col-md-9">
                <label><?= _('Person or Family address'); ?>:</label>
                <select name="Family" size="8" class="form-control" id="optionFamily">
                    <option value="0" selected><?= _('Unassigned') ?></option>
                    <option value="-1" ><?= _("Create a new Address or A new family (using last name)") ?></option>
                    <option value="0" disabled>-----------------------</option>
                    <?php 
                        foreach ($ormFamilies as $ormFamily) {
                            echo '<option value="'.$ormFamily->getId().'"';
                            if ($iFamily == $ormFamily->getId() || $_GET['FamilyID'] == $ormFamily->getId()) {
                                echo ' selected';
                            }
                            echo '>'.$ormFamily->getName().'&nbsp;'.FormatAddressLine($ormFamily->getAddress1(), $ormFamily->getCity(), $ormFamily->getState());
                        }
                     ?>
                </select>
            </div>
            
            <!-- start of the new code PL -->
            <div id="familyAddress">
              <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                      <div class="box-header with-border">
                        <h3 class="box-title"><?= _('Person or Family Address') ?></h3>
                      </div>
                    </div><!-- /.box-header -->
                </div>
                <p/>
                <div class="row">
                  <div class="col-md-6">
                    <label><?= _('Address') ?> 1:</label>
                      <input type="text" name="FamAddress1" value="<?= htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') ?>" size="50" maxlength="250"  class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label><?= _('Address') ?> 2:</label>
                    <input type="text" Name="FamAddress2" value="<?= htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') ?>" size="50" maxlength="250"  class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label><?= _('City') ?>:</label>
                    <input type="text" Name="FamCity" value="<?= htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') ?>" maxlength="50"  class="form-control">
                  </div>
                </div>
                <p/>
                <div class="row">
                  <div <?= (SystemConfig::getValue('bStateUnusefull'))?"style=\"display: none;\"":"class=\"form-group col-md-3\" "?>>
                    <label for="StatleTextBox"><?= _('State')?>: </label><br>
                    <?php                          
                        $statesDDF = new StateDropDown();     
                        echo $statesDDF->getDropDown($sState,"FamState");
                     ?>
                  </div>
                  <div <?= (SystemConfig::getValue('bStateUnusefull'))?"style=\"display: none;\"":"class=\"form-group col-md-3\" "?>>
                    <label><?= _('None US/CND State') ?>:</label>
                    <input type="text"  class="form-control" name="FamStateTextbox" value="<?php if ($sCountry != 'United States' && $sCountry != 'Canada') {
                        echo htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8');
                    } ?>" size="20" maxlength="30">
                  </div>
                  <div class="form-group col-md-3">
                    <label><?= _('Zip')?>:</label>
                    <input type="text" Name="FamZip"  class="form-control" <?php
                                    // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
                                    if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
                                        echo 'style="text-transform:uppercase" ';
                                    }
                                    echo 'value="'.htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8').'" '; ?>
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
            
        </div>
    </div>
    <div class="box box-info clearfix">
        <div class="box-header with-border">
            <h3 class="box-title"><?= _('Contact Info') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="box-body">
           <div id="personAddress">
            <?php if (!SystemConfig::getValue('bHidePersonAddress')) { /* Person Address can be hidden - General Settings */ ?>
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-4">
                            <label>
                                <?php if ($bFamilyAddress1) {
                        echo '<span style="color: red;">';
                    }

                        echo _('Address').' 1:';

                        if ($bFamilyAddress1) {
                            echo '</span>';
                        } ?>
                            </label>
                            <input type="text" name="Address1"
                                   value="<?= htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') ?>"
                                   size="30" maxlength="50" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>
                                <?php if ($bFamilyAddress2) {
                            echo '<span style="color: red;">';
                        }

                        echo _('Address').' 2:';

                        if ($bFamilyAddress2) {
                            echo '</span>';
                        } ?>
                            </label>
                            <input type="text" name="Address2"
                                   value="<?= htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') ?>"
                                   size="30" maxlength="50" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>
                                <?php if ($bFamilyCity) {
                            echo '<span style="color: red;">';
                        }

                        echo _('City').':';

                        if ($bFamilyCity) {
                            echo '</span>';
                        } ?>
                            </label>
                            <input type="text" name="City"
                                   value="<?= htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') ?>"
                                   class="form-control">
                        </div>
                    </div>
                </div>
                <p/>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label for="StatleTextBox">
                            <?php if ($bFamilyState) {
                            echo '<span style="color: red;">';
                        }

                        echo _('State').':';

                        if ($bFamilyState) {
                            echo '</span>';
                        } ?>
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
                               size="20" maxlength="30" class="form-control">
                    </div>

                    <div class="form-group col-md-1">
                        <label for="Zip">
                            <?php if ($bFamilyZip) {
                            echo '<span style="color: red;">';
                        }

                        echo _('Zip').':';

                        if ($bFamilyZip) {
                            echo '</span>';
                        } ?>
                        </label>
                        <input type="text" name="Zip" class="form-control"
                            <?php
                            // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
                            if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
                                echo 'style="text-transform:uppercase" ';
                            }

                        echo 'value="'.htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8').'" '; ?>
                               maxlength="10" size="8">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="Zip">
                            <?php if ($bFamilyCountry) {
                            echo '<span style="color: red;">';
                        }

                        echo _('Country').':';

                        if ($bFamilyCountry) {
                            echo '</span>';
                        } ?>
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
                            echo '<span style="color: red;">'._('Home Phone').':</span>';
                        } else {
                            echo _('Home Phone').':';
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <input type="text" name="HomePhone"
                               value="<?= htmlentities(stripslashes($sHomePhone), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="30" class="form-control" data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormat')?>"' data-mask>
                        <br><input type="checkbox" name="NoFormat_HomePhone"
                                   value="1" <?php if ($bNoFormat_HomePhone) {
                            echo ' checked';
                        } ?>><?= _('Do not auto-format') ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="WorkPhone">
                        <?php
                        if ($bFamilyWorkPhone) {
                            echo '<span style="color: red;">'._('Work Phone').':</span>';
                        } else {
                            echo _('Work Phone').':';
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <input type="text" name="WorkPhone"
                               value="<?= htmlentities(stripslashes($sWorkPhone), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="30" class="form-control"
                               data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormatWithExt')?>"' data-mask/>
                        <br><input type="checkbox" name="NoFormat_WorkPhone"
                                   value="1" <?php if ($bNoFormat_WorkPhone) {
                            echo ' checked';
                        } ?>><?= _('Do not auto-format') ?>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <label for="CellPhone">
                        <?php
                        if ($bFamilyCellPhone) {
                            echo '<span style="color: red;">'._('Mobile Phone').':</span>';
                        } else {
                            echo _('Mobile Phone').':';
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <input type="text" name="CellPhone"
                               value="<?= htmlentities(stripslashes($sCellPhone), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="30" class="form-control" data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormatCell')?>"' data-mask>
                        <br><input type="checkbox" name="NoFormat_CellPhone"
                                   value="1" <?php if ($bNoFormat_CellPhone) {
                            echo ' checked';
                        } ?>><?= _('Do not auto-format') ?>
                    </div>
                </div>
            </div>
            <p/>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="Email">
                        <?php
                        if ($bFamilyEmail) {
                            echo '<span style="color: red;">'._('Email').':</span></td>';
                        } else {
                            echo _('Email').':</td>';
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </div>
                        <input type="text" name="Email"
                               value="<?= htmlentities(stripslashes($sEmail), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class="form-control">
                        <?php if ($sEmailError) {
                            ?><font color="red"><?php echo $sEmailError ?></font><?php
                        } ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="WorkEmail"><?= _('Work / Other Email') ?>:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </div>
                        <input type="text" name="WorkEmail"
                               value="<?= htmlentities(stripslashes($sWorkEmail), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class="form-control">
                        <?php if ($sWorkEmailError) {
                            ?><font
                            color="red"><?php echo $sWorkEmailError ?></font></td><?php
                        } ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="FacebookID">
                        <?php
                        if ($bFacebookID) {
                            echo '<span style="color: red;">'._('Facebook').' ID:</span></td>';
                        } else {
                            echo _('Facebook').' ID:</td>';
                        }
                        ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-facebook"></i>
                        </div>
                        <input type="text" name="Facebook"
                               value="<?= htmlentities(stripslashes($iFacebookID), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class="form-control">
                        <?php if ($sFacebookError) {
                            ?><font color="red"><?php echo $sFacebookError ?></font><?php
                        } ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="Twitter"><?= _('Twitter') ?>:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-twitter"></i>
                        </div>
                        <input type="text" name="Twitter"
                               value="<?= htmlentities(stripslashes($sTwitter), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                               maxlength="100" class="form-control">
                        <?php if ($sTwitterError) {
                            ?><font
                            color="red"><?php echo $sTwitterError ?></font></td><?php
                        } ?>
                    </div>
                </div>
                <div class="form-group col-md-4">
                      <label for="LinkedIn"><?= _('LinkedIn') ?>:</label>
                      <div class="input-group">
                          <div class="input-group-addon">
                              <i class="fa fa-linkedin"></i>
                          </div>
                          <input type="text" name="LinkedIn"
                                 value="<?= htmlentities(stripslashes($sLinkedIn), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                                 maxlength="100" class="form-control">
                          <?php if ($sLinkedInError) {
                            ?><font
                              color="red"><?php echo $sLinkedInError ?></font></td><?php
                        } ?>
                      </div>
                  </div>
            </div>
        </div>
    </div>
    <div class="box box-info clearfix">
        <div class="box-header with-border">
            <h3 class="box-title"><?= _('Membership Info') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="box-body">
            <div class="row">
              <div class="form-group col-md-3 col-lg-3">
                <label><?= _('Classification') ?>:</label>
                <select name="Classification" class="form-control">
                  <option value="0"><?= _('Unassigned') ?></option>
                  <option value="0" disabled>-----------------------</option>
                  
                  <?php 
                       foreach ($ormClassifications as $ormClassification) {
                           echo '<option value="'.$ormClassification->getOptionId().'"';
                           if ($iClassification == $ormClassification->getOptionId()) {
                               echo ' selected';
                           }
                           echo '>'.$ormClassification->getOptionName().'&nbsp;';
                       }
                        ?>
                </select>
              </div>
                <div class="form-group col-md-3 col-lg-3">
                    <label><?= _('Membership Date') ?>:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <!-- Philippe Logel -->
                        <input type="text" name="MembershipDate" class="form-control date-picker"
                               value="<?= OutputUtils::change_date_for_place_holder($dMembershipDate) ?>" maxlength="10" id="sel1" size="11"
                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                        <?php if ($sMembershipDateError) {
                            ?><font
                            color="red"><?= $sMembershipDateError ?></font><?php
                        } ?>
                    </div>
                </div>
              <?php if (!SystemConfig::getBooleanValue('bHideFriendDate')) { /* Friend Date can be hidden - General Settings */ ?>
                <div class="form-group col-md-3 col-lg-3">
                  <label><?= _('Friend Date') ?>:</label>
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" name="FriendDate" class="form-control date-picker"
                           value="<?= OutputUtils::change_date_for_place_holder($dFriendDate) ?>" maxlength="10" id="sel2" size="10"
                           placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                    <?php if ($sFriendDateError) {
                            ?><font
                      color="red"><?php echo $sFriendDateError ?></font><?php
                        } ?>
                  </div>
                </div>
              <?php
                        } ?>
            </div>
        </div>
    </div>
  <?php if ($numCustomFields > 0) {
                            ?>
    <div class="box box-info clearfix">
        <div class="box-header with-border">
            <h3 class="box-title"><?= _('Custom Fields') ?></h3>
            <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
            </div>
        </div><!-- /.box-header -->
        <div class="box-body">
            <?php if ($numCustomFields > 0) {

              $cnt = 0;
              
              foreach ($ormCustomFields as $rowCustomField) {

                  if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
                      if ($cnt == 0) {
                          echo "<div class='row'>";
                      }


                      echo "<div class=\"form-group col-md-4\"><label>".$rowCustomField->getCustomName().'</label><br>';

                      if (array_key_exists($rowCustomField->getCustomField(), $aCustomData)) {
                          $currentFieldData = trim($aCustomData[$rowCustomField->getCustomField()]);
                      } else {
                          $currentFieldData = '';
                      }

                      if ($type_ID == 11) {
                          $custom_Special = $sPhoneCountry;
                      }

                      OutputUtils::formCustomField($rowCustomField->getTypeId(), $rowCustomField->getCustomField(), $currentFieldData, $rowCustomField->getCustomSpecial(), !isset($_POST['PersonSubmit']));
                      if (isset($aCustomErrors[$rowCustomField->getTypeId()])) {
                          echo '<span style="color: red; ">'.$aCustomErrors[$rowCustomField->getTypeId()].'</span>';
                      }
                      echo '</div>';

                      $cnt+=1;
                      $cnt%=3;

                      if ($cnt == 0) {
                          echo '</div>';
                      }
                  }
              }                            

              if ($cnt) {
                  echo '</div>';
              }
          } ?>
        </div>
    </div>
  <?php
    } 
  ?>
    <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PersonSubmit">
    <?php if ($_SESSION['user']->isAddRecordsEnabled()) {
                            echo '<input type="submit" class="btn btn-success" value="'._('Save and Add').'" name="PersonSubmitAndAdd">';
                        } ?>
    <input type="button" class="btn btn-default" value="<?= _('Cancel') ?>" name="PersonCancel"
           onclick="javascript:document.location='SelectList.php?mode=person';">
</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.iFamily  = <?= $iFamily ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/PersonEditor.js"></script>

<?php require 'Include/Footer.php' ?>
