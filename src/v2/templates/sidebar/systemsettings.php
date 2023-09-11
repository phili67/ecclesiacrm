<?php
/*******************************************************************************
 *
 *  filename    : systemsettings.php
 *  last change : 2023-05-01
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2023 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

if ($saved) {
    $sGlobalMessage = _('Setting saved');
}

require $sRootDocument . '/Include/Header.php';

?>

<form method=post action="<?= $sRootPath ?>/v2/systemsettings">

<div class="card">
    <div class="card-body">
        <div id="JSONSettingsModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?= gettext('Edit JSON Settings') ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="JSONSettingsDiv">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= gettext("Close") ?></button>
                        <button type="button" class="btn btn-primary jsonSettingsClose"><?= gettext("Save") ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-5 col-sm-3">
                <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                    <?php foreach (SystemConfig::getCategories() as $category => $settings) {
                        ?>
                            <a class="nav-link <?= ($category == 'Church Information' && $Mode == "" 
                            || $category == "Email Setup" && $Mode == "mailsettings"
                            || $category == "Integration" && $Mode == "Integration"
                            || $category == "Map Settings" && $Mode == "mapsettings"
                            || $category == "Pastoral Care" && $Mode == "pastoralcare"
                            || $category == "GDPR" && $Mode == "GDPR"
                            ) ? 'active' : "" ?>"
                               href="#<?= str_replace(" ", '', $category) ?>"
                               data-toggle="pill" role="tab" aria-controls="custom-tabs-three-messages"
                               aria-selected="<?= ($category == 'Church Information') ? 'true' : "false" ?>">
                                <?= gettext($category) ?>
                            </a>
                        <?php
                    } ?>
                    <hr>
                    <input type="submit" class="btn btn-primary" name="save" id="save"
                           value="<?= gettext("Save Settings") ?>" style="margin:20px">
                </div>
            </div>
            <div class="col-7 col-sm-9">
                <div class="tab-content" id="vert-tabs-tabContent">
                    <?php
                    // Build Category Pages
                    $categories = SystemConfig::getCategories();
                    foreach (SystemConfig::getCategories() as $category => $settings) {
                        ?>
                        <div class="tab-pane <?= ($category == 'Church Information' && $Mode == "" 
                            || $category == "Email Setup" && $Mode == "mailsettings"
                            || $category == "Integration" && $Mode == "Integration"
                            || $category == "Map Settings" && $Mode == "mapsettings"
                            || $category == "Pastoral Care" && $Mode == "pastoralcare"
                            || $category == "GDPR" && $Mode == "GDPR"
                            ) ? 'active' : '' ?>"
                             id="<?= str_replace(" ", '', $category) ?>">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th width="150px"><?= gettext('Variable name') ?></th>
                                        <th width="400px"><?= gettext('Value') ?></th>
                                        <th><?= gettext('Default Value') ?></th>
                                    </tr>
                                    <?php
                                    foreach ($settings as $settingName) {
                                        $setting = SystemConfig::getConfigItem($settingName)
                                        ?>
                                        <tr>
                                            <td><?= $setting->getName() ?></td>
                                            <input type=hidden name='type[<?= $setting->getId() ?>]'
                                                   value='<?= $setting->getType() ?>'>
                                            <td>
                                                <!--  Current Value -->
                                                <?php
                                                if ($setting->getType() == 'choice') {
                                                    ?>
                                                    <select name='new_value[<?= $setting->getId() ?>]'
                                                            class="choiceSelectBox form-control " style="width: 100%">
                                                        <?php
                                                        foreach (json_decode($setting->getData())->Choices as $choice) {
                                                            if (strpos($choice, ":") === false) {
                                                                $text = $choice;
                                                                $value = $choice;
                                                            } else {
                                                                $keyValue = explode(":", $choice);
                                                                $value = $keyValue[1];
                                                                $text = gettext($keyValue[0]) . ' [' . $value . ']';
                                                            }
                                                            echo '<option value = "' . $value . '" ' . ($setting->getValue() == $value ? 'selected' : '') . '>' . $text . '</option>';
                                                        } ?>
                                                    </select>
                                                    <?php
                                                } elseif ($setting->getType() == 'text') {
                                                    ?>
                                                    <input type=text size=40 maxlength=255
                                                           name='new_value[<?= $setting->getId() ?>]'
                                                           value='<?= htmlspecialchars($setting->getValue(), ENT_QUOTES) ?>'
                                                           class= "form-control ">
                                                    <?php
                                                } elseif ($setting->getType() == 'password') {
                                                    ?>
                                                    <input type=password size=40 maxlength=255
                                                           name='new_value[<?= $setting->getId() ?>]'
                                                           value='<?= htmlspecialchars($setting->getValue(), ENT_QUOTES) ?>'
                                                           class= "form-control ">
                                                    <?php
                                                } elseif ($setting->getType() == 'textarea') {
                                                    ?>
                                                    <textarea rows=4 cols=40 name='new_value[<?= $setting->getId() ?>]'
                                                              class= "form-control "><?= htmlspecialchars($setting->getValue(), ENT_QUOTES) ?></textarea>
                                                    <?php
                                                } elseif ($setting->getType() == 'number' || $setting->getType() == 'date') {
                                                    ?>
                                                    <input type=text size=40 maxlength=15
                                                           name='new_value[<?= $setting->getId() ?>]'
                                                           value='<?= $setting->getValue() ?>'
                                                           class= "form-control ">
                                                    <?php
                                                } elseif ($setting->getType() == 'boolean') {
                                                    if ($setting->getValue()) {
                                                        $sel1 = '';
                                                        $sel2 = 'SELECTED';
                                                    } else {
                                                        $sel1 = 'SELECTED';
                                                        $sel2 = '';
                                                    } ?>
                                                    <select name='new_value[<?= $setting->getId() ?>]'
                                                            class="choiceSelectBox form-control " style="width: 100%">
                                                        <option value='' <?= $sel1 ?>><?= gettext('False') ?>
                                                        <option value='1' <?= $sel2 ?>><?= gettext('True') ?>
                                                    </select>
                                                    <?php
                                                } elseif ($setting->getType() == 'json') {
                                                    ?>
                                                    <input type="hidden" name='new_value[<?= $setting->getId() ?>]'
                                                           value='<?= $setting->getValue() ?>'>
                                                    <button class="btn btn-primary jsonSettingsEdit"
                                                            id="set_value<?= $setting->getId() ?>"
                                                            data-cfgid="<?= $setting->getId() ?>"><?= gettext('Edit Settings') ?>
                                                    </button>
                                                    <?php
                                                } elseif ($setting->getType() == 'ajax') {
                                                    ?>
                                                    <select id='ajax-<?= $setting->getId() ?>'
                                                            name='new_value[<?= $setting->getId() ?>]'
                                                            data-url="<?= $setting->getData() ?>"
                                                            data-value="<?= $setting->getValue() ?>"
                                                            class="choiceSelectBox form-control " style="width: 100%">
                                                        <option value=''><?= gettext('Unassigned') ?>
                                                    </select>
                                                    <?php
                                                } else {
                                                    echo gettext("Unknown Type") . " " . $setting->getType();
                                                } ?>
                                            </td>
                                            <?php
                                            // Default Value
                                            $display_default = $setting->getDefault();
                                            if ($setting->getType() == 'boolean') {
                                                if ($setting->getDefault()) {
                                                    $display_default = 'True';
                                                } else {
                                                    $display_default = 'False';
                                                }
                                            } ?>
                                            <td>
                                                <?php if (!empty($setting->getTooltip())) {
                                                    ?>
                                                    <a data-toggle="popover"
                                                       title="<?= _("Definition") ?>" data-content="<?= gettext($setting->getTooltip()) ?>" target="_blank" class="blue"><i
                                                            class="far  fa-question-circle"></i></a>
                                                    <?php
                                                }
                                                if (!empty($setting->getUrl())) {
                                                    ?>
                                                    <a href="<?= $setting->getUrl() ?>" target="_blank"><i
                                                            class="fa  fa-link"></i></a>
                                                    <?php
                                                } ?>
                                                <?= $display_default ?>
                                            </td>
                                        </tr>
                                        <?php
                                    } ?>
                                </table>
                            </div>
                        </div>

                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

</form>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href") // activated tab
            $(target + " .choiceSelectBox").select2({width: 'resolve'});
        });
        $(".choiceSelectBox").select2({width: 'resolve'});

        <?php
        foreach (SystemConfig::getCategories() as $category=>$settings) {
        foreach ($settings as $settingName) {
        $setting = SystemConfig::getConfigItem($settingName);
        if ($setting->getType() == 'ajax') {
        ?>
        updateDropDrownFromAjax($('#ajax-<?= $setting->getId() ?>'));
        <?php
        }
        }
        } ?>
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/system/SystemSettings.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
