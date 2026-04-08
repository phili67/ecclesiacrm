<?php
/*******************************************************************************
 *
 *  filename    : finddepositslip.php
 *  last change : 2016-02-28
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2023 EcclesiaCRM
 *
 ******************************************************************************/

// we place this part to avoid a problem during the upgrade process
// Set the page title
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

require $sRootDocument . '/Include/Header.php';

$depositData = false;
if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
    $deposits = DepositQuery::create()->filterByDate(['min' => date('Y-m-d', strtotime('-90 days'))])->find();
    if (count($deposits) > 0) {
        $depositData = $deposits->toJSON();
    }
}

$newDepositColClass = $depositData ? 'col-xl-7 col-lg-7' : 'col-lg-12';
?>

<div class="row">
    <div class="<?= $newDepositColClass ?> mb-3">
        <div class="card card-primary card-outline h-100">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i><?= _('New Deposit') ?></h3>
            </div>
            <div class="card-body p-3">
                <div class="alert alert-light border d-flex align-items-start mb-3 py-2 px-3">
                    <i class="fas fa-lightbulb text-warning mt-1 mr-2"></i>
                    <div class="small mb-0 text-muted">
                        <div class="font-weight-bold text-dark mb-1"><?= _('Quick tip') ?></div>
                        <div><?= _('Add a short comment to quickly identify this deposit later.') ?></div>
                        <div><?= _('Choose the payment type and date before creating the deposit slip.') ?></div>
                    </div>
                </div>
                <form action="#" method="get">
                    <div class="form-row">
                        <div class="col-12 mb-2">
                            <label class="small mb-1" for="depositComment"><?= _('Deposit Comment') ?></label>
                            <input class="form-control form-control-sm newDeposit" name="depositComment" id="depositComment" placeholder="<?= _('Optional comment…') ?>">
                        </div>
                    </div>
                    <div class="form-row align-items-end">
                        <div class="col-md-4">
                            <label class="small mb-1" for="depositType"><?= _('Type') ?></label>
                            <select class="form-control form-control-sm" id="depositType" name="depositType">
                                <option value="Bank"><i class="fas fa-university"></i> <?= _('Bank') ?></option>
                                <option value="CreditCard"><?= _('Credit Card') ?></option>
                                <option value="BankDraft"><?= _('Bank Draft') ?></option>
                                <option value="eGive"><?= _('eGive') ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small mb-1" for="depositDate"><?= _('Date') ?></label>
                            <input class="form-control form-control-sm date-picker" name="depositDate" id="depositDate">
                        </div>
                        <div class="col-md-4 mt-1">
                            <button type="button" class="btn btn-primary btn-sm btn-block" id="addNewDeposit">
                                <i class="fas fa-plus mr-1"></i><?= _('Add Deposit') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($depositData) { ?>
        <div class="col-xl-5 col-lg-5 mb-3">
            <div class="card bg-gradient-info h-100">
                <div class="card-header text-white border-0 py-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i><?= _('Deposit Tracking') ?>
                        <small class="text-light">(<?= _('Last 90 days') ?>)</small>
                    </h3>
                </div>
                <div class="card-body p-2">
                    <div class="chart-container" style="position: relative; width: 100%; min-height: 240px; aspect-ratio: 2 / 1; max-height: 340px;">
                        <canvas id="deposit-lineGraph" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; min-height: 240px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<div class="card card-outline card-secondary">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-list mr-2"></i><?= _('Deposits') ?></h3>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" id="deleteSelectedRows" class="btn btn-danger" disabled>
                <i class="fas fa-trash-alt mr-1"></i><?= _('Delete') ?>
            </button>
            <button type="button" id="exportSelectedRows" class="btn btn-outline-success exportButton" data-exportType="ofx" disabled>
                <i class="fas fa-download mr-1"></i>OFX
            </button>
            <button type="button" id="exportSelectedRowsCSV" class="btn btn-outline-success exportButton" data-exportType="csv" disabled>
                <i class="fas fa-download mr-1"></i>CSV
            </button>
            <button type="button" id="generateDepositSlip" class="btn btn-outline-primary exportButton" data-exportType="pdf" disabled>
                <i class="fas fa-file-pdf mr-1"></i><?= _('Slip PDF') ?>
            </button>
        </div>
    </div>
    <div class="card-body p-2">
        <table class="table table-striped table-bordered table-sm data-table" id="depositsTable" width="100%"></table>
    </div>
</div>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.bEnabledFinance = <?= (SystemConfig::getBooleanValue('bEnabledFinance')) ? 'true' : 'false' ?>;
    window.CRM.depositData = <?= ($depositData) ? $depositData : 'false' ?>;
</script>

<?php if ($depositData) { ?>
    <script src="<?= $sRootPath ?>/skin/js/finance/DepositTrackingChart.js"></script>
<?php } ?>

<script src="<?= $sRootPath ?>/skin/js/finance/FindDepositSlip.js"></script>

<script nonce="<?= $CSPNonce ?>">
    $('#deleteSelectedRows').on('click', function () {
        var deletedRows = dataT.rows('.selected').data()
        bootbox.confirm({
            title: '<?= _("Confirm Delete") ?>',
            message: "<p><?= _("Are you sure you want to delete the selected"); ?> " + deletedRows.length + ' <?= _("Deposit(s)"); ?>?' +
                "</p><p><?= _("This will also delete all payments associated with this deposit"); ?></p>" +
                "<p><?= _("This action CANNOT be undone, and may have legal implications!") ?></p>" +
                "<p><?= _("Please ensure this what you want to do.") ?></p>",
            buttons: {
                cancel: {
                    label: '<?= _("Close"); ?>'
                },
                confirm: {
                    label: '<?php echo _("Delete"); ?>'
                }
            },
            callback: function (result) {
                if (result) {
                    $.each(deletedRows, function (index, value) {
                        window.CRM.APIRequest({
                            method: 'DELETE',
                            path: 'deposits/' + value.Id, // the url where we want to POST
                        }, function (data) {
                            dataT.rows('.selected').remove().draw(false);
                            $(".count-deposit").html(dataT.column(0).data().length);
                            if (dataT.column(0).data().length == 0) {
                                $(".current-deposit").html('');
                                $(".deposit-current-deposit-item").hide();
                            }

                            if (value.Id == $(".current-deposit").data("id")) {
                                $(".current-deposit").html('');
                                $(".current-deposit-item").html('');
                                $(".deposit-current-deposit-item").hide();
                            }
                        });
                    });
                }
            }
        });
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




