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

if ( $addCustomLink ) {
?>
    <div class="callout callout-danger"><i class="fa fa-warning" aria-hidden="true"></i>
      <?= _('Be carefull ! By deleting pastoral care type, the recorded datas for each persons will be lost.') ?>
    </div>

    <p align="center">
      <button class="btn btn-primary" id="add-new-pastoral-care"><?= _("Add a New Pastoral Care Type") ?></button>
    </p>
<?php 
}else {
?>
    <div class="callout callout-warning">
      <i class="fa fa-warning" aria-hidden="true"></i>
      <?= _('Only an admin can modify or delete this records.') ?>
    </div>
<?php
}
?>

<div class="box box-body">
  <table class="table table-striped table-bordered" id="pastoral-careTable" cellpadding="5" cellspacing="0"  width="100%"></table>
</div>

<script src="<?=  $sRootPath ?>/skin/js/sidebar/PastoralCareList.js" ></script>

<?php
require $sRootDocument . '/Include/Footer.php';
?>
