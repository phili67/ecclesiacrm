<?php
/*******************************************************************************
 *
 *  filename    : Login.php
 *  website     : http://www.ecclesiacrm.com
 *  description : page header used for most pages
 *
 *  Copyright 2017 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';

$bSuppressSessionTests = true; // DO NOT MOVE
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Emails\LockedEmail;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\Bootstrapper;

use RobThree\Auth\TwoFactorAuth;


if (!Bootstrapper::isDBCurrent()) {
    RedirectUtils::Redirect('SystemDBUpdate.php');
    exit;
}

$twofa = false;

// Get the UserID out of user name submitted in form results
if (isset($_POST['User'])) {
    // Get the information for the selected user
    $UserName = InputUtils::LegacyFilterInput($_POST['User'], 'string', 32);
    $currentUser = UserQuery::create()->findOneByUserName($UserName);
    if ($currentUser == null) {
        // Set the error text
        $sErrorText = _('Invalid login or password');
    } // Block the login if a maximum login failure count has been reached
    elseif ($currentUser->isLocked()) {
        $sErrorText = _('Too many failed logins: your account has been locked.  Please contact an administrator.');
    } // test if the account has been deactivated
    elseif ($currentUser->getIsDeactivated()) {
        $sErrorText = _('This account has been deactiveted by an administrator.');
    } // Does the password match?
    elseif (!$currentUser->isPasswordValid($_POST['Password'])) {
        // Increment the FailedLogins
        $currentUser->setFailedLogins($currentUser->getFailedLogins() + 1);
        $currentUser->save();
        if (!empty($currentUser->getEmail()) && $currentUser->isLocked()) {
            $lockedEmail = new LockedEmail($currentUser);
            $lockedEmail->send();
        }

        // Set the error text
        $sErrorText = _('Invalid login or password');
    } else {
        // manage the token for the secret JWT UUID
        if ( !is_null($currentUser->getTwoFaSecret()) and $currentUser->getTwoFaSecretConfirm() and !isset($_POST['twofafield']) ) {
            // we're in case of a two factors authentication
            session_destroy();

            session_start();
            $_SESSION['username'] = $UserName;
            $_SESSION['password'] = $_POST['Password'];

            $twofa = true;
        } else {
            $validate2FA = false;

            if ( isset($_POST['twofafield']) ){
                $currentUser = UserQuery::create()->findOneByUserName($UserName);
                $code = $_POST['twofafield'];

                $tfa = new TwoFactorAuth('EcclesiaCRM');

                $secret = $currentUser->getTwoFaSecret();

                $origin = $currentUser->getTwoFaRescueDateTime();
                $target = new DateTime('now');
                $seconds = $target->getTimestamp() - $origin->getTimestamp();// difference in seconds

                if ( $tfa->verifyCode($secret, $code) or ( strlen($code) == 10 and strstr($currentUser->getTwoFaRescuePasswords(), $code) and $seconds < 60 ) ) {
                    $validate2FA = true;
                } else {
                    session_destroy();

                    session_start();
                    $_SESSION['username'] = $UserName;
                    $_SESSION['password'] = $_POST['Password'];

                    $twofa = true;
                }
            } else {
                $validate2FA = true;
            }

            if ($validate2FA) {
                $currentUser->LoginPhaseActivations();

                if (isset($_SESSION['lastPage'])) {
                    RedirectUtils::Redirect($_SESSION['lastPage']);
                    exit;
                }
                RedirectUtils::Redirect('v2/dashboard');
                exit;
            }
        }
    }
} elseif (isset($_GET['username'])) {
    $urlUserName = $_GET['username'];
}

$id = 0;
$type = "";
$lastPage = null;

// we hold down the last id
if (isset($_SESSION['iUserID'])) {
    $id = $_SESSION['iUserID'];
}

// we hold down the last type of login : lock or nothing
if ( isset($_POST['iLoginType']) ) {
    $type = $_POST['iLoginType'];
} else if (isset($_SESSION['iLoginType'])) {
    $type = $_SESSION['iLoginType'];
}

// last page
if ( isset($_SESSION['lastPage']) ) {
    $lastPage = $_SESSION['lastPage'];
}


if (isset($_GET['session']) && $_GET['session'] == "Lock") {// We are in a Lock session
    $type = $_SESSION['iLoginType'] = "Lock";
}

if (empty($urlUserName)) {
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
        $urlUserName = $user->getUserName();
    } elseif (isset($_SESSION['username'])) {
        $urlUserName = $_SESSION['username'];
    }
}

if (empty($urlPassword)) {
    if (isset($_SESSION['password'])) {
        $urlPassword = $_SESSION['password'];
    }
}

if ( ((isset($_SESSION['iUserID']) and $_SESSION['iUserID'] == 0) or !isset($_SESSION['iUserID'])) and $type == 'Lock' and  isset($_SESSION['username']) ) {
    $currentUser = UserQuery::create()->findOneByUserName($_SESSION['username']);
    $id = $currentUser->getId();
}

// we destroy the session
session_destroy();

// we reopen a new one
session_start();

// we restore only this part
$_SESSION['iLoginType'] = $type;
$_SESSION['username'] = $urlUserName;
$_SESSION['iUserID'] = $id;
$_SESSION['lastPage'] = $lastPage;

if ($type == "Lock" && $id > 0) {// this point is important for the photo in a lock session
    $user = UserQuery::create()->findOneByPersonId($_SESSION['iUserID']);
    $user->setIsLoggedIn(false);
    $user->save();

    $person = PersonQuery::Create()
        ->findOneByID($_SESSION['iUserID']);
}

if (is_null($person)) {
    $type = "";
    $_SESSION['iLoginType'] = "";
}

// Set the page title and include HTML header
$sPageTitle = _('Login');
require 'Include/HeaderNotLoggedIn.php';

?>

<!-- login-box -->
<div class="login-box" id="Login" <?= ($_SESSION['iLoginType'] != "Lock") ? "" : 'style="display: none;"' ?>>
    <!-- /.login-logo -->
    <div class="card login-box-body card card-outline card-primary">
        <div class="card-header login-logo">
            Ecclesia<b>CRM</b><?= SystemService::getDBMainVersion() ?>
        </div>
        <div class="card-body login-card-body">

            <p class="login-box-msg">
                <b><?= ChurchMetaData::getChurchName() ?></b><br/>
                <?= _('Please Login') ?>
            </p>

            <?php
            if (isset($_GET['Timeout'])) {
                $loginPageMsg = _('Your previous session timed out.  Please login again.');
            }

            // output warning and error messages
            if (isset($sErrorText)) {
                echo '<div class="alert alert-error text-center">' . $sErrorText . '</div>';
            }
            if (isset($loginPageMsg)) {
                echo '<div class="alert alert-warning text-center">' . $loginPageMsg . '</div>';
            }
            ?>

            <form class="form-signin" role="form" method="post" name="LoginForm" action="Login.php">
                <div class="form-group has-feedback">
                    <div class="input-group">
                        <input type="text" name="User" class= "form-control form-control-sm" value="<?= $urlUserName ?>"
                               placeholder="<?= _('Email/Username') ?>" required>
                        <input type="hidden" name="iLoginType" class="form-control form-control-sm" value="<?= $type ?>">

                        <div class="input-group-append" style="cursor: pointer;">
                            <button tabindex="100" class="btn btn-outline-secondary" type="button" style="width: 39px;">
                                <i class="icon-user fas fa-user"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" name="Password" class= "form-control form-control-sm" data-toggle="password"
                           placeholder="<?= _('Password') ?>" required value="<?= $urlPassword ?>">
                </div>
                <?php if ($twofa): ?>
                <div class="form-group has-feedback">
                    <input type="text" id="TwoFaBox" name="twofafield" class= "form-control form-control-sm twofact_textfield" data-toggle="TwoFaBox"
                           placeholder="<?= _("2FA : OTP key") ?>" required autofocus>
                    <br/>
                </div>
                <?php endif ?>
                <div class="row  mb-3">
                    <!-- /.col -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block"><i
                                class="fas fa-sign-in-alt"></i> <?= _('Login') ?></button>
                    </div>
                </div>
                <p class="mb-1">
                    <?php if (SystemConfig::getBooleanValue('bEnableLostPassword')) {
                        ?>
                        <span class="text-right"><a
                                href="external/password/"><?= _("I forgot my password") ?></a></span>
                        <?php
                    } ?>
                </p>
            </form>

            <?php if (SystemConfig::getBooleanValue('bEnableSelfRegistration')) {
                ?>
                <div class="row  mb-3">
                    <!-- /.col -->
                    <div class="col-12">
                        <a href="<?= SystemURLs::getRootPath() ?>/external/register/" class="btn btn-primary btn-block bg-olive"><i
                                class="fas fa-user-plus"></i> <?= _('Register a new Family'); ?></a><br>
                    </div>
                </div>
                <?php
            } ?>
            <!--<a href="external/family/verify" class="text-center">Verify Family Info</a> -->

        </div>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- lockscreen-wrapper -->
<div class="lockscreen-wrapper" id="Lock" <?= ($_SESSION['iLoginType'] == "Lock") ? "" : 'style="display: none;"' ?>>
    <div class="login-logo">
        Ecclesia<b>CRM</b><?= SystemService::getDBMainVersion() ?>
    </div>

    <p class="login-box-msg">
        <b><?= ChurchMetaData::getChurchName() ?></b><br/>
        <?= _('Please Login') ?>
    </p>


    <div>
        <?php
        if (isset($_GET['Timeout'])) {
            $loginPageMsg = _('Your previous session timed out.  Please login again.');
        }

        // output warning and error messages
        if (isset($sErrorText)) {
            echo '<div class="alert alert-error text-center">' . $sErrorText . '</div>';
        }
        if (isset($loginPageMsg)) {
            echo '<div class="alert alert-warning text-center">' . $loginPageMsg . '</div>';
        }
        ?>
    </div>

    <div class="lockscreen-name text-center"><?= $urlUserName ?></div>

    <form class="lockscreen-credentials" role="form" method="post" name="LoginForm" action="Login.php">
        <div class="lockscreen-item lockscreen-item-pos">
            <!-- lockscreen image -->
            <div class="lockscreen-image">
                <?php if ($_SESSION['iLoginType'] == "Lock") {
                    ?>
                    <img src="<?= str_replace(SystemURLs::getDocumentRoot(), "", $person->getPhoto()->getThumbnailURI()) ?>"
                         alt="User Image">
                    <?php
                } ?>
            </div>
            <!-- /.lockscreen-image -->

            <!-- lockscreen credentials (contains the form) -->
            <div class="lockscreen-credentials">
                <div class="input-group">
                    <input type="hidden" name="User" class="form-control form-control-sm" value="<?= $urlUserName ?>">
                    <input type="hidden" name="iLoginType" class="form-control form-control-sm" value="<?= $type ?>">

                    <input type="password" name="Password" class= "form-control form-control-sm"
                           placeholder="<?= _('Password') ?>" required value="<?= $urlPassword ?>">

                    <div class="input-group-append"><button type="submit" class="btn btn-default"><i class="fas fa-arrow-right text-muted"></i></button></div>
                </div>
            </div>
            <!-- /.lockscreen credentials -->
        </div>

        <?php if ($twofa): ?>
            <div class="form-group twofact">
                <input type="text" id="TwoFaBox" name="twofafield" class= "form-control form-control-sm twofact_textfield" data-toggle="TwoFaBox"
                       placeholder="<?= _("2FA : OTP key") ?>" required autofocus>
                <br/>
            </div>
        <?php endif ?>
    </form>
    <!-- /.lockscreen-item -->
    <div class="help-block text-center">
        <?= _("Enter your password to retrieve your session") ?>
    </div>
    <div class="text-center">
        <a href="#" id="Login-div-appear"><?= _("Or sign in as a different user") ?></a>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.lockscreen-wrapper -->

<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-show-password/bootstrap-show-password.min.js"></script>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.twofa = <?= ($twofa)?'true':'false' ?>;

    <?php
    if ($_SESSION['iLoginType'] == "Lock") {
    ?>
    $('.login-page').addClass('lockscreen').removeClass('login-page');

    $(document).ready(function () {
        $("#Login").hide();
        document.title = 'Lock';
    });

    $("#Login-div-appear").click(function () {
        // 200 is the interval in milliseconds for the fade-in/out, we use jQuery's callback feature to fade
        // in the new div once the first one has faded out

        $("#Lock").fadeOut(100, function () {
            $("#Login").fadeIn(300);
            document.title = 'Login';
            $('.lockscreen').addClass('login-page').removeClass('lockscreen');
        });
    });
    <?php
    } else {
    ?>
    $('.hold-transition').addClass('login-page').removeClass('lockscreen');
    $(document).ready(function () {
        $("#Lock").hide();
        document.title = 'Login';
    });
    <?php
    }
    ?>
    var $buoop = {vs: {i: 13, f: -2, o: -2, s: 9, c: -2}, unsecure: true, api: 4};

    function $buo_f() {
        var e = document.createElement("script");
        e.src = "//browser-update.org/update.min.js";
        document.body.appendChild(e);
    }

    try {
        document.addEventListener("DOMContentLoaded", $buo_f, false)
    } catch (e) {
        window.attachEvent("onload", $buo_f)
    }

    $('#password').password('toggle');
    $("#password").password({
        eyeOpenClass: 'glyphicon-eye-open',
        eyeCloseClass: 'glyphicon-eye-close'
    });
</script>

<?php require 'Include/FooterNotLoggedIn.php'; ?>
