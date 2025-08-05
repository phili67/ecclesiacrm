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
            <h3 id="realFamilyCNT">
                <?= $dashboardCounts['familyCount'] ?>
            </h3>
            <p>
                <?= dgettext("messages-FamilyDashboardWidget","Families") ?>
            </p>
        </div>
        <div class="icon">
            <i class="fas fa-male" style="right: 124px"></i><i class="fas fa-female" style="right: 67px"></i><i
                class="fas fa-child"></i>
        </div>
        <div class="small-box-footer">
            <a href="<?= $sRootPath ?>/v2/people/list/family" style="color:#ffffff">
                <?= dgettext("messages-FamilyDashboardWidget","View Families") ?> <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div><!-- ./col -->