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
                <a class="nav-link active" id="custom-tabs-latest-families-tab" data-toggle="pill"
                   href="#custom-tabs-latest-families" role="tab"
                   aria-controls="custom-tabs-latest-families"
                   aria-selected="true">
                    <i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i><i
                        class="fas fa-plus"></i> <?= dgettext("messages-FamilyInfosDashboard",'Latest Families') ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-updated-families-tab" data-toggle="pill"
                   href="#custom-tabs-updated-families" role="tab"
                   aria-controls="custom-tabs-updated-families"
                   aria-selected="false">
                    <i class="fas fa-female"></i><i class="fas fa-child"></i><i
                        class="fas fa-check"></i> <?= dgettext("messages-FamilyInfosDashboard",'Updated Families') ?>
                </a>
            </li>
        </ul>
        <div class="card-tools" style="margin-top:-33px">
            <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body" style="<?= $Card_body ?>;padding: .0rem;"">
        <div class="tab-content" id="custom-tabs-two-tabContent">
            <div class="tab-pane fade  active show" id="custom-tabs-latest-families" role="tabpanel"
                 aria-labelledby="custom-tabs-latest-families-tab">
                <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                       id="latestFamiliesDashboardItem"
                       style="width:100%">
                    <thead>
                    <tr>
                        <th data-field="name"><?= dgettext("messages-FamilyInfosDashboard",'Family Name') ?></th>
                        <th data-field="address"><?= dgettext("messages-FamilyInfosDashboard",'Address') ?></th>
                        <th data-field="city"><?= dgettext("messages-FamilyInfosDashboard",'Created') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="custom-tabs-updated-families" role="tabpanel"
                 aria-labelledby="custom-tabs-updated-families-tab">
                <table class=" table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                       id="updatedFamiliesDashboardItem"
                       style="width:100%">
                    <thead>
                    <tr>
                        <th data-field="name"><?= dgettext("messages-FamilyInfosDashboard",'Family Name') ?></th>
                        <th data-field="address"><?= dgettext("messages-FamilyInfosDashboard",'Address') ?></th>
                        <th data-field="city"><?= dgettext("messages-FamilyInfosDashboard",'Updated') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /.card -->
</div>
