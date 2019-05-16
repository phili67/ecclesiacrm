<?php


/*******************************************************************************
 *
 *  filename    : /Include/Functions.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001-2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-1012 Michael Wilt
 *                Copyright 2017 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\Cart;

$personService = new PersonService();
$systemService = new SystemService();

$_SESSION['sSoftwareInstalledVersion'] = SystemService::getInstalledVersion();

//
// Basic security checks:
//

if (empty($bSuppressSessionTests)) {  // This is used for the login page only.
    // Basic security: If the UserID isn't set (no session), redirect to the login page
    if (is_null(SessionUser::getUser())) {
        RedirectUtils::Redirect('Login.php');
        exit;
    }

    // Check for login timeout.  If login has expired, redirect to login page
    if (SystemConfig::getValue('iSessionTimeout') > 0) {
        if ((time() - $_SESSION['tLastOperation']) > SystemConfig::getValue('iSessionTimeout')) {
            RedirectUtils::Redirect('Login.php');
            exit;
        } else {
            if ($_SESSION['lastPage'] != $_SERVER['PHP_SELF']) {
                $_SESSION['lastPage'] = $_SERVER['PHP_SELF'];            
                $_SESSION['tLastOperation'] = time();
            }
        }
    }

    // If this user needs to change password, send to that page
    if (SessionUser::getUser()->getNeedPasswordChange() && !isset($bNoPasswordRedirect)) {
        RedirectUtils::Redirect('UserPasswordChange.php?PersonID='.SessionUser::getUser()->getPersonId());
        exit;
    }

    // Check if https is required

  // Note: PHP has limited ability to access the address bar
  // url.  PHP depends on Apache or other web server
  // to provide this information.  The web server
  // may or may not be configured to pass the address bar url
  // to PHP.  As a workaround this security check is now performed
  // by the browser using javascript.  The browser always has
  // access to the address bar url.  Search for basic security checks
  // in Include/Header-functions.php
}
// End of basic security checks


// if magic_quotes off and array
function addslashes_deep($value)
{
    $value = is_array($value) ?
    array_map('addslashes_deep', $value) :
    addslashes($value);

    return $value;
}

// If Magic Quotes is turned off, do the same thing manually..
if (!isset($_SESSION['bHasMagicQuotes'])) {
    foreach ($_REQUEST as $key => $value) {
        $value = addslashes_deep($value);
    }
}

// Constants
$aPropTypes = [
  1  => _('True / False'),
  2  => _('Date'),
  3  => _('Text Field (50 char)'),
  4  => _('Text Field (100 char)'),
  5  => _('Text Field (Long)'),
  6  => _('Year'),
  7  => _('Season'),
  8  => _('Number'),
  9  => _('Person from Group'),
  10 => _('Money'),
  11 => _('Phone Number'),
  12 => _('Custom Drop-Down List'),
];

$sGlobalMessageClass = 'success';

if (isset($_GET['Registered'])) {
    $sGlobalMessage = _('Thank you for registering your EcclesiaCRM installation.');
}

if (isset($_GET['AllPDFsEmailed'])) {
    $sGlobalMessage = _('PDFs successfully emailed ').$_GET['AllPDFsEmailed'].' '._('families').".";
}

if (isset($_GET['PDFEmailed'])) {
    if ($_GET['PDFEmailed'] == 1) {
        $sGlobalMessage = _('PDF successfully emailed to family members.');
    } else {
        $sGlobalMessage = _('Failed to email PDF to family members.');
    }
}

if (isset($_POST['BulkAddToCart'])) {
    $aItemsToProcess = explode(',', $_POST['BulkAddToCart']);

    if (isset($_POST['AndToCartSubmit'])) {
        if (isset($_SESSION['aPeopleCart'])) {
            $_SESSION['aPeopleCart'] = array_intersect($_SESSION['aPeopleCart'], $aItemsToProcess);
        }
    } elseif (isset($_POST['NotToCartSubmit'])) {
        if (isset($_SESSION['aPeopleCart'])) {
            $_SESSION['aPeopleCart'] = array_diff($_SESSION['aPeopleCart'], $aItemsToProcess);
        }
    } else {
        for ($iCount = 0; $iCount < count($aItemsToProcess); $iCount++) {
            Cart::AddPerson(str_replace(',', '', $aItemsToProcess[$iCount]));
        }
        $sGlobalMessage = $iCount.' '._('item(s) added to the Cart.');
    }
}

// Runs an SQL query.  Returns the result resource.
// By default stop on error, unless a second (optional) argument is passed as false.
function RunQuery($sSQL, $bStopOnError = true)
{
    global $cnInfoCentral;
    mysqli_query($cnInfoCentral, "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
    if ($result = mysqli_query($cnInfoCentral, $sSQL)) {
        return $result;
    } elseif ($bStopOnError) {
        if (SystemConfig::getValue('sLogLevel') == "100") { // debug level
            die(_('Cannot execute query.')."<p>$sSQL<p>".mysqli_error());
        } else {
            die('Database error or invalid data');
        }
    } else {
        return false;
    }
}

function ConvertCartToString($aCartArray)
{
    // Implode the array
    $sCartString = implode(',', $aCartArray);

    // Make sure the comma is chopped off the end
    if (mb_substr($sCartString, strlen($sCartString) - 1, 1) == ',') {
        $sCartString = mb_substr($sCartString, 0, strlen($sCartString) - 1);
    }

    // Make sure there are no duplicate commas
    $sCartString = str_replace(',,', '', $sCartString);

    return $sCartString;
}

/******************************************************************************
 * Returns the proper information to use for a field.
 * Person info overrides Family info if they are different.
 * If using family info and bFormat set, generate HTML tags for text color red.
 * If neither family nor person info is available, return an empty string.
 *****************************************************************************/

