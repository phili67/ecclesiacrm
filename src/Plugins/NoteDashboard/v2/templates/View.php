<?php

// we've to load the model make the plugin to workmv
spl_autoload_register(function ($className) {
    $res = str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
    if (is_file($res)) {
        include_once $res;
    }
});

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

use PluginStore\NoteDashboardQuery;
use PluginStore\NoteDashboard;

use EcclesiaCRM\SessionUser;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$note = NoteDashboardQuery::create()
    ->findOneByUserId(SessionUser::getId());

if ( is_null($note) ) {
    $note = new NoteDashboard();

    $note->setUserId(SessionUser::getId());
    $note->setNote('');
    $note->save();
}
?>

<div class="card note-dashboard-yellow <?= $plugin->getName() ?> <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header border-0 ui-sortable-handle">
        <h5 class="card-title"><?=  dgettext("messages-NoteDashboard","Note") ?></h5>
        <div class="card-tools">
            <button type="button" class="btn btn-warning btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body sub-content-body-6 note-dashboard-body">
        <textarea cols="80" rows="8" id="NoteDashboardContent" class="form-control form-control-sm note-dashboard-text"><?= $note->getNote() ?></textarea>
    </div>
    <div class="card-footer text-right note-dashboard-footer">
        <button class="btn btn-warning btn-xs" id="saveDashboardNote"><?=  dgettext("messages-NoteDashboard","Save")  ?></button>
    </div>
</div>
