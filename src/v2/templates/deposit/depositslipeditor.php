<?php
/*******************************************************************************
 *
 *  filename    : depositslipeditor.php
 *  description : menu that appears after login, shows login attempts
 *
 *  http://www.ecclesiacrm.com/
 *
 *  2023 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
    <div class="col-lg-7">
        <div class="card card-primary card-outline">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-receipt mr-2"></i><?= _('Deposit Details') ?></h3>
            </div>
            <div class="card-body p-3">
                <form method="post" action="#" name="DepositSlipEditor" id="DepositSlipEditor">
                    <div class="form-row">
                        <div class="col-lg-4">
                            <label class="small mb-1" for="Date"><?= _('Date'); ?></label>
                            <input type="text" class=" form-control  form-control-sm date-picker" name="Date"
                                   value="<?= $thisDeposit->getDate(SystemConfig::getValue('sDatePickerFormat')); ?>"
                                   id="DepositDate">
                        </div>
                        <div class="col-lg-4">
                            <label class="small mb-1" for="Comment"><?= _('Comment') ?></label>
                            <input type="text" class= "form-control form-control-sm" name="Comment" id="Comment"
                                   value="<?= $thisDeposit->getComment() ?>"/>
                        </div>
                        <div class="col-lg-4 d-flex align-items-end">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="Closed" id="Closed"
                                       value="1" <?= ($thisDeposit->getClosed()) ? ' checked' : '' ?>/>
                                <label class="custom-control-label" for="Closed"><?= _('Close deposit slip') ?></label>
                                <div class="small text-muted"><?= _('Remember to press Save') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-sm btn-success mr-2 mb-2" id="DepositSlipSubmit"
                                    name="DepositSlipSubmit">
                                <i class="fas fa-save mr-1"></i><?= _('Save') ?>
                            </button>

                            <a href="<?= $sRootPath ?>/v2/deposit/find" class="btn btn-sm btn-secondary mr-2 mb-2">
                                <i class="fas fa-chevron-left mr-1"></i><?= _('Return to Deposit Listing') ?>
                            </a>

                            <?php
                            if (count($funds)) {
                                ?>
                                <a href="<?= $sRootPath ?>/api/deposits/<?= $thisDeposit->getId() ?>/pdf"
                                   class="btn btn-sm btn-outline-primary mb-2" name="DepositSlipGeneratePDF">
                                    <i class="fas fa-file-pdf mr-1"></i><?= _('Deposit Slip Report') ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
                        ?>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i><?= _('Important note: failed transactions will be deleted permanantly when the deposit slip is closed.') ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-info card-outline h-100">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i><?= _('Deposit Summary') ?></h3>
            </div>
            <div class="card-body p-2">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="bg-light rounded p-2 mb-2 d-flex align-items-center justify-content-center" style="height:160px;">
                            <canvas id="fund-donut" style="max-height:140px; max-width:140px;"></canvas>
                        </div>
                        <ul class="list-unstyled small mb-0 px-1" id="mainFundTotals">
                            <?php
                            foreach ($thisDeposit->getFundTotals() as $fund) {
                                ?>
                                <li>
                                    <b><?= _($fund['Name']) ?> </b>: <?= SystemConfig::getValue('sCurrency') . OutputUtils::money_localized($fund['Total']) ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <div class="bg-light rounded p-2 mb-2 d-flex align-items-center justify-content-center" style="height:160px;">
                            <canvas id="type-donut" style="max-height:140px; max-width:140px;"></canvas>
                        </div>
                        <ul class="list-unstyled small mb-0 px-1" id="GlobalTotal">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<div class="card card-secondary card-outline">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-list mr-2"></i><?= _('Payments on this deposit slip') ?></h3>
        <div>
            <div class="d-flex flex-wrap justify-content-end">
            <?php
            if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                if ($thisDeposit->getType() == 'eGive') {
                    ?>
                    <div class="mr-2 mb-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" name="ImporteGive"
                           onclick="document.location='<?= $sRootPath ?>/v2/deposit/egive/<?= $iDepositSlipID ?>'">
                        <i class="fas fa-file-import mr-1"></i><?= _('Import eGive') ?>
                    </button>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="mr-2 mb-1">
                    <button type="button" class="btn btn-sm btn-success" name="AddPayment"
                           onclick="document.location='<?= $sRootPath ?>/v2/deposit/pledge/editor/CurrentDeposit/<?= $iDepositSlipID ?>/Payment/v2-deposit-slipeditor-<?= $iDepositSlipID ?>'">
                        <i class="fas fa-plus mr-1"></i><?= _('Add Payment') ?>
                    </button>
                    </div>
                    <?php
                }
                if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
                    ?>
                    <div class="mb-1">
                    <form method="post" action="<?= $sRootPath ?>/v2/deposit/slipeditor/<?= $iDepositSlipID ?>" name="DepositSlipEditor">
                        <button type="submit" class="btn btn-sm btn-primary mr-1" name="DepositSlipLoadAuthorized">
                            <i class="fas fa-cloud-download-alt mr-1"></i><?php echo _('Load Authorized Transactions'); ?>
                        </button>
                        <button type="submit" class="btn btn-sm btn-warning" name="DepositSlipRunTransactions">
                            <i class="fas fa-play mr-1"></i><?php echo _('Run Transactions'); ?>
                        </button>
                    </form>
                    </div>
                    <?php
                }
            }
            ?>
            </div>
        </div>
    </div>
    <div class="card-body p-2">
        <table class="table table-sm table-striped table-hover" id="paymentsTable" width="100%"></table>
        <div class="row mt-2">
                <div class="col-md-6 mb-2">
                    <?php
                    if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                        //if ($thisDeposit->getType() == 'Bank') {
                        ?>
                        <span class="small text-muted mr-2"><?= _("Action") ?>:</span>
                        <button type="button" id="deleteSelectedRows" class="btn btn-sm btn-danger"
                                disabled><?= _("Delete Selected Rows") ?></button>
                        <?php
                        //}
                    }
                    ?>
                </div>

                <div class="col-md-6 mb-2 text-md-right">
                    <?php
                    if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                        ?>
                        <span class="small text-muted mr-2"><?= _("Statut") ?>:</span>
                        <button type="button" id="validateSelectedRows" class="btn btn-sm btn-success exportButton"
                                disabled><?= _("Payment") ?> (0) <?= _("Selected Rows") ?></button>
                        <button type="button" id="invalidateSelectedRows" class="btn btn-sm btn-info"
                                disabled><?= _("Pledge") ?> (0) <?= _("Selected Rows") ?></button>
                        <?php
                    }
                    ?>
                </div>
            </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/finance/DepositSlipEditor.js"></script>

<script nonce="<?= $CSPNonce ?>">
    var depositType = '<?php echo $thisDeposit->getType(); ?>';
    var depositSlipID = <?php echo $iDepositSlipID; ?>;
    var isDepositClosed = Boolean(<?=  $thisDeposit->getClosed(); ?>);
    var fundData;
    var pledgeData;
    var is_closed = <?= ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) ? 0 : 1 ?>;
    var DepositType = '<?= $thisDeposit->getType() ?>';
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




