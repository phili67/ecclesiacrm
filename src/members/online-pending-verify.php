<?php
/*******************************************************************************
 *
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2017
 *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

//Set the page title
$sPageTitle = gettext('Pending Self Verify');
require '../Include/Header.php';

?>

<div class="row">
    <div class="col-12">      
        <div class="alert alert-light border mb-3">
            <i class="fas fa-info-circle mr-1 text-primary"></i>
            <?= gettext('Review online pending verification requests and open each family profile to validate submitted changes.') ?>
        </div>

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h2 class="h4 mb-2 mb-md-0">
                <i class="fas fa-user-clock mr-2 text-primary"></i><?= gettext('Pending Self Verify') ?>
            </h2>
            <div class="d-flex align-items-center">
                <span class="badge badge-info px-3 py-2 mr-2"><?= gettext('Pending requests') ?></span>
                <a class="btn btn-sm btn-outline-secondary" href="#"
                   onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='<?= $sRootPath ?>/v2/dashboard'; } return false;">
                    <i class="fas fa-arrow-left mr-1"></i><?= gettext('Back') ?>
                </a>
            </div>
        </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="families" class="table table-sm table-hover table-bordered data-table dataTable no-footer dtr-inline mb-0">
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= \EcclesiaCRM\dto\SystemURLs::getRootPath() ?>/skin/js/people/online-pending-verify.js"></script>

<?php require '../Include/Footer.php'; ?>
