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
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


$_SESSION['sSoftwareInstalledVersion'] = SystemService::getInstalledVersion();

//
// Basic security checks:
//

SessionUser::setCurrentPageName($_SERVER['REQUEST_URI']);

if (empty($bSuppressSessionTests)) {  // This is used for the login page only.
    // Basic security: If the UserID isn't set (no session), redirect to the login page
    if (is_null(SessionUser::getUser())) {
        RedirectUtils::Redirect('session/login');
        exit;
    }

    // Check for login timeout.  If login has expired, redirect to login page
    if (SystemConfig::getValue('iSessionTimeout') > 0) {
        if ((time() - $_SESSION['tLastOperation']) > SystemConfig::getValue('iSessionTimeout')) {
            RedirectUtils::Redirect('session/login');
            exit;
        } else {
            if (!str_contains($_SERVER['REQUEST_URI'], '/api/')) {
                $_SESSION['lastPage'] = $_SERVER['REQUEST_URI'];
            }
            $t = time();
            $_SESSION['user']->setLastoperationDate($t);
            $_SESSION['user']->save();
            $_SESSION['tLastOperation'] = $t;
        }
    }

    if ( SessionUser::getUser()->getNeedPasswordChange() ) {
        $pos = strpos($_SERVER['REQUEST_URI'], "/v2/users/change/password" );
        $path = SystemURLs::getRootPath().'/v2/users/change/password';
        if ($pos === false) {
            RedirectUtils::Redirect('v2/users/change/password');
            exit;
        }
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
if (isset($_SESSION['bHasMagicQuotes']) and !$_SESSION['bHasMagicQuotes']) {
    foreach ($_REQUEST as $key => $value) {
        $value = addslashes_deep($value);
    }
}