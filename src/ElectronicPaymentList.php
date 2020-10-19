<?php
/*******************************************************************************
 *
 *  filename    : ElectronicPaymentLIst.php
 *  last change : 2014-11-29
 *  description : displays a list of all automatic payment records
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2014 Michael Wilt
  *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\DonationFundTableMap;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


// Security: User must be an Admin to access this page.
// Otherwise, re-direct them to the main menu.
// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

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

// Set the page title and include HTML header
$sPageTitle = _('Electronic Payment Listing');
require 'Include/Header.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
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

    xmlhttp.open("GET","<?= RedirectUtils::RedirectURL('AutoPaymentClearAccounts.php') ?>?customerid="+AutID,true);
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

<?php
  if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
?>
function CreatePaymentMethodsForChecked()
{
  var checkboxes = document.getElementsByName("SelectForAction");
  for(var i=0, n=checkboxes.length;i<n;i++) {
      if (checkboxes[i].checked) {
        var id = checkboxes[i].id.split("Select")[1];
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.uniqueid = id;
        xmlhttp.open("GET","<?= RedirectUtils::RedirectURL('ConvertOnePaymentXML.php') ?>?autid="+id,true);
        xmlhttp.onreadystatechange=function() {
        if (this.readyState==4 && this.status==200) {
                var jsonresp=JSON.parse(this.response);
                var index;

                var Success = false;
                var ErrStr = "";
                var AutID = 0;
                var PaymentMethod = 0;
                var PaymentType = "";

                for (index = 0; index < jsonresp.length; ++index) {
                    var oneResp = jsonresp[index];
                    if (oneResp.hasOwnProperty("Error"))
                      ErrStr += oneResp.Error;
                    if (oneResp.hasOwnProperty("AutID"))
                      AutID = oneResp.AutID;
                    if (oneResp.hasOwnProperty("PaymentMethod"))
                      PaymentMethod = oneResp.PaymentMethod[0];
                    if (oneResp.hasOwnProperty("Success"))
                      Success = oneResp.Success;
                    if (oneResp.hasOwnProperty("PaymentType"))
                      PaymentType = oneResp.PaymentType;
                }

                // Update fields on the page to show status of this action
                if (Success && PaymentType=="CC")
                  document.getElementById("CreditCardVanco"+AutID).innerHTML = PaymentMethod;
                if (Success && PaymentType=="C")
                  document.getElementById("AccountVanco"+AutID).innerHTML = PaymentMethod;

                if (!Success && PaymentType=="CC")
                  document.getElementById("CreditCardVanco"+AutID).innerHTML = ErrStr;
                if (!Success && PaymentType=="C")
                  document.getElementById("AccountVanco"+AutID).innerHTML = ErrStr;

                document.getElementById("Select"+AutID).checked = false;
              }
        };
        xmlhttp.send();
      }
  }
}
<?php
} ?>
</script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
  function toggle(source, groupName) {
    var checkboxes = document.getElementsByName(groupName);
    for(var i=0, n=checkboxes.length;i<n;i++) {
      checkboxes[i].checked = source.checked;
  }
}
</script>
<div class="card card-body">

<p align="center"><a href="AutoPaymentEditor.php?linkBack=ElectronicPaymentList.php" class="btn btn-primary"><?= _('Add a New Electronic Payment Method') ?></a></p>
<div class="table-responsive">
<table class="table table-hover dt-responsive" id="PaymentMethodTable"  style="width:100%;">
  <thead>
    <tr>
      <th>
        <input type=checkbox onclick="toggle(this, 'SelectForAction')" />
      </th>
      <th><b><?= _('Edit') ?></b></th>
      <th><b><?= _('Delete') ?></b></th>
      <th align="center"><b><?= _('Family') ?></b></th>
      <th align="center"><b><?= _('Type') ?></b></th>
      <th align="center"><b><?= _('Fiscal Year') ?></b></th>
      <th align="center"><b><?= _('Next Date') ?></b></th>
      <th align="center"><b><?= _('Amount') ?></b></th>
      <th align="center"><b><?= _('Interval') ?></b></th>
      <th align="center"><b><?= _('Fund') ?></b></th>
      <th align="center"><b><?= _('Bank') ?></b></th>
      <th align="center"><b><?= _('Routing') ?></b></th>
      <th align="center"><b><?= _('Account') ?></b></th>
      <?php if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
          ?>
      <th align="center"><b><?= _('Vanco ACH') ?></b></th>
      <?php
      }?>
      <th align="center"><b><?= _('Credit Card') ?></b></th>
      <th align="center"><b><?= _('Month') ?></b></th>
      <th align="center"><b><?= _('Year') ?></b></th>
      <?php if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
          ?>
      <th align="center"><b><?= _('Vanco CC') ?></b></th>
      <?php
      }?>
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
    <td><a href="AutoPaymentEditor.php?AutID=<?= $payment->getId() ?>&amp;FamilyID=<?php echo $payment->getFamilyid() ?>&amp;linkBack=ElectronicPaymentList.php" class="btn btn-success"><?= _('Edit') ?></a></td>
    <td>
      <button onclick="ConfirmDeleteAutoPayment(<?= $payment->getId() ?>)" class="btn btn-danger"><?= _('Delete') ?></button>
    </td>
    <td>
        <a id="FamName<?= $payment->getId() ?>" href="FamilyView.php?FamilyID=<?= $payment->getFamilyid() ?>"><?= $payment->getFamName().' '.$payment->getFamAddress1().', '.$payment->getFamCity().', '.$payment->getFamState() ?></a>
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
  <?php
      if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
  ?>
    <td align="center" id="AccountVanco<?= $payment->getId() ?>"><?= $payment->getAccountVanco() ?></td>
  <?php
    }
  ?>
    <td id="CreditCard<?= $payment->getId() ?>">
      <?= (strlen($payment->getCreditCard()) == 16)?'*************'.mb_substr($payment->getCreditCard(), 12, 4):'' ?>
    </td>
    <td><?= $payment->getExpMonth() ?></td>
    <td><?= $payment->getExpYear() ?></td>
    <?php if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
                ?>
    <td align="center" id="CreditCardVanco<?= $payment->getId() ?>"><?= $payment->getCreditCardVanco() ?></td>
    <?php
      }
    ?>
  </tr>
  <?php
}
?>
  </tbody>
</table>
</div>
<div>

<b><?= _("With checked") ?>:</b><br>
<?php if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
    ?>
<input type="button" class="btn btn-default" id="CreatePaymentMethodsForChecked" value="<?= _("Store Private Data at Vanco") ?>" onclick="CreatePaymentMethodsForChecked();" />
<?php
} ?>
<input type="button" class="btn btn-warning" id="DeleteChecked" value="<?= _("Delete") ?>" onclick="DeleteChecked();" />
<input type="button" class="btn btn-default" id="DeleteChecked" value="<?= _("Clear Account Numbers") ?>" onclick="ClearAccountsChecked();" />
    </div>
</div>
<?php require 'Include/Footer.php' ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $("#PaymentMethodTable").DataTable(window.CRM.plugin.dataTable);
</script>
