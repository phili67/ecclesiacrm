<?php
/*******************************************************************************
 *
 *  filename    : taxReport.php
 *  last change : 2023-06-17
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2023 EcclesiaCRM
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';
// Is this the second pass?
if (isset($_POST['Submit'])) {
    $iYear = InputUtils::LegacyFilterInput($_POST['Year'], 'int');
    RedirectUtils::Redirect('Reports/TaxReport.php?Year='.$iYear);
} else {
    $iYear = date('Y') - 1;
}
?>

<div class="card card-body">
    <form class="form-horizontal" method="post" action="<?= $sRootPath ?>/v2/deposit/tax/report">
        <div class="form-group">
            <label class="control-label col-sm-2" for="Year"><?= gettext('Calendar Year') ?>:</label>
            <div class="col-sm-2">
                <input class="form-control" type="text" name="Year" id="Year" value="<?= $iYear ?>">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-8">
                <button type="submit" class="btn btn-primary" name="Submit"><?= gettext('Create Report') ?></button>
                <button type="button" class="btn btn-default" name="Cancel"
                        onclick="javascript:document.location='v2/dashboard';"><?= gettext('Cancel') ?></button>
            </div>
        </div>

    </form>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




