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
use EcclesiaCRM\Utils\MiscUtils;

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

<div class="card card-body">
  <form class="form-horizontal" method="post" action="<?= $sRootPath ?>/Reports/ReminderReport.php">
      <div class="form-group">
          <label class="control-label col-sm-2" for="FYID"><?= _('Fiscal Year') ?>:</label>
          <div class="col-sm-2">
              <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
          </div>
      </div>

      <div class="form-group">
          <div class="col-sm-offset-2 col-sm-8">
              <button type="submit" class="btn btn-primary" name="Submit"><?= _('Create Report') ?></button>
              <button type="button" class="btn btn-default" name="Cancel"
                      onclick="javascript:document.location='<?= $sRootPath ?>/v2/dashboard';"><?= _('Cancel') ?></button>
          </div>
      </div>
  </form>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


