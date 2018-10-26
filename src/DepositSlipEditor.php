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

$iDepositSlipID = 0;
$thisDeposit = 0;
$dep_Closed = false;

// Security: User must have finance permission or be the one who created this deposit
if ( !( $_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    Redirect('Menu.php');
    exit;
}

if (array_key_exists('DepositSlipID', $_GET)) {
    $iDepositSlipID = InputUtils::LegacyFilterInput($_GET['DepositSlipID'], 'int');
}

// Get the current deposit slip data
if ($iDepositSlipID) {
    $sSQL = 'SELECT dep_Closed, dep_Date, dep_Type from deposit_dep WHERE dep_ID = '.$iDepositSlipID;
    $rsDeposit = RunQuery($sSQL);
    extract(mysqli_fetch_array($rsDeposit));
}

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
    if (!($_SESSION['user']->isFinanceEnabled() || $_SESSION['user']->getPersonId() == $thisDeposit->getEnteredby()) && SystemConfig::getBooleanValue('bEnabledFinance')) {
        Redirect('Menu.php');
        exit;
    }
} else {
    Redirect('Menu.php');
}


$funds = $thisDeposit->getFundTotals();

//Set the page title
$sPageTitle = gettext($thisDeposit->getType()).' : '.gettext('Deposit Slip Number: ')."#".$iDepositSlipID;

if ($dep_Closed) {
    $sPageTitle .= ' &nbsp; <font color=red>'.gettext('Deposit closed').'</font>';
}

//Is this the second pass?

if (isset($_POST['DepositSlipLoadAuthorized'])) {
    $thisDeposit->loadAuthorized($thisDeposit->getType());    
} elseif (isset($_POST['DepositSlipRunTransactions'])) {
    $thisDeposit->runTransactions();
}

$_SESSION['iCurrentDeposit'] = $iDepositSlipID;  // Probably redundant

/* @var $currentUser \EcclesiaCRM\User */
$currentUser = $_SESSION['user'];
$currentUser->setCurrentDeposit($iDepositSlipID);
$currentUser->save();

require 'Include/Header.php';
?>
<div class="row">
  <div class="col-lg-7">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><?php echo gettext('Deposit Details: '); ?></h3>
      </div>
      <div class="box-body">
        <form method="post" action="#" name="DepositSlipEditor" id="DepositSlipEditor">
          <div class="row">
            <div class="col-lg-4">
              <label for="Date"><?= gettext('Date'); ?>:</label>
              <input type="text" class="form-control date-picker" name="Date" value="<?= $thisDeposit->getDate(SystemConfig::getValue('sDatePickerFormat')); ?>" id="DepositDate" >
            </div>
            <div class="col-lg-4">
              <label for="Comment"><?php echo gettext('Comment:'); ?></label>
              <input type="text" class="form-control" name="Comment" id="Comment" value="<?php echo $thisDeposit->getComment(); ?>"/>
            </div>
            <div class="col-lg-4">
              <label for="Closed"><?php echo gettext('Closed:'); ?></label>
              <input type="checkbox"  name="Closed" id="Closed" value="1" <?php if ($thisDeposit->getClosed()) {
    echo ' checked';
} ?>/><?php echo gettext('Close deposit slip (remember to press Save)'); ?>
            </div>
          </div>
          <div class="row p-2">
            <div class="col-lg-5 m-2" style="text-align:center">
              <input type="submit" class="btn btn-primary" id="DepositSlipSubmit" value="<?php echo gettext('Save'); ?>" name="DepositSlipSubmit">
            </div>
            <div class="col-lg-5 m-2" style="text-align:center">
              <?php
                 if (count($funds)) {
              ?>
              <a href="<?= SystemURLs::getRootPath() ?>/api/deposits/<?php echo $thisDeposit->getId() ?>/pdf" class="btn btn-default" name="DepositSlipGeneratePDF"><?php echo gettext('Deposit Slip Report'); ?></a>
              <?php
                }
              ?>
            </div>
          </div>
          <?php
          if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
              echo '<p>'.gettext('Important note: failed transactions will be deleted permanantly when the deposit slip is closed.').'</p>';
          }
          ?>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><?php echo gettext('Deposit Summary: '); ?></h3>
      </div>
      <div class="box-body">        
         <div class="col-lg-6">
          <canvas id="fund-donut" style="height:250px"></canvas>
          <ul style="margin:0px; border:0px; padding:0px;" id="mainFundTotals">
          <?php
          foreach ($thisDeposit->getFundTotals() as $fund) {
              echo '<li><b>'.gettext($fund['Name']).'</b>: '.SystemConfig::getValue('sCurrency').OutputUtils::money_localized($fund['Total']).'</li>';
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
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?php echo gettext('Payments on this deposit slip:'); ?></h3>
    <div class="pull-right">
      <?php
      if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
          if ($thisDeposit->getType() == 'eGive') {
              echo '<input type=button class="btn btn-default" value="'.gettext('Import eGive')."\" name=ImporteGive onclick=\"javascript:document.location='eGive.php?DepositSlipID=$iDepositSlipID&linkBack=DepositSlipEditor.php?DepositSlipID=$iDepositSlipID&PledgeOrPayment=Payment&CurrentDeposit=$iDepositSlipID';\">";
          } else {
              echo '<input type=button class="btn btn-success" value="'.gettext('Add Payment')."\" name=AddPayment onclick=\"javascript:document.location='PledgeEditor.php?CurrentDeposit=$iDepositSlipID&PledgeOrPayment=Payment&linkBack=DepositSlipEditor.php?DepositSlipID=$iDepositSlipID&PledgeOrPayment=Payment&CurrentDeposit=$iDepositSlipID';\">";
          }
          if ($thisDeposit->getType() == 'BankDraft' || $thisDeposit->getType() == 'CreditCard') {
              ?>
             <input type="submit" class="btn btn-success" value="<?php echo gettext('Load Authorized Transactions'); ?>" name="DepositSlipLoadAuthorized">
             <input type="submit" class="btn btn-warning" value="<?php echo gettext('Run Transactions'); ?>" name="DepositSlipRunTransactions">
          <?php
          }
      }
      ?>
    </div>
  </div>
  <div class="box-body">
    <table class="table" id="paymentsTable" width="100%"></table>
    <div class="container-fluid">
    <div id="depositsTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
     <div class="row">
       <div class="col-lg-4">
        <?php
        if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
            //if ($thisDeposit->getType() == 'Bank') {
                ?>
            <label><?= gettext("Action") ?> : </label>
            <button type="button" id="deleteSelectedRows"  class="btn btn-danger" disabled><?= gettext("Delete Selected Rows") ?></button>
            <?php
            //}
        }
        ?>
      </div>
  
      <div class="col-lg-8">
        <?php
          if ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed()) {
        ?>
          <label><?= gettext("Statut") ?> : </label>
          <button type="button" id="validateSelectedRows" class="btn btn-success exportButton" disabled><?= gettext("Payment") ?> (0) <?= gettext("Selected Rows") ?></button>          
          <button type="button" id="invalidateSelectedRows" class="btn btn-info" disabled><?= gettext("Pledge") ?> (0) <?= gettext("Selected Rows") ?></button>
        <?php
          }
        ?>      
       </div>
     </div>
    </div>
  </div>
