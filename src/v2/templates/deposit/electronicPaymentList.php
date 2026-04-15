<?php
/*******************************************************************************
 *
 *  filename    : templates/electronicPaymentList.php
 *  last change : 2023-06-17
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2023 EcclesiaCRM
 *
 ******************************************************************************/

 use EcclesiaCRM\dto\SystemConfig;
 use EcclesiaCRM\AutoPaymentQuery;
 use EcclesiaCRM\Map\FamilyTableMap;
 use EcclesiaCRM\Map\DonationFundTableMap;
 use EcclesiaCRM\Utils\OutputUtils;
 use EcclesiaCRM\Utils\MiscUtils;
 use EcclesiaCRM\Utils\RedirectUtils;
 
 
 // Get all the electronic payment records
 $ormAutopayments = AutoPaymentQuery::Create()
        ->leftJoinFamily()
        ->leftJoinDonationFund()
        ->useFamilyQuery()
          ->orderByName()
          ->addAsColumn('FamName',FamilyTableMap::COL_FAM_NAME)
          ->addAsColumn('FamAddress1',FamilyTableMap::COL_FAM_ADDRESS1)
          ->addAsColumn('FamAddress2',FamilyTableMap::COL_FAM_ADDRESS2)
          ->addAsColumn('FamCity',FamilyTableMap::COL_FAM_CITY)
          ->addAsColumn('FamCity',FamilyTableMap::COL_FAM_CITY)
          ->addAsColumn('FamState',FamilyTableMap::COL_FAM_STATE)
        ->endUse()
        ->useDonationFundQuery()
          ->addAsColumn('FunName',DonationFundTableMap::COL_FUN_NAME)
        ->enduse()
        ->find();

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';
?>

<script nonce="<?= $CSPNonce ?>" >
  function deleteAutoPayment(autId) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", window.CRM.root + "/api/payments/delete/" + autId, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.paymentID = autId;

    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 2 && this.status == 200) {
        document.getElementById("Select" + this.paymentID).checked = false;
        document.getElementById("PaymentMethodRow" + this.paymentID).style.display = 'none';
      }
    };
    xmlhttp.send();
  }

  function clearAccounts(autId) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", window.CRM.root + '/v2/deposit/auto/payment/clear/Account/' + autId, true);
    xmlhttp.paymentID = autId;

    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("Select" + this.paymentID).checked = false;
        var ccVal = document.getElementById("CreditCard" + this.paymentID).innerHTML;
        document.getElementById("CreditCard" + this.paymentID).innerHTML = "************" + ccVal.substr(ccVal.length - 4, 4);
        var aVal = document.getElementById("Account" + this.paymentID).innerHTML;
        document.getElementById("Account" + this.paymentID).innerHTML = "*****" + aVal.substr(aVal.length - 4, 4);
      }
    };
    xmlhttp.send();
  }

  function confirmDeleteAutoPayment(autId) {
    var famName = document.getElementById("FamName" + autId).innerHTML;
    bootbox.confirm({
      title: "<?= _('Confirm Delete') ?>",
      message: "<?= _('Delete automatic payment for') ?> " + famName + " ?",
      buttons: {
        cancel: { label: "<?= _('Cancel') ?>" },
        confirm: { label: "<?= _('Delete') ?>" }
      },
      callback: function(result) {
        if (result) {
          deleteAutoPayment(autId);
        }
      }
    });
  }

  function confirmClearAccounts(autId) {
    var famName = document.getElementById("FamName" + autId).innerHTML;
    bootbox.confirm({
      title: "<?= _('Clear account numbers') ?>",
      message: "<?= _('Clear account numbers for') ?> " + famName + " ?",
      buttons: {
        cancel: { label: "<?= _('Cancel') ?>" },
        confirm: { label: "<?= _('Confirm') ?>" }
      },
      callback: function(result) {
        if (result) {
          clearAccounts(autId);
        }
      }
    });
  }

  function deleteChecked() {
    var checkboxes = document.getElementsByName("SelectForAction");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
      if (checkboxes[i].checked) {
        var id = checkboxes[i].id.split("Select")[1];
        confirmDeleteAutoPayment(id);
      }
    }
  }

  function clearAccountsChecked() {
    var checkboxes = document.getElementsByName("SelectForAction");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
      if (checkboxes[i].checked) {
        var id = checkboxes[i].id.split("Select")[1];
        confirmClearAccounts(id);
      }
    }
  }

  function toggleAll(source, groupName) {
    var checkboxes = document.getElementsByName(groupName);
    for (var i = 0, n = checkboxes.length; i < n; i++) {
      checkboxes[i].checked = source.checked;
    }
  }
</script>
<div class="card card-primary card-outline">
<div class="card-header py-2 d-flex align-items-center justify-content-between">
  <h3 class="card-title mb-0"><i class="fas fa-bolt mr-2"></i><?= _('Electronic Payment Methods') ?></h3>
  <a href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/-1/-1/v2-deposit-electronic-payment-list" class="btn btn-sm btn-primary">
    <i class="fas fa-plus mr-1"></i><?= _('Add a New Electronic Payment Method') ?>
  </a>
