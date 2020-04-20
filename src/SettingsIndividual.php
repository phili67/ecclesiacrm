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
                                    <select class="form-control input-sm" name="new_value[<?= $config->getId() ?>]">
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
                                    <select class="form-control input-sm" name="new_value[<?= $config->getId() ?>]">
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
</script>
<?php
require 'Include/Footer.php';
?>