</div>
</div>

<div>
  <a href="<?= SystemURLs::getRootPath() ?>/FindDepositSlip.php" class="btn btn-default">
    <i class="fa fa-chevron-left"></i>
    <?= gettext('Return to Deposit Listing') ?></a>
</div>

<script  src="<?= SystemURLs::getRootPath() ?>/skin/js/DepositSlipEditor.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var depositType = '<?php echo $thisDeposit->getType(); ?>';
  var depositSlipID = <?php echo $iDepositSlipID; ?>;
  var isDepositClosed = Boolean(<?=  $thisDeposit->getClosed(); ?>);
  var fundData;
  var pledgeData;
  var is_closed = <?= ($iDepositSlipID and $thisDeposit->getType() and !$thisDeposit->getClosed())?0:1 ?>;

  $(document).ready(function() {
    initPaymentTable('<?= $thisDeposit->getType() ?>');
    initDepositSlipEditor();
    load_charts();
    
    function load_charts()
    {
        window.CRM.APIRequest({
          method: 'POST',
          path: 'payments/getchartsarrays',
          data: JSON.stringify({"depositSlipID" : depositSlipID})
        }).done(function(data) {
          fundData = data.fundData;
          pledgeData = data.pledgeTypeData;
          initCharts(pledgeData, fundData);
          
          var len = fundData.length;
      
          $("#mainFundTotals").empty();
          var globalTotal = 0;
          for (i=0; i<len; ++i) {
            $("#mainFundTotals").append('<li><b>'+fundData[i].label+'</b>: '+window.CRM.currency+Number(fundData[i].value).toLocaleString(window.CRM.lang)+'</li>');
            globalTotal += Number(fundData[i].value);
          }
          
          $("#GlobalTotal").empty();          
          $("#GlobalTotal").append('<li><b>'+i18next.t("TOTAL")+"("+len+"):</b> "+window.CRM.currency+globalTotal.toLocaleString(window.CRM.lang)+'</li>');
          
          if (pledgeData[0].value != null) {
            $("#GlobalTotal").append('<li><b>'+pledgeData[0].label+" ("+pledgeData[0].countCash+"):</b> "+window.CRM.currency+Number(pledgeData[0].value).toLocaleString(window.CRM.lang)+"</b></li>");
          }
          if (pledgeData[1].value != null) {
            $("#GlobalTotal").append('<li><b>'+pledgeData[1].label+" ("+pledgeData[1].countChecks+"):</b> "+window.CRM.currency+Number(pledgeData[1].value).toLocaleString(window.CRM.lang)+"</b></li>");
          }
        });
    }

    $('#deleteSelectedRows').click(function() {
      var deletedRows = dataT.rows('.selected').data();
      bootbox.confirm({
        title:'<?= gettext("Confirm Delete")?>',
        message: "<p><?= gettext("Are you sure ? You're about to delete the selected")?> " + deletedRows.length + " <?= gettext("payments(s)?") ?></p>" +
        "<p><?= gettext("This action CANNOT be undone, and may have legal implications!") ?></p>"+
        "<p><?= gettext("Please ensure this what you want to do.</p>") ?>",
        buttons: {
          cancel : {
            label: '<?= gettext("Close"); ?>'
          },
          confirm: {
            label: '<?php echo gettext("Delete"); ?>'
          }
        },
        callback: function ( result ) {
          if ( result )
          {
            window.CRM.deletesRemaining = deletedRows.length;
            
            $.each(deletedRows, function(index, value) {
              $.ajax({
                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                url: window.CRM.root+'/api/payments/' + value.Groupkey, // the url where we want to POST
                dataType: 'json', // what type of data do we expect back from the server
                data: {"_METHOD":"DELETE"},
                encode: true
              })
              .done(function(data) {
                dataT.rows('.selected').remove().draw(false);
                window.CRM.deletesRemaining --;
                if ( window.CRM.deletesRemaining == 0 )
                {
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
      $("#invalidateSelectedRows").text(i18next.t("Pledge")+" ("+ selectedRows + ") "+i18next.t("Selected Rows"));
      $("#validateSelectedRows").prop('disabled', !(selectedRows));
      $("#validateSelectedRows").text(i18next.t("Payment")+" ("+ selectedRows + ") "+i18next.t("Selected Rows"));
      $(this).toggleClass('selected')
    });
    
    $("#invalidateSelectedRows").click(function(e) {
        var rows = dataT.rows('.selected').data();
        
        var newData = new Array();
        
        for(i=0;i<rows.length;i++){
            newData.push(rows[i]);
        }
        
        window.CRM.APIRequest({
          method: 'POST',
          path: 'payments/invalidate',
          data: JSON.stringify({"data" : newData})
        }).done(function(data) {
          dataT.ajax.reload();
        });
    });
    
    $("#validateSelectedRows").click(function(e) {
        var rows = dataT.rows('.selected').data();
        
        var newData = new Array();
        
        for(i=0;i<rows.length;i++){
            newData.push(rows[i]);
        }
        
        window.CRM.APIRequest({
          method: 'POST',
          path: 'payments/validate',
          data: JSON.stringify({"data" : newData})
        }).done(function(data) {
          dataT.ajax.reload();
        });
    });
    
    //$(".detailButton").click(function(e) {
    $(document).on('click','.detailButton', function(){
        var gk = $(this).data("gk");   
        
        window.CRM.APIRequest({
          method: 'POST',
          path: 'pledges/detail',
          data: JSON.stringify({"groupKey" : gk})
        }).done(function(data) {
          var len = data.Pledges.length;
          var fmt = window.CRM.datePickerformat.toUpperCase();      
          var date = moment(data.Date).format(fmt);
    
          var message = "<table>"; 

          message += "<tr><td><label>"+i18next.t("Depid")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Pledges[0].Depid+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Name")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Pledges[0].FamilyName+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Address1")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Pledges[0].Address1)+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Date")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+date+"</td></tr>";
            
          var type = "Disabled";
          if (data.Pledges[0].EnableCreditCard) {
            type = "Credit Card";
          } else if (data.Pledges[0].EnableBankDraft){
            type = "Bank Draft";
          }
          message += "<tr><td><label>"+i18next.t("Type")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(type)+"</td></tr>";          
          var BankName = "";
          if (data.Pledges[0].BankName) {
            BankName = data.Pledges[0].BankName;
          }
          message += "<tr><td><label>"+i18next.t("Bank Name")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+BankName+"</td></tr>";
          
          message += "<tr><td><label>"+i18next.t("Non deductible")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Pledges[0].Nondeductible+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Statut")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Pledges[0].Pledgeorpayment)+"</td></tr>";
          message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";


          for (i=0;i<len;i++) {
            message += "<tr><td><u><b>"+i18next.t("Deposit")+" "+(i+1)+"</b></u></td><td></td><td></td></tr>";
            message += "<tr><td><label>"+i18next.t("Schedule")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Pledges[i].Schedule)+"</td></tr>";

          
            message += "<tr><td><label>"+i18next.t("Amount")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Pledges[i].Amount+"</td></tr>";
            message += "<tr><td><label>"+i18next.t("Comment")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Pledges[i].Comment)+"</td></tr>";
            message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";
          }

          message += "</table>";
          
          bootbox.alert({ 
            //size: "small",
            title: i18next.t("Electronic Transaction Details"),
            message: message, 
            callback: function(){ /* your callback code */ }
          })
        });
    });
  });
</script>
<?php
  require 'Include/Footer.php';
?>
