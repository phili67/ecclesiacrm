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

use EcclesiaCRM\dto\SystemURLs;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-primary">
            <div class="card-header  border-0">
                <h3 class="card-title"><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i> <?= gettext("Families") ?></h3>
            </div>
            <div class="card-body">
                <table id="families" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/online-pending-verify.js"></script>

<?php require '../Include/Footer.php'; ?>
