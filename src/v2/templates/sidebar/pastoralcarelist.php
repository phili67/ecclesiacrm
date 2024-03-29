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

if ( $isPastoralCareEnabled ) {
?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
      <?= _('Be carefull ! By deleting pastoral care type, the recorded datas for each persons will be lost.') ?>
    </div>

    <p align="center">
      <button class="btn btn-primary" id="add-new-pastoral-care"><?= _("Add a New Pastoral Care Type") ?></button>
    </p>
<?php
}else {
?>
    <div class="alert alert-warning">
      <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
      <?= _('Only an admin can modify or delete this records.') ?>
    </div>
<?php
}
?>

<div class="card card-body">
  <table class="table table-striped table-bordered" id="pastoral-careTable" cellpadding="5" cellspacing="0"  width="100%"></table>
</div>

<script type="module" src="<?=  $sRootPath ?>/skin/js/sidebar/PastoralCareList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
