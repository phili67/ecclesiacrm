<?php
/*******************************************************************************
 *
 *  filename    : SettingsIndividual.php
 *  website     : http://www.ecclesiacrm.com
 *  description : Page where users can modify their own settings
 *                   File copied from SettingsUser.php with minor edits.
 *
 *  Contributors:
 *  2006 Ed Davis
 *  2019 Philippe Logel All rights reserved
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\UserConfig;
use EcclesiaCRM\UserConfigChoicesQuery;

use EcclesiaCRM\Theme;


$iPersonID = SessionUser::getUser()->getPersonId();

// Save Settings
if (isset($_POST['save'])) {
    $new_value = $_POST['new_value'];
    $type = $_POST['type'];
    ksort($type);
    reset($type);
    while ($current_type = current($type)) {
        $id = key($type);
        // Filter Input
        if ($current_type == 'text' || $current_type == 'textarea') {
            $value = InputUtils::LegacyFilterInput($new_value[$id]);
        } elseif ($current_type == 'number') {
            $value = InputUtils::LegacyFilterInput($new_value[$id], 'float');
        } elseif ($current_type == 'date') {
            $value = InputUtils::LegacyFilterInput($new_value[$id], 'date');
        } elseif ($current_type == 'boolean') {
            if ($new_value[$id] != '1') {
                $value = '';
            } else {
                $value = '1';
            }
        } elseif ($current_type == 'choice') {
            $value = $new_value[$id];
        }

        // We can't update unless values already exist.
        $userConf = UserConfigQuery::create()->filterById($id)->findOneByPersonId($iPersonID);

        if (is_null($userConf)) { // If Row does not exist then insert default values.
            // Defaults will be replaced in the following Update
            $userDefault = UserConfigQuery::create()->filterById($id)->findOneByPersonId(0);

            if (!is_null($userDefault)) {
                $userConf = new UserConfig();

                $userConf->setPersonId($iPersonID);
                $userConf->setId($id);
                $userConf->setName($userDefault->getName());
                $userConf->setValue($value);
                $userConf->setType($userDefault->getType());
                $userConf->setChoicesId($userDefault->getChoicesId());
                $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                $userConf->setPermission($userDefault->getPermission());
                $userConf->setCat($userDefault->getCat());

                $userConf->save();
            } else {
                echo '<br> Error on line ' . __LINE__ . ' of file ' . __FILE__;
                exit;
            }
        } else {

            $userConf->setValue($value);

            $userConf->save();

        }
        next($type);
    }

    RedirectUtils::Redirect('SettingsIndividual.php');// to reflect the tooltip change, we have to refresh the page
}

// Set the page title and include HTML header
$sPageTitle = _('My User Settings');
require 'Include/Header.php';

// Get settings
$configs = UserConfigQuery::create()->orderById()->findByPersonId($iPersonID);

$numberRow = 0;
?>
<form method=post action=<?= SystemURLs::getRootPath() ?>/SettingsIndividual.php>
    <div class="row">
        <div class="col-md-6"><!-- begin a card col-md-6 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= _("Classic User Profile Settings") ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7"><label><?= _('Notes') ?></label></div>
                        <div class="col-md-3"><label><?= _('Current Value') ?></label></div>
                        <div class="col-md-2"><label><?= _('Variable name') ?></label></div>
                    </div>
                    <?php
                    $r = 1;

                    // List Individual Settings
                    foreach ($configs

                    as $config) {
                    if ($config->getName() == "bSidebarExpandOnHover"
                        || !(($config->getPermission() == 'TRUE') || $config->getName() != "bSidebarExpandOnHover" || SessionUser::getUser()->isAdmin())) {
                        continue;
                    }
                    ?>
                    <div class="row">
                        <?php
                        // Cancel, Save Buttons every 20 rows or the bsiderbar collapsed
                        if ($r == 20 || $config->getName() == "bSidebarCollapse") {
                        ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-7">
                            <input type=submit class='btn btn-default' name=cancel value="<?= _('Cancel') ?>">
                            <input type=submit class='btn btn-primary' name=save value="<?= _('Save Settings') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- end a card col-md-6 -->
        <?php
        $numberRow++;
        if ($numberRow % 2 == 0) {
        ?>
    </div>
    <div class="row">
        <?php
        }
        ?>
        <div class="col-md-6"><!-- begin a card col-md-6 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= ($config->getName() == "bSidebarCollapse") ? _("Skin") : _("Other Settings") ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7"><label><?= _('Notes') ?></label></div>
                        <div class="col-md-3"><label><?= _('Current Value') ?></label></div>
                        <div class="col-md-2"><label><?= _('Variable name') ?></label></div>
                    </div>
                    <div class="row">
                        <?php
                        $r = 1;
                        }
                        // Variable Name & Type
                        ?>
                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-2">
                                    <?php if ($config->getName() == 'sStyleSideBar') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 20%; float: left; height: 20px; background: #222"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'sStyleNavBarColor') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 7px; background: #e4a337"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 20%; float: left; height: 20px; background: #e2dddd"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'sStyleBrandLinkColor') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 20%; float: left; height: 7px; background: #4674aa"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 20%; float: left; height: 20px; background: #e2dddd"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'sStyleSideBarColor') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 20%; float: left; height: 7px; background: #4674aa"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 20%; float: left; height: 20px; background: #e2dddd">
                                                <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1); padding: 2px;"
                                                     class="clearfix">
                                                    <span
                                                        style="display:block; top:10px; width: 90%; float: left; height: 7px; background: #5aac84">
                                                    <span
                                                        style="display:block; top:10px; width: 10%; float: left; height: 7px; background: #e2dddd"></span>
                                                </div>
                                            </span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'bSidebarCollapse') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 7%; float: left; height: 7px; background: #fefefe"></span>
                                                <span
                                                    style="display:block; width: 93%; float: left; height: 7px; background: #fefefe"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 7%; float: left; height: 20px; background: #222"></span>
                                                <span
                                                    style="display:block; width: 93%; float: left; height: 20px; background: #f4f5f7"></span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'sStyleFontSize') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 20%; float: left; height: 20px; background: #e2dddd"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7">
                                                            &nbsp; <big>A</big>A<small>A</small>
                                                        </span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'sDarkMode') { ?>
                                        <a href="javascript:void(0)" data-skin="skin-black"
                                           style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)"
                                           class="clearfix full-opacity-hover">
                                            <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                                            <span
                                                style="display:block; width: 20%; float: left; height: 7px; background: #d19b34"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 7px; background: #d19b34"></span>
                                            </div>
                                            <div>
                                            <span
                                                style="display:block; width: 20%; float: left; height: 20px; background: #524e4e"></span>
                                                <span
                                                    style="display:block; width: 80%; float: left; height: 20px; background: #6f6f6f;color:white">
                                                            <big>A</big>A<small>A</small>
                                                        </span>
                                            </div>
                                        </a>
                                    <?php } else if ($config->getName() == 'sMapExternalProvider') { ?>
                                        <span class="align-text-bottom"><i class="fa fa-map-o fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'bEmailMailto' || $config->getName() == 'sMailtoDelimiter') { ?>
                                        <span class="align-text-bottom"><i class="fa fa-envelope fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'bUSAddressVerification') { ?>
                                        <span class="align-text-bottom"><i class="fa fa-address-card-o fa-lg"></i><i
                                                class="fa fa-check fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'bShowTooltip') { ?>
                                        <span class="align-text-bottom"><i class="fa fa-info-circle fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'sCSVExportDelemiter' || $config->getName() == 'sCSVExportCharset') { ?>
                                        <span class="align-text-bottom"><i class="fa fa-file-excel-o fa-lg"></i></span>
                                    <?php } ?>
                                </div>
                                <div class="col-md-10">
                                    <?= _($config->getTooltip()) ?>
                                    <input type=hidden name="type[<?= $config->getId() ?>]"
                                           value="<?= $config->getType() ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <?php
                            // Current Value
                            if ($config->getType() == 'text') {
                                ?>
                                <input class="form-control input-md" type=text size=30 maxlength=255
                                       name="new_value[<?= $config->getId() ?>]"
                                       value="<?= htmlspecialchars($config->getValue(), ENT_QUOTES) ?>">
                                <?php
                            } elseif ($config->getType() == 'textarea') {
                                ?>
                                <textarea rows=4 cols=30 name="new_value[<?= $config->getId() ?>]">
                                            <?= htmlspecialchars($config->getValue(), ENT_QUOTES) ?>
                                        </textarea>
                                <?php
                            } elseif ($config->getType() == 'number' || $config->getType() == 'date') {
                                ?>
                                <input type=text size=15 maxlength=15 name="new_value[<?= $config->getId() ?>]"
                                       value="<?= $config->getValue() ?>">
                                <?php
                            } elseif ($config->getType() == 'boolean') {
                                if ($config->getValue()) {
                                    $sel2 = 'SELECTED';
                                    $sel1 = '';
                                } else {
                                    $sel1 = 'SELECTED';
                                    $sel2 = '';
                                }
                                ?>
                                <select class="form-control form-control-sm <?= $config->getName() ?>"
                                        name="new_value[<?= $config->getId() ?>]">
                                    <option value='' <?= $sel1 ?>><?= _('False') ?>
                                    <option value='1' <?= $sel2 ?>><?= _('True') ?>
                                </select>
                                <?php
                            } elseif ($config->getType() == 'choice') {
                                $userChoices = UserConfigChoicesQuery::create()->findOneById($config->getChoicesId());

                                $choices = explode(",", $userChoices->getChoices());
                                ?>
                                <select class="form-control form-control-sm  <?= $config->getName() ?>"
                                        name="new_value[<?= $config->getId() ?>]">
                                    <?php
                                    foreach ($choices

                                    as $choice) {
                                    ?>
                                    <option
                                        value="<?= $choice ?>"<?= (($config->getValue() == $choice) ? ' selected' : '') ?>><?= _($choice) ?>
                                        <?php
                                        }
                                        ?>
                                </select>
                                <?php
                            }

                            // Notes
                            ?>
                        </div>
                        <div class="col-md-2">
                            <a data-toggle="popover" title="<?= $config->getName() ?>" target="_blank"><i
                                    class="fa fa-fw fa-question-circle"></i></a>
                        </div>
                    </div>
                    <br/>
                    <?php
                    if ($config->getName() == 'sDarkMode') {
                        $r = 20;
                    } else {
                        $r++;
                    }
                    }
                    ?>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-6">
                            <input type=submit class='btn btn-default' name=cancel value="<?= _('Cancel') ?>">
                            <input type=submit class='btn btn-primary' name=save value="<?= _('Save Settings') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- end a card col-md-6 -->
    </div>
</form>

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <div class="card-title"><?= _("Two-factor authentication (2fa)") ?> </div>
            </div>
            <div class="card-body" id="TwoFAEnrollmentSteps">
                <p><?= _("Enrolling your EcclesiaCRM user account in Two Factor Authentication provides an
                    EcclesiaCRM layer of defense against bad actors trying to access your account") ?>.</p>
                <p><?= _("EcclesiaCRM Two factor supports any TOTP authenticator app") ?></p>
                <hr>
                <div class="row">
                    <div class="col-md-4">

                        <p><i class="fa fa-id-card-o"></i> <?= _("When you sign in to EcclesiaCRM, you'll still enter your username and password like
                        normal") ?></p></div>
                    <div class="col-md-4">

                        <p><i class="fa fa-key fa-10x"></i> <?= _("However, you'll also need to supply a one-time code from your authenticator device to
                        complete your login") ?></p></div>
                    <div class="col-md-4">

                        <p><i class="fa fa-check-square-o"></i> <?= _("After successfully entering both your credentials, and the one-time code, you'll be
                        logged in as normal") ?></p></div>
                </div>
                <div class="clearfix"></div>
                <div class="callout callout-warning"><p><?= _("To prevent being locked out of your EcclesiaCRM account,
                        please ensure you're ready to complete two factor enrollment before clicking begin") ?></p>
                </div>
                <ul>
                    <li><?= _("Beginning enrollment will invalidate any previously enrolled 2 factor devices and
                        recovery codes.") ?>
                    </li>
                    <li><?= _("When you click next, you'll be prompted to scan a QR code to enroll your authenticator
                        app.") ?>
                    </li>
                    <li><?= _("To confirm enrollment, you'll need to enter the code generated by your authenticator
                        app") ?>
                    </li>
                    <li><?= _("After confirming app enrollment, single-use recovery codes will be generated and
                        displayed.") ?>
                        <ul>
                            <li><?= _("Recovery codes can be used instead of a code generated from your authenticator
                                app.") ?>
                            </li>
                            <li><?= _("Store these in a secure location") ?></li>
                        </ul>
                    </li>
                </ul>

                <div class="clearfix"></div>
                <button
                    class="btn btn-success Twofa-activation"><?= _("Begin Two Factor Authentication Enrollment") ?></button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var mode = "<?= Theme::getCurrentSideBarMainColor() ?>";
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/SettingsIndividual.js"></script>
<?php
require 'Include/Footer.php';
?>
