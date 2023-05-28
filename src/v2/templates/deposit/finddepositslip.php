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
require $sRootDocument . '/Include/Header.php';
?>

<div class="card">
    <div class="card-header border-1">
        <h3 class="card-title"><?php echo _('Add New Deposit: '); ?></h3>
    </div>
    <div class="card-body">
        <form action="#" method="get" class="form">
            <div class="row">
                <div class="col-md-3">
                    <label for="depositComment"><?= _('Deposit Comment') ?></label>
                    <input class="form-control newDeposit" name="depositComment" id="depositComment" style="width:100%">
                </div>
                <!--<div class="col-lg-3">
            <label for="depositType"><?= _('Fund') ?></label>
            <select class= "form-control form-control-sm" id="depositFund" name="depositFund">
            <?php
                foreach ($donationFunds as $donationFund) {
                    ?>
              <option value="<?= $donationFund->getId() ?>"><?= $donationFund->getName() ?></option>
            <?php
                }
                ?>
            </select>
          </div>-->
                <div class="col-md-3">
                    <label for="depositType"><?= _('Deposit Type') ?></label>
                    <select class= "form-control form-control-sm" id="depositType" name="depositType">
                        <option value="Bank"><?= _('Bank') ?></option>
                        <option value="CreditCard"><?= _('Credit Card') ?></option>
                        <option value="BankDraft"><?= _('Bank Draft') ?></option>
                        <option value="eGive"><?= _('eGive') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="depositDate"><?= _('Deposit Date') ?></label>
                    <input class= "form-control form-control-sm" name="depositDate" id="depositDate" style="width:100%"
                           class="date-picker">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary"
                                id="addNewDeposit"><?= _('Add New Deposit') ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header border-1">
        <h3 class="card-title"><?php echo _('Deposits: '); ?></h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-striped table-bordered data-table" id="depositsTable" width="100%"></table>

            <button type="button" id="deleteSelectedRows" class="btn btn-danger"
                    disabled> <?= _('Delete Selected Rows') ?> </button>
            <button type="button" id="exportSelectedRows" class="btn btn-success exportButton" data-exportType="ofx"
                    disabled><i class="fas fa-download"></i> <?= _('Export Selected Rows (OFX)') ?></button>
            <button type="button" id="exportSelectedRowsCSV" class="btn btn-success exportButton" data-exportType="csv"
                    disabled><i class="fas fa-download"></i> <?= _('Export Selected Rows (CSV)') ?></button>
            <button type="button" id="generateDepositSlip" class="btn btn-success exportButton" data-exportType="pdf"
                    disabled> <?= _('Generate Deposit Slip for Selected Rows (PDF)') ?></button>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/finance/FindDepositSlip.js"></script>

<script nonce="<?= $CSPNonce ?>">
    $('#deleteSelectedRows').click(function () {
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




