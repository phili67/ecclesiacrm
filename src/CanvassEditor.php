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

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\CanvassUtilities;
use EcclesiaCRM\CanvassDataQuery;
use EcclesiaCRM\CanvassData;
use EcclesiaCRM\FamilyQuery;

// Security: User must have canvasser permission to use this form
if (!SessionUser::getUser()->isCanvasserEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

//require 'Include/CanvassUtilities.php';

$iCanvassID = 0;
if (array_key_exists('CanvassID', $_GET)) {
    $iCanvassID = InputUtils::LegacyFilterInput($_GET['CanvassID'], 'int');
}
$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack']);
$iFamily  = InputUtils::LegacyFilterInput($_GET['FamilyID']);
$iFYID    = InputUtils::LegacyFilterInput($_GET['FYID']);

$sDateError = '';
$bNotInterested = false;

//Get Family name
$family = FamilyQuery::Create()->findOneById ($iFamily);

$fyStr = MiscUtils::MakeFYString($iFYID);

if ($family->getPeople()->count() == 1) {
    $sPageTitle = $fyStr.' : '._('Canvass Input for the').' '.$family->getName()." ".$family->getPeople()[0]->getFirstName().' ('._('Person').")";
} else {
    $sPageTitle = $fyStr.' : '._('Canvass Input for the').' '.$family->getName().' ('._('family').')';
}



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
        $canvas = new CanvassData();

        $canvas->setFamilyId($iFamily);
        $canvas->setCanvasser($iCanvasser);
        $canvas->setFyid($iFYID);
        $canvas->setDate($dDate);
        $canvas->setPositive($tPositive);
        $canvas->setCritical($tCritical);
        $canvas->setInsightful($tInsightful);
        $canvas->setFinancial($tFinancial);
        $canvas->setSuggestion($tSuggestion);
        $canvas->setNotInterested($bNotInterested);
        $canvas->setWhyNotInterested($tWhyNotInterested);

        $canvas->save();

        //Execute the SQL
        $iCanvassID = $canvas->getId();
    } else {
        $canvas = CanvassDataQuery::Create()->findOneByFamilyId ($iFamily);

        $canvas->setFamilyId($iFamily);
        $canvas->setCanvasser($iCanvasser);
        $canvas->setFyid($iFYID);
        $canvas->setDate($dDate);
        $canvas->setPositive($tPositive);
        $canvas->setCritical($tCritical);
        $canvas->setInsightful($tInsightful);
        $canvas->setFinancial($tFinancial);
        $canvas->setSuggestion($tSuggestion);
        $canvas->setNotInterested($bNotInterested);
        $canvas->setWhyNotInterested($tWhyNotInterested);

        $canvas->save();
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
    $canvas = CanvassDataQuery::Create()->filterByFyid($iFYID)->findOneByFamilyId ($iFamily);

    if (!is_null ($canvas)) {
        $iCanvassID         = $canvas->getId();
        $iCanvasser         = $canvas->getCanvasser();
        $iFYID              = $canvas->getFyid();
        if (!is_null($canvas->getDate())) {
            $dDate = $canvas->getDate()->format('Y-m-d');
        } else {
            $dDate = '';
        }
        $tPositive          = $canvas->getPositive();
        $tCritical          = $canvas->getCritical();
        $tInsightful        = $canvas->getInsightful();
        $tFinancial         = $canvas->getFinancial();
        $tSuggestion        = $canvas->getSuggestion();
        $bNotInterested     = $canvas->getNotInterested();
        $tWhyNotInterested  = $canvas->getWhyNotInterested();
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
$canvassers      = CanvassUtilities::CanvassGetCanvassers('Canvassers');
$braveCanvassers = CanvassUtilities::CanvassGetCanvassers('BraveCanvassers');

require 'Include/Header.php';
?>

<div class="card card-body">
<form method="post" action="<?= SystemURLs::getRootPath() ?>/CanvassEditor.php?<?= 'FamilyID='.$iFamily.'&FYID='.$iFYID.'&CanvassID='.$iCanvassID.'&linkBack='.$linkBack ?>" name="CanvassEditor">

<?php
    if ((!is_null($canvassers) && $canvassers->count() > 0) ||
        (!is_null($braveCanvassers) && $braveCanvassers->count() > 0)) {
?>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Canvasser') ?>:
        </div>
        <div class="col-lg-9">
           <select name='Canvasser' class= "form-control form-control-sm"><option value="0"><?= _('None selected') ?></option>
           <?php
              if (!is_null($braveCanvassers) && $braveCanvassers->count() != 0) {
                  foreach ($braveCanvassers as $braveCanvasser) {
            ?>
                      <option value="<?= $braveCanvasser->getId() ?>" <?= ($braveCanvasser->getId() == $iCanvasser)?' selected':'' ?>>
                          <?= $braveCanvasser->getFirstName().' '.$braveCanvasser->getLastName() ?>
                      </option>
            <?php
                  }
              }
              if (!is_null($canvassers) && $canvassers->count() != 0) {
                  foreach ($canvassers as $canvasser) {
              ?>
                      <option value="<?= $canvasser->getId() ?>" <?= ($canvasser->getId() == $iCanvasser)?' selected':'' ?>>
                          <?= $canvasser->getFirstName().' '.$canvasser->getLastName() ?>
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
          <textarea name="Positive" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tPositive ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Critical') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Critical" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tCritical ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Insightful') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Insightful" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tInsightful ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Financial') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Financial" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tFinancial ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
          <?= _('Suggestions') ?>
        </div>
        <div class="col-lg-9">
          <textarea name="Suggestion" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tSuggestion ?></textarea>
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
          <textarea name="WhyNotInterested" rows="3" style="width:100%" class= "form-control form-control-sm"><?= $tWhyNotInterested ?></textarea>
        </div>
    </div>

    <div>
            <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="Submit">
            <input type="button" class="btn btn-default" value="<?= _('Cancel') ?>" name="Cancel" onclick="javascript:document.location='<?php if (strlen($linkBack) > 0) {
                echo $linkBack;
            } else {
                echo 'v2/dashboard';
            } ?>';">

    </div>

  </form>
</div>

<?php require 'Include/Footer.php'; ?>
