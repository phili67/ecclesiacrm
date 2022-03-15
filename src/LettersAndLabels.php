<?php
/*******************************************************************************
 *
 *  filename    : LettersAndLabels.php
 *  website     : http://www.ecclesiacrm.com
 *
 *  Contributors:
 *  2006 Ed Davis
 *
 *
 *  Copyright 2006 Contributors
  *

 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\LabelUtils;
use EcclesiaCRM\utils\RedirectUtils;

// Set the page title and include HTML header
$sPageTitle = gettext('Letters and Mailing Labels');
require 'Include/Header.php';

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
      <div class="card-header ">
        <h3 class="card-title"><?= gettext('People Reports')?></h3>
      </div>
      <div class="card-body">
        <form method="post" action="LettersAndLabels.php">
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

                <input type="button" class="btn btn-default" name="Cancel" value="<?= gettext('Cancel') ?>" onclick="javascript:document.location = 'v2/dashboard';">
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'Include/Footer.php' ?>
