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

<div class="card <?= $plugin->getName() ?> <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header  border-1">
            <h3 class="card-title">
                <i class="fas fa-money-bill"
                                      style="font-size:26px"></i> <?= dgettext("messages-FinanceDashboard",'Deposit Tracking') ?>
            </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div><!-- /.box-header -->
    <div class="card-body"  style="<?= $Card_body ?>">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <canvas id="deposit-lineGraph" style="height:225px; width:100%"></canvas>
                </div>
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

<script src="<?= $sRootPath ?>/Plugins/FinanceDashboard/skin/financialdashboard.js"></script>
