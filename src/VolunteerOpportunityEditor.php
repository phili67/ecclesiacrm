<?php
/*******************************************************************************
 *
 *  filename    : VolunteerOpportunityEditor.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Michael Wilt
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;

// Security: User must have proper permission
// For now ... require $bAdmin
// Future ... $bManageVol

if ( !( $_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isCanvasserEnabled() ) ) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Volunteer Opportunity Editor');

require 'Include/Header.php';
?>

<?php if ($_SESSION['user']->isCanvasserEnabled()) {// only an admin can modify the options
?>
    <p align="center"><button class="btn btn-primary" id="add-new-volunteer-opportunity"><?= gettext("Add Volunteer Opportunity") ?></button></p>
<?php 
} else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>
<div class="box box-body">

<table class="table table-striped table-bordered" id="VolunteerOpportunityTable" cellpadding="5" cellspacing="0"  width="100%"></table>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/VolunteerOpportunity.js" ></script>


<?php require 'Include/Footer.php' ?>
