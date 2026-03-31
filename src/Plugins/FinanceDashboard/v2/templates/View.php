<?php

use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;


$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$depositData = false;  //Determine whether or not we should display the deposit line graph
$deposits = Null;
if (SessionUser::getUser()->isFinanceEnabled()) {
    $deposits = DepositQuery::create()->filterByDate(['min' => date('Y-m-d', strtotime('-90 days'))])->find();
    if (count($deposits) > 0) {
        $depositData = $deposits->toJSON();
    }
}

if ($depositData && SystemConfig::getBooleanValue('bEnabledFinance')) { // If the user has Finance permissions, then let's display the deposit line chart
    ?>

<div class="card <?= $plugin->getName() ?> bg-gradient-info <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header text-white border-0 ui-sortable-handle">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-line me-2"></i> <?= dgettext("messages-FinanceDashboard",'Deposit Tracking') ?>
                <small class="text-light">(<?= dgettext("messages-FinanceDashboard",'Last 90 days') ?>)</small>
            </h5>
            <div class="card-tools">
                <button type="button" class="btn  btn-sm text-white" data-card-widget="remove" aria-label="Remove">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn btn-sm text-white" data-card-widget="collapse" title="Collapse" aria-label="Collapse">
                    <i class="fas <?= $Card_collapsed_button?>"></i>
                </button>
            </div>
        </div>
    </div><!-- /.box-header -->
    <div class="card-body" style="<?= $Card_body ?>">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="chart-container" style="position: relative; width: 100%; min-height: 250px; aspect-ratio: 2 / 1;max-height: 400px;">
                    <canvas id="deposit-lineGraph" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; min-height: 250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="text-center mt-2">
            <small class="text-mute text-white"><?= dgettext("messages-FinanceDashboard",'Interactive chart showing deposit trends over the past 90 days.') ?></small>
        </div>
    </div>
</div>
    <?php
}  //END IF block for Finance permissions to include HTML for Deposit Chart
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.bEnabledFinance = <?= (SystemConfig::getBooleanValue('bEnabledFinance')) ? 'true' : 'false' ?>;
    window.CRM.depositData = <?= ($depositData) ? $depositData : "false" ?>;
</script>
