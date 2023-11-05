<?php
/*******************************************************************************
 *
 *  filename    : lettersandlabels.php
 *                2006 Ed Davis
 *  last change : 2023-05-30
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/


use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\LabelUtils;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;

require $sRootDocument . '/Include/Header.php';


// Get the list of custom person fields
$ormCustomFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();
$numCustomFields = $ormCustomFields->count();

// Get Field Security List Matrix
$ormSecurityGrps = ListOptionQuery::Create()
->orderByOptionSequence()
->findById(5);

foreach ($ormSecurityGrps as $ormSecurityGrp) {
$aSecurityType[$ormSecurityGrp->getOptionId()] = $ormSecurityGrp->getOptionName();
}

// Is this the second pass?
if (isset($_POST['SubmitNewsLetter']) || isset($_POST['SubmitConfirmReport']) || isset($_POST['SubmitConfirmLabels']) || isset($_POST['SubmitConfirmReportEmail'])) {
    $sLabelFormat = InputUtils::LegacyFilterInput($_POST['labeltype']);
    $sFontInfo = $_POST['labelfont'];
    $sFontSize = $_POST['labelfontsize'];
    $bRecipientNamingMethod = $_POST['recipientnamingmethod'];
    $sLabelInfo = '&labelfont='.urlencode($sFontInfo).'&labelfontsize='.$sFontSize."&recipientnamingmethod=".$bRecipientNamingMethod;

    if (isset($_POST['SubmitNewsLetter'])) {
        RedirectUtils::Redirect('Reports/NewsLetterLabels.php?labeltype='.$sLabelFormat.$sLabelInfo);
    } elseif (isset($_POST['SubmitConfirmReport'])) {
        $_SESSION['POST_Datas'] = $_POST;
        RedirectUtils::Redirect('Reports/ConfirmReport.php');
    } elseif (isset($_POST['SubmitConfirmLabels'])) {
        RedirectUtils::Redirect('Reports/ConfirmLabels.php?labeltype='.$sLabelFormat.$sLabelInfo);
    } elseif (isset($_POST['SubmitConfirmReportEmail'])) {
        RedirectUtils::Redirect('Reports/ConfirmReportEmail.php');
    }
} else {
    $sLabelFormat = 'Tractor';
}
?>
<form method="post" action="<?= $sRootPath ?>/v2/people/LettersAndLabels">
    <div class="card card-secondary">
      <div class="card-header border-1">
        <h3 class="card-title"><?= gettext('People Reports')?></h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h3><?= _("Badge") ?></h1>
            <hr/>
              <?php
                LabelUtils::LabelSelect('labeltype');
                LabelUtils::FontSelect('labelfont');
                LabelUtils::FontSizeSelect('labelfontsize');
              ?>
              <div class="row">
                <div class="col-md-6"><label><?= _("Recipient Naming Method")?></label></div>
                <div class="col-md-6">
                  <select class="form-control form-control-sm" name="recipientnamingmethod">
                    <option value="salutationutility"><?= gettext("Salutation Utility") ?></option>
                    <option value="familyname"><?= gettext("Family Name") ?></option>
                  </select>
                </div>
              </div>
          </div>
          <div class="col-md-1"></div>
          <div class="col-md-4">
              <h3><?= _("Custom Fields") ?></h1>
              <hr/>
              <?php
              if ($numCustomFields > 0) {
                  foreach ($ormCustomFields as $ormCustomField) {
                      if (($aSecurityType[$ormCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormCustomField->getCustomFieldSec()]])) {
                          ?>
                          <input type="checkbox" Name="bCustom<?= $ormCustomField->getCustomOrder() ?>" value="1"
                                  checked> <?= $ormCustomField->getCustomName() ?><br>
                          <?php
                      }
                  }
              }
              ?>
          </div>
      </div>        
      <div class="card-footer">
          <button class="btn btn-success" type="submit" name="SubmitNewsLetter" value="delete">
              <i class="fas fa-file-pdf"></i> <?= gettext('Newsletter labels') ?>
          </button>
          <button class="btn btn-primary" type="submit" name="SubmitConfirmReport" value="delete">
              <i class="fas fa-file-pdf"></i> <?= gettext('Confirm data letter') ?>
          </button>
          <button class="btn btn-primary" type="submit" name="SubmitConfirmLabels" value="delete">
              <i class="fas fa-file-pdf"></i> <?= gettext('Confirm data labels') ?>
          </button>
          <button class="btn btn-danger" type="submit" name="SubmitConfirmReportEmail" value="delete">
              <i class="fas fa-paper-plane"></i> <?= gettext('Confirm data Email') ?>
          </button>

          <input type="button" class="btn btn-default" name="Cancel" value="x <?= gettext('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/v2/dashboard';">
      </div>
    </div>
</form>    
  
<?php require $sRootDocument . '/Include/Footer.php'; ?>


