<?php
/*******************************************************************************
 *
 *  filename    : manageEnvelopes.php
 *  last change : 2023-06-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2023 EcclesiaCRM
 *
 ******************************************************************************/

use EcclesiaCRM\dto\EnvelopeUtilities;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\ListOptionQuery;

use EcclesiaCRM\Utils\RedirectUtils;

require $sRootDocument . '/Include/Header.php';

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

?>

<div class="card card-primary card-outline">
<div class="card-header py-2 d-flex align-items-center justify-content-between">
    <h3 class="card-title mb-0"><i class="fas fa-envelope-open-text mr-2"></i><?= _('Manage Envelopes') ?></h3>
    <small class="text-muted"><?= _('Assign, sort and validate family envelope numbers') ?></small>
</div>
<div class="card-body p-3">
<form method="post" action="<?= $sRootPath ?>/v2/deposit/manage/envelopes" name="ManageEnvelopes">
<?php

$duplicateEnvelopeHash = [];
$updateEnvelopes = 0;

// Service the action buttons
if (isset($_POST['PrintReport'])) {
    RedirectUtils::Redirect('Reports/EnvelopeReport.php');
} elseif (isset($_POST['AssignAllFamilies'])) {
    EnvelopeUtilities::EnvelopeAssignAllFamilies($iClassificationFamily);
    RedirectUtils::Redirect('v2/deposit/manage/envelopes');
} elseif (isset($_POST['ZeroAll'])) {
    $envelopesByFamID = []; // zero it out
    foreach ($familyArray as $fam_ID => $fam_Data) {
        $envelopesByFamID[$fam_ID] = 0;
        $envelopesToWrite[$fam_ID] = 0;
    }
}

?>

<div class="d-flex flex-wrap mb-3">
    <button type="button" class="btn btn-sm btn-primary mr-2 mb-2" data-toggle="modal" data-target="#updateEnvelopesModal">
        <i class="fas fa-save mr-1"></i><?= _('Update Family Records') ?>
    </button>
    <button type="submit" class="btn btn-sm btn-outline-success mb-2" name="PrintReport">
        <i class="fas fa-print mr-1"></i><?= _('Print Report') ?>
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="updateEnvelopesModal" tabindex="-1" role="dialog" aria-labelledby="updateEnvelopesModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="upload-Image-label"><?= _('Update Envelopes') ?></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

<div class="card card-outline card-secondary mb-3">
<div class="card-body py-2">
<div class="form-row align-items-end">
    <div class="col-md-2 mb-2">
        <label class="small mb-1"><?= _('Family Select') ?> <?= _('with at least one:'); ?></label>
        <select class= "form-control form-control-sm" name="Classification">
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
   <div class="col-md-3 mb-2">
        <label class="small mb-1 d-block"><?= _('Sort by') ?></label>
        <div class="custom-control custom-radio custom-control-inline">
            <input class="custom-control-input" type="radio" id="sortByName" name="SortBy" value="name"
            <?php if ($sSortBy == 'name') {
                echo ' checked';
            } ?>>
            <label class="custom-control-label" for="sortByName"><?= _('Last Name') ?></label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            <input class="custom-control-input" type="radio" id="sortByEnvelope" name="SortBy" value="envelope"
            <?php if ($sSortBy == 'envelope') {
                echo ' checked';
            } ?>>
            <label class="custom-control-label" for="sortByEnvelope"><?= _('Envelope Number') ?></label>
        </div>
    </div>
   <div class="col-md-2 mb-2">
        <label class="small mb-1 d-block"><?= _('Envelope') ?></label>
        <button type="submit" class="btn btn-sm btn-outline-danger" name="ZeroAll">
            <i class="fas fa-eraser mr-1"></i><?= _('Zero') ?>
        </button>
   </div>
   <div class="col-md-2 mb-2">
        <label class="small mb-1 d-block"><?= _('Classification Family') ?></label>
        <select class= "form-control form-control-sm" name="ClassificationFamily">
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
   <div class="col-md-2 mb-2">
        <label class="small mb-1 d-block"><?= _('Assign starting at #') ?></label>
        <button type="submit" class="btn btn-sm btn-outline-primary" name="AssignAllFamilies">
            <i class="fas fa-random mr-1"></i><?= _('Assign') ?>
        </button>
   </div>
   <div class="col-md-1 mb-2">
        <label class="small mb-1 d-block"><?= _('#') ?></label>
        <input type="text" class= "form-control form-control-sm" name="AssignStartNum" value="<?= $iAssignStartNum ?>">
    </div>
   <div class="col-md-12 mb-2">
        <button type="submit" class="btn btn-sm btn-secondary" name="Sort">
            <i class="fas fa-sort mr-1"></i><?= _('Apply') ?>
        </button>
   </div>
</div>
</div>
</div>

<div class="form-group mb-2">
    <label class="small mb-1" for="envelopeSearch"><?= _('Quick search') ?></label>
    <input type="text" id="envelopeSearch" class="form-control form-control-sm" placeholder="<?= _('Filter by family name...') ?>">
</div>

<div class="card card-outline card-secondary">
<div class="card-body p-2">
<div class="row font-weight-bold small text-muted mb-2">
    <div class="col-md-8"><?= _('Family') ?></div>
    <div class="col-md-4"><?= _('Envelope Number') ?></div>
</div>
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
<div class="row align-items-center envelope-row py-1 border-top" data-family="<?= htmlspecialchars(mb_strtolower($fam_Data), ENT_QUOTES, 'UTF-8') ?>">
   <div class="col-md-8"><?= $fam_Data ?>&nbsp;</div>
<?php
    if ($envelope and $duplicateEnvelopeHash and array_key_exists($envelope, $duplicateEnvelopeHash)) {
        $tdTag = '<div class="col-md-4 text-danger">';
    } else {
        $duplicateEnvelopeHash[$envelope] = $fam_ID;
        $tdTag = '<div class="col-md-4">';
    }
    echo $tdTag;
?>
    <input class= "form-control form-control-sm" type="text" name="EnvelopeID_<?= $fam_ID ?>" value="<?= $envelope ?>" maxlength="10">
  </div>
</div>
    <?php
}
?>
</div>
</div>
</form>
</div>
</div>

<script nonce="<?= $CSPNonce ?>">
    $(function () {
        $('#envelopeSearch').on('input', function () {
            var term = $(this).val().toLowerCase();
            $('.envelope-row').each(function () {
                var family = $(this).data('family');
                $(this).toggle(family.indexOf(term) !== -1);
            });
        });
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




