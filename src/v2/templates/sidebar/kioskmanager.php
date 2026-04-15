<?php
/*******************************************************************************
 *
 *  filename    : kioskmanager.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2018 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

?>



<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-primary shadow-sm rounded-4">
            <div class="card-header border-1 bg-body">
                <h3 class="card-title mb-0">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    <?= _('Register new Kiosk') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="mb-0 fw-bold">
                            <i class="fas fa-toggle-on text-secondary me-1"></i>
                            <?= _('Enable New Kiosk Registration') ?>
                        </label>
                    </div>
                    <div class="col-md-2">
                        <input data-width="150" id="isNewKioskRegistrationActive" type="checkbox" data-toggle="toggle"
                               data-on="<?= _('Active') ?>" data-off="<?= _('Inactive') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card card-outline card-secondary shadow-sm rounded-4">
            <div class="card-header border-1 bg-body">
                <h3 class="card-title mb-0">
                    <i class="fas fa-desktop text-primary me-2"></i>
                    <?= _('Active Kiosks') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="KioskTable"
                                 class="table table-striped table-bordered data-table dataTable no-footer dtr-inline"
                                 style="width:100%"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/system/KioskManager.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
