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
  



<div class="row">
  <div class="col-md-12">
    <div class="card card-outline card-primary shadow-sm rounded-4">
      <div class="card-header border-1 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
          <i class="fas fa-hands-helping text-primary me-2"></i> <?= _('Pastoral Cares') ?>
        </h3>
        <?php if ($isPastoralCareEnabled) { ?>
        <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-pastoral-care">
          <i class="fas fa-plus-circle me-2"></i> <?= _('Add a New Pastoral Care Type') ?>
        </a>
        <?php } ?>
      </div>
      <div class="card-body">
        <div class="alert alert-danger d-flex align-items-center mb-4">
          <i class="fas fa-exclamation-triangle fa-2x text-white me-2" aria-hidden="true"></i>
          <div>
            <span class="fw-bold text-danger-emphasis"><?= _('Warning!') ?></span><br>
            <span class="text-white small"><?= _('By deleting a pastoral care type, the recorded data for each person will be lost.') ?></span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="pastoral-careTable" style="width:100%"></table>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/PastoralCareList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>