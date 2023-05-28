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
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header border-1">
                <h3 class="card-title"><?= _('Deposit Details: ') ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="#" name="DepositSlipEditor" id="DepositSlipEditor">
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="Date"><?= _('Date'); ?>:</label>
                            <input type="text" class=" form-control  form-control-sm date-picker" name="Date"
                                   value="<?= $thisDeposit->getDate(SystemConfig::getValue('sDatePickerFormat')); ?>"
                                   id="DepositDate">
                        </div>
                        <div class="col-lg-4">
                            <label for="Comment"><?= _('Comment:') ?></label>
                            <input type="text" class= "form-control form-control-sm" name="Comment" id="Comment"
                                   value="<?= $thisDeposit->getComment() ?>"/>
                        </div>
                        <div class="col-lg-4">
                            <label for="Closed"><?= _('Closed:') ?></label>
                            <input type="checkbox" name="Closed" id="Closed"
                                   value="1" <?= ($thisDeposit->getClosed()) ? ' checked' : '' ?>/>
                            <?= _('Close deposit slip (remember to press Save)') ?>
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-lg-5 m-2" style="text-align:center">
                            <input type="submit" class="btn btn-primary" id="DepositSlipSubmit" value="<?= _('Save') ?>"
                                   name="DepositSlipSubmit">
                        </div>
                        <div class="col-lg-5 m-2" style="text-align:center">
                            <?php
                            if (count($funds)) {
                                ?>
                                <a href="<?= $sRootPath ?>/api/deposits/<?= $thisDeposit->getId() ?>/pdf"
                                   class="btn btn-default" name="DepositSlipGeneratePDF">
                                    <?= _('Deposit Slip Report') ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <p><?= _('Important note: failed transactions will be deleted permanantly when the deposit slip is closed.') ?></p>
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
        <div class="card">
            <div class="card-header border-1">
                <h3 class="card-title"><?= _('Deposit Summary: ') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <canvas id="fund-donut" style="height:250px"></canvas>
                        <ul style="margin:0px; border:0px; padding:0px;" id="mainFundTotals">
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
                        <canvas id="type-donut" style="height:250px"></canvas>
                        <ul style="margin:0px; border:0px; padding:0px;" id="GlobalTotal">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('Payments on this deposit slip:') ?></h3>
        <div class="pull-right">
            <div class="row">
            <?php
            if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                if ($thisDeposit->getType() == 'eGive') {
                    ?>
                    <div class="col-md-3">
                    <input type=button class="btn btn-default" value="<?= _('Import eGive') ?>" name=ImporteGive
                           onclick="javascript:document.location='<?= $sRootPath ?>/eGive.php?DepositSlipID=<?= $iDepositSlipID ?>&linkBack=<?= $sRootPath ?>/v2/deposit/slipeditor/<?= $iDepositSlipID ?>&PledgeOrPayment=Payment&CurrentDeposit=<?= $iDepositSlipID ?>';">
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="col-md-3">
                    <input type=button class="btn btn-success" value="<?= _('Add Payment') ?> " name=AddPayment
                           onclick="javascript:document.location='<?= $sRootPath ?>/PledgeEditor.php?CurrentDeposit=<?= $iDepositSlipID ?>&PledgeOrPayment=Payment&linkBack=<?= $sRootPath ?>/v2/deposit/slipeditor/<?= $iDepositSlipID ?>&PledgeOrPayment=Payment&CurrentDeposit=<?= $iDepositSlipID ?>';">
                    </div>
                    <?php
                }
                if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
                    ?>
                    <div class="col-md-9">
                    <form method="post" action="<?= $sRootPath ?>/v2/deposit/slipeditor/<?= $iDepositSlipID ?>" name="DepositSlipEditor">
                        <input type="submit" class="btn btn-primary"
                            value="<?php echo _('Load Authorized Transactions'); ?>" name="DepositSlipLoadAuthorized">
                        <input type="submit" class="btn btn-warning" value="<?php echo _('Run Transactions'); ?>"
                            name="DepositSlipRunTransactions">
                    </form>
                    </div>
                    <?php
                }
            }
            ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table" id="paymentsTable" width="100%"></table>
        <div class="container-fluid">
            <div id="depositsTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer"></div>
            <div class="row">
                <div class="col-md-4">
                    <?php
                    if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                        //if ($thisDeposit->getType() == 'Bank') {
                        ?>
                        <label><?= _("Action") ?> : </label>
                        <button type="button" id="deleteSelectedRows" class="btn btn-danger"
                                disabled><?= _("Delete Selected Rows") ?></button>
                        <?php
                        //}
                    }
                    ?>
                </div>

                <div class="col-md-8">
                    <?php
                    if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                        ?>
                        <label><?= _("Statut") ?> : </label>
                        <button type="button" id="validateSelectedRows" class="btn btn-success exportButton"
                                disabled><?= _("Payment") ?> (0) <?= _("Selected Rows") ?></button>
                        <button type="button" id="invalidateSelectedRows" class="btn btn-info"
                                disabled><?= _("Pledge") ?> (0) <?= _("Selected Rows") ?></button>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <a href="<?= $sRootPath ?>/v2/deposit/find" class="btn btn-default">
        <i class="fas fa-chevron-left"></i>
        <?= _('Return to Deposit Listing') ?></a>
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




