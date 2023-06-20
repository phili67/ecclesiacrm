<?php

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

if ( !is_null(SessionUser::getUser()) ) {

    if (!isset($_SESSION['sshowPledges']) || ($_SESSION['sshowPledges'] == '')) {
        $_SESSION['sshowPledges'] = 0;
    }
    if (!isset($_SESSION['sshowPayments']) || ($_SESSION['sshowPayments'] == '')) {
        $_SESSION['sshowPayments'] = 0;
    }
    if (!isset($_SESSION['bSearchFamily']) || ($_SESSION['bSearchFamily'] == '')) {
        $_SESSION['bSearchFamily'] = 0;
    }

    $currentUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

    // unset jwt token
    $userName = $currentUser->getUserName();
    if (isset($_COOKIE[$userName])) {
        unset($_COOKIE[$userName]);
        setcookie($userName, null, -1, '/');
    }

    if (isset($_SESSION['ControllerAdminUserId'])) {
        // in the case the account is in control of an admin
        unset($_SESSION['ControllerAdminUserId']);
        unset($_SESSION['ControllerAdminUserName']);
        unset($_SESSION['ControllerAdminUserSecret']);
        unset($_SESSION['ControllerAdminUserToken']);
    }

    if (!is_null($currentUser)) {

      $currentUser->setShowPledges($_SESSION['sshowPledges']);
      $currentUser->setShowPayments($_SESSION['sshowPayments']);
      $currentUser->setDefaultFY($_SESSION['idefaultFY']);
      $currentUser->setCurrentDeposit($_SESSION['iCurrentDeposit']);
      $currentUser->setIsLoggedIn(false);

      // we've to leave the old jwt secret and token
      $currentUser->setJwtToken(NULL);
      $currentUser->setJwtSecret(NULL);

      $currentUser->save();
    }
}

$_COOKIE = [];
$_SESSION = [];
session_destroy();

RedirectUtils::Redirect('session/login');
exit;
