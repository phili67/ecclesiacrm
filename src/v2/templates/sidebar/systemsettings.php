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
    $sGlobalMessageClass = 'success';
}

require $sRootDocument . '/Include/Header.php';
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    .settings-shell .card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .08);
    }

    .settings-side {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-right: 1px solid rgba(148, 163, 184, .2);
    }

    .settings-tab-link {
        border-radius: .65rem !important;
        margin-bottom: .35rem;
        padding: .55rem .75rem;
        color: #334155;
        font-weight: 600;
        transition: all .2s ease;
    }

    .settings-tab-link.active {
        background: #0ea5e9 !important;
        color: #fff !important;
        box-shadow: 0 8px 20px rgba(14, 165, 233, .28);
    }

    .settings-pane {
        border: 1px solid rgba(148, 163, 184, .2);
        border-radius: .9rem;
        background: #fff;
        padding: .9rem;
    }

    .settings-table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #475569;
        border-top: 0;
    }

    .settings-name {
        font-size: .8rem;
        color: #0f172a;
        font-weight: 700;
    }

    .settings-default {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        flex-wrap: wrap;
    }

    .settings-help {
        color: #0ea5e9;
    }

    .settings-link {
        color: #64748b;
    }

    .settings-save {
        box-shadow: 0 8px 18px rgba(34, 197, 94, .25);
    }

    /* Dark mode */
    .dark-mode .settings-shell .card {
        background: #111827;
        box-shadow: 0 14px 32px rgba(0, 0, 0, .45);
    }

    .dark-mode .settings-side {
        background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        border-right-color: rgba(148, 163, 184, .22);
    }

    .dark-mode .settings-tab-link {
        color: #cbd5e1;
        background: rgba(148, 163, 184, .08);
    }

    .dark-mode .settings-tab-link:hover {
        color: #e2e8f0;
        background: rgba(148, 163, 184, .16);
    }

    .dark-mode .settings-tab-link.active {
        background: #0284c7 !important;
        color: #f8fafc !important;
        box-shadow: 0 8px 20px rgba(2, 132, 199, .35);
    }

    .dark-mode .settings-pane {
        background: #0b1220;
        border-color: rgba(148, 163, 184, .24);
    }

    .dark-mode .settings-table {
        color: #d1d5db;
    }

    .dark-mode .settings-table thead th {
        color: #93c5fd;
        background: rgba(30, 41, 59, .95);
        border-bottom-color: rgba(148, 163, 184, .3);
    }

    .dark-mode .settings-table td,
    .dark-mode .settings-table th {
        border-color: rgba(148, 163, 184, .2);
    }

    .dark-mode .settings-table.table-hover tbody tr:hover {
        background: rgba(30, 41, 59, .65);
    }

    .dark-mode .settings-name {
        color: #e5e7eb;
    }

    .dark-mode .settings-help {
        color: #38bdf8;
    }

    .dark-mode .settings-link {
        color: #94a3b8;
    }

    .dark-mode .settings-save {
        box-shadow: 0 8px 18px rgba(34, 197, 94, .18);
    }

    .dark-mode .settings-shell .form-control,
    .dark-mode .settings-shell .select2-container--default .select2-selection--single,
    .dark-mode .settings-shell .select2-container--default .select2-selection--multiple {
        background-color: #1f2937;
        color: #e5e7eb;
        border-color: rgba(148, 163, 184, .35);
    }

    .dark-mode .settings-shell .form-control:focus,
    .dark-mode .settings-shell .select2-container--default.select2-container--focus .select2-selection--multiple,
    .dark-mode .settings-shell .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #38bdf8;
        box-shadow: 0 0 0 .2rem rgba(56, 189, 248, .2);
    }

    .dark-mode .settings-shell .select2-container--default .select2-selection__rendered,
    .dark-mode .settings-shell .select2-container--default .select2-selection__choice,
    .dark-mode .settings-shell .select2-container--default .select2-search__field {
        color: #e5e7eb;
    }

    .dark-mode .settings-shell .select2-dropdown {
        background: #111827;
        border-color: rgba(148, 163, 184, .35);
    }

    .dark-mode .settings-shell .select2-container--default .select2-results__option {
        color: #e5e7eb;
    }

    .dark-mode .settings-shell .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #0369a1;
    }

    .dark-mode #JSONSettingsModal .modal-content {
        background: #111827;
        color: #e5e7eb;
    }

    .dark-mode #JSONSettingsModal .modal-header,
    .dark-mode #JSONSettingsModal .modal-footer {
        border-color: rgba(148, 163, 184, .3);
    }

    .dark-mode #JSONSettingsModal .modal-header.bg-light {
        background: #1f2937 !important;
    }

    .dark-mode #JSONSettingsModal .close {
        color: #f8fafc;
        text-shadow: none;
        opacity: .85;
    }
</style>

