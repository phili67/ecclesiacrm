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

use EcclesiaCRM\dto\SystemURLs;

$plugins = PluginQuery::create()->find();

$nbr_activated = PluginQuery::create()->findByActiv(1)->count();
$nbr_deactivated = $plugins->count() - $nbr_activated;

?>

<div class="card card-gray">
    <div class="card-header border-0">
        <h3 class="card-title"><?= _('Plugins managements') ?></h3>
        <div class="card-tools">
            <div class="btn-group">
                <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="color: lightcoral">
                    <i class="fas fa-wrench"></i> <?= _("Add a plugin") ?>
                </button>
                <div class="dropdown-menu dropdown-menu-right" role="menu" style="">
                    <!--
                    TODO : plugins remote manage
                    <a href="#" class="dropdown-item"><?= _("Add new plugin") ?></a>
                    <a class="dropdown-divider" style="color: #0c0c0c"></a>
                    -->
                    <a href="#" class="dropdown-item" id="add-plugin"><?= _("Upload A Plugin") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Sélectionnez l’action groupée</label>
            <select name="action" id="action-selector" class="plugin-select">
                <option value="-1"><?= _("Grouped actions") ?></option>
                <option value="activate-selected" value="activate-selected"><?= _("Activate") ?></option>
                <option value="deactivate-selected" value="deactivate-selected"><?= _("Deactivate") ?></option>
                <option value="delete-selected" value="delete-selected"><?= _("Delete") ?></option>
            </select>
            <!--<input type="submit" id="doaction" class="button-action" value="Appliquer">-->
        </div>
        <ul class="subsubsub"
            style="list-style: none;margin: 8px 0 0;padding: 0;font-size: 13px;float: left;color: #646970;">
            <li class="all"><a href="plugins.php?plugin_status=all" class="" aria-current="page">Toutes <span
                        class="count">(<?= $plugins->count() ?>)</span></a> |
            </li>
            <li class="active"><a href="plugins.php?plugin_status=active">Activées <span
                        class="count">(<?= $nbr_activated ?>)</span></a> |
            </li>
            <li class="inactive"><a href="plugins.php?plugin_status=inactive">Désactivées <span
                        class="count">(<?= $nbr_deactivated ?>)</span></a>
            </li>
            <!--<li class="auto-update-disabled"><a href="plugins.php?plugin_status=auto-update-disabled">Mises à jour auto désactivées <span class="count">(22)</span></a></li>-->
        </ul>
        <table class="table table-hover dt-responsive table-bordered" id="plugins-listing-table" style="width:100%;">
            <thead>
            <tr>
                <th align="center" style="width:60px">
                    <input type="checkbox" class="check_all" id="check_all" data-toggle="tooltip"
                           data-placement="bottom" title="" data-original-title="<?= _("Check all boxes") ?>">
                </th>
                <th><?= _('Plugin') ?></th>
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
                    <td <?= $plugin->getActiv()?'class="activate-column"':'' ?>>
                        <input type="checkbox" class="checkbox_plugins checkbox_plugin<?= $plugin->getId() ?>"
                               name="CheckPlugins" data-id="<?= $plugin->getId() ?>">
                    </td>
                    <td>
                        <strong><?= $json_a['Name'] ?></strong>
                        <div class="row-actions visible">
                            <span class="0"></span>
                            <span class="1"><a href="<?= SystemURLs::getRootPath() ?>/<?= $json_a['Settings_url'] ?>"
                                               class="js-updraftplus-settings"><?= _("Settings") ?></a> | </span>
                            <span class="deactivate"><a href="#" class="<?= $plugin->getActiv()?"Deactivate":"Activate" ?>-plugin"
                                                        data-id="<?= $plugin->getId() ?>"
                                                        aria-label="<?= $plugin->getActiv()?_("Deactivate"):_("Activate") ?> - <?= $json_a['Name'] ?>"><?= $plugin->getActiv()?_("Deactivate"):_("Activate") ?></a> |</span>
                            <span class="<?= $json_a['Name'] ?>_tour"><a href="<?= $json_a['url_docs'] ?>"
                                                                         class="js-updraftplus-tour"><?= _("guided tour") ?></a>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="plugin-description">
                            <p><?= $json_a['Description'] ?></p>
                        </div>
                        <div class="active second plugin-version-author-uri">Version <?= $json_a['version'] ?> | By <a
                                href="<?= $json_a['url_infos'] ?>"><?= $json_a['infos'] ?></a> | <a
                                href="<?= $json_a['Details'] ?>" class="thickbox open-plugin-details-modal"
                                aria-label="More infos about : <?= $json_a['Name'] ?>"
                                data-title="<?= $json_a['Name'] ?>"><?= _("Details") ?></a></div>
                    </td>
                    <td>
                        <?= $plugin->getActiv()?_("Activated Plugin"):_("Deactivated Plugin") ?>
                    </td>
                    <td>
                        <?= $plugin->getDescription() ?>
                    </td>

                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/plugins/Plugins.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
