<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;
use EcclesiaCRM\Service\DashboardItemService;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

// Dashboard People and so on event count
$dshiS = new DashboardItemService();
$dashboardCounts = $dshiS->getAllItems();
?>

<div class="col-lg-2 col-xs-6">
    <!-- small box -->
    <div class="small-box <?= $plugin->getPlgnColor() ?>">
        <div class="inner">
            <h3 id="singleCNT">
                <?= $dashboardCounts['singleCount'] ?>
            </h3>
            <p>
                <?= dgettext("messages-SingleDashboardWidget", 'Single Persons') ?>
            </p>
        </div>
        <div class="icon">
            <i class="fas fa-male"></i>
        </div>
        <div class="small-box-footer">
            <a href="<?= $sRootPath ?>/v2/people/list/singles" style="color:#ffffff">
                <?= dgettext("messages-SingleDashboardWidget", "View Singles") ?> <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div><!-- ./col -->