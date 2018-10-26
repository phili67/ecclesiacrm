<?php
/*******************************************************************************
 *
 *  filename    : FinancialReports.php
 *  last change : 2005-03-26
 *  description : form to invoke financial reports
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;


// Security
if ( !( $_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    Redirect('Menu.php');
    exit;
}

$sReportType = '';

if (array_key_exists('ReportType', $_POST)) {
    $sReportType = InputUtils::LegacyFilterInput($_POST['ReportType']);
}

if ($sReportType == '' && array_key_exists('ReportType', $_GET)) {
    $sReportType = InputUtils::LegacyFilterInput($_GET['ReportType']);
}

// Set the page title and include HTML header
$sPageTitle = gettext("Financial Reports");
if ($sReportType) {
    $sPageTitle .= ': '.gettext($sReportType);
}
require 'Include/Header.php';
?>
<div class="box box-body">

<?php

// No Records Message if previous report returned no records.
if (array_key_exists('ReturnMessage', $_GET) && $_GET['ReturnMessage'] == 'NoRows') {
?>
    <h3><font color=red><?= gettext("No records were returned from the previous report.")?></font></h3>
<?php
}

if ($sReportType == '') {
    // First Pass - Choose report type
?>
<form method=post action='<?= SystemURLs::getRootPath()?>/FinancialReports.php'>
  <table cellpadding=3 align=left>
    <tr>
      <td class=LabelColumn><?= gettext("Report Type:") ?>&nbsp;&nbsp;</td>
      <td class=TextColumn>
        <select name=ReportType class="form-control input-sm">
          <option value=0><?= gettext("Select Report Type") ?></option>
          <option value='Pledge Summary'><?= gettext("Pledge Summary") ?></option>
          <option value='Pledge Family Summary'><?= gettext("Pledge Family Summary") ?></option>
          <option value='Pledge Reminders'><?= gettext("Pledge Reminders") ?></option>
          <option value='Voting Members'><?= gettext("Voting Members") ?></option>
          <option value='Giving Report'><?= gettext("Giving Report (Tax Statements)") ?></option>
          <option value='Zero Givers'><?= gettext("Zero Givers") ?></option>
          <option value='Individual Deposit Report'><?= gettext("Individual Deposit Report") ?></option>
          <option value='Advanced Deposit Report'><?= gettext("Advanced Deposit Report") ?></option>
        </select>
      </td>
    </tr>
    
<?php
    // First Pass Cancel, Next Buttons
?>
    <tr>
      <td>&nbsp;</td>
      <td><br><input type=button class='btn bt-default' name=Cancel value='<?= gettext("Cancel")?>'
        onclick="javascript:document.location='<?= SystemURLs::getRootPath() ?>/ReportList.php';">
        <input type=submit class='btn btn-primary' name=Submit1 value='<?= gettext("Next") ?>'>
      </td>
    </tr>
  </table>
</form>
<?php
} else {
    $iFYID = $_SESSION['idefaultFY'];
    $iCalYear = date('Y');
    // 2nd Pass - Display filters and other settings
    // Set report destination, based on report type
    switch ($sReportType) {
        case 'Giving Report':
            $action = SystemURLs::getRootPath().'/Reports/TaxReport.php';
        break;
        case 'Zero Givers':
            $action = SystemURLs::getRootPath().'/Reports/ZeroGivers.php';
        break;
        case 'Pledge Summary':
            $action = SystemURLs::getRootPath().'/Reports/PledgeSummary.php';
        break;
        case 'Pledge Family Summary':
            $action = SystemURLs::getRootPath().'/Reports/FamilyPledgeSummary.php';
        break;
        case 'Pledge Reminders':
            $action = SystemURLs::getRootPath().'/Reports/ReminderReport.php';
        break;
        case 'Voting Members':
            $action = SystemURLs::getRootPath().'/Reports/VotingMembers.php';
        break;
        case 'Individual Deposit Report':
            $action = SystemURLs::getRootPath().'/Reports/PrintDeposit.php';
        break;
        case 'Advanced Deposit Report':
            $action = SystemURLs::getRootPath().'/Reports/AdvancedDeposit.php';
        break;
    }
?>
<form method=post action="<?= $action ?>">
  <input type=hidden name=ReportType value='<?= $sReportType?>'>
  <table cellpadding=3 align=left>
    <tr>
      <td>
        <h3><?= gettext("Filters")?></h3>
      </td>
    </tr>
<?php
    // Filter by Classification and Families
    if ($sReportType == 'Giving Report' || $sReportType == 'Pledge Reminders' || $sReportType == 'Pledge Family Summary' || $sReportType == 'Advanced Deposit Report') {

        //Get Classifications for the drop-down
        $sSQL = 'SELECT * FROM list_lst WHERE lst_ID = 1 ORDER BY lst_OptionSequence';
        $rsClassifications = RunQuery($sSQL); ?>
    <tr>
      <td class="LabelColumn"><?= gettext("Classification") ?>:<br></td>
      <td class=TextColumnWithBottomBorder><div class=SmallText>
          </div><select name="classList[]" style="width:100%" multiple id="classList">
          <?php
          while ($aRow = mysqli_fetch_array($rsClassifications)) {
              extract($aRow);
              echo '<option value="'.$lst_OptionID.'"';
              echo '>'.$lst_OptionName.'&nbsp;';
          } ?>
          </select>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <br/>
        <button type="button" id="addAllClasses" class="btn btn-success"><?= gettext("Add All Classes") ?></button>
        <button type="button" id="clearAllClasses" class="btn btn-danger"><?= gettext("Clear All Classes") ?></button><br/><br/>
      </td>
    </tr>
        <?php
          $sSQL = 'SELECT fam_ID, fam_Name, fam_Address1, fam_City, fam_State FROM family_fam ORDER BY fam_Name';
          $rsFamilies = RunQuery($sSQL); 
        ?>
    <tr>
      <td class=LabelColumn><?= gettext("Filter by Family") ?>:<br></td>
      <td class=TextColumnWithBottomBorder>
        <select name="family[]" id="family" multiple style="width:100%">
        <?php
        // Build Criteria for Head of Household
        if (!$sDirRoleHead) {
            $sDirRoleHead = '1';
        }
        $head_criteria = ' per_fmr_ID = '.$sDirRoleHead;
        // If more than one role assigned to Head of Household, add OR
        $head_criteria = str_replace(',', ' OR per_fmr_ID = ', $head_criteria);
        // Add Spouse to criteria
        if (intval($sDirRoleSpouse) > 0) {
            $head_criteria .= " OR per_fmr_ID = $sDirRoleSpouse";
        }
        // Build array of Head of Households and Spouses with fam_ID as the key
        $sSQL = 'SELECT per_FirstName, per_fam_ID FROM person_per WHERE per_fam_ID > 0 AND ('.$head_criteria.') ORDER BY per_fam_ID';
        $rs_head = RunQuery($sSQL);
        $aHead = [];
        while (list($head_firstname, $head_famid) = mysqli_fetch_row($rs_head)) {
            if ($head_firstname && array_key_exists($head_famid, $aHead)) {
                $aHead[$head_famid] .= ' & '.$head_firstname;
            } elseif ($head_firstname) {
                $aHead[$head_famid] = $head_firstname;
            }
        }
        while ($aRow = mysqli_fetch_array($rsFamilies)) {
            extract($aRow);
            ?>
            <option value=<?= $fam_ID ?>><?= $fam_Name ?>
            <?php
            if (array_key_exists($fam_ID, $aHead)) {
            ?>
                , <?= $aHead[$fam_ID]?>
            <?php
            }
            ?>
            <?= FormatAddressLine($fam_Address1, $fam_City, $fam_State) ?>
        <?php
        }
        ?>

        </select>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <br/>
        <button type="button" id="addAllFamilies" class="btn btn-success"><?= gettext("Add All Families") ?></button>
        <button type="button" id="clearAllFamilies" class="btn btn-danger"><?= gettext("Clear All Families") ?></button><br/><br/>
      </td>
    </tr>
  <?php
    }

    // Starting and Ending Dates for Report
    if ($sReportType == 'Giving Report' || $sReportType == 'Advanced Deposit Report' || $sReportType == 'Zero Givers') {
        $today = date(SystemConfig::getValue('sDateFormatLong'));
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Report Start Date:")?></td>
      <td class=TextColumn>
        <input type=text name=DateStart class='date-picker form-control' maxlength=10 id=DateStart size=11 value='<?= $today ?>'>
      </td>
    </tr>
    <tr>
      <td class=LabelColumn><?= gettext("Report End Date:") ?></td>
      <td class=TextColumn>
        <input type=text name=DateEnd class='date-picker form-control' maxlength=10 id=DateEnd size=11 value='<?= $today?>'>
      </td>
    </tr>
        
  <?php
  if ($sReportType == 'Giving Report' || $sReportType == 'Advanced Deposit Report') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Apply Report Dates To:") ?></td>
      <td class=TextColumnWithBottomBorder>
        <input name=datetype type=radio checked value='Deposit'>
         <?= gettext("Deposit Date (Default)") ?>
       &nbsp; 
        <input name=datetype type=radio value='Payment'>
          <?= gettext("Payment Date")?>
    </tr>
  <?php
        }
    }

    // Fiscal Year
    if ($sReportType == 'Pledge Summary' || $sReportType == 'Pledge Reminders' || $sReportType == 'Pledge Family Summary' || $sReportType == 'Voting Members') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Fiscal Year:") ?></td>
      <td class=TextColumn>
        <?= PrintFYIDSelect($iFYID, 'FYID')?>
      </td>
    </tr>
    <?php
    }

  // Filter by Deposit
  if ($sReportType == 'Giving Report' || $sReportType == 'Individual Deposit Report' || $sReportType == 'Advanced Deposit Report') {
      $sSQL = 'SELECT dep_ID, dep_Date, dep_Type FROM deposit_dep ORDER BY dep_ID DESC LIMIT 0,200';
      $rsDeposits = RunQuery($sSQL);
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Filter by Deposit:")?><br></td>
      <td class=TextColumnWithBottomBorder>
        <div class=SmallText>
          <?php
          if ($sReportType != 'Individual Deposit Report') {
          ?>
              <?= gettext("If deposit is selected, date criteria will be ignored.") ?>
          <?php
          }
          ?>
        </div>
        <select name="deposit" class="form-control">
        <?php
        if ($sReportType != 'Individual Deposit Report') {
        ?>
            <option value=0 selected><?= gettext("All deposits within date range")?></option>
        <?php
        }
        while ($aRow = mysqli_fetch_array($rsDeposits)) {
            extract($aRow);
        ?>
            <option value=<?= $dep_ID ?>">#<?= $dep_ID ?> &nbsp; (<?= date(SystemConfig::getValue('sDateFormatLong'), strtotime($dep_Date)) ?>) &nbsp;-&nbsp;<?= gettext($dep_Type) ?>
        <?php
        }
        ?>
        </select>
      </td>
    </tr>
  <?php
    }

    // Filter by Account
    if ($sReportType == 'Pledge Summary' || $sReportType == 'Pledge Family Summary' || $sReportType == 'Giving Report' || $sReportType == 'Advanced Deposit Report' || $sReportType == 'Pledge Reminders') {
        $sSQL = 'SELECT fun_ID, fun_Name, fun_Active FROM donationfund_fun ORDER BY fun_Active, fun_Name';
        $rsFunds = RunQuery($sSQL); 
   ?>
    <tr>
       <td class="LabelColumn"><?= gettext("Filter by Fund") ?>:<br></td>
       <td>
         <select name="funds[]" multiple id="fundsList" style="width:100%">
    <?php
      while ($aRow = mysqli_fetch_array($rsFunds)) {
        extract($aRow);
    ?>
           <option value=<?= $fun_ID?>><?= $fun_Name.(($fun_Active == 'false')?' &nbsp; '.gettext("INACTIVE"):"")?>
    <?php
      } 
    ?>
         </select>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <br/>
        <button type="button" id="addAllFunds" class="btn btn-success"><?= gettext("Add All Funds") ?></button>
        <button type="button" id="clearAllFunds" class="btn btn-danger"><?= gettext("Clear All Funds") ?></button>
        <br/><br/>
      </td>
    </tr>
  <?php
    }
    // Filter by Payment Method
    if ($sReportType == 'Advanced Deposit Report') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Filter by Payment Type:")?><br></td>
      <td class=TextColumnWithBottomBorder>
        <div class=SmallText>
          <?= gettext("Use Ctrl Key to select multiple")?>
        </div>
        <select name=method[] size=5 multiple>
          <option value=0 selected><?= gettext("All Methods")?>
          <option value='CHECK'><?=gettext("Check")?>
          <option value='CASH'><?= gettext("Cash")?>
          <option value='CREDITCARD'><?= gettext("Credit Card") ?>
          <option value='BANKDRAFT'><?= gettext("Bank Draft")?>
          <option value='EGIVE'><?= gettext("eGive")?>
        </select>
      </td>
    </tr>
  <?php
    }

    if ($sReportType == 'Giving Report') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Minimun Total Amount:") ?></td>
      <td class=TextColumnWithBottomBorder>
         <div class=SmallText>
           (<?= gettext("0 - No Minimum") ?>)
         </div>
         <input name=minimum type=text value='0' size=8 class="form-control">
      </td>
    </tr>
  <?php
    }
    // Other Settings
  ?>
    <tr>
      <td>
        <h3><?= gettext("Other Settings") ?></h3>
      </td>
    </tr>
  <?php
    if ($sReportType == 'Pledge Reminders') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Include:") ?></td>
      <td class=TextColumnWithBottomBorder>
         <input name=pledge_filter type=radio value='pledge' checked><?= gettext("Only Payments with Pledges") ?>
          &nbsp; <input name=pledge_filter type=radio value='all'><?= gettext("All Payments") ?>
      </td>
    </tr>
    <tr>
      <td class=LabelColumn><?= gettext("Generate:") ?></td>
      <td class=TextColumnWithBottomBorder>
         <input name=only_owe type=radio value='yes' checked><?= gettext("Only Families with unpaid pledges") ?>
         &nbsp; <input name=only_owe type=radio value='no'><?= gettext("All Families") ?>
      </td>
    </tr>
  <?php
    }

    if ($sReportType == 'Giving Report' || $sReportType == 'Zero Givers') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Report Heading:") ?></td>
      <td class=TextColumnWithBottomBorder>
         <input name=letterhead type=radio value='graphic'><?= gettext("Graphic")?>
         <input name=letterhead type=radio value='address' checked><?= gettext("Church Address") ?>
         <input name=letterhead type=radio value='none'><?= gettext("Blank") ?>
      </td>
    </tr>
    <tr>
      <td class=LabelColumn><?= gettext("Remittance Slip:") ?></td>
      <td class=TextColumnWithBottomBorder>
        <input name=remittance type=radio value='yes'><?= gettext("Yes") ?>
        <input name=remittance type=radio value='no' checked><?= gettext("No")?>
      </td>
    </tr>
  <?php
    }

    if ($sReportType == 'Advanced Deposit Report') {
  ?>
    <tr>
      <td class=LabelColumn><?= gettext("Sort Data by:")?></td>
      <td class=TextColumnWithBottomBorder>
        <input name=sort type=radio value='deposit' checked><?= gettext("Deposit") ?>
          &nbsp;<input name=sort type=radio value='fund'><?= gettext("Fund") ?>
          &nbsp;<input name=sort type=radio value='family'><?= gettext("Family")?>
      </td>
    </tr>
    <tr>
      <td class=LabelColumn><?= gettext("Report Type:") ?></td>
      <td class=TextColumnWithBottomBorder>
        <input name=detail_level type=radio value='detail' checked><?= gettext("All Data") ?>
        <input name=detail_level type=radio value='medium'><?= gettext("Moderate Detail") ?>
        <input name=detail_level type=radio value='summary'><?= gettext("Summary Data") ?>
      </td>
    </tr>
  <?php
    }

    if ($sReportType == 'Voting Members') {
  ?>
    <tr>
       <td class=LabelColumn><?= gettext("Voting members must have made<br> a donation within this many years<br> (0 to not require a donation):")?></td>
       <td class=TextColumnWithBottomBorder><input name=RequireDonationYears type=text value=0 size=5 class="form-control"></td>
    </tr>
  <?php
    }

    if ((($_SESSION['user']->isAdmin() && $_SESSION['bCSVAdminOnly'] ) || !$_SESSION['bCSVAdminOnly'] )
        && 
        ($sReportType == 'Pledge Summary' 
          || $sReportType == 'Giving Report' 
          || $sReportType == 'Individual Deposit Report' 
          || $sReportType == 'Advanced Deposit Report' 
          || $sReportType == 'Zero Givers'
        )) {
  ?>
    <tr>
      <td class=LabelColumn><?= ('Output Method:') ?></td>
      <td class=TextColumnWithBottomBorder>
        <input name=output type=radio checked value='pdf'>PDF
        <input name=output type=radio value='csv'>CSV
      </td>
    </tr>
  <?php
    } else {
  ?>
    <tr><td><input name=output type=hidden value='pdf'></td></tr>
  <?php
    }
    // Back, Next Buttons
  ?>
    <tr>
      <td>&nbsp;</td>
      <td><input type=button class='btn btn-default' name=Cancel value='<?= gettext("Back") ?>'
        onclick="javascript:document.location='<?= SystemURLs::getRootPath()?>/FinancialReports.php';">
        <input type=submit class='btn btn-primary' name=Submit2 value='<?= gettext("Create Report") ?>'>
      </td>
    </tr>
  </table>
</form>

<?php
}
?>
</div>
<?php
require 'Include/Footer.php';
?>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/FinancialReports.js"></script>