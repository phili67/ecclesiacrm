<?php
/*******************************************************************************
 *
 *  filename    : CanvassEditor.php
 *  last change : 2013-02-22
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003, 2013 Deane Barker, Chris Gebhardt, Michael Wilt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\CanvassUtilities;

// Security: User must have canvasser permission to use this form
if (!SessionUser::getUser()->isCanvasserEnabled()) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

//require 'Include/CanvassUtilities.php';

$iCanvassID = 0;
if (array_key_exists('CanvassID', $_GET)) {
    $iCanvassID = InputUtils::LegacyFilterInput($_GET['CanvassID'], 'int');
}
$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack']);
$iFamily = InputUtils::LegacyFilterInput($_GET['FamilyID']);
$iFYID = InputUtils::LegacyFilterInput($_GET['FYID']);

$sDateError = '';
$bNotInterested = false;

//Get Family name
$sSQL = 'SELECT fam_Name FROM family_fam where fam_ID = '.$iFamily;
$rsFamily = RunQuery($sSQL);
extract(mysqli_fetch_array($rsFamily));

$fyStr = MakeFYString($iFYID);

$sPageTitle = _($fyStr.' '._('Canvass Input for the').' '.$fam_Name.' '._('family'));

//Is this the second pass?
if (isset($_POST['Submit'])) {
    $iCanvasser = InputUtils::LegacyFilterInput($_POST['Canvasser']);
    if (!$iCanvasser) {
        $iCanvasser = 0;
    }
    $dDate = InputUtils::FilterDate($_POST['Date']);
    $tPositive = InputUtils::LegacyFilterInput($_POST['Positive']);
    $tCritical = InputUtils::LegacyFilterInput($_POST['Critical']);
    $tInsightful = InputUtils::LegacyFilterInput($_POST['Insightful']);
    $tFinancial = InputUtils::LegacyFilterInput($_POST['Financial']);
    $tSuggestion = InputUtils::LegacyFilterInput($_POST['Suggestion']);
    $bNotInterested = isset($_POST['NotInterested']);
    if ($bNotInterested == '') {
        $bNotInterested = 0;
    }
    $tWhyNotInterested = InputUtils::LegacyFilterInput($_POST['WhyNotInterested']);

    // New canvas input (add)
    if ($iCanvassID < 1) {
        $sSQL = 'INSERT INTO canvassdata_can (can_famID, can_Canvasser, can_FYID, can_date, can_Positive,
                                          can_Critical, can_Insightful, can_Financial, can_Suggestion,
                        can_NotInterested, can_WhyNotInterested)
          VALUES ('.$iFamily.','.
                            $iCanvasser.','.
                            $iFYID.','.
                            '"'.$dDate.'",'.
                            '"'.$tPositive.'",'.
                            '"'.$tCritical.'",'.
                            '"'.$tInsightful.'",'.
                            '"'.$tFinancial.'",'.
                            '"'.$tSuggestion.'",'.
                            '"'.$bNotInterested.'",'.
                            '"'.$tWhyNotInterested.'")';
        //Execute the SQL
        RunQuery($sSQL);
        $sSQL = 'SELECT MAX(can_ID) AS iCanvassID FROM canvassdata_can';
        $rsLastEntry = RunQuery($sSQL);
        $newRec = mysqli_fetch_array($rsLastEntry);
        $iCanvassID = $newRec['iCanvassID'];
    } else {
        $sSQL = 'UPDATE canvassdata_can SET can_famID='.$iFamily.','.
                                          'can_Canvasser='.$iCanvasser.','.
                                          'can_FYID='.$iFYID.','.
                                          'can_date="'.$dDate.'",'.
                                          'can_Positive="'.$tPositive.'",'.
                                          'can_Critical="'.$tCritical.'",'.
                                          'can_Insightful="'.$tInsightful.'",'.
                                          'can_Financial="'.$tFinancial.'",'.
                                          'can_Suggestion="'.$tSuggestion.'",'.
                                          'can_NotInterested="'.$bNotInterested.'",'.
                                          'can_WhyNotInterested="'.$tWhyNotInterested.
                                          '" WHERE can_FamID = '.$iFamily;
        //Execute the SQL
        RunQuery($sSQL);
    }

    if (isset($_POST['Submit'])) {
        // Check for redirection to another page after saving information: (ie. PledgeEditor.php?previousPage=prev.php?a=1;b=2;c=3)
        if ($linkBack != '') {
            RedirectUtils::Redirect($linkBack);
        } else {
            RedirectUtils::Redirect('CanvassEditor.php?FamilyID='.$iFamily.'&FYID='.$iFYID.'&CanvassID='.$iCanvassID.'&linkBack=', $linkBack);
        }
    }
} else {
    $sSQL = 'SELECT * FROM canvassdata_can WHERE can_famID = '.$iFamily.' AND can_FYID='.$iFYID;
    $rsCanvass = RunQuery($sSQL);
    if (mysqli_num_rows($rsCanvass) > 0) {
        extract(mysqli_fetch_array($rsCanvass));

        $iCanvassID = $can_ID;
        $iCanvasser = $can_Canvasser;
        $iFYID = $can_FYID;
        $dDate = $can_date;
        $tPositive = $can_Positive;
        $tCritical = $can_Critical;
        $tInsightful = $can_Insightful;
        $tFinancial = $can_Financial;
        $tSuggestion = $can_Suggestion;
        $bNotInterested = $can_NotInterested;
        $tWhyNotInterested = $can_WhyNotInterested;
    } else {
        // Set some default values
        $iCanvasser = SessionUser::getUser()->getPersonId();
        $dDate = date('Y-m-d');

        $dDate = '';
        $tPositive = '';
        $tCritical = '';
        $tInsightful = '';
        $tFinancial = '';
        $tSuggestion = '';
        $bNotInterested = false;
        $tWhyNotInterested = '';
    }
}

// Get the lists of canvassers for the drop-down
$rsCanvassers = CanvassUtilities::CanvassGetCanvassers('Canvassers');
$rsBraveCanvassers = CanvassUtilities::CanvassGetCanvassers('BraveCanvassers');

require 'Include/Header.php';
?>

<div class="box box-body">
<form method="post" action="CanvassEditor.php?<?= 'FamilyID='.$iFamily.'&FYID='.$iFYID.'&CanvassID='.$iCanvassID.'&linkBack='.$linkBack ?>" name="CanvassEditor">

<?php
    if (($rsBraveCanvassers != 0 && mysqli_num_rows($rsBraveCanvassers) > 0) ||
        ($rsCanvassers != 0 && mysqli_num_rows($rsCanvassers) > 0)) {
?>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Canvasser') ?>:
        </div>
        <div class="col-lg-9">
           <select name='Canvasser'><option value="0"><?= _('None selected') ?></option>
           <?php
              if ($rsBraveCanvassers != 0) {
                  while ($aCanvasser = mysqli_fetch_array($rsBraveCanvassers)) {
            ?>
              <option value="<?= $aCanvasser['per_ID'] ?>" <?= ($aCanvasser['per_ID'] == $iCanvasser)?' selected':'' ?>>
                <?= $aCanvasser['per_FirstName'].' '.$aCanvasser['per_LastName'] ?>
              </option>
            <?php
                  }
              }
              if ($rsCanvassers != 0) {
                  while ($aCanvasser = mysqli_fetch_array($rsCanvassers)) {
              ?>
              <option value="<?= $aCanvasser['per_ID'] ?>" <?= ($aCanvasser['per_ID'] == $iCanvasser)?' selected':'' ?>>
                  <?= $aCanvasser['per_FirstName'].' '.$aCanvasser['per_LastName'] ?>
              </option>
            <?php
                  }
              }
            ?>
            </select>
        </div>
    </div>
<?php
  }
?>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Date') ?>:
        </div>
        <div class="col-lg-9">
          <input type="text" name="Date" value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>" 
            maxlength="10" id="sel1" size="11"  class="form-control pull-right active date-picker" 
            placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"?><font color="red"><?= $sDateError ?></font>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Positive') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Positive" rows="3" style="width:100%"><?= $tPositive ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Critical') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Critical" rows="3" style="width:100%"><?= $tCritical ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Insightful') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Insightful" rows="3" style="width:100%"><?= $tInsightful ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Financial') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Financial" rows="3" style="width:100%"><?= $tFinancial ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Suggestions') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Suggestion" rows="3" style="width:100%"><?= $tSuggestion ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Not Interested') ?>
        </div>
        <div class="col-lg-9">
          <input type="checkbox" Name="NotInterested" value="1" <?= ($bNotInterested)?' checked':'' ?>>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Why Not Interested?') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="WhyNotInterested" rows="3" style="width:100%"><?= $tWhyNotInterested ?></textarea>
        </div>
    </div>

    <div>
            <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="Submit">
            <input type="button" class="btn" value="<?= _('Cancel') ?>" name="Cancel" onclick="javascript:document.location='<?php if (strlen($linkBack) > 0) {
                echo $linkBack;
            } else {
                echo 'Menu.php';
            } ?>';">

    </div>

  </form>
</div>

<?php require 'Include/Footer.php'; ?>
