<?php
/*******************************************************************************
 *
 *  filename    : PropertyList.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php'; ?>

<div class="box box-body">

<?php 
   if ($isMenuOption) {
    //Display the new property link
?>
    <p align="center"><a class='btn btn-primary' href="#" id="add-new-prop"><?= _('Add a New') ?> <?= $sTypeName?> <?= _('Property') ?></a></p>
<?php
}

//Start the table
?>
<table class='table table-hover dt-responsive dataTable no-footer dtr-inline' id="property-listing-table-v2"></table>
</div>

<?php
require $sRootDocument . '/Include/Footer.php';
?>

<script nonce="<?= $CSPNonce ?>">
  window.CRM.menuOptionEnabled = <?= ($isMenuOption)?'true':'false' ?>;
  window.CRM.propertyType      = "<?= $sType ?>";
  window.CRM.propertyTypeName  = "<?= $sTypeName ?>";
  window.CRM.propertyTypesAll  = <?= json_encode($propertyTypes->toArray()) ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sidebar/PropertyList.js" ></script>