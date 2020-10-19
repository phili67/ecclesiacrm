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
            <div class="card-header with-border">
                <h3 class="card-title"><?= _('Deposit Details: ') ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="#" name="DepositSlipEditor" id="DepositSlipEditor">
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="Date"><?= _('Date'); ?>:</label>
                            <input type="text" class="form-control date-picker" name="Date"
                                   value="<?= $thisDeposit->getDate(SystemConfig::getValue('sDatePickerFormat')); ?>"
                                   id="DepositDate">
                        </div>
                        <div class="col-lg-4">
                            <label for="Comment"><?= _('Comment:') ?></label>
                            <input type="text" class="form-control" name="Comment" id="Comment"
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
            <div class="card-header with-border">
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
    <div class="card-header with-border">
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
        <i class="fa fa-chevron-left"></i>
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

    $(document).ready(function () {
        initPaymentTable('<?= $thisDeposit->getType() ?>');
        initDepositSlipEditor();
        load_charts();

        function load_charts() {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'payments/getchartsarrays',
                data: JSON.stringify({"depositSlipID": depositSlipID})
            }).done(function (data) {
                fundData = data.fundData;
                pledgeData = data.pledgeData;
                pledgeDataType = data.pledgeTypeData;

                initCharts(fundData, pledgeData);

                var len = fundData.datasets[0].data.length;

                $("#mainFundTotals").empty();
                var globalTotal = 0;
                for (i = 0; i < len; ++i) {
                    $("#mainFundTotals").append('<li><b>' + fundData.labels[i] + '</b>: ' + window.CRM.currency + Number(fundData.datasets[0].data[i]).toLocaleString(window.CRM.lang) + '</li>');
                    globalTotal += Number(fundData.datasets[0].data[i]);
                }

                $("#GlobalTotal").empty();
                $("#GlobalTotal").append('<li><b>' + i18next.t("TOTAL") + "(" + len + "):</b> " + window.CRM.currency + globalTotal.toLocaleString(window.CRM.lang) + '</li>');

                if (pledgeDataType[0].value != null) {
                    $("#GlobalTotal").append('<li><b>' + pledgeDataType[0].label + " (" + pledgeDataType[0].countCash + "):</b> " + window.CRM.currency + Number(pledgeDataType[0].value).toLocaleString(window.CRM.lang) + "</b></li>");
                }
                if (pledgeDataType[1].value != null) {
                    $("#GlobalTotal").append('<li><b>' + pledgeDataType[1].label + " (" + pledgeDataType[1].countChecks + "):</b> " + window.CRM.currency + Number(pledgeDataType[1].value).toLocaleString(window.CRM.lang) + "</b></li>");
                }
            });
        }

        $('#deleteSelectedRows').click(function () {
            var deletedRows = dataT.rows('.selected').data();
            bootbox.confirm({
                title: '<?= _("Confirm Delete")?>',
                message: "<p><?= _("Are you sure ? You're about to delete the selected")?> " + deletedRows.length + " <?= _("payments(s)?") ?></p>" +
                    "<p><?= _("This action CANNOT be undone, and may have legal implications!") ?></p>" +
                    "<p><?= _("Please ensure this what you want to do.</p>") ?>",
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
                        window.CRM.deletesRemaining = deletedRows.length;

                        $.each(deletedRows, function (index, value) {
                            $.ajax({
                                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                                url: window.CRM.root + '/api/payments/' + value.Groupkey, // the url where we want to POST
                                dataType: 'json', // what type of data do we expect back from the server
                                data: {"_METHOD": "DELETE"},
                                encode: true
                            })
                                .done(function (data) {
                                    dataT.rows('.selected').remove().draw(false);
                                    window.CRM.deletesRemaining--;
                                    if (window.CRM.deletesRemaining == 0) {
                                        dataT.ajax.reload();
                                        load_charts();
                                    }
                                });
                        });
                    }
                }
            })
        });

        $("#paymentsTable tbody").on('click', 'tr', function () {
            $(this).toggleClass('selected');
            var selectedRows = dataT.rows('.selected').data().length;
            $("#invalidateSelectedRows").prop('disabled', !(selectedRows));
            $("#invalidateSelectedRows").text(i18next.t("Pledge") + " (" + selectedRows + ") " + i18next.t("Selected Rows"));
            $("#validateSelectedRows").prop('disabled', !(selectedRows));
            $("#validateSelectedRows").text(i18next.t("Payment") + " (" + selectedRows + ") " + i18next.t("Selected Rows"));
            $(this).toggleClass('selected')
        });

        $("#invalidateSelectedRows").click(function (e) {
            var rows = dataT.rows('.selected').data();

            var newData = new Array();

            for (i = 0; i < rows.length; i++) {
                newData.push(rows[i]);
            }

            window.CRM.APIRequest({
                method: 'POST',
                path: 'payments/invalidate',
                data: JSON.stringify({"data": newData})
            }).done(function (data) {
                dataT.ajax.reload();
            });
        });

        $("#validateSelectedRows").click(function (e) {
            var rows = dataT.rows('.selected').data();

            var newData = new Array();

            for (i = 0; i < rows.length; i++) {
                newData.push(rows[i]);
            }

            window.CRM.APIRequest({
                method: 'POST',
                path: 'payments/validate',
                data: JSON.stringify({"data": newData})
            }).done(function (data) {
                dataT.ajax.reload();
            });
        });

        //$(".detailButton").click(function(e) {
        $(document).on('click', '.detailButton', function () {
            var gk = $(this).data("gk");

            window.CRM.APIRequest({
                method: 'POST',
                path: 'pledges/detail',
                data: JSON.stringify({"groupKey": gk})
            }).done(function (data) {
                var len = data.Pledges.length;
                var fmt = window.CRM.datePickerformat.toUpperCase();
                var date = moment(data.Date).format(fmt);

                var message = "<table>";

                message += "<tr><td><label>" + i18next.t("Depid") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[0].Depid + "</td></tr>";
                message += "<tr><td><label>" + i18next.t("Name") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[0].FamilyName + "</td></tr>";
                message += "<tr><td><label>" + i18next.t("Address1") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[0].Address1) + "</td></tr>";
                message += "<tr><td><label>" + i18next.t("Date") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + date + "</td></tr>";

                var type = "Disabled";
                if (data.Pledges[0].EnableCreditCard) {
                    type = "Credit Card";
                } else if (data.Pledges[0].EnableBankDraft) {
                    type = "Bank Draft";
                }
                message += "<tr><td><label>" + i18next.t("Type") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(type) + "</td></tr>";
                var BankName = "";
                if (data.Pledges[0].BankName) {
                    BankName = data.Pledges[0].BankName;
                }
                message += "<tr><td><label>" + i18next.t("Bank Name") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + BankName + "</td></tr>";

                message += "<tr><td><label>" + i18next.t("Non deductible") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[0].Nondeductible + "</td></tr>";
                message += "<tr><td><label>" + i18next.t("Statut") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[0].Pledgeorpayment) + "</td></tr>";
                message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";


                for (i = 0; i < len; i++) {
                    message += "<tr><td><u><b>" + i18next.t("Deposit") + " " + (i + 1) + "</b></u></td><td></td><td></td></tr>";
                    message += "<tr><td><label>" + i18next.t("Schedule") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[i].Schedule) + "</td></tr>";


                    message += "<tr><td><label>" + i18next.t("Amount") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[i].Amount + "</td></tr>";
                    message += "<tr><td><label>" + i18next.t("Comment") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[i].Comment) + "</td></tr>";
                    message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";
                }

                message += "</table>";

                bootbox.alert({
                    //size: "small",
                    title: i18next.t("Electronic Transaction Details"),
                    message: message,
                    callback: function () { /* your callback code */
                    }
                })
            });
        });
    });
</script>
<?php
require 'Include/Footer.php';
?>
