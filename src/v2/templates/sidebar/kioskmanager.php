<?php
/*******************************************************************************
 *
 *  filename    : FundList.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2018 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?= _('Register new Kiosk') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label><?= _('Enable New Kiosk Registration') ?>:</label>
                    </div>
                    <div class="col-md-2">
                        <input data-width="150" id="isNewKioskRegistrationActive" type="checkbox" data-toggle="toggle"
                               data-on="<?= _('Active') ?>" data-off="<?= _('Inactive') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= _('Active Kiosks') ?></h3>
            </div>
            <div class="card-body">
                <table id="KioskTable"
                       class="table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                       style="width:100%"></table>

            </div>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/system/KioskManager.js"></script>

<?php
require $sRootDocument . '/Include/Footer.php';
?>