function SelectWhichInfo($sPersonInfo, $sFamilyInfo, $bFormat = false)
{
    $finalData = '';
    $isFamily = false;

    if (SystemConfig::getValue('bShowFamilyData')) {
        if ($sPersonInfo != '') {
            $finalData = $sPersonInfo;
        } elseif ($sFamilyInfo != '') {
            $isFamily = true;
            $finalData = $sFamilyInfo;
        }
    } elseif ($sPersonInfo != '') {
        $finalData = $sPersonInfo;
    }

    if ($bFormat && $isFamily) {
        $finalData = $finalData."<i class='fa fa-fw fa-tree'></i>";
    }

    return $finalData;
}

//
// Returns the correct address to use via the sReturnAddress arguments.
// Function value returns 0 if no info was given, 1 if person info was used, and 2 if family info was used.
// We do address lines 1 and 2 in together because seperately we might end up with half family address and half person address!
//
function SelectWhichAddress(&$sReturnAddress1, &$sReturnAddress2, $sPersonAddress1, $sPersonAddress2, $sFamilyAddress1, $sFamilyAddress2, $bFormat = false)
{
    if (SystemConfig::getValue('bShowFamilyData')) {
        if ($bFormat) {
            $sFamilyInfoBegin = "<span style='color: red;'>";
            $sFamilyInfoEnd = '</span>';
        }

        if ($sPersonAddress1 || $sPersonAddress2) {
            $sReturnAddress1 = $sPersonAddress1;
            $sReturnAddress2 = $sPersonAddress2;

            return 1;
        } elseif ($sFamilyAddress1 || $sFamilyAddress2) {
            if ($bFormat) {
                if ($sFamilyAddress1) {
                    $sReturnAddress1 = $sFamilyInfoBegin.$sFamilyAddress1.$sFamilyInfoEnd;
                } else {
                    $sReturnAddress1 = '';
                }
                if ($sFamilyAddress2) {
                    $sReturnAddress2 = $sFamilyInfoBegin.$sFamilyAddress2.$sFamilyInfoEnd;
                } else {
                    $sReturnAddress2 = '';
                }

                return 2;
            } else {
                $sReturnAddress1 = $sFamilyAddress1;
                $sReturnAddress2 = $sFamilyAddress2;

                return 2;
            }
        } else {
            $sReturnAddress1 = '';
            $sReturnAddress2 = '';

            return 0;
        }
    } else {
        if ($sPersonAddress1 || $sPersonAddress2) {
            $sReturnAddress1 = $sPersonAddress1;
            $sReturnAddress2 = $sPersonAddress2;

            return 1;
        } else {
            $sReturnAddress1 = '';
            $sReturnAddress2 = '';

            return 0;
        }
    }
}

function ChopLastCharacter($sText)
{
    return mb_substr($sText, 0, strlen($sText) - 1);
}

function AlternateRowStyle($sCurrentStyle)
{
    if ($sCurrentStyle == 'RowColorA') {
        return 'RowColorB';
    } else {
        return 'RowColorA';
    }
}

