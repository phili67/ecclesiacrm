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
require $sRootDocument . '/Include/Header.php'; ?>

<div class="card">
  <div class="card-header border-1 d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-person mr-2" aria-hidden="true"></i> <?= _('Properties') ?></h3>
    <?php if ($isMenuOption) { ?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-prop">
        <i class="fas fa-plus-circle mr-2"></i> <?= _('Add a New') ?> <?= $sTypeName?> <?= _('Property') ?>
    </a>  
    <?php } ?>
  </div>
  <div class="card-body">    
    <table class='table table-hover dt-responsive dataTable no-footer dtr-inline' id="property-listing-table-v2" width="100%"></table>
  </div>
</div>


<script nonce="<?= $CSPNonce ?>">
  window.CRM.menuOptionEnabled = <?= ($isMenuOption)?'true':'false' ?>;
  window.CRM.propertyType      = "<?= $sType ?>";
  window.CRM.propertyTypeName  = "<?= $sTypeName ?>";
  window.CRM.propertyTypesAll  = <?= json_encode($propertyTypes->toArray()) ?>;
</script>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/PropertyList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

