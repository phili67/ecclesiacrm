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

require $sRootDocument . '/Include/Header.php';

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
<div class="row">
  <div class="col-lg-12">
    <div class="card card-secondary">
      <div class="card-header border-1">
        <h3 class="card-title"><?= gettext('People Reports')?></h3>
      </div>
      <div class="card-body">
        <form method="post" action="<?= $sRootPath ?>/v2/people/LettersAndLabels">
            <div class="table-responsive">

          <table class="table" cellpadding="3" align="left">
            <?php
              LabelUtils::LabelSelect('labeltype');
              LabelUtils::FontSelect('labelfont');
              LabelUtils::FontSizeSelect('labelfontsize');
            ?>
            <tr>
              <td class="LabelColumn"><?= gettext("Recipient Naming Method")?>:</td>
              <td class="TextColumn">
                <select class="form-control form-control-sm" name="recipientnamingmethod">
                  <option value="salutationutility"><?= gettext("Salutation Utility") ?></option>
                  <option value="familyname"><?= gettext("Family Name") ?></option>
                </select>
              </td>
            </tr>

          </table>
            </div>
            <div>
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

                <input type="button" class="btn btn-default" name="Cancel" value="<?= gettext('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/v2/dashboard';">
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


