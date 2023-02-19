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

    // unset jwt token
    if (isset($_COOKIE[$this->getUserName()])) {
        unset($_COOKIE[$this->getUserName()]);
        setcookie($this->getUserName(), null, -1, '/');
    }

    $currentUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

    if (!is_null($currentUser)) {

      $currentUser->setShowPledges($_SESSION['sshowPledges']);
      $currentUser->setShowPayments($_SESSION['sshowPayments']);
      $currentUser->setDefaultFY($_SESSION['idefaultFY']);
      $currentUser->setCurrentDeposit($_SESSION['iCurrentDeposit']);
      $currentUser->setIsLoggedIn(false);

      $currentUser->save();
    }
}

$_COOKIE = [];
$_SESSION = [];
session_destroy();

RedirectUtils::Redirect('Login.php');
exit;
