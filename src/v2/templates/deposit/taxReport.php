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

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0"><i class="fas fa-receipt mr-2"></i><?= gettext('Tax Report') ?></h3>
            <small class="text-muted d-block mt-1"><?= gettext('Generate annual tax contribution statements.') ?></small>
        </div>
        <span class="badge badge-light border px-2 py-1"><?= gettext('Year') ?>: <?= (int) $iYear ?></span>
    </div>
    <div class="card-body py-3">
        <form id="taxReportForm" class="mb-0" method="post" action="<?= $sRootPath ?>/v2/deposit/tax/report">
            <div class="form-group row align-items-center mb-3">
                <label class="col-sm-3 col-md-2 col-form-label font-weight-semibold" for="Year"><?= gettext('Calendar Year') ?>:</label>
                <div class="col-sm-4 col-md-3 col-lg-2">
                    <input class="form-control form-control-sm" type="number" name="Year" id="Year" value="<?= $iYear ?>" min="1900" max="<?= date('Y') + 1 ?>" step="1" required>
                    <small class="form-text text-muted"><?= gettext('Enter a 4-digit year.') ?></small>
                </div>
            </div>

            <div class="form-group row mb-0 pt-2 border-top">
                <div class="col-sm-9 offset-sm-3 col-md-10 offset-md-2 d-flex flex-wrap align-items-center">
                    <button type="submit" class="btn btn-sm btn-primary mr-2 mb-1" name="Submit"><i class="fas fa-file-export mr-1"></i><?= gettext('Create Report') ?></button>
                    <button type="button" class="btn btn-sm btn-secondary" name="Cancel"
                            onclick="javascript:document.location='<?= $sRootPath ?>/v2/dashboard';"><i class="fas fa-times mr-1"></i><?= gettext('Cancel') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/finance/TaxReport.js"></script>




