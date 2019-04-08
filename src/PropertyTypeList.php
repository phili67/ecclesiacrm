<?php
/*******************************************************************************
 *
 *  filename    : PropertyTypeList.php
 *  last change : 2003-03-27
 *  website     : http://www.churchcrm.io
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                Copyright 2018
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

// Set the page title
$sPageTitle = gettext('Property Type List');

if ( !( SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

require 'Include/Header.php';

//Display the new property link
if ( SessionUser::getUser()->isMenuOptionsEnabled()) {
?>
    <p align="center"><a class='btn btn-primary' href="<?= SystemURLs::getRootPath() ?>/PropertyTypeEditor.php"><?= gettext('Add a New Property Type') ?></a></p>
<?php
} else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>

<div class="callout callout-danger"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Be carefull ! By deleting properties, all persons, families and groups will be affected.') ?></div>

<div class="box box-body">
    <div class="table-responsive">
<?php
//Start the table
?>

<table class='table table-hover dt-responsive dataTable no-footer dtr-inline' id="property-listing-table-v2"></table>

</div>
</div>


<?php
require 'Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.tableType         = "<?= $sType ?>";
  window.CRM.menuOptionEnabled = <?= SessionUser::getUser()->isMenuOptionsEnabled() ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/sidebar/PropertyTypeList.js" ></script>