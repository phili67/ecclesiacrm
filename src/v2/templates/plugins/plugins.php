<?php

/*******************************************************************************
 *
 *  filename    : templates/backup.php
 *  last change : 2019-11-21
 *  description : manage the backup
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

use EcclesiaCRM\PluginQuery;

$plugins = PluginQuery::create()->find();

$nbr_activated = PluginQuery::create()->findByActiv(1)->count();
$nbr_deactivated = $plugins->count() - $nbr_activated;

?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-2 mb-md-0"><i class="fas fa-plug mr-2 text-primary"></i> <?= _('Plugins managements') ?></h3>
            <div class="card-tools">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-wrench mr-1"></i> <?= _("Add a plugin") ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" role="menu">
                    <!--
                    TODO : plugins remote manage
                    <a href="#" class="dropdown-item"><?= _("Add new plugin") ?></a>
                    <a class="dropdown-divider" style="color: #0c0c0c"></a>
                    -->
                        <a href="#" class="dropdown-item" id="add-plugin"><i class="fas fa-upload mr-1"></i><?= _("Upload A Plugin") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
            <div class="alignleft actions bulkactions form-inline mb-2 mb-md-0">
                <label for="action-selector" class="mr-2 mb-0 text-muted small"><?= _("Select the grouped action") ?></label>
                <select name="action" id="action-selector" class="plugin-select custom-select custom-select-sm">
                    <option value="-1"><?= _("Grouped actions") ?></option>
                    <option value="activate-selected" value="activate-selected"><?= _("Activate") ?></option>
                    <option value="deactivate-selected" value="deactivate-selected"><?= _("Deactivate") ?></option>
                    <option value="delete-selected" value="delete-selected"><?= _("Delete") ?></option>
                </select>
            </div>

            <ul class="subsubsub d-flex flex-wrap align-items-center mb-0 pl-0" style="list-style: none;">
                <li class="all mr-2 mb-1">
                    <a href="plugins.php?plugin_status=all" class="btn btn-outline-secondary btn-sm" aria-current="page">
                        <?= _("All") ?> <span class="badge badge-light ml-1"><?= $plugins->count() ?></span>
                    </a>
                </li>
                <li class="active mr-2 mb-1">
                    <a href="plugins.php?plugin_status=active" class="btn btn-outline-success btn-sm">
                        <?= _("Activated") ?> <span class="badge badge-light ml-1"><?= $nbr_activated ?></span>
                    </a>
                </li>
                <li class="inactive mb-1">
                    <a href="plugins.php?plugin_status=inactive" class="btn btn-outline-warning btn-sm">
                        <?= _("Deactivated") ?> <span class="badge badge-light ml-1"><?= $nbr_deactivated ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered mb-0" id="plugins-listing-table" style="width:100%;">
            <thead>
            <tr>
                <th class="text-center align-middle" style="width:60px">
                    <input type="checkbox" class="check_all" id="check_all" data-toggle="tooltip"
                           data-placement="bottom" title="" data-original-title="<?= _("Check all boxes") ?>">
                </th>
                <th><?= _('Plugin') ?></th>
                <th><?= _('Category') ?></th>
                <th><?= _('Description') ?></th>
                <th><?= _('Status') ?></th>
                <th><?= _('Update') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($plugins as $plugin) { //Loop through the person
                $string = file_get_contents(__DIR__ . '/../../../Plugins/' . $plugin->getName() . '/config.json');
                $json_a = json_decode($string, true);
                ?>
                <tr id="row-<?= $plugin->getId() ?>" <?= $plugin->getActiv()?'class="activate-row"':'' ?>>
                    <td class="text-center align-middle <?= $plugin->getActiv()?'activate-column':'' ?>">
                        <input type="checkbox" class="checkbox_plugins checkbox_plugin<?= $plugin->getId() ?>"
                               name="CheckPlugins" data-id="<?= $plugin->getId() ?>">
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-bold mb-1"><?= $json_a['Name'] ?></div>
                        <div class="row-actions visible small">
                            <span class="0"></span>
                            <span class="1"><a href="<?= \EcclesiaCRM\dto\SystemURLs::getRootPath() ?>/<?= $json_a['Settings_url'] ?>"
                                               class="js-updraftplus-settings btn btn-outline-primary btn-xs mr-1 mb-1"><?= _("Settings") ?></a></span>
                            <span class="deactivate"><a href="#" class="<?= $plugin->getActiv()?"Deactivate":"Activate" ?>-plugin btn btn-outline-secondary btn-xs mr-1 mb-1"
                                                        data-id="<?= $plugin->getId() ?>"
                                                        aria-label="<?= $plugin->getActiv()?_("Deactivate"):_("Activate") ?> - <?= $json_a['Name'] ?>"><?= $plugin->getActiv()?_("Deactivate"):_("Activate") ?></a></span>
                            <span class="<?= $json_a['Name'] ?>_tour"><a href="<?= $json_a['url_docs'] ?>"
                                                                         class="js-updraftplus-tour btn btn-outline-info btn-xs mb-1"><?= _("Guided tour") ?></a>
                            </span>
                        </div>
                    </td>
                    <td class="align-middle">
                        <?php if ($plugin->getCategory() == 'Dashboard' and $plugin->getDashboardDefaultOrientation() != 'widget') : ?>
                            <i class="fas fa-columns text-muted mr-2"></i> <?= _("Dashboard") ?>
                        <?php elseif ($plugin->getCategory() == 'Dashboard' and $plugin->getDashboardDefaultOrientation() == 'widget') : ?>
                            <i class="fas fa-th text-muted mr-2"></i> <?= _("Widget") ?>
                        <?php else : ?>
                            <i class="fas fa-puzzle-piece text-muted mr-2"></i> <?= _("Plugin") ?>
                        <?php endif; ?>

                        
                    </td>
                    <td class="align-middle">
                        <div class="plugin-description">
                            <p class="mb-1"><?= $json_a['Description'] ?> <span class="text-muted">(<?= $json_a['copyrights'] ?>)</span></p>
                        </div>
                        <div class="active second plugin-version-author-uri small text-muted"><?= _("Version") ?> <span class="font-weight-bold text-dark"><?= $json_a['version'] ?></span> | <?= _("By") ?> <a
                                href="<?= $json_a['url_infos'] ?>"><?= $json_a['infos'] ?></a> | <a
                                href="<?= $json_a['Details'] ?>" class="thickbox open-plugin-details-modal"
                                aria-label="More infos about : <?= $json_a['Name'] ?>"
                                data-title="<?= $json_a['Name'] ?>"><?= _("Details") ?></a></div>
                    </td>
                    <td class="align-middle">
                        <?php if ($plugin->getActiv()) { ?>
                            <span class="badge badge-success"><?= _("Activated Plugin") ?></span>
                        <?php } else { ?>
                            <span class="badge badge-secondary"><?= _("Deactivated Plugin") ?></span>
                        <?php } ?>
                    </td>
                    <td class="align-middle">
                        <!--
                        TODO : plugins remote update
                        <a href="#" class="dropdown-item"><?= _("Add new plugin") ?></a>
                        <a class="dropdown-divider" style="color: #0c0c0c"></a>
                        -->
                        <button class="btn btn-outline-primary btn-sm update-plugin"  data-name="<?= $plugin->getName() ?>">
                            <i class="fas fa-sync-alt mr-1"></i><?= _("Update/upgrade") ?>
                        </button>
                    </td>

                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/plugins/Plugins.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
