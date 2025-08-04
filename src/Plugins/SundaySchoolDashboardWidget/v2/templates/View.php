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
            <h3 id="groupStatsSundaySchool">
                <?= $dashboardCounts['SundaySchoolCount'] ?>
            </h3>
            <p>
                <?= dgettext("messages-SundaySchoolDashboardWidget",'Sunday School Classes') ?>
            </p>
        </div>
        <div class="icon">
            <i class="fas fa-child"></i>
        </div>
        <a href="<?= $sRootPath ?>/v2/sundayschool/dashboard" class="small-box-footer">
            <?= dgettext("messages-SundaySchoolDashboardWidget",'More info') ?> <i class="fas fa-arrow-circle-right"></i>
        </a>
    </div>
</div><!-- ./col -->