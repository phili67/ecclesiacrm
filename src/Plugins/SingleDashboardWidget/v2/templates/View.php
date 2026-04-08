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

<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #1cc88a!important;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-uppercase text-muted" style="font-size:.7rem;font-weight:700;letter-spacing:.05em;"><?= dgettext("messages-SingleDashboardWidget", 'Singles') ?></span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(28,200,138,.15);">
                    <i class="fas fa-male" style="color:#1cc88a;font-size:16px;"></i>
                </div>
            </div>
            <div class="h2 mb-0 font-weight-bold" id="singleCNT"><?= $dashboardCounts['singleCount'] ?></div>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-2 px-3">
            <a href="<?= $sRootPath ?>/v2/people/list/singles" class="small font-weight-bold" style="color:#1cc88a;">
                <?= dgettext("messages-SingleDashboardWidget", "View Singles") ?> <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>