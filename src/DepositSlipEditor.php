<?php
/*******************************************************************************
 *
 *  filename    : DepositSlipEditor.php
 *  last change : 2014-12-14
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003-2014 Deane Barker, Chris Gebhardt, Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\InputUtils;
use EcclesiaCRM\utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\utils\MiscUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

$iDepositSlipID = 0;
$thisDeposit = 0;
$dep_Closed = false;

// Security: User must have finance permission or be the one who created this deposit
if (!(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance'))) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (array_key_exists('DepositSlipID', $_GET)) {
    $iDepositSlipID = InputUtils::LegacyFilterInput($_GET['DepositSlipID'], 'int');
}

// Get the current deposit slip data
if ($iDepositSlipID) {
    $thisDeposit = DepositQuery::create()->findOneById($iDepositSlipID);
    // Set the session variable for default payment type so the new payment form will come up correctly
    if ($thisDeposit->getType() == 'Bank') {
        $_SESSION['idefaultPaymentMethod'] = 'CHECK';
    } elseif ($thisDeposit->getType() == 'CreditCard') {
        $_SESSION['idefaultPaymentMethod'] = 'CREDITCARD';
    } elseif ($thisDeposit->getType() == 'BankDraft') {
        $_SESSION['idefaultPaymentMethod'] = 'BANKDRAFT';
    } elseif ($thisDeposit->getType() == 'eGive') {
        $_SESSION['idefaultPaymentMethod'] = 'EGIVE';
    }

    // Security: User must have finance permission or be the one who created this deposit
    if (!(SessionUser::getUser()->isFinanceEnabled() || SessionUser::getUser()->getPersonId() == $thisDeposit->getEnteredby()) && SystemConfig::getBooleanValue('bEnabledFinance')) {
        RedirectUtils::Redirect('v2/dashboard');
        exit;
    }
} else {
    RedirectUtils::Redirect('v2/dashboard');
}


$funds = $thisDeposit->getFundTotals();

//Set the page title
$sPageTitle = _($thisDeposit->getType()) . ' : ' . _('Deposit Slip Number: ') . "#" . $iDepositSlipID;

if ($thisDeposit->getClosed()) {
    $sPageTitle .= ' &nbsp; <font color=red>' . _('Deposit closed') . " (" . $thisDeposit->getDate()->format(SystemConfig::getValue('sDateFormatLong')) . ')</font>';
}

//Is this the second pass?

if (isset($_POST['DepositSlipLoadAuthorized'])) {
    $thisDeposit->loadAuthorized($thisDeposit->getType());
} elseif (isset($_POST['DepositSlipRunTransactions'])) {
    $thisDeposit->runTransactions();
}

$_SESSION['iCurrentDeposit'] = $iDepositSlipID;  // Probably redundant

/* @var $currentUser \EcclesiaCRM\User */
$currentUser = SessionUser::getUser();
$currentUser->setCurrentDeposit($iDepositSlipID);
$currentUser->save();

require 'Include/Header.php';
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
                                <a href="<?= SystemURLs::getRootPath() ?>/api/deposits/<?= $thisDeposit->getId() ?>/pdf"
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
            <?php
            if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
                if ($thisDeposit->getType() == 'eGive') {
                    ?>
                    <input type=button class="btn btn-default" value="<?= _('Import eGive') ?>" name=ImporteGive
                           onclick="javascript:document.location='eGive.php?DepositSlipID=$iDepositSlipID&linkBack=DepositSlipEditor.php?DepositSlipID=<?= $iDepositSlipID ?>&PledgeOrPayment=Payment&CurrentDeposit=<?= $iDepositSlipID ?>';">
                    <?php
                } else {
                    ?>
                    <input type=button class="btn btn-success" value="<?= _('Add Payment') ?> " name=AddPayment
                           onclick="javascript:document.location='PledgeEditor.php?CurrentDeposit=$iDepositSlipID&PledgeOrPayment=Payment&linkBack=DepositSlipEditor.php?DepositSlipID=<?= $iDepositSlipID ?>&PledgeOrPayment=Payment&CurrentDeposit=<?= $iDepositSlipID ?>';">
                    <?php
                }
                if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
                    ?>
                    <input type="submit" class="btn btn-success"
                           value="<?php echo _('Load Authorized Transactions'); ?>" name="DepositSlipLoadAuthorized">
                    <input type="submit" class="btn btn-warning" value="<?php echo _('Run Transactions'); ?>"
                           name="DepositSlipRunTransactions">
                    <?php
                }
            }
            ?>
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
    <a href="<?= SystemURLs::getRootPath() ?>/FindDepositSlip.php" class="btn btn-default">
        <i class="fas fa-chevron-left"></i>
        <?= _('Return to Deposit Listing') ?></a>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/finance/DepositSlipEditor.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var depositType = '<?php echo $thisDeposit->getType(); ?>';
    var depositSlipID = <?php echo $iDepositSlipID; ?>;
    var isDepositClosed = Boolean(<?=  $thisDeposit->getClosed(); ?>);
    var fundData;
    var pledgeData;
    var is_closed = <?= ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) ? 0 : 1 ?>;
    var DepositType = '<?= $thisDeposit->getType() ?>';
</script>
<?php
require 'Include/Footer.php';
?>
