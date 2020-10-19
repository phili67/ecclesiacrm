<?php
/*******************************************************************************
 *
 *  filename    : ManageEnvelopes.php
 *  last change : 2005-02-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2006 Michael Wilt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\EnvelopeUtilities;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;

//Set the page title
$sPageTitle = _('Envelope Manager');

// Security: User must have finance permission to use this form
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (isset($_POST['Classification'])) {
    $iClassification = $_POST['Classification'];
    $_SESSION['classification'] = $iClassification;
} elseif (isset($_SESSION['classification'])) {
    $iClassification = $_SESSION['classification'];
} else {
    $iClassification = 0;
}

if (isset($_POST['ClassificationFamily'])) {
  $iClassificationFamily = $_POST['ClassificationFamily'];
  $_SESSION['ClassificationFamily'] = $iClassificationFamily;
} elseif (isset($_SESSION['ClassificationFamily'])) {
    $iClassificationFamily = $_SESSION['ClassificationFamily'];
} else {
  $iClassificationFamily = 0;
}

if (isset($_POST['SortBy'])) {
    $sSortBy = $_POST['SortBy'];
} else {
    $sSortBy = 'name';
}

if (isset($_POST['AssignStartNum'])) {
    $iAssignStartNum = $_POST['AssignStartNum'];
} else {
    $iAssignStartNum = 1;
}

$envelopesToWrite = [];
// get the array of envelopes of interest, indexed by family id
$envelopesByFamID = EnvelopeUtilities::getEnvelopes($iClassification);

// get the array of family name/description strings, also indexed by family id
$familyArray = MiscUtils::getFamilyList(SystemConfig::getValue('sDirRoleHead'), SystemConfig::getValue('sDirRoleSpouse'), $iClassification);
asort($familyArray);

if (isset($_POST['Confirm'])) {
    foreach ($familyArray as $fam_ID => $fam_Data) {
        $key = 'EnvelopeID_'.$fam_ID;
        if (isset($_POST[$key])) {
            $newEnvelope = $_POST[$key];
            $priorEnvelope = $envelopesByFamID[$fam_ID];
            if ($newEnvelope != $priorEnvelope) {
                $envelopesToWrite[$fam_ID] = $newEnvelope;
                $envelopesByFamID[$fam_ID] = $newEnvelope;
            }
        }
    }
    foreach ($envelopesToWrite as $fam_ID => $envelope) {
        $fam = FamilyQuery::Create()->findOneById ($fam_ID);
        $fam->setEnvelope ($envelope);
        $fam->save();
    }
}

//Get Classifications for the drop-down
$ormClassifications = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(1);

foreach ($ormClassifications as $ormClassification) {
  $classification[$ormClassification->getOptionId()] = $ormClassification->getOptionName();
}

require 'Include/Header.php';

?>

<div class="card card-body">
<form method="post" action="<?= SystemURLs::getRootPath() ?>/ManageEnvelopes.php" name="ManageEnvelopes">
<?php

$duplicateEnvelopeHash = [];
$updateEnvelopes = 0;

// Service the action buttons
if (isset($_POST['PrintReport'])) {
    RedirectUtils::Redirect('Reports/EnvelopeReport.php');
} elseif (isset($_POST['AssignAllFamilies'])) {
    EnvelopeUtilities::EnvelopeAssignAllFamilies($iClassificationFamily);
    RedirectUtils::Redirect('ManageEnvelopes.php');
} elseif (isset($_POST['ZeroAll'])) {
    $envelopesByFamID = []; // zero it out
    foreach ($familyArray as $fam_ID => $fam_Data) {
        $envelopesByFamID[$fam_ID] = 0;
        $envelopesToWrite[$fam_ID] = 0;
    }
}

?>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateEnvelopesModal"><?= _('Update Family Records') ?></button>
<button type="submit" class="btn btn-success" name="PrintReport"><i class="fa fa-print"></i></button>

<br><br>

<!-- Modal -->
<div class="modal fade" id="updateEnvelopesModal" tabindex="-1" role="dialog" aria-labelledby="updateEnvelopesModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="upload-Image-label"><?= _('Update Envelopes') ?></h4>
        </div>
        <div class="modal-body">
        <span style="color:red"><?= _('This will overwrite the family envelope numbers in the database with those selected on this page.  Continue?')?></span>
        </div>
        <div class="modal-footer">
            <input type="submit" class="btn btn-primary" value="<?= _('Confirm') ?>" name="Confirm">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= _('Cancel') ?></button>
        </div>
    </div>
  </div>
</div>

<div class="row">
   <div class="col-md-1">
      <b><?= _('Family Select')?></b> <?= _('with at least one:'); ?>
   </div>
   <div class="col-md-2">
        <select class="form-control" name="Classification">
          <option value="0"><?= _('All') ?></option>
        <?php
          foreach ($classification as $lst_OptionID => $lst_OptionName) {
        ?>
          <option value="<?= $lst_OptionID ?>" <?= ($iClassification == $lst_OptionID)?' selected':"" ?>><?= $lst_OptionName ?>&nbsp;
        <?php
          }
        ?>
        </select>
   </div>
   <div class="col-md-3">
        <input type="submit" class="btn btn-default" value="<?= _('Sort by') ?>" name="Sort">
        <input type="radio" Name="SortBy" value="name"
        <?php if ($sSortBy == 'name') {
            echo ' checked';
        } ?>><?= _('Last Name') ?>
        <input type="radio" Name="SortBy" value="envelope"
        <?php if ($sSortBy == 'envelope') {
            echo ' checked';
        } ?>><?= _('Envelope Number') ?>
    </div>
   <div class="col-md-2">
        <b>Envelope</b>
        <input type="submit" class="btn  btn-default" value="<?= _('Zero') ?>"
                 name="ZeroAll">
   </div>
   <div class="col-md-1">
        <select class="form-control" name="ClassificationFamily">
          <option value="0"><?= _('All') ?></option>
        <?php
          foreach ($classification as $lst_OptionID => $lst_OptionName) {
        ?>
          <option value="<?= $lst_OptionID ?>" <?= ($iClassificationFamily == $lst_OptionID)?' selected':"" ?>><?= $lst_OptionName ?>&nbsp;
        <?php
          }
        ?>
        </select>
   </div>
   <div class="col-md-2">
        <input type="submit" class="btn btn-default" value="<?= _('Assign starting at #') ?>"
                 name="AssignAllFamilies">
   </div>
   <div class="col-md-1">
        <input type="text" class="form-control" name="AssignStartNum" value="<?= $iAssignStartNum ?>">
    </div>
</div>
<hr/>
<?php
if ($sSortBy == 'envelope') {
    asort($envelopesByFamID);
    $arrayToLoop = $envelopesByFamID;
} else {
    $arrayToLoop = $familyArray;
}

foreach ($arrayToLoop as $fam_ID => $value) {
    if ($sSortBy == 'envelope') {
        $envelope = $value;
        $fam_Data = $familyArray[$fam_ID];
    } else {
        $fam_Data = $value;
        $envelope = $envelopesByFamID[$fam_ID];
    }
?>
<div class="row">
   <div class="col-md-6"><?= $fam_Data ?>&nbsp;</div>
<?php
    if ($envelope and $duplicateEnvelopeHash and array_key_exists($envelope, $duplicateEnvelopeHash)) {
        $tdTag = '<div class="col-md-4" style="color:red">';
    } else {
        $duplicateEnvelopeHash[$envelope] = $fam_ID;
        $tdTag = '<div class="col-md-4">';
    }
    echo $tdTag;
?>
    <input class="form-control" type="text" name="EnvelopeID_<?= $fam_ID ?>" value="<?= $envelope ?>" maxlength="10">
  </div>
</div>
<br>
    <?php
}
?>
<br>
</form>
</div>

<?php
require 'Include/Footer.php';
?>
