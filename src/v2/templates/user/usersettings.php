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

use EcclesiaCRM\UserConfigChoicesQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;

use Propel\Runtime\ActiveQuery\Criteria;

require_once $sRootDocument . '/Include/Header.php';
?>
<form method="post" action="<?= $sRootPath ?>/v2/users/settings">
    <div class="card card-outline card-primary card-tabs shadow-sm">
        <div class="card-header p-0 pt-1 border-0">
            <ul class="nav nav-tabs nav-tabs-right" id="usersettings-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="usersettings-tab-classic" data-toggle="tab" href="#usersettings-pane-profile" role="tab" aria-controls="usersettings-pane-profile" aria-selected="true">
                        <i class="fas fa-user mr-2"></i><?= _("Classic User Profile Settings") ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="usersettings-tab-skin" data-toggle="tab" href="#usersettings-pane-skin" role="tab" aria-controls="usersettings-pane-skin" aria-selected="false">
                        <i class="fas fa-paint-brush mr-2"></i><?= _("Skin") ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="usersettings-tab-specific" data-toggle="tab" href="#usersettings-pane-specific" role="tab" aria-controls="usersettings-pane-specific" aria-selected="false">
                        <i class="fas fa-plug mr-2"></i><?= _("Specific settings") ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="usersettings-tab-2fa" data-toggle="tab" href="#usersettings-pane-2fa" role="tab" aria-controls="usersettings-pane-2fa" aria-selected="false">
                        <i class="fas fa-shield-alt mr-2"></i><?= _("Two-factor authentication (2fa)") ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="usersettings-tabs-content">
                <div class="tab-pane fade show active" id="usersettings-pane-profile" role="tabpanel" aria-labelledby="usersettings-tab-classic">
                    <div class="row">
                        <div class="col-md-12" id="usersettings-classic-col"><!-- begin a card col-md-6 -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="row border-bottom pb-1 mb-2">
                                        <div class="col-md-7"><label class="text-muted small font-weight-bold"><i class="fas fa-sticky-note mr-1"></i><?= _('Notes') ?></label></div>
                                        <div class="col-md-3"><label class="text-muted small font-weight-bold"><i class="fas fa-sliders-h mr-1"></i><?= _('Current Value') ?></label></div>
                                        <div class="col-md-2"><label class="text-muted small font-weight-bold"><i class="fas fa-code mr-1"></i><?= _('Variable name') ?></label></div>
                                    </div>
                                    <?php
                                    $r = 1;

                                    // List Individual Settings
                                    foreach (
                                        $configs

                                        as $config
                                    ) {
                                        if (
                                            $config->getName() == "bSidebarExpandOnHover"
                                            || !(($config->getPermission() == 'TRUE') || $config->getName() != "bSidebarExpandOnHover" || SessionUser::getUser()->isAdmin())
                                        ) {
                                            continue;
                                        }
                                    ?>
                                        <div class="row" <?= ($config->getName() == 'sStyleSideBar' or $config->getName() == 'sDarkMode' or $config->getName() == 'sStyleBrandLinkColor' or $config->getName() == 'sStyleNavBarColor') ? 'style="display:none;height:0px"' : '' ?>>
                                            <?php
                                            // Cancel, Save Buttons every 20 rows or the bsiderbar collapsed
                                            if ($r == 20 || $config->getName() == "bSidebarCollapse") {
                                            ?>
                                        </div>
                                </div>
                                <div class="card-footer border-0">
                                    <div class="d-flex justify-content-end" style="gap:.5rem;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" name="cancel" value="<?= _('Cancel') ?>">
                                            <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-primary" name="save" value="<?= _('Save Settings') ?>">
                                            <i class="fas fa-save mr-1"></i><?= _('Save Settings') ?>
                                        </button>
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
                    <div class="col-md-6" id="usersettings-skin-col"><!-- begin a card col-md-6 -->
                        <div class="card  shadow-sm">
                            <div class="card-body">
                                <div class="row border-bottom pb-1 mb-2">
                                    <div class="col-md-7"><label class="text-muted small font-weight-bold"><i class="fas fa-sticky-note mr-1"></i><?= _('Notes') ?></label></div>
                                    <div class="col-md-3"><label class="text-muted small font-weight-bold"><i class="fas fa-sliders-h mr-1"></i><?= _('Current Value') ?></label></div>
                                    <div class="col-md-2"><label class="text-muted small font-weight-bold"><i class="fas fa-code mr-1"></i><?= _('Variable name') ?></label></div>
                                </div>
                                <div class="row" <?= ($config->getName() == 'sStyleSideBar' or $config->getName() == 'sDarkMode' or $config->getName() == 'sStyleBrandLinkColor' or $config->getName() == 'sStyleNavBarColor') ? 'style="display:none;height:0px"' : '' ?>>
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
                                            foreach (
                                                $choices

                                                as $choice
                                            ) {
                                            ?>
                                                <option
                                                    value="<?= $choice ?>" <?= (($config->getValue() == $choice) ? ' selected' : '') ?>><?= _($choice) ?>
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
                                    <a data-toggle="popover" title="<?= _("Property") ?>" data-content="<?= $config->getName() ?>" target="_blank"><i class="fas fa-info-circle text-info"></i></a>
                                </div>
                                </div>
                                <?php if (!($config->getName() == 'sStyleSideBar' or $config->getName() == 'sDarkMode' or $config->getName() == 'sStyleBrandLinkColor' or $config->getName() == 'sStyleNavBarColor')): ?>
                                    <br />
                                <?php endif ?>
                            <?php
                                        if ($config->getName() == 'sDarkMode') {
                                            $r = 20;
                                        } else {
                                            $r++;
                                        }
                                    }
                            ?>
                            </div>
                            <div class="card-footer border-0">
                                <div class="d-flex justify-content-end" style="gap:.5rem;">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" name="cancel" value="<?= _('Cancel') ?>">
                                        <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary" name="save" value="<?= _('Save Settings') ?>">
                                        <i class="fas fa-save mr-1"></i><?= _('Save Settings') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div><!-- end a card col-md-6 -->
                    </div>
                </div>

                <div class="tab-pane fade" id="usersettings-pane-skin" role="tabpanel" aria-labelledby="usersettings-tab-skin">
                    <div class="row" id="usersettings-skin-row"></div>
                </div>

                <div class="tab-pane fade" id="usersettings-pane-specific" role="tabpanel" aria-labelledby="usersettings-tab-specific">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Dashboard Plugin settings -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-muted font-weight-bold border-bottom pb-2 mb-3"><i class="fas fa-th-large mr-2"></i><?= _("Widgets") ?></h6>
                                    <div class="row align-items-center border-bottom pb-1 mb-2">
                                        <div class="col-md-7"><label class="text-muted small font-weight-bold"><i class="fas fa-th mr-1"></i><?= _('Plugin') ?></label></div>
                                        <div class="col-md-3"><label class="text-muted small font-weight-bold"><i class="fas fa-eye mr-1"></i><?= _('Visible') ?></label></div>
                                    </div>
                                    <?php
                                    $allRights = SessionUser::getUser()->allSecuritiesBits();

                                    $plugins = PluginQuery::create()
                                        ->filterByCategory('Dashboard', Criteria::EQUAL)
                                        ->filterByDashboardDefaultOrientation('widget')
                                        ->orderByName()
                                        ->find();

                                    foreach ($plugins as $plugin) {
                                        $role = PluginUserRoleQuery::create()
                                            ->filterByUserId($iPersonID)
                                            ->findOneByPluginId($plugin->getId());

                                        $security = $plugin->getSecurities();

                                        if (!(SessionUser::getUser() != null and SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                                            continue;

                                        $visible = 0;
                                        $place = $plugin->getDashboardDefaultOrientation();
                                        if (!is_null($role)) {
                                            $visible = $role->getDashboardVisible();
                                            $place = $role->getDashboardOrientation();
                                        }
                                    ?>
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-7 small">
                                                <i class="fas fa-th mr-2 text-muted"></i><?= $plugin->getName() ?>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control form-control-sm"
                                                    name="new_plugin[<?= $plugin->getId() ?>]">
                                                    <option value="0" <?= ($visible == false) ? 'selected' : '' ?>><?= _('No') ?></option>
                                                    <option value="1" <?= ($visible == true) ? 'selected' : '' ?>><?= _('Yes') ?></option>
                                                </select>
                                            </div>
                                            <div class="d-none">
                                                <select class="form-control form-control-sm"
                                                    name="new_plugin_place[<?= $plugin->getId() ?>]">
                                                    <option value="widget" selected><?= _('widget') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>

                                    <h6 class="text-muted font-weight-bold border-bottom pb-2 mb-3 mt-3"><i class="fas fa-puzzle-piece mr-2"></i><?= _("Dashboard Plugins") ?></h6>
                                    <div class="row align-items-center border-bottom pb-1 mb-2">
                                        <div class="col-md-7"><label class="text-muted small font-weight-bold"><i class="fas fa-puzzle-piece mr-1"></i><?= _('Plugin') ?></label></div>
                                        <div class="col-md-2"><label class="text-muted small font-weight-bold"><i class="fas fa-eye mr-1"></i><?= _('Visible') ?></label></div>
                                        <div class="col-md-3"><label class="text-muted small font-weight-bold"><i class="fas fa-map-pin mr-1"></i><?= _('Position') ?></label></div>
                                    </div>

                                    <?php
                                    $plugins = PluginQuery::create()
                                        ->filterByCategory('Dashboard', Criteria::EQUAL)
                                        ->filterByDashboardDefaultOrientation('widget', Criteria::NOT_EQUAL)
                                        ->orderByName()
                                        ->find();

                                    foreach ($plugins as $plugin) {
                                        $role = PluginUserRoleQuery::create()
                                            ->filterByUserId($iPersonID)
                                            ->findOneByPluginId($plugin->getId());

                                        $security = $plugin->getSecurities();

                                        if (!(SessionUser::getUser() != null and SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                                            continue;

                                        $visible = 0;
                                        $place = $plugin->getDashboardDefaultOrientation();
                                        if (!is_null($role)) {
                                            $visible = $role->getDashboardVisible();
                                            $place = $role->getDashboardOrientation();
                                        }
                                    ?>
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-7 small">
                                                <i class="fas fa-puzzle-piece mr-2 text-muted"></i><?= $plugin->getName() ?>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control form-control-sm"
                                                    name="new_plugin[<?= $plugin->getId() ?>]">
                                                    <option value="0" <?= ($visible == false) ? 'selected' : '' ?>><?= _('No') ?></option>
                                                    <option value="1" <?= ($visible == true) ? 'selected' : '' ?>><?= _('Yes') ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control form-control-sm"
                                                    name="new_plugin_place[<?= $plugin->getId() ?>]">
                                                    <option value="top" <?= ($place == 'top') ? 'selected' : '' ?>><?= _('Top') ?></option>
                                                    <option value="left" <?= ($place == 'left') ? 'selected' : '' ?>><?= _('Left') ?></option>
                                                    <option value="center" <?= ($place == 'center') ? 'selected' : '' ?>><?= _('Center') ?></option>
                                                    <option value="right" <?= ($place == 'right') ? 'selected' : '' ?>><?= _('Right') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="card-footer border-0">
                                    <div class="d-flex justify-content-end" style="gap:.5rem;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" name="cancel" value="<?= _('Cancel') ?>">
                                            <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-primary" name="save" value="<?= _('Save Settings') ?>">
                                            <i class="fas fa-save mr-1"></i><?= _('Save Settings') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Dashboard Plugin settings -->
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="usersettings-pane-2fa" role="tabpanel" aria-labelledby="usersettings-tab-2fa">
                    <div class="row">
                        <div class="col-md-8">

                            <!-- 2fa -->
                            <div class="card shadow-sm">
                                <div class="card-body" id="TwoFAEnrollmentSteps">
                                    <p class="text-muted"><?= _("Enrolling your CRM user account in Two Factor Authentication provides an CRM layer of defense against bad actors trying to access your account") ?>.</p>
                                    <p class="text-muted"><?= _("CRM Two factor supports any TOTP authenticator app") ?></p>
                                    <hr>
                                    <div class="row text-center mb-3">
                                        <div class="col-md-4">
                                            <div class="p-3">
                                                <i class="far fa-id-card fa-2x text-primary mb-2"></i>
                                                <p class="small"><?= _("When you sign in to EcclesiaCRM, you'll still enter your username and password like normal") ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3">
                                                <i class="fas fa-key fa-2x text-warning mb-2"></i>
                                                <p class="small"><?= _("However, you'll also need to supply a one-time code from your authenticator device to complete your login") ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3">
                                                <i class="far fa-check-square fa-2x text-success mb-2"></i>
                                                <p class="small"><?= _("After successfully entering both your credentials, and the one-time code, you'll be logged in as normal") ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i><?= _("To prevent being locked out of your CRM account, please ensure you're ready to complete two factor enrollment before clicking begin") ?>
                                    </div>
                                    <ul class="small text-muted">
                                        <li class="mb-1"><?= _("Beginning enrollment will invalidate any previously enrolled 2 factor devices and recovery codes.") ?></li>
                                        <li class="mb-1"><?= _("When you click next, you'll be prompted to scan a QR code to enroll your authenticator app.") ?></li>
                                        <li class="mb-1"><?= _("To confirm enrollment, you'll need to enter the code generated by your authenticator app") ?></li>
                                        <li class="mb-1"><?= _("After confirming app enrollment, single-use recovery codes will be generated and displayed.") ?>
                                            <ul>
                                                <li><?= _("Recovery codes can be used instead of a code generated from your authenticator app.") ?></li>
                                                <li><?= _("Store these in a secure location") ?></li>
                                            </ul>
                                        </li>
                                    </ul>

                                    <div class="mt-4">
                                        <button type="button" class="btn btn-success btn-sm Twofa-activation">
                                            <i class="fas fa-lock-open mr-2"></i><?= _("Begin Two Factor Authentication Enrollment") ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- 2fa -->
                        </div>
                        <div class="col-md-4" id="two-factor-results"></div>
                    </div>
                </div>
            </div>
        </div>
</form>

<script nonce="<?= $cSPNonce ?>">
    var mode = window.CRM.bDarkMode ? 'Dark' : 'Light';
</script>

<script src="<?= $sRootPath ?>/skin/js/system/SettingsIndividual.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>