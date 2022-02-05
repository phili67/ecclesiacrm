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
<div class="card">
    <div class="card-header border-0">
        <h3 class="card-title"><?= _('Filters') ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-2">
                <?= _('Number') ?>:
            </div>
            <div class="col-lg-2">
                <input type="text" name="ID" id="ID" value="" class="form-control input-sm">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <?= _('Date Start') ?>:
            </div>
            <div class="col-lg-2">
                <input type="text" name="DateStart" maxlength="10" id="DateStart" size="11"
                       value=""
                       class="date-picker form-control input-sm"
                       placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
            </div>
            <div class="col-lg-1">
            </div>
            <div class="col-lg-3">
                <input type="submit" class="btn btn-primary btn-sm" id="submitFilter" value="<?= _('Apply Filters') ?>"
                       name="FindFundRaiserSubmit">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <?= _('Date End') ?>:
            </div>
            <div class="col-lg-2">
                <input type="text" name="DateEnd" maxlength="10" id="DateEnd" size="11"
                       value=""
                       class="date-picker form-control input-sm"
                       placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
            </div>
            <div class="col-lg-1">
            </div>
            <div class="col-lg-3">
                <input type="submit" class="btn btn-danger btn-sm" id="clearFiltersSubmit" value="<?= _('Clear Filters') ?>"
                       name="FilterClear">
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-0">
        <h3 class="card-title"><?= _('Fundraisers') ?></h3>
    </div>
    <div class="card-body">
        <br>
        <table cellpadding='4' align='center' cellspacing='0' width='100%' id='fundraiser-listing-table'
               class="table table-striped table-bordered dataTable no-footer dtr-inline"></table>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.fundraiserID = '0';
    window.CRM.startDate = '-1';
    window.CRM.endDate = '-1';
</script>

<script src="<?= $sRootPath ?>/skin/js/fundraiser/findFundRaiser.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
