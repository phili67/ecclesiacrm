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
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemConfig;

$iDepositSlipID = 0;
$thisDeposit = 0;

if (array_key_exists('DepositSlipID', $_GET)) {
    $iDepositSlipID = InputUtils::LegacyFilterInput($_GET['DepositSlipID'], 'int');
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
    if (!($_SESSION['bFinance'] || $_SESSION['iUserID'] == $thisDeposit->getEnteredby())) {
        Redirect('Menu.php');
        exit;
    }
} else {
    Redirect('Menu.php');
}


$funds = $thisDeposit->getFundTotals();

//Set the page title
$sPageTitle = gettext($thisDeposit->getType()).' : '.gettext('Deposit Slip Number: ').$iDepositSlipID;

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
              <input type="button" class="btn btn-default" value="<?php echo gettext('Deposit Slip Report'); ?>" name="DepositSlipGeneratePDF" onclick="window.CRM.VerifyThenLoadAPIContent(window.CRM.root + '/api/deposits/<?php echo $thisDeposit->getId() ?>/pdf');">
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
          <ul style="margin:0px; border:0px; padding:0px;">
          <?php
          foreach ($thisDeposit->getFundTotals() as $fund) {
              echo '<li><b>'.gettext($fund['Name']).'</b>: '.SystemConfig::getValue('sCurrency').$fund['Total'].'</li>';
          }
          ?>
        </div>
        <div class="col-lg-6">
          <canvas id="type-donut" style="height:250px"></canvas>
          <ul style="margin:0px; border:0px; padding:0px;">
          <?php
          // Get deposit totals
          echo '<li><b>TOTAL ('.$thisDeposit->getPledges()->count().'):</b> '.SystemConfig::getValue('sCurrency').$thisDeposit->getVirtualColumn('totalAmount').'</li>';
                        if ($thisDeposit->getCountCash()) {
                          ?>
                            <li><b><?= gettext("CASH")." (".$thisDeposit->getCountCash().'):</b> '.SystemConfig::getValue('sCurrency').$thisDeposit->getTotalCash() ?></b></li>
                          <?php
                        }
                        if ($thisDeposit->getCountChecks()) {
                          ?>
                            <li><b><?= gettext("CHECKS").' ('.$thisDeposit->getCountChecks().'):</b> '.SystemConfig::getValue('sCurrency').$thisDeposit->getTotalChecks() ?></b> </li>
                          <?php
                        }
          ?>
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
          <label><?= gettext("Statut") ?> : </label>
          <button type="button" id="validateSelectedRows" class="btn btn-success exportButton" disabled><?= gettext("Validate") ?> (0) <?= gettext("Selected Rows") ?></button>          
          <button type="button" id="invalidateSelectedRows" class="btn btn-info" disabled><?= gettext("Invalidate") ?> (0) <?= gettext("Selected Rows") ?></button>
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
<?php
  $fundData = [];
  foreach ($funds as $tmpfund) {
      $fund = new StdClass();
      $fund->color = '#'.random_color();
      $fund->highlight = '#'.random_color();
      $fund->label = $tmpfund['Name'];
      $fund->value = $tmpfund['Total'];
      array_push($fundData, $fund);
  }
  $pledgeTypeData = [];
  $t1 = new stdClass();
  $t1->value = $thisDeposit->getTotalamount() ? $thisDeposit->getTotalCash() : '0';
  $t1->color = '#197A05';
  $t1->highlight = '#4AFF23';
  $t1->label = 'Cash';
  array_push($pledgeTypeData, $t1);
  $t1 = new stdClass();
  $t1->value = $thisDeposit->getTotalamount() ? $thisDeposit->getTotalChecks() : '0';
  $t1->color = '#003399';
  $t1->highlight = '#3366ff';
  $t1->label = 'Checks';
  array_push($pledgeTypeData, $t1);
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var depositType = '<?php echo $thisDeposit->getType(); ?>';
  var depositSlipID = <?php echo $iDepositSlipID; ?>;
  var isDepositClosed = Boolean(<?=  $thisDeposit->getClosed(); ?>);
  var fundData = <?= json_encode($fundData) ?>;
  var pledgeData = <?= json_encode($pledgeTypeData) ?>;

  $(document).ready(function() {
    initPaymentTable('<?= $thisDeposit->getType() ?>');
    initCharts(pledgeData, fundData);
    initDepositSlipEditor();

    $('#deleteSelectedRows').click(function() {
      var deletedRows = dataT.rows('.selected').data();
      bootbox.confirm({
        title:'<?= gettext("Confirm Delete")?>',
        message: "<p><?= gettext("Are you sure you want to delete the selected")?> " + deletedRows.length + " <?= gettext("payments(s)?") ?></p>" +
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
      $("#invalidateSelectedRows").text(i18next.t("Invalidate")+" ("+ selectedRows + ") "+i18next.t("Selected Rows"));
      $("#validateSelectedRows").prop('disabled', !(selectedRows));
      $("#validateSelectedRows").text(i18next.t("Validate")+" ("+ selectedRows + ") "+i18next.t("Selected Rows"));
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
        var id = $(this).data("id");   
        
        window.CRM.APIRequest({
          method: 'POST',
          path: 'pledge/detail',
          data: JSON.stringify({"id" : id})
        }).done(function(data) {
          var fmt = window.CRM.datePickerformat.toUpperCase();      
          var date = moment(data.Date).format(fmt);
    
          var message = "<table>"; 

          message += "<tr><td><label>"+i18next.t("Depid")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Depid+"</td></tr>";
          message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";

          if (data.EnableCreditCard) {
            message += "<tr><td><label>"+i18next.t("Type")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t("Credit Card")+"</td></tr>";
          } else if (data.EnableBankDraft){
            message += "<tr><td><label>"+i18next.t("Type")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t("Bank Draft")+"</td></tr>";
          }
          
          message += "<tr><td><label>"+i18next.t("Name")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.FamilyName+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Address1")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Address1)+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Comment")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Comment)+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Schedule")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Schedule)+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Date")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+date+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Amount")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Amount+"</td></tr>";
          message += "<tr><td><label>"+i18next.t("Non deductible")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+data.Nondeductible+"</td></tr>";
          message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";
          message += "<tr><td><label>"+i18next.t("Statut")+" </label> </td><td>&nbsp;:&nbsp;</td><td>"+i18next.t(data.Statut)+"</td></tr>";

          message += "</table>";
          
          bootbox.alert({ 
            size: "small",
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
