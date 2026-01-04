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
?>

<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>   
    <?= _('Be carefull ! By deleting properties, all persons, families and groups will be affected.') ?>
</div>

<div class="card card-body">
    <?php if ( $isMenuOption ) { ?>
        <p align="center"><a class='btn btn-primary' href="#" id="add-new-prop"><i class="fas fa-add"></i> <?= _('Add a New Property Type') ?></a></p>
    <?php } ?>
    <div class="table-responsive" width="100%">
        <table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="property-listing-table-v2"></table>
    </div>
</div>

<script nonce="<?= $CSPNonce ?>">
  window.CRM.menuOptionEnabled = <?= ($isMenuOption)?'true':'false' ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sidebar/PropertyTypeList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

