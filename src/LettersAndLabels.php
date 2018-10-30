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
        Redirect('Reports/NewsLetterLabels.php?labeltype='.$sLabelFormat.$sLabelInfo);
    } elseif (isset($_POST['SubmitConfirmReport'])) {
        Redirect('Reports/ConfirmReport.php');
    } elseif (isset($_POST['SubmitConfirmReportEmail'])) {
        Redirect('Reports/ConfirmReportEmail.php');
    } elseif (isset($_POST['SubmitConfirmLabels'])) {
        Redirect('Reports/ConfirmLabels.php?labeltype='.$sLabelFormat.$sLabelInfo);
    }
} else {
    $sLabelFormat = 'Tractor';
}
?>
<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><?= gettext('People Reports')?></h3>
      </div>
      <div class="box-body">
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
                <select class="form-control input-sm" name="recipientnamingmethod">
                  <option value="salutationutility"><?= gettext("Salutation Utility") ?></option>
                  <option value="familyname"><?= gettext("Family Name") ?></option>
                </select>
              </td>
            </tr>

          </table>
            </div>
            <div>
              <input type="submit" class="btn btn-success" name="SubmitNewsLetter" value="<?= gettext('Newsletter labels') ?>">
              <input type="submit" class="btn btn-primary" name="SubmitConfirmReport" value="<?= gettext('Confirm data letter') ?>">
              <input type="submit" class="btn btn-primary" name="SubmitConfirmLabels" value="<?= gettext('Confirm data labels') ?>">
              <input type="submit" class="btn btn-primary" name="SubmitConfirmReportEmail" value="<?= gettext('Confirm data Email') ?>">
              <input type="button" class="btn btn-default" name="Cancel" value="<?= gettext('Cancel') ?>" onclick="javascript:document.location = 'Menu.php';">
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'Include/Footer.php' ?>
