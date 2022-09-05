<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

?>

<div class="card card-gray card-tabs <?= $plugin->getName() ?> <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="custom-tabs-latest-members-tab" data-toggle="pill"
                   href="#custom-tabs-latest-members" role="tab" aria-controls="custom-tabs-latest-members"
                   aria-selected="false">
                    <i class="fas fa-user-plus"></i> <?= dgettext("messages-PersonInfosDashboard",'Latest Members') ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-two-settings-tab" data-toggle="pill"
                   href="#custom-tabs-two-settings" role="tab" aria-controls="custom-tabs-two-settings"
                   aria-selected="false">
                    <i class="fas fa-user"></i><i class="fas fa-check"></i> <?= dgettext("messages-PersonInfosDashboard",'Updated Members') ?>
                </a>
            </li>
        </ul>
        <div class="card-tools">
            <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body"  style="<?= $Card_body ?>">
        <div class="tab-content" id="custom-tabs-two-tabContent">
            <div class="tab-pane fade   active show" id="custom-tabs-latest-members" role="tabpanel"
                 aria-labelledby="custom-tabs-latest-members-tab">
                <table class=" table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                       id="latestPersonsDashboardItem"
                       style="width:100%">
                    <thead>
                    <tr>
                        <th data-field="lastname"><?= dgettext("messages-PersonInfosDashboard",'Name') ?></th>
                        <th data-field="address"><?= dgettext("messages-PersonInfosDashboard",'Address') ?></th>
                        <th data-field="city"><?= dgettext("messages-PersonInfosDashboard",'Updated') ?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="custom-tabs-two-settings" role="tabpanel"
                 aria-labelledby="custom-tabs-two-settings-tab">
                <table class=" table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                       id="updatedPersonsDashboardItem"
                       style="width:100%">
                    <thead>
                    <tr>
                        <th data-field="lastname"><?= dgettext("messages-PersonInfosDashboard",'Name') ?></th>
                        <th data-field="address"><?= dgettext("messages-PersonInfosDashboard",'Address') ?></th>
                        <th data-field="city"><?= dgettext("messages-PersonInfosDashboard",'Updated') ?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /.card -->
</div>
