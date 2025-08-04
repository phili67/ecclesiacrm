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
            <h3 id="peopleStatsDashboard">
                <?= $dashboardCounts['personCount'] ?>
            </h3>
            <p>
                <?= dgettext("messages-PersonInfosDashboard",'People') ?>
            </p>
        </div>
        <div class="icon">
            <i class="fas fa-user"></i>
        </div>
        <a href="<?= $sRootPath ?>/v2/people/list/person" class="small-box-footer">
            <?= dgettext("messages-PersonInfosDashboard",'See All People') ?> <i class="fas fa-arrow-circle-right"></i>
        </a>
    </div>
</div>