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

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\Service\SystemService;
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
            if (!str_contains($_SERVER['REQUEST_URI'], '/api/')) {
                $_SESSION['lastPage'] = $_SERVER['REQUEST_URI'];
            }
            $_SESSION['tLastOperation'] = time();
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

if (isset($_POST['BulkAddToCart'])) {// for the QueryView.php
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
        $aItemsToProcess = array_filter($aItemsToProcess);
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
