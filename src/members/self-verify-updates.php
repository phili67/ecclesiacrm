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
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?= _("Families") ?></h3>
            </div>
            <div class="box-body">
                <table id="families" class="table table-striped table-bordered table-responsive data-table">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/self-verify-updates.js"></script>

<?php require '../Include/Footer.php'; ?>