function ConvertToBoolean($sInput)
{
    if (empty($sInput)) {
        return false;
    } else {
        if (is_numeric($sInput)) {
            if ($sInput == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            $sInput = strtolower($sInput);
            if (in_array($sInput, ['true', 'yes', 'si'])) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function ConvertFromBoolean($sInput)
{
    if ($sInput) {
        return 1;
    } else {
        return 0;
    }
}

// Returns a string of a person's full name, formatted as specified by $Style
// $Style = 0  :  "Title FirstName MiddleName LastName, Suffix"
// $Style = 1  :  "Title FirstName MiddleInitial. LastName, Suffix"
// $Style = 2  :  "LastName, Title FirstName MiddleName, Suffix"
// $Style = 3  :  "LastName, Title FirstName MiddleInitial., Suffix"
//
function FormatFullName($Title, $FirstName, $MiddleName, $LastName, $Suffix, $Style)
{
    $nameString = '';

    switch ($Style) {

    case 0:
      if ($Title) {
          $nameString .= $Title.' ';
      }
      $nameString .= $FirstName;
      if ($MiddleName) {
          $nameString .= ' '.$MiddleName;
      }
      if ($LastName) {
          $nameString .= ' '.$LastName;
      }
      if ($Suffix) {
          $nameString .= ', '.$Suffix;
      }
      break;

    case 1:
      if ($Title) {
          $nameString .= $Title.' ';
      }
      $nameString .= $FirstName;
      if ($MiddleName) {
          $nameString .= ' '.strtoupper(mb_substr($MiddleName, 0, 1, 'UTF-8')).'.';
      }
      if ($LastName) {
          $nameString .= ' '.$LastName;
      }
      if ($Suffix) {
          $nameString .= ', '.$Suffix;
      }
      break;

    case 2:
      if ($LastName) {
          $nameString .= $LastName.', ';
      }
      if ($Title) {
          $nameString .= $Title.' ';
      }
      $nameString .= $FirstName;
      if ($MiddleName) {
          $nameString .= ' '.$MiddleName;
      }
      if ($Suffix) {
          $nameString .= ', '.$Suffix;
      }
      break;

    case 3:
      if ($LastName) {
          $nameString .= $LastName.', ';
      }
      if ($Title) {
          $nameString .= $Title.' ';
      }
      $nameString .= $FirstName;
      if ($MiddleName) {
          $nameString .= ' '.strtoupper(mb_substr($MiddleName, 0, 1, 'UTF-8')).'.';
      }
      if ($Suffix) {
          $nameString .= ', '.$Suffix;
      }
      break;
  }

    return $nameString;
}

// Prepare data for entry into MySQL database.
// This function solves the problem of inserting a NULL value into MySQL since
// MySQL will not accept 'NULL'.  One drawback is that it is not possible
// to insert the character string "NULL" because it will be inserted as a MySQL NULL!
// This will produce a database error if NULL's are not allowed!  Do not use this
// function if you intend to insert the character string "NULL" into a field.
function MySQLquote($sfield)
{
    $sfield = trim($sfield);

    if ($sfield == 'NULL') {
        return 'NULL';
    } elseif ($sfield == "'NULL'") {
        return 'NULL';
    } elseif ($sfield == '') {
        return 'NULL';
    } elseif ($sfield == "''") {
        return 'NULL';
    } else {
        if ((mb_substr($sfield, 0, 1) == "'") && (mb_substr($sfield, strlen($sfield) - 1, 1)) == "'") {
            return $sfield;
        } else {
            return "'".$sfield."'";
        }
    }
}

function genGroupKey($methodSpecificID, $famID, $fundIDs, $date)
{
    $uniqueNum = 0;
    while (1) {
        $GroupKey = $methodSpecificID.'|'.$uniqueNum.'|'.$famID.'|'.$fundIDs.'|'.$date;
        $sSQL = "SELECT COUNT(plg_GroupKey) FROM pledge_plg WHERE plg_PledgeOrPayment='Payment' AND plg_GroupKey='".$GroupKey."'";
        $rsResults = RunQuery($sSQL);
        list($numGroupKeys) = mysqli_fetch_row($rsResults);
        if ($numGroupKeys) {
            ++$uniqueNum;
        } else {
            return $GroupKey;
        }
    }
}

function requireUserGroupMembership($allowedRoles = null)
{
    if ( isset($_SESSION['updateDataBase']) && $_SESSION['updateDataBase'] == true ) {// we don't have to interfer with this test
      return true;
    }
    
    if (!$allowedRoles) {
        throw new Exception('Role(s) must be defined for the function which you are trying to access.  End users should never see this error unless something went horribly wrong.');
    }
    if ($_SESSION[$allowedRoles] || SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isAddRecordsEnabled()) {  //most of the time the API endpoint will specify a single permitted role, or the user is an admin
        // new SessionUser::getUser()->isAddRecordsEnabled() : Philippe Logel
        return true;
    } elseif (is_array($allowedRoles)) {  //sometimes we might have an array of allowed roles.
        foreach ($allowedRoles as $role) {
            if ($_SESSION[$role]) {
                // The current allowed role is in the user's session variable
                return true;
            }
        }
    }

    //if we get to this point in the code, then the user is not authorized.
    throw new Exception('User is not authorized to access '.debug_backtrace()[1]['function'], 401);
}

function generateGroupRoleEmailDropdown($roleEmails, $href)
{
    foreach ($roleEmails as $role => $Email) {
        if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($Email, SystemConfig::getValue('sToEmailAddress'))) {
            $Email .= SessionUser::getUser()->MailtoDelimiter().SystemConfig::getValue('sToEmailAddress');
        }
        $Email = urlencode($Email);  // Mailto should comply with RFC 2368
    ?>
      <li> <a href="<?= $href.mb_substr($Email, 0, -3) ?>"><?= _($role) ?></a></li>
    <?php
    }
}