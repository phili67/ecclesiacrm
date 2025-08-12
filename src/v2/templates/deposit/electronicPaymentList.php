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
  function ConfirmDeleteAutoPayment (AutID)
  {
    var FamName = document.getElementById("FamName"+AutID).innerHTML;
    var r = confirm("<?= _('Delete automatic payment for') ?> " + FamName );
    if (r == true) {
      DeleteAutoPayment (AutID);
    }
  }

  function ConfirmClearAccounts (AutID)
  {
    var FamName = document.getElementById("FamName"+AutID).innerHTML;
    var r = confirm("<?= _('Clear account numbers for')?> "+FamName);
    if (r == true) {
      ClearAccounts (AutID);
    }
  }

  function ClearAccounts (AutID)
  {
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.uniqueid = AutID;

      xmlhttp.open("GET",window.CRM.root + '/v2/deposit/auto/payment/clear/Account/' + AutID,true);
      xmlhttp.PaymentID = AutID; // So we can see it when the request finishes

      xmlhttp.onreadystatechange=function() {
      if (this.readyState==4 && this.status==200) { // Hide them as the requests come back, deleting would mess up the outside loop
              document.getElementById("Select"+this.PaymentID).checked = false;
            ccVal = document.getElementById("CreditCard"+this.PaymentID).innerHTML;
            document.getElementById("CreditCard"+this.PaymentID).innerHTML = "************" + ccVal.substr (ccVal.length-4,4);
            aVal = document.getElementById("Account"+this.PaymentID).innerHTML;
            document.getElementById("Account"+this.PaymentID).innerHTML = "*****" + aVal.substr (aVal.length-4,4);
          }
      };
      xmlhttp.send();
  }

  function DeleteAutoPayment (AutID)
  {
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.uniqueid = AutID;

      xmlhttp.open("GET","/api/payments/delete/"+AutID,true);
      xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xmlhttp.PaymentID = AutID; // So we can see it when the request finishes

      xmlhttp.onreadystatechange=function() {
        if (this.readyState==2 && this.status==200) { // Hide them as the requests come back, deleting would mess up the outside loop
          document.getElementById("Select"+this.PaymentID).checked = false;
          document.getElementById("PaymentMethodRow"+this.PaymentID).style.display = 'none';
        }
      };
      xmlhttp.send();
  }

  function DeleteChecked()
  {
    var checkboxes = document.getElementsByName("SelectForAction");
    for(var i=0, n=checkboxes.length;i<n;i++) {
        if (checkboxes[i].checked) {
          var id = checkboxes[i].id.split("Select")[1];
          ConfirmDeleteAutoPayment (id);
        }
    }
  }

  function ClearAccountsChecked()
  {
    var checkboxes = document.getElementsByName("SelectForAction");
    for(var i=0, n=checkboxes.length;i<n;i++) {
        if (checkboxes[i].checked) {
          var id = checkboxes[i].id.split("Select")[1];
          ConfirmClearAccounts (id);
        }
    }
  }
</script>

<script nonce="<?= $CSPNonce ?>" >
  function toggle(source, groupName) {
    var checkboxes = document.getElementsByName(groupName);
    for(var i=0, n=checkboxes.length;i<n;i++) {
      checkboxes[i].checked = source.checked;
  }
}
</script>
<div class="card card-body">

<p><a href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/-1/-1/v2-deposit-electronic-payment-list" class="btn btn-primary"><?= _('Add a New Electronic Payment Method') ?></a></p>
<div class="table-responsive">
<table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="PaymentMethodTable"  style="width:100%;">
  <thead>
    <tr>
      <th>
        <input type=checkbox onclick="toggle(this, 'SelectForAction')" />
      </th>
      <th style="min-width:80px"><b><?= _('Action') ?></b></th>
      <th><b><?= _('Family') ?></b></th>
      <th><b><?= _('Type') ?></b></th>
      <th><b><?= _('Fiscal Year') ?></b></th>
      <th><b><?= _('Next Date') ?></b></th>
      <th><b><?= _('Amount') ?></b></th>
      <th><b><?= _('Interval') ?></b></th>
      <th><b><?= _('Fund') ?></b></th>
      <th><b><?= _('Bank') ?></b></th>
      <th><b><?= _('Routing') ?></b></th>
      <th><b><?= _('Account') ?></b></th>     
      <th><b><?= _('Credit Card') ?></b></th>
      <th><b><?= _('Month') ?></b></th>
      <th><b><?= _('Year') ?></b></th>      
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
  <tr id="PaymentMethodRow<?= $payment->getId() ?>" class="<?= $sRowClass ?>">
    <td>
      <input type=checkbox id=Select<?= $payment->getId() ?> name="SelectForAction" />
    </td>
    <td>
      <a class="btn btn-primary btn-sm" href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/<?= $payment->getId() ?>/<?= $payment->getFamilyid() ?>/v2-deposit-electronic-payment-list" data-typeid="2" class="edit-prop"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>      
      &nbsp;<a class="btn btn-danger btn-sm" href="#" onclick="ConfirmDeleteAutoPayment(<?= $payment->getId() ?>)"><i class="far fa-trash-alt" aria-hidden="true"></i></a></td>
    </td>
    <td>
        <a id="FamName<?= $payment->getId() ?>" href="v2/people/family/view/<?= $payment->getFamilyid() ?>"><?= $payment->getFamName().' '.$payment->getFamAddress1().', '.$payment->getFamCity().', '.$payment->getFamState() ?></a>
    <td>
    <?php
      if ($payment->getEnableBankDraft()) {
          echo _('Bank ACH');
      } elseif ($payment->getEnableCreditCard()) {
          echo _('Credit Card');
      } else {
          echo _('Disabled');
      } ?>
    </td>

    <td><?= MiscUtils::MakeFYString($payment->getFyid()) ?></td>
    <td><?= $payment->getNextPayDate()->format(SystemConfig::getValue('sDateFormatLong')) ?></td>
    <td><?= OutputUtils::number_localized($payment->getAmount()) ?></td>
    <td><?= $payment->getInterval() ?></td>
    <td><?= $payment->getFunName() ?></td>
    <td><?= $payment->getBankName() ?></td>
    <td><?= (strlen($payment->getRoute()) == 9)?'*****'.mb_substr($payment->getRoute(), 5, 4):'' ?></td>
    <td id="Account<?= $payment->getId() ?>">
      <?= (strlen($payment->getAccount()) > 4)?'*****'.mb_substr($payment->getAccount(), strlen($payment->getAccount()) - 4, 4):'' ?>
    </td>
    <td id="CreditCard<?= $payment->getId() ?>">
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
<div>

<b><?= _("With checked") ?>:</b><br>
<input type="button" class="btn btn-warning" id="DeleteChecked" value="<?= _("Delete") ?>" onclick="DeleteChecked();" />
<input type="button" class="btn btn-default" id="DeleteChecked" value="<?= _("Clear Account Numbers") ?>" onclick="ClearAccountsChecked();" />
    </div>
</div>


<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script nonce="<?= $CSPNonce ?>">
  $("#PaymentMethodTable").DataTable(window.CRM.plugin.dataTable);
</script>
