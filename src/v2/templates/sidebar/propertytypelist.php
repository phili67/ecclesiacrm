<?php
/*******************************************************************************
 *
 *  filename    : PropertyTypeList.php
 *  last change : 2019-04-08
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require $sRootDocument . '/Include/Header.php';

//Display the new property link
if ( $isMenuOption ) {
?>
    <p align="center"><a class='btn btn-primary' href="#" id="add-new-prop"><?= _('Add a New Property Type') ?></a></p>
<?php
} else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= _('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>

<div class="callout callout-danger"><i class="fa fa-warning" aria-hidden="true"></i>   <?= _('Be carefull ! By deleting properties, all persons, families and groups will be affected.') ?></div>

<div class="box box-body">
    <div class="table-responsive">
<?php
//Start the table
?>

<table class='table table-hover dt-responsive dataTable no-footer dtr-inline' id="property-listing-table-v2"></table>

</div>
</div>


<?php
require $sRootDocument . '/Include/Footer.php';
?>

<script nonce="<?= $CSPNonce ?>">
  window.CRM.menuOptionEnabled = <?= ($isMenuOption)?'true':'false' ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sidebar/PropertyTypeList.js" ></script>