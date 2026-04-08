<?php
/*******************************************************************************
 *
 *  filename    : reminderreport.php
 *  last change : 2023-05-30
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/


use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;

require $sRootDocument . '/Include/Header.php';

// Is this the second pass?
if (isset($_POST['Submit'])) {
  $iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
  $_SESSION['idefaultFY'] = $iFYID;
  RedirectUtils::Redirect('Reports/ReminderReport.php?FYID='.$_SESSION['idefaultFY']);
} else {
  $iFYID = $_SESSION['idefaultFY'];
}

?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-bell mr-1"></i><?= _('Reminder Report') ?></h3>
  </div>
  <div class="card-body">
    <p class="text-muted mb-3"><?= _('Select a fiscal year to generate a reminder report.') ?></p>
    <form method="post" action="<?= $sRootPath ?>/Reports/ReminderReport.php">
      <div class="form-group row align-items-center">
        <label class="col-sm-3 col-form-label col-form-label-sm font-weight-bold" for="FYID\"><?= _('Fiscal Year') ?></label>
        <div class="col-sm-4">
            <?php \EcclesiaCRM\Utils\MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
        </div>
      </div>

      <div class="form-group row mb-0">
        <div class="col-sm-7 offset-sm-3">
          <div class="btn-group" role="group" aria-label="Reminder report actions">
            <button type="submit" class="btn btn-primary" name="Submit">
              <i class="fas fa-file-alt mr-1"></i><?= _('Create Report') ?>
            </button>
            <button type="button" class="btn btn-outline-secondary" name="Cancel"
                onclick="javascript:document.location='<?= $sRootPath ?>/v2/people/dashboard';">
              <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


