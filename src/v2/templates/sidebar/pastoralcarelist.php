<?php

/*******************************************************************************
 *
 *  filename    : PastoralCareList.php
 *  last change : 2019-02-05
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2018 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>
  


<div class="card">
  <div class="card-header border-1 d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-person mr-2" aria-hidden="true"></i> <?= _('Pastoral Cares') ?></h3>
    <?php if ($isPastoralCareEnabled) { ?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-pastoral-care">
        <i class="fas fa-plus-circle mr-2"></i> <?= _('Add a New Pastoral Care Type') ?>
    </a>  
    <?php } ?>
  </div>
  <div class="card-body">    
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
      <?= _('Be carefull ! By deleting pastoral care type, the recorded datas for each persons will be lost.') ?>
    </div>

    <table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="pastoral-careTable" cellpadding="5" cellspacing="0" width="100%"></table>
  </div>
</div>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/PastoralCareList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>