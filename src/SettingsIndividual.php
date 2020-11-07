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
?>
<div class="card card-body">
    <form method=post action=SettingsIndividual.php>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-hover data-person data-table no-footer dtr-inline dataTable"
                       id="user-listing-table" style="width:100%;">
                    <thead>
                    <tr>
                        <th><?= _('Variable name') ?></th>
                        <th><?= _('Current Value') ?></th>
                        <th><?= _('Notes') ?></h3></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $r = 1;

                    // List Individual Settings
                    foreach ($configs as $config) {
                        if (!(($config->getPermission() == 'TRUE') || SessionUser::getUser()->isAdmin())) {
                            continue;
                        } // Don't show rows that can't be changed : BUG, you must continue the loop, and not break it PL

                        // Cancel, Save Buttons every 20 rows
                        if ($r == 20) {
                            ?>
                            <tr>
                                <td>
                                    <input type=submit class="btn btn-default" name=cancel value="<?= _('Cancel') ?>">&nbsp;
                                </td>
                                <td>
                                    <input type=submit class="btn btn-primary" name=save
                                           value="<?= _('Save Settings') ?>">
                                </td>
                            </tr>
                            <?php
                            $r = 1;
                        }
                        // Variable Name & Type
                        ?>
                        <tr>
                            <td class=LabelColumn>
                                <?= $config->getName() ?>
                                <input type=hidden name="type[<?= $config->getId() ?>]"
                                       value="<?= $config->getType() ?>">
                            </td>
                            <?php
                            // Current Value
                            if ($config->getType() == 'text') {
                                ?>
                                <td class=TextColumnWithBottomBorder>
                                    <input class="form-control input-md" type=text size=30 maxlength=255
                                           name="new_value[<?= $config->getId() ?>]"
                                           value="<?= htmlspecialchars($config->getValue(), ENT_QUOTES) ?>">
                                </td>
                                <?php
                            } elseif ($config->getType() == 'textarea') {
                                ?>
                                <td class=TextColumnWithBottomBorder>
                                    <textarea rows=4 cols=30 name="new_value[<?= $config->getId() ?>]">
                                        <?= htmlspecialchars($config->getValue(), ENT_QUOTES) ?>
                                    </textarea>
                                </td>
                                <?php
                            } elseif ($config->getType() == 'number' || $config->getType() == 'date') {
                                ?>
                                <td class=TextColumnWithBottomBorder>
                                    <input type=text size=15 maxlength=15 name="new_value[<?= $config->getId() ?>]"
                                           value="<?= $config->getValue() ?>">
                                </td>
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
                                <td class=TextColumnWithBottomBorder>
                                    <select class="form-control form-control-sm <?= $config->getName() ?>" name="new_value[<?= $config->getId() ?>]">
                                        <option value='' <?= $sel1 ?>><?= _('False') ?>
                                        <option value='1' <?= $sel2 ?>><?= _('True') ?>
                                    </select>
                                </td>
                                <?php
                            } elseif ($config->getType() == 'choice') {
                                $userChoices = UserConfigChoicesQuery::create()->findOneById($config->getChoicesId());

                                $choices = explode(",", $userChoices->getChoices());
                                ?>
                                <td>
                                    <select class="form-control form-control-sm  <?= $config->getName() ?>" name="new_value[<?= $config->getId() ?>]">
                                        <?php
                                        foreach ($choices

                                        as $choice) {
                                        ?>
                                        <option
                                            value="<?= $choice ?>"<?= (($config->getValue() == $choice) ? ' selected' : '') ?>><?= $choice ?>
                                            <?php
                                            }
                                            ?>
                                    </select>
                                </td>
                                <?php
                            }

                            // Notes
                            ?>
                            <td><?= _($config->getTooltip()) ?></td>
                        </tr>
                        <?php
                        $r++;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-2">
            </div>
            <div class="col-md-6">
                <input type=submit class='btn btn-default' name=cancel value="<?= _('Cancel') ?>">
                <input type=submit class='btn btn-primary' name=save value="<?= _('Save Settings') ?>">
            </div>
        </div>
    </form>


</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var mode  = "<?= Theme::getCurrentSideBarMainColor() ?>";
    var sidebar_colors = [
        'bg-blue',
        'bg-secondary',
        'bg-green',
        'bg-cyan',
        'bg-yellow',
        'bg-red',
        'bg-fuchsia',
        'bg-blue',
        'bg-yellow',
        'bg-indigo',
        'bg-navy',
        'bg-purple',
        'bg-pink',
        'bg-maroon',
        'bg-orange',
        'bg-lime',
        'bg-teal',
        'bg-olive',
        'bg-black',
        'bg-gray-dark',
        'bg-gray',
        'bg-light'
    ]

    var accent_colors = [
        'accent-blue',
        'accent-secondary',
        'accent-green',
        'accent-cyan',
        'accent-yellow',
        'accent-red',
        'accent-fuchsia',
        'accent-blue',
        'accent-yellow',
        'accent-indigo',
        'accent-navy',
        'accent-purple',
        'accent-pink',
        'accent-maroon',
        'accent-orange',
        'accent-lime',
        'accent-teal',
        'accent-olive',
        'accent-black',
        'accent-gray-dark',
        'accent-gray',
        'accent-light'
    ]

    var navbar_colors = [
        'navbar-blue',
        'navbar-secondary',
        'navbar-green',
        'navbar-cyan',
        'navbar-yellow',
        'navbar-red',
        'navbar-fuchsia',
        'navbar-blue',
        'navbar-yellow',
        'navbar-indigo',
        'navbar-navy',
        'navbar-purple',
        'navbar-pink',
        'navbar-maroon',
        'navbar-orange',
        'navbar-lime',
        'navbar-teal',
        'navbar-olive',
        'navbar-black',
        'navbar-gray-dark',
        'navbar-gray',
        'navbar-light'
    ]

    var sidebar_skins = [
        'sidebar-dark-blue',
        'sidebar-dark-secondary',
        'sidebar-dark-green',
        'sidebar-dark-cyan',
        'sidebar-dark-yellow',
        'sidebar-dark-red',
        'sidebar-dark-fuchsia',
        'sidebar-dark-blue',
        'sidebar-dark-yellow',
        'sidebar-dark-indigo',
        'sidebar-dark-navy',
        'sidebar-dark-purple',
        'sidebar-dark-pink',
        'sidebar-dark-maroon',
        'sidebar-dark-orange',
        'sidebar-dark-lime',
        'sidebar-dark-teal',
        'sidebar-dark-olive',
        'sidebar-dark-black',
        'sidebar-dark-gray-dark',
        'sidebar-dark-gray',
        'sidebar-dark-light',
        'sidebar-light-blue',
        'sidebar-light-secondary',
        'sidebar-light-green',
        'sidebar-light-cyan',
        'sidebar-light-yellow',
        'sidebar-light-red',
        'sidebar-light-fuchsia',
        'sidebar-light-blue',
        'sidebar-light-yellow',
        'sidebar-light-indigo',
        'sidebar-light-navy',
        'sidebar-light-purple',
        'sidebar-light-pink',
        'sidebar-light-maroon',
        'sidebar-light-orange',
        'sidebar-light-lime',
        'sidebar-light-teal',
        'sidebar-light-olive',
        'sidebar-light-black',
        'sidebar-light-gray-dark',
        'sidebar-light-gray',
        'sidebar-light-light'
    ]

    $(document).ready(function () {
        $(".data-table").DataTable({
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
            pageLength: 100,
            info: false,
            bSort: false,
            searching: false, paging: false,
            responsive: true
        });
    });

    $('.sStyleSideBar').change(function() {
        var color = $('.sStyleSideBarColor').val();
        mode        = $(this).val();
        var sidebar      = $('.main-sidebar');
        var sidebar_class = 'sidebar-' + mode + '-' + color;
        sidebar_skins.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)

        if (mode == 'dark') {
            $('.main-sidebar').css({'background': 'repeating-linear-gradient(to top, rgba(0, 0, 0, 0.95), rgba(114, 114, 114, 0.95)),url(/Images/sidebar.jpg)','background-repeat': 'repeat-y'});
            $('.control-sidebar').removeClass('control-sidebar-light');
            $('.control-sidebar').addClass('control-sidebar-dark');
        } else {
            $('.main-sidebar').css({'background': 'repeating-linear-gradient(0deg,rgba(255,255,255,0.95),rgba(200,200,200,0.95)),url(/Images/sidebar.jpg)', 'background-repeat': 'repeat-y'});
            $('.control-sidebar').removeClass('control-sidebar-dark');
            $('.control-sidebar').addClass('control-sidebar-light');
        }
    });

    $('.sStyleFontSize').change(function() {
        if ($(this).val() == "Small") {
            $('.sidebar-mini').addClass('text-sm')
        } else {
            $('.sidebar-mini').removeClass('text-sm')
        }
    });

    $(".bSidebarCollapse").change(function() {
        $('[data-widget="pushmenu"]').PushMenu('toggle');
    });


    $(".sStyleBrandLinkColor").change(function() {
        var color         = $(this).val();
        var sidebar_class = 'navbar-' + color
        var sidebar      = $('.brand-link')
        navbar_colors.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });

    $(".sStyleNavBarColor").change(function() {
        var color         = $(this).val();
        var sidebar_class = 'navbar-' + color
        var sidebar      = $('.main-header')
        navbar_colors.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });

    $(".sStyleSideBarColor").change(function() {
        var color         = $(this).val();
        var sidebar_class = 'sidebar-' + ((mode == 'light')?'light':'dark') + '-' + color
        var sidebar      = $('.main-sidebar')
        sidebar_skins.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });
</script>
<?php
require 'Include/Footer.php';
?>
