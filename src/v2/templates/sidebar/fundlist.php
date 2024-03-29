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

<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>   <?= _('Be carefull ! By deleting Fund type, the recorded datas for pledges or payments will be lost.') ?></div>

<div class="card card-body">

<?php if ( $isMenuOption ) {
?>
    <p align="center"><button class="btn btn-primary delete-payment" id="add-new-fund"><?= _('Add a New Fund') ?></button></p>
<?php
}

?>

<table class="table table-striped table-bordered" id="fundTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/FundList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
