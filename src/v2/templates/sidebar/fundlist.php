<?php
/*******************************************************************************
 *
 *  filename    : FundList.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2018 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

?>


<div class="card">
  <div class="card-header border-1 d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-person mr-2" aria-hidden="true"></i> <?= _('Funds list') ?></h3>
    <?php if ($isMenuOption) { ?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-fund">
        <i class="fas fa-add"></i>  <?= _('Add a New Fund') ?>
    </a>  
    <?php } ?>
  </div>
  <div class="card-body">    
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>   <?= _('Be carefull ! By deleting Fund type, the recorded datas for pledges or payments will be lost.') ?></div>

    <table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="fundTable" cellpadding="5" cellspacing="0"  width="100%"></table>
  </div>
</div>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/FundList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
