<?php
/*******************************************************************************
 *
 *  filename    : PaddleNumList.php
 *  last change : 2020-09-22
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2020 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-users mr-1"></i><?= _('Buyers') ?></h3>
        <span class="badge badge-light border" id="buyerCountBadge">0</span>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center mb-3 pb-2 border-bottom">
            <?php if ($iFundRaiserID > 0) { ?>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="SelectAll" name="SelectAll">
                    <label class="custom-control-label" for="SelectAll"><?= _('Select all') ?></label>
                </div>
            <?php } ?>
            <button type="button" class="btn btn-sm btn-success mr-2 mb-1" id="AddBuyer" name="AddBuyer">
                <i class="fas fa-user-plus mr-1"></i><?= _('Add Buyer') ?>
            </button>
            <button type="button" class="btn btn-sm btn-outline-info mr-2 mb-1" id="AddDonnor" name="AddDonnor">
                <i class="fas fa-user-check mr-1"></i><?= _('Add Donors to Buyer List') ?>
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary mb-1" id="GenerateStatements" name="GenerateStatements">
                <i class="fas fa-file-export mr-1"></i><?= _('Generate Statements for Selected') ?>
            </button>
        </div>

        <div class="table-responsive">
            <table cellpadding="5" cellspacing="0"
                   class="table table-striped table-hover table-sm mb-0 dataTable no-footer dtr-inline"
                   id="buyer-listing-table"
                   width="100%"></table>
        </div>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(function() {
        window.CRM.fundraiserID = <?= $iFundRaiserID ?>;
        window.CRM.checkAll = false;
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/fundraiser/paddleNumList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
