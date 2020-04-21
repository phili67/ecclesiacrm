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
$sPageTitle = gettext('Self Verify');
require '../Include/Header.php';

use EcclesiaCRM\dto\SystemURLs;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-male"></i><i class="fa fa-female"></i><i class="fa fa-child"></i> <?= _("Families") ?></h3>
            </div>
            <div class="card-body">
                <table id="families" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/self-verify-updates.js"></script>

<?php require '../Include/Footer.php'; ?>