<form id="form-save">
    <input type="hidden" id="modeID" name="Mode" value="<?= $Mode ?>" />

    <div class="settings-shell">
    <div class="card">
        <div class="card-body p-0">
            <div id="JSONSettingsModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h4 class="modal-title"><?= gettext('Edit JSON Settings') ?></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="JSONSettingsDiv">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><?= gettext("Close") ?></button>
                            <button type="button" class="btn btn-primary jsonSettingsClose"><?= gettext("Save") ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row no-gutters">
                <div class="col-12 col-md-4 col-lg-3 settings-side p-3">
                    <div class="nav flex-column nav-pills h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                        <?php                        
                        $categories_modes = [];
                        foreach (array_keys($categories) as $key) {
                            if ($key == 'Entity Information') {
                                $categories_modes[$key] = '';
                            } else {
                                $categories_modes[$key] = strtolower(str_replace(' ', '', $key));
                            }
                        }

                        foreach ($categories as $category => $settings) {
                        ?>
                            <a class="nav-link settings-tab-link <?= ($categories_modes[$category] == $Mode) ? 'active' : "" ?>"
                                href="#<?= str_replace(" ", '', $category) ?>"
                                data-toggle="pill" role="tab" aria-controls="custom-tabs-three-messages"
                                aria-selected="<?= ($category == 'Church Information') ? 'true' : "false" ?>"
                                data-mode="<?= $categories_modes[$category] ?>">
                                <?= gettext($category) ?>
                            </a>
                        <?php
                        } ?>

                        <hr/>

                        <input class="btn btn-success settings-save" name="save" id="save"
                                value="<?= gettext("Save Settings") ?>">
                        
                    </div>
                </div>
                <div class="col-12 col-md-8 col-lg-9 p-3">
                    <div class="tab-content" id="vert-tabs-tabContent">
                        <?php
                        // Build Category Pages                    
                        foreach ($categories as $category => $settings) {
                        ?>
                            <div class="tab-pane settings-pane <?= ($categories_modes[$category] == $Mode) ? 'active' : '' ?>"
                                id="<?= str_replace(" ", '', $category) ?>">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover settings-table mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="170px"><?= gettext('Variable name') ?></th>
                                                <th><?= gettext('Value') ?></th>
                                                <th width="220px"><?= gettext('Default Value') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($settings as $settingName) {
                                            $setting = SystemConfig::getConfigItem($settingName)
                                        ?>
                                            <tr>
                                                <td><span class="settings-name"><?= $setting->getName() ?></span></td>
                                                <input type=hidden name='type[<?= $setting->getId() ?>]'
                                                    value='<?= $setting->getType() ?>'>
                                                <td>
                                                    <!--  Current Value -->
                                                    <?php
                                                    if ($setting->getType() == 'choice') {
                                                    ?>
                                                        <select name='new_value[<?= $setting->getId() ?>]'
                                                            class="choiceSelectBox form-control form-control-sm" style="width: 100%">
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
                                                        <input type=text size=40 maxlength=1000
                                                            name='new_value[<?= $setting->getId() ?>]'
                                                            value='<?= htmlspecialchars($setting->getValue(), ENT_QUOTES) ?>'
                                                            class="form-control form-control-sm">
                                                    <?php
                                                    } elseif ($setting->getType() == 'password') {
                                                    ?>
                                                        <input type=password size=40 maxlength=255
                                                            name='new_value[<?= $setting->getId() ?>]'
                                                            value='<?= htmlspecialchars($setting->getValue(), ENT_QUOTES) ?>'
                                                            class="form-control form-control-sm" autocomplete="off">
                                                    <?php
                                                    } elseif ($setting->getType() == 'textarea') {
                                                    ?>
                                                        <textarea rows=4 cols=40 name='new_value[<?= $setting->getId() ?>]'
                                                            class="form-control form-control-sm"><?= htmlspecialchars($setting->getValue(), ENT_QUOTES) ?></textarea>
                                                    <?php
                                                    } elseif ($setting->getType() == 'number' || $setting->getType() == 'date') {
                                                    ?>
                                                        <input type=text size=40 maxlength=15
                                                            name='new_value[<?= $setting->getId() ?>]'
                                                            value='<?= $setting->getValue() ?>'
                                                            class="form-control form-control-sm">
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
                                                            class="choiceSelectBox form-control form-control-sm" style="width: 100%">
                                                            <option value='' <?= $sel1 ?>><?= gettext('False') ?>
                                                            <option value='1' <?= $sel2 ?>><?= gettext('True') ?>
                                                        </select>
                                                    <?php
                                                    } elseif ($setting->getType() == 'json') {
                                                    ?>
                                                        <input type="hidden" name='new_value[<?= $setting->getId() ?>]'
                                                            value='<?= $setting->getValue() ?>'>
                                                        <button class="btn btn-sm btn-outline-primary jsonSettingsEdit"
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
                                                            class="choiceSelectBox form-control form-control-sm" style="width: 100%">
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
                                                <td class="text-nowrap">
                                                    <div class="settings-default">
                                                    <?php if (!empty($setting->getTooltip())) {
                                                    ?>
                                                        <a data-toggle="popover"
                                                            title="<?= _("Definition") ?>" data-content="<?= gettext($setting->getTooltip()) ?>" target="_blank" class="settings-help"><i
                                                                class="far  fa-question-circle"></i></a>
                                                    <?php
                                                    }
                                                    if (!empty($setting->getUrl())) {
                                                    ?>
                                                        <a href="<?= $setting->getUrl() ?>" target="_blank" class="settings-link"><i
                                                                class="fa  fa-link"></i></a>
                                                    <?php
                                                    } ?>
                                                    <?= $display_default ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        } ?>
                                        </tbody>
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
    </div>
</form>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(function() {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href") // activated tab
            $(target + " .choiceSelectBox").select2({
                width: 'resolve'
            });
        });
        $(".choiceSelectBox").select2({
            width: 'resolve'
        });

        <?php
        foreach (SystemConfig::getCategories() as $category => $settings) {
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

<script src="<?= $sRootPath ?>/skin/js/sidebar/SystemSettings.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>