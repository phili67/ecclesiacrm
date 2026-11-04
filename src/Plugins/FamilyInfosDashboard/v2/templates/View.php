<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

?>

<div class="card card-tabs family-infos-dashboard <?= $plugin->getName() ?> <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header family-dashboard-header">
        <ul class="nav nav-tabs family-dashboard-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="custom-tabs-latest-families-tab" data-toggle="pill"
                   href="#custom-tabs-latest-families" role="tab"
                   aria-controls="custom-tabs-latest-families"
                   aria-selected="true">
                    <i class="fa-solid fa-people-roof mr-1"></i> <?= dgettext("messages-FamilyInfosDashboard", 'Latest Families') ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-updated-families-tab" data-toggle="pill"
                   href="#custom-tabs-updated-families" role="tab"
                   aria-controls="custom-tabs-updated-families"
                   aria-selected="false">
                    <i class="fa-solid fa-people-roof"></i><i class="fas fa-check mr-1"></i> <?= dgettext("messages-FamilyInfosDashboard",'Updated Families') ?>
                </a>
            </li>
        </ul>
        <div class="card-tools">
            <button type="button" class="btn btn-sm text-muted" data-card-widget="remove" aria-label="Remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-sm text-muted" data-card-widget="collapse" title="Collapse" aria-label="Collapse">
                <i class="fas <?= $Card_collapsed_button ?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body" style="<?= $Card_body ?>">
        <div class="tab-content">
            <div class="tab-pane fade active show" id="custom-tabs-latest-families" role="tabpanel"
                 aria-labelledby="custom-tabs-latest-families-tab">
                <table class="table family-dashboard-table" id="latestFamiliesDashboardItem">
                    <thead>
                    <tr>
                        <th scope="col" data-field="name"><?= dgettext("messages-FamilyInfosDashboard",'Family Name') ?></th>
                        <th scope="col" data-field="address"><?= dgettext("messages-FamilyInfosDashboard",'Address') ?></th>
                        <th scope="col" data-field="city"><?= dgettext("messages-FamilyInfosDashboard",'Created') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="custom-tabs-updated-families" role="tabpanel"
                 aria-labelledby="custom-tabs-updated-families-tab">
                <table class="table family-dashboard-table" id="updatedFamiliesDashboardItem">
                    <thead>
                    <tr>
                        <th scope="col" data-field="name"><?= dgettext("messages-FamilyInfosDashboard",'Family Name') ?></th>
                        <th scope="col" data-field="address"><?= dgettext("messages-FamilyInfosDashboard",'Address') ?></th>
                        <th scope="col" data-field="city"><?= dgettext("messages-FamilyInfosDashboard",'Updated') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
