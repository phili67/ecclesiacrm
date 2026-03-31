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


<div class="card">
  <div class="card-header border-1 d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-person mr-2" aria-hidden="true"></i> <?= _('Property Types') ?></h3>
    <?php if ($isMenuOption) { ?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-prop">
        <i class="fas fa-plus-circle mr-2"></i> <?= _('Add a New Property Type') ?>
    </a>  
    <?php } ?>
  </div>
  <div class="card-body">    
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>   
        <?= _('Be carefull ! By deleting properties, all persons, families and groups will be affected.') ?>
    </div>

    <table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="property-listing-table-v2"></table>
  </div>
</div>

<script nonce="<?= $CSPNonce ?>">
  window.CRM.menuOptionEnabled = <?= ($isMenuOption)?'true':'false' ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sidebar/PropertyTypeList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

