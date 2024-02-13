<?php
/*******************************************************************************
 *
 *  filename    : usersettings.php
 *  last change : 2023-05-08
 *  description : user settings
 *
 *  http://www.ecclesiacrm.com/
 *  Cpoyright 2023Philippe Logel all right reserved not MIT
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Theme;

use EcclesiaCRM\UserConfigChoicesQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;

use Propel\Runtime\ActiveQuery\Criteria;

require_once $sRootDocument . '/Include/Header.php';
?>
<form method=post action=<?= $sRootPath ?>/v2/users/settings>
    <div class="row">
        <div class="col-md-6"><!-- begin a card col-md-6 -->
            <div class="card">
                <div class="card-header  border-1">
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
                <div class="card-header  border-1">
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
                                        <span class="align-text-bottom"><i class="far fa-map fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'bEmailMailto' || $config->getName() == 'sMailtoDelimiter') { ?>
                                        <span class="align-text-bottom"><i class="far fa-envelope fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'bShowTooltip') { ?>
                                        <span class="align-text-bottom"><i class="fas fa-info-circle fa-lg"></i></span>
                                    <?php } else if ($config->getName() == 'sCSVExportDelemiter' || $config->getName() == 'sCSVExportCharset') { ?>
                                        <span class="align-text-bottom"><i class="fas fa-file-excel fa-lg"></i></span>
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
                                <input class="form-control form-control-sm" type=text size=30 maxlength=255
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
                            <a data-toggle="popover" title="<?= _("Property") ?>" data-content="<?= $config->getName() ?>" target="_blank"><i
                                    class="fa  fa-question-circle"></i></a>
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
<br/>
    <hr/>
    <section class="content-header">
        <h1><?= _("Specific settings") ?></h1>
    </section>

<div class="row">
    <div class="col-md-4">
        <!-- Dashboard Plugin settings -->
        <div class="card">
            <div class="card-header">
                <label class="card-title">
                    <?= _("Visibilities of the dashboard plugins") ?>
                </label>
            </div>
            <div class="card-body">
                <?php
                $allRights = SessionUser::getUser()->allSecuritiesBits();

                $plugins = PluginQuery::create()
                    ->filterByCategory('Dashboard', Criteria::EQUAL)
                    ->orderByName()
                    ->find();

                foreach ($plugins as $plugin) {
                    $role = PluginUserRoleQuery::create()
                        ->filterByUserId($iPersonID)
                        ->findOneByPluginId($plugin->getId());

                    $securities = $plugin->getSecurities();

                    if (($securities & $allRights) == 0) continue;

                    $visible = 0;
                    $place = 'top';
                    if ( !is_null($role) ) {
                        $visible = $role->getDashboardVisible();
                        $place = $role->getDashboardOrientation();
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-7">&bullet;
                            <?= $plugin->getName() ?>:
                        </div>
                        <div class="col-md-2">
                            <select class="form-control form-control-sm"
                                    name="new_plugin[<?= $plugin->getId() ?>]">
                                <option value="0" <?= ($visible == false)?'SELECTED':'' ?>><?= _('No') ?>
                                <option value="1" <?= ($visible == true)?'SELECTED':'' ?>><?= _('Yes') ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control form-control-sm"
                                    name="new_plugin_place[<?= $plugin->getId() ?>]">
                                <option value="top" <?= ($place == 'top')?'SELECTED':'' ?>><?= _('Top') ?>
                                <option value="left" <?= ($place == 'left')?'SELECTED':'' ?>><?= _('Left') ?>
                                <option value="center" <?= ($place == 'center')?'SELECTED':'' ?>><?= _('Center') ?>
                                <option value="right" <?= ($place == 'right')?'SELECTED':'' ?>><?= _('Right') ?>
                            </select>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-2">
                        <input type=submit class='btn btn-default' name=cancel value="<?= _('Cancel') ?>">
                    </div>
                    <div class="col-md-4">
                        <input type=submit class='btn btn-primary' name=save value="<?= _('Save Settings') ?>">
                    </div>
                    <div class="col-md-6"></div>
                </div>
            </div>
        </div>
        <!-- Dashboard Plugin settings -->
    </div>
    <div class="col-md-4">

        <!-- 2fa -->
        <div class="card card-primary">
            <div class="card-header  border-1">
                <div class="card-title"><?= _("Two-factor authentication (2fa)") ?> </div>
            </div>
            <div class="card-body" id="TwoFAEnrollmentSteps">
                <p><?= _("Enrolling your EcclesiaCRM user account in Two Factor Authentication provides an EcclesiaCRM layer of defense against bad actors trying to access your account") ?>.</p>
                <p><?= _("EcclesiaCRM Two factor supports any TOTP authenticator app") ?></p>
                <hr>
                <div class="row">
                    <div class="col-md-4">

                        <p><i class="far fa-id-card"></i> <?= _("When you sign in to EcclesiaCRM, you'll still enter your username and password like normal") ?></p></div>
                    <div class="col-md-4">

                        <p><i class="fas fa-key fa-1x"></i> <?= _("However, you'll also need to supply a one-time code from your authenticator device to complete your login") ?></p></div>
                    <div class="col-md-4">

                        <p><i class="far fa-check-square"></i> <?= _("After successfully entering both your credentials, and the one-time code, you'll be logged in as normal") ?></p></div>
                </div>
                <div class="clearfix"></div>
                <div class="callout callout-warning"><p><?= _("To prevent being locked out of your EcclesiaCRM account, please ensure you're ready to complete two factor enrollment before clicking begin") ?></p>
                </div>
                <ul>
                    <li><?= _("Beginning enrollment will invalidate any previously enrolled 2 factor devices and recovery codes.") ?>
                    </li>
                    <li><?= _("When you click next, you'll be prompted to scan a QR code to enroll your authenticator app.") ?>
                    </li>
                    <li><?= _("To confirm enrollment, you'll need to enter the code generated by your authenticator app") ?>
                    </li>
                    <li><?= _("After confirming app enrollment, single-use recovery codes will be generated and displayed.") ?>
                        <ul>
                            <li><?= _("Recovery codes can be used instead of a code generated from your authenticator app.") ?>
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
        <!-- 2fa -->
    </div>
    <div class="col-md-4" id="two-factor-results"></div>
</div>
</form>

<script nonce="<?= $cSPNonce ?>">
    var mode = "<?= Theme::getCurrentSideBarMainColor() ?>";
</script>

<script src="<?= $sRootPath ?>/skin/js/system/SettingsIndividual.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