</div>
<div class="card-body p-2">
<div class="table-responsive">
<table class="table table-sm table-striped table-hover table-bordered dataTable mb-0" id="PaymentMethodTable" style="width:100%;">
  <thead class="thead-light">
    <tr>
      <th>
        <input type="checkbox" onclick="toggleAll(this, 'SelectForAction')" />
      </th>
      <th style="min-width:90px"><?= _('Action') ?></th>
      <th><?= _('Family') ?></th>
      <th><?= _('Type') ?></th>
      <th><?= _('Fiscal Year') ?></th>
      <th><?= _('Next Date') ?></th>
      <th><?= _('Amount') ?></th>
      <th><?= _('Interval') ?></th>
      <th><?= _('Fund') ?></th>
      <th><?= _('Bank') ?></th>
      <th><?= _('Routing') ?></th>
      <th><?= _('Account') ?></th>
      <th><?= _('Credit Card') ?></th>
      <th><?= _('Month') ?></th>
      <th><?= _('Year') ?></th>
    </tr>
  </thead>
  <tbody>
<?php

//Set the initial row color
$sRowClass = 'RowColorA';

//Loop through the autopayment records
foreach ($ormAutopayments as $payment) {
  //Alternate the row color
  $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

    //Display the row?>
  <tr id="PaymentMethodRow<?= $payment->getId() ?>" class="<?= $sRowClass ?> align-middle">
    <td>
      <input type="checkbox" id="Select<?= $payment->getId() ?>" name="SelectForAction" />
    </td>
    <td>
      <div class="btn-group btn-group-sm" role="group" aria-label="<?= _('Actions') ?>">
        <a class="btn btn-outline-primary" href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/<?= $payment->getId() ?>/<?= $payment->getFamilyid() ?>/v2-deposit-electronic-payment-list" data-typeid="2"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>
        <a class="btn btn-outline-danger" href="#" onclick="confirmDeleteAutoPayment(<?= $payment->getId() ?>); return false;"><i class="far fa-trash-alt" aria-hidden="true"></i></a>
      </div>
    </td>
    <td>
        <a id="FamName<?= $payment->getId() ?>" class="font-weight-600" href="v2/people/family/view/<?= $payment->getFamilyid() ?>"><?= $payment->getFamName().' '.$payment->getFamAddress1().', '.$payment->getFamCity().', '.$payment->getFamState() ?></a>
    </td>
    <td>
    <?php
      if ($payment->getEnableBankDraft()) {
          echo '<span class="badge badge-info">'. _('Bank ACH') .'</span>';
      } elseif ($payment->getEnableCreditCard()) {
          echo '<span class="badge badge-primary">'. _('Credit Card') .'</span>';
      } else {
          echo '<span class="badge badge-secondary">'. _('Disabled') .'</span>';
      } ?>
    </td>

    <td><?= MiscUtils::MakeFYString($payment->getFyid()) ?></td>
    <td><?= $payment->getNextPayDate()->format(SystemConfig::getValue('sDateFormatLong')) ?></td>
    <td><strong><?= OutputUtils::number_localized($payment->getAmount()) ?></strong></td>
    <td><span class="badge badge-light"><?= $payment->getInterval() ?></span></td>
    <td><?= $payment->getFunName() ?></td>
    <td><?= $payment->getBankName() ?></td>
    <td class="text-monospace"><?= (strlen($payment->getRoute()) == 9)?'*****'.mb_substr($payment->getRoute(), 5, 4):'' ?></td>
    <td id="Account<?= $payment->getId() ?>" class="text-monospace">
      <?= (strlen($payment->getAccount()) > 4)?'*****'.mb_substr($payment->getAccount(), strlen($payment->getAccount()) - 4, 4):'' ?>
    </td>
    <td id="CreditCard<?= $payment->getId() ?>" class="text-monospace">
      <?= (strlen($payment->getCreditCard()) == 16)?'*************'.mb_substr($payment->getCreditCard(), 12, 4):'' ?>
    </td>
    <td><?= $payment->getExpMonth() ?></td>
    <td><?= $payment->getExpYear() ?></td>
  </tr>
  <?php
}
?>
  </tbody>
</table>
</div>
<div class="d-flex align-items-center mt-2">
<span class="small text-muted mr-2"><?= _("With checked") ?>:</span>
<button type="button" class="btn btn-sm btn-warning mr-2" id="DeleteCheckedBtn" onclick="deleteChecked();">
  <i class="fas fa-trash-alt mr-1"></i><?= _("Delete") ?>
</button>
<button type="button" class="btn btn-sm btn-outline-secondary" id="ClearAccountsCheckedBtn" onclick="clearAccountsChecked();">
  <i class="fas fa-eraser mr-1"></i><?= _("Clear Account Numbers") ?>
</button>
    </div>
</div>
</div>


<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script nonce="<?= $CSPNonce ?>">
  var tableConfig = {
    pageLength: 10,
    lengthChange: false,
    autoWidth: false,
    order: [[5, 'desc']],
    dom: 'frtip',
    language: {
      url: window.CRM.plugin.dataTable.language.url
    },
    createdRow: function (row) {
      $(row).addClass('align-middle');
    }
  };

  $.extend(tableConfig, window.CRM.plugin.dataTable);
  $("#PaymentMethodTable").DataTable(tableConfig);
</script>
