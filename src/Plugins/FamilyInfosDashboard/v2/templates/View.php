<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

?>

<style>
    .family-infos-dashboard .family-dashboard-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .25rem .75rem;
        padding: .75rem 1rem 0;
    }

    .family-infos-dashboard .family-dashboard-tabs {
        flex: 1 1 auto;
        flex-wrap: wrap;
        border-bottom: 0;
    }

    .family-infos-dashboard .family-dashboard-tabs .nav-link {
        border: 0;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding: .6rem .75rem;
        font-weight: 600;
    }

    .family-infos-dashboard .family-dashboard-tabs .nav-link.active {
        border-bottom-color: #007bff;
    }

    .family-infos-dashboard .card-header > .card-tools {
        float: none;
        margin: 0 0 .4rem;
        white-space: nowrap;
    }

    .family-infos-dashboard .card-body {
        padding: .75rem 1rem 1rem;
    }

    .family-infos-dashboard table.family-dashboard-table {
        width: 100% !important;
        margin: 0 !important;
        font-size: .875rem;
    }

    .family-infos-dashboard .family-dashboard-table thead th {
        padding: .75rem;
        border-top: 0;
        border-bottom-width: 1px;
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .03em;
    }

    .family-infos-dashboard .family-dashboard-table tbody td {
        padding: .85rem .75rem;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    .family-infos-dashboard .family-dashboard-table tbody tr:hover {
        background-color: rgba(0, 123, 255, .06);
    }

    .family-infos-dashboard .family-dashboard-table tbody td:first-child a {
        font-weight: 600;
    }

    .family-infos-dashboard .family-dashboard-table tbody td:first-child img {
        width: 36px;
        height: 36px;
        margin-right: .4rem;
        border-radius: 50%;
        object-fit: cover;
    }
</style>

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
            <button type="button" class="btn btn-sm" data-card-widget="remove" aria-label="Remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-sm" data-card-widget="collapse" title="Collapse" aria-label="Collapse">
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
