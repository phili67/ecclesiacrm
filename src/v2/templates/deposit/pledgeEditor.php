<?php
/*******************************************************************************
 *
 *  filename    : templates/pledgeEditor.php
 *  last change : 2023-07-03
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-2012Michael Wilt
 *                Copyright 2023 Philippe Logel
 ******************************************************************************/

// Include the function library
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\AutoPaymentQuery;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';
?>

<form method="post"
      action="<?= $sRootPath ?>/v2/deposit/pledge/editor/CurrentDeposit/<?= $iCurrentDeposit ?>/<?= $PledgeOrPayment ?>/<?= $origLinkBack ?><?= !empty($sGroupKey)?("/".$sGroupKey):"" ?>"
      name="PledgeEditor">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-1">
                    <h3 class="card-title"><i class="fa-solid fa-money-bill"></i>  <?= _("Payment Details") ?></h3>
                </div>
                <div class="card-body">
                    <input type="hidden" name="FamilyID" id="FamilyID" value="<?= $iFamily ?>">
                    <input type="hidden" name="PledgeOrPayment" id="PledgeOrPayment" value="<?= $PledgeOrPayment ?>">

                    <div class="col-md-12">
                        <label for="FamilyName"><?= _('Family') . " " . _("or") . " " . _("Person") ?></label>
                        <select class= "form-control form-control-sm" id="FamilyName" name="FamilyName" width="100%">
                            <option selected><?= $sFamilyName ?></option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php if (!$dDate) {
                                $dDate = $dep_Date;
                            } ?>
                            <label for="Date"><?= _('Date') ?></label>
                            <input class= "form-control form-control-sm" data-provide="datepicker"
                                   data-date-format='<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>' type="text"
                                   name="Date" value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>"><span 
                                style="color:red"><?= $sDateError ?></span>
                            <label for="FYID"><?= _('Fiscal Year') ?></label>
                            <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>

                            <?php if ($dep_Type == 'Bank' && SystemConfig::getValue('bUseDonationEnvelopes')) {
                                ?>
                                <label for="Envelope"><?= _('Envelope Number') ?></label>
                                <input class= "form-control form-control-sm" type="number" name="Envelope" size=8 id="Envelope"
                                       value="<?= $iEnvelope ?>">
                                <?php if (!$dep_Closed) {
                                    ?>
                                    <input class= "form-control form-control-sm" type="submit" class="btn btn-default" value="<?= _('Find family->') ?>"
                                           name="MatchEnvelope">
                                    <?php
                                } ?>

                                <?php
                            } ?>

                            <?php if ($PledgeOrPayment == 'Pledge') {
                                ?>

                                <label for="Schedule"><?= _('Payment Schedule') ?></label>
                                <select name="Schedule" class= "form-control form-control-sm">
                                    <option value="0"><?= _('Select Schedule') ?></option>
                                    <option value="Weekly" <?php if ($iSchedule == 'Weekly') {
                                        echo 'selected';
                                    } ?>><?= _('Weekly') ?>
                                    </option>
                                    <option value="Monthly" <?php if ($iSchedule == 'Monthly') {
                                        echo 'selected';
                                    } ?>><?= _('Monthly') ?>
                                    </option>
                                    <option value="Quarterly" <?php if ($iSchedule == 'Quarterly') {
                                        echo 'selected';
                                    } ?>><?= _('Quarterly') ?>
                                    </option>
                                    <option value="Once" <?php if ($iSchedule == 'Once') {
                                        echo 'selected';
                                    } ?>><?= _('Once') ?>
                                    </option>
                                    <option value="Other" <?php if ($iSchedule == 'Other') {
                                        echo 'selected';
                                    } ?>><?= _('Other') ?>
                                    </option>
                                </select>

                                <?php
                            } ?>
                            <label for="statut"><?= _('Statut') ?></label>
                            <select name="PledgeOrPayment" id="PledgeOrPaymentSelect" class= "form-control form-control-sm">
                                <option
                                    value="Pledge" <?= ($PledgeOrPayment == 'Pledge') ? "selected" : "" ?>><?= _('Pledge') ?></option>
                                <option
                                    value="Payment" <?= ($PledgeOrPayment == 'Payment') ? "selected" : "" ?>><?= _('Payment') ?></option>
                            </select>

                        </div>

                        <div class="col-md-6">
                            <label for="Method"><?= _('Payment by') ?></label>
                            <select class= "form-control form-control-sm" name="Method" id="Method">
                                <?php if ($dep_Type == 'Bank' || !$iCurrentDeposit) {
                                    ?>
                                    <option value="CHECK" <?php if ($iMethod == 'CHECK') {
                                        echo 'selected';
                                    } ?>><?= _('Check'); ?>
                                    </option>
                                    <option value="CASH" <?php if ($iMethod == 'CASH') {
                                        echo 'selected';
                                    } ?>><?= _('Cash'); ?>
                                    </option>
                                    <?php
                                } ?>
                                <?php if (($dep_Type == 'CreditCard' || !$iCurrentDeposit) && $dep_Type != 'BankDraft' && $dep_Type != 'Bank') {
                                    ?>
                                    <option value="CREDITCARD" <?php if ($iMethod == 'CREDITCARD') {
                                        echo 'selected';
                                    } ?>><?= _('Credit Card') ?>
                                    </option>
                                    <?php
                                } ?>
                                <?php if (($dep_Type == 'BankDraft' || !$iCurrentDeposit) && $dep_Type != 'CreditCard' && $dep_Type != 'Bank') {
                                    ?>
                                    <option value="BANKDRAFT" <?php if ($iMethod == 'BANKDRAFT') {
                                        echo 'selected';
                                    } ?>><?= _('Bank Draft') ?>
                                    </option>
                                    <?php
                                } ?>
                                <?php if (($PledgeOrPayment == 'Pledge') && $dep_Type != 'CreditCard' && $dep_Type != 'BankDraft' && $dep_Type != 'Bank') {
                                    ?>
                                    <option value="EGIVE" <?= $iMethod == 'EGIVE' ? 'selected' : '' ?>>
                                        <?= _('eGive') ?>
                                    </option>
                                    <?php
                                } ?>
                            </select>

                            <div id="checkNumberGroup">
                                <label for="CheckNo"><?= _('Check') ?><?= _(' #') ?></label>
                                <input class= "form-control form-control-sm" type="number" name="CheckNo" id="CheckNo"
                                       value="<?= $iCheckNo ?>"/><span style="color:red"><?= $sCheckNoError ?></span>
                            </div>

                            <label for="TotalAmount"><?= _('Total') . " " . SystemConfig::getValue('sCurrency') ?></label>
                            <input class= "form-control form-control-sm" type="number" step="any" name="TotalAmount" id="TotalAmount"
                                   disabled/>

                        </div>
                    </div>

                    <div class="row">
                    <?php
                    if ($dep_Type == 'CreditCard' || $dep_Type == 'BankDraft') {
                        ?>
                        <div class="col-md-6">

                            <tr>
                                <td class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>">
                                    <label><?= _('Choose online payment method') ?></label></td>
                                <td class="TextColumnWithBottomBorder">
                                    <select name="AutoPay" class= "form-control form-control-sm">
                                        <?php
                                        echo '<option value=0';
                                        if ($iAutID == 'CreditCard') {
                                            echo ' selected';
                                        }
                                        echo '>' . _('Select online payment record') . "</option>\n";
                                        echo '<option value=0>----------------------</option>';

                                        if ($dep_Type == 'CreditCard') {
                                            $autoPayements = AutoPaymentQuery::Create()->filterByFamilyid($iFamily)->filterByEnableCreditCard(true)->filterByInterval(1)->find();
                                        } else {
                                            $autoPayements = AutoPaymentQuery::Create()->filterByFamilyid($iFamily)->filterByEnableBankDraft(true)->filterByInterval(1)->find();
                                        }

                                        foreach ($autoPayements as $autoPayement) {
                                            if ($autoPayement->getCreditCard()) {
                                                $showStr = _('Credit card') . " : " . mb_substr($autoPayement->getCreditCard(), strlen($autoPayement->getCreditCard()) - 4, 4);
                                            } else if ($autoPayement->getEnableBankDraft()) {
                                                $showStr = _('Bank account') . " : " . $autoPayement->getBankName() . ' ' . $aut_Route . ' ' . $aut_Account;
                                            }

                                            echo '<option value=' . $autoPayement->getId();
                                            if ($iAutID == $autoPayement->getId()) {
                                                echo ' selected';
                                            }
                                            echo '>' . $showStr . "</option>\n";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                        </div>
                        <?php
                    } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php if (SystemConfig::getValue('bUseScannedChecks') && ($dep_Type == 'Bank' || $PledgeOrPayment == 'Pledge')) {
                                ?>
                                <td align="center"
                                    class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Scan check') ?>
                                    <textarea name="ScanInput" rows="2" cols="70"><?= $tScanString ?></textarea></td>
                                <?php
                            } ?>
                        </div>

                        <div class="col-md-6">
                            <?php if (SystemConfig::getValue('bUseScannedChecks') && $dep_Type == 'Bank') {
                                ?>
                                <input type="submit" class="btn btn-default" value="<?= _('find family from check account #') ?>"
                                       name="MatchFamily">
                                <input type="submit" class="btn btn-default"
                                       value="<?= _('Set default check account number for family') ?>"
                                       name="SetDefaultCheck">
                                <?php
                            } ?>
                        </div>
                    </div>                    
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-12">
                            <br>
                            <?php if (!$dep_Closed) {
                                ?>
                                <input type="submit" class="btn btn-primary" value="&check; <?= _('Save') ?>" name="PledgeSubmit">
                                <?php if (SessionUser::getUser()->isAddRecordsEnabled()) {
                                    echo '<input type="submit" class="btn btn-info" value="&check; ' . _('Save and Add') . '" name="PledgeSubmitAndAdd">';
                                } ?>
                                <?php
                            } ?>
                            <?php if (!$dep_Closed) {
                                $cancelText = _('Cancel');
                            } else {
                                $cancelText = _('Return');
                            } ?>
                            <input type="button" class="btn btn-default" value="X <?= _($cancelText) ?>" name="PledgeCancel"
                                onclick="javascript:document.location='<?= $sRootPath ?>/<?= $linkBack ? $linkBack : 'v2/dashboard' ?>';">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header border-1">
                    <h3 class="card-title"><i class="fa-solid fa-bank"></i> <i class="fa-solid fa-info-circle"></i> <?= _("Fund Split") ?></h3>
                </div>
                <div class="card-body">
                    <table id="FundTable" style="border-spacing: 10px;border-collapse: separate;">
                        <thead>
                        <tr>
                            <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Fund Name') ?></th>
                            <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Amount') ?></th>

                            <?php if ($bEnableNonDeductible) {
                                ?>
                                <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Non-deductible amount') ?></th>
                                <?php
                            } ?>

                            <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Comment') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($fundId2Name as $fun_id => $fun_name) {
                            ?>
                            <tr>
                                <td class="TextColumn"><?= _($fun_name) ?></td>
                                <td class="TextColumn">
                                    <input class="form-control form-control-sm FundAmount" type="number" step="any"
                                           name="<?= $fun_id ?>_Amount" id="<?= $fun_id ?>_Amount"
                                           value="<?= $nAmount[$fun_id] ?>">
                                    <br>
                                    <?php if ($sAmountError[$fun_id]) { ?>
                                        <span style="color:red"><?= $sAmountError[$fun_id] ?></span>
                                    <?php } ?>
                                </td>
                                <?php
                                if ($bEnableNonDeductible) {
                                    ?>
                                    <td class="TextColumn">
                                        <input class= "form-control form-control-sm" type="number" step="any"
                                               name="<?= $fun_id ?>_NonDeductible" id="<?= $fun_id ?>_NonDeductible"
                                               value="<?= $nNonDeductible[$fun_id] ?>"/>
                                        <br>
                                        <?php if ($sNonDeductibleError[$fun_id]) { ?>
                                            <span style="color:red"><?= $sNonDeductibleError[$fun_id] ?></span>
                                        <?php } ?>
                                    </td>
                                    <?php
                                } ?>
                                <td class="TextColumn">
                                    <input class= "form-control form-control-sm" type="text" size=40 name="<?= $fun_id ?>_Comment"
                                           id="<?= $fun_id ?>_Comment" value="<?= $sComment[$fun_id] ?>">
                                           <br>
                                </td>
                            </tr>
                            <?php
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</form>

<script nonce="<?= $CSPNonce ?>">
    var dep_Date = "<?= OutputUtils::change_date_for_place_holder($dep_Date) ?>";
    var dep_Type = "<?= $dep_Type ?>";
    var dep_Closed = <?= ($dep_Closed) ? '1' : '0' ?>;
    var CurrentDeposit = <?= $iCurrentDeposit ?>;
    var Closed = "<?= ($dep_Closed && $sGroupKey && $PledgeOrPayment == 'Payment') ? ' &nbsp; <span style="color:red">' . _('Deposit closed') . '</span>' : "" ?>";

    $(document).ready(function () {
        $("#FamilyName").select2({
            minimumInputLength: 2,
            language: window.CRM.shortLocale,
            ajax: {
                url: function (params) {
                    var a = window.CRM.root + '/api/families/search/' + params.term;
                    return a;
                },
                dataType: 'json',
                delay: 250,
                data: "",
                processResults: function (data, params) {
                    var results = [];
                    var families = JSON.parse(data).Families
                    $.each(families, function (key, object) {
                        results.push({
                            id: object.Id,
                            text: object.displayName
                        });
                    });
                    return {
                        results: results
                    };
                }
            }
        });

        $("#FamilyName").on("select2:select", function (e) {
            $('[name=FamilyID]').val(e.params.data.id);

            window.CRM.APIRequest({
                method: "POST",
                path: "payments/families",
                data: JSON.stringify({"famId": e.params.data.id, "type": "<?= $dep_Type ?>"})
            }, function (data) {
                var my_list = $("[name=AutoPay]").empty();
                var len = data.length;

                my_list.append($('<option>', {
                    value: 0,
                    text: i18next.t("Select online payment record")
                }));

                my_list.append($('<option>', {
                    value: 0,
                    text: '----------------------'
                }));

                for (i = 0; i < len; ++i) {
                    my_list.append($('<option>', {
                        value: data[i].authID,
                        text: data[i].showStr
                    }));
                }

                console.log("Add the Menu OK");
            });
        });

        var fundTableConfig = {
            paging: false,
            searching: false,
        };

        $.extend(fundTableConfig, window.CRM.plugin.dataTable);

        $("#FundTable").DataTable(fundTableConfig);


        $(".FundAmount").change(function () {
            CalculateTotal();
        });

        $("#Method").change(function () {
            EvalCheckNumberGroup();
        });

        EvalCheckNumberGroup();
        CalculateTotal();
    });

    $("#PledgeOrPaymentSelect").change(function () {
        if (dep_Closed) {
            window.CRM.DisplayAlert(i18next.t("Warning !!!"), i18next.t("Deposit closed"));
            var sel = $("#PledgeOrPaymentSelect");
            sel.data("prev", sel.val());
            return false;
        }

        EvalCheckNumberGroup();

        if ($("#Method option:selected").val() === "CASH" && $("#PledgeOrPaymentSelect option:selected").val() === 'Payment') {
            $("#Method").val("CHECK");
            $("#checkNumberGroup").show();
        }

        if ($("#PledgeOrPaymentSelect option:selected").val() === 'Payment') {
            $(".content-header").html("<h1>" + i18next.t("Payment Editor") + ": " + i18next.t(dep_Type) + i18next.t(" Deposit Slip #") + CurrentDeposit + " (" + dep_Date + ")" + Closed + "</h1>");
        } else {
            $(".content-header").html("<h1>" + i18next.t("Pledge Editor") + ": " + i18next.t(dep_Type) + i18next.t(" Deposit Slip #") + CurrentDeposit + " (" + dep_Date + ")" + Closed + "</h1>");
        }
    });

    function EvalCheckNumberGroup() {
        if ($("#Method option:selected").val() === "CHECK" && $("#PledgeOrPaymentSelect option:selected").val() === 'Payment') {
            $("#checkNumberGroup").show();
        } else {
            $("#checkNumberGroup").hide();

            if ($("#Method option:selected").val() === "CHECK") {
                $("#Method").val("CASH");
            }
            $("#CheckNo").val('');
        }
    }

    function CalculateTotal() {
        var Total = 0.0;
        $(".FundAmount").each(function (object) {
            var FundAmount = Number($(this).val());
            if (FundAmount > 0) {
                Total += FundAmount;
            }
        });
        $("#TotalAmount").val(Number(Total).toFixed(2));
    }
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




