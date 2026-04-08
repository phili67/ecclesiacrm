<?php
/*******************************************************************************
 *
 *  filename    : FindFundRaiser.php
 *  last change : 2020-09-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2020 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';
?>
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-filter mr-1"></i><?= _('Filters') ?></h3>
        <span class="badge badge-light border"><?= _('Fundraiser search') ?></span>
    </div>
    <div class="card-body py-3">
        <div class="form-row align-items-center mb-2">
            <div class="col-lg-2 col-md-3 font-weight-semibold mb-1 mb-md-0">
                <?= _('Number') ?>
            </div>
            <div class="col-lg-3 col-md-4">
                <input type="text" name="ID" id="ID" value="" class="form-control form-control-sm">
            </div>
        </div>

        <div class="form-row align-items-center mb-2">
            <div class="col-lg-2 col-md-3 font-weight-semibold mb-1 mb-md-0">
                <?= _('Date Start') ?>
            </div>
            <div class="col-lg-3 col-md-4">
                <input type="text" name="DateStart" maxlength="10" id="DateStart" size="11"
                       value=""
                       class="date-picker form-control form-control-sm"
                       placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
            </div>
            <div class="col-lg-7 col-md-5 d-none d-md-block">
                <small class="text-muted"><?= _('Use a date range to narrow down fundraiser records.') ?></small>
            </div>
        </div>

        <div class="form-row align-items-center mb-0">
            <div class="col-lg-2 col-md-3 font-weight-semibold mb-1 mb-md-0">
                <?= _('Date End') ?>
            </div>
            <div class="col-lg-3 col-md-4">
                <input type="text" name="DateEnd" maxlength="10" id="DateEnd" size="11"
                       value=""
                       class="date-picker form-control form-control-sm"
                       placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
            </div>
        </div>

        <div class="pt-2 mt-3 border-top d-flex flex-wrap align-items-center">
            <button type="button" class="btn btn-sm btn-primary mr-2 mb-1" id="submitFilter" name="FindFundRaiserSubmit">
                <i class="fas fa-search mr-1"></i><?= _('Apply Filters') ?>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-1" id="clearFiltersSubmit" name="FilterClear">
                <i class="fas fa-eraser mr-1"></i><?= _('Clear Filters') ?>
            </button>
            <small class="text-muted ml-md-3 ml-0 mb-1"><?= _('Tip: press Enter in a field to apply filters.') ?></small>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-1"></i><?= _('Fundraisers') ?></h3>
        <span class="badge badge-light border" id="fundraiserCountBadge">0</span>
    </div>
    <div class="card-body py-2">
        <div class="table-responsive">
            <table cellpadding='4' align='center' cellspacing='0' width='100%' id='fundraiser-listing-table'
                   class="table table-striped table-hover table-bordered mb-0 dataTable no-footer dtr-inline"></table>
        </div>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.fundraiserID = '0';
    window.CRM.startDate = '-1';
    window.CRM.endDate = '-1';
</script>

<script src="<?= $sRootPath ?>/skin/js/fundraiser/findFundRaiser.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
