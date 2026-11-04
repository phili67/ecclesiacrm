<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);
?>

<div class="card card-tabs person-infos-dashboard <?= $plugin->getName() ?> <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header person-dashboard-header">
        <ul class="nav nav-tabs person-dashboard-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="custom-tabs-latest-members-tab" data-toggle="pill"
                   href="#custom-tabs-latest-members" role="tab" aria-controls="custom-tabs-latest-members"
                   aria-selected="true">
                    <i class="fas fa-user-plus mr-1"></i> <?= dgettext("messages-PersonInfosDashboard",'Latest Members') ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-two-settings-tab" data-toggle="pill"
                   href="#custom-tabs-two-settings" role="tab" aria-controls="custom-tabs-two-settings"
                   aria-selected="false">
                    <i class="fas fa-user"></i><i class="fas fa-check mr-1"></i> <?= dgettext("messages-PersonInfosDashboard",'Updated Members') ?>
                </a>
            </li>
        </ul>
        <div class="card-tools">
            <button type="button" class="btn btn-sm text-muted" data-card-widget="remove" aria-label="Remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-sm text-muted" data-card-widget="collapse" title="Collapse" aria-label="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body" style="<?= $Card_body ?>">
        <div class="tab-content">
            <div class="tab-pane fade active show" id="custom-tabs-latest-members" role="tabpanel"
                 aria-labelledby="custom-tabs-latest-members-tab">
                <table class="table person-dashboard-table" id="latestPersonsDashboardItem">
                    <thead>
                    <tr>
                        <th scope="col" data-field="lastname"><?= dgettext("messages-PersonInfosDashboard",'Name') ?></th>
                        <th scope="col" data-field="address"><?= dgettext("messages-PersonInfosDashboard",'Address') ?></th>
                        <th scope="col" data-field="city"><?= dgettext("messages",'Created') ?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="custom-tabs-two-settings" role="tabpanel"
                 aria-labelledby="custom-tabs-two-settings-tab">
                <table class="table person-dashboard-table" id="updatedPersonsDashboardItem">
                    <thead>
                    <tr>
                        <th scope="col" data-field="lastname"><?= dgettext("messages-PersonInfosDashboard",'Name') ?></th>
                        <th scope="col" data-field="address"><?= dgettext("messages-PersonInfosDashboard",'Address') ?></th>
                        <th scope="col" data-field="city"><?= dgettext("messages-PersonInfosDashboard",'Updated') ?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
