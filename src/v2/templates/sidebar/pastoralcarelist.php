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

if ($isPastoralCareEnabled) {
?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
    <?= _('Be carefull ! By deleting pastoral care type, the recorded datas for each persons will be lost.') ?>
  </div>


<?php
}
?>

<div class="card card-body">
<?php if ($isPastoralCareEnabled) { ?>
  <p align="center">    
    <button class="btn btn-primary" id="add-new-pastoral-care"><i class="fas fa-add"></i> <?= _("Add a New Pastoral Care Type") ?></button>
  </p>
<?php } ?>
<table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="pastoral-careTable" cellpadding="5" cellspacing="0" width="100%"></table>
</div>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/PastoralCareList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>