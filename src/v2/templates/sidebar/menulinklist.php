<?php
/*******************************************************************************
 *
 *  filename    : MenuLinksList.php
 *  last change : 2019-02-5
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';

if ( $addCustomLink ) {
?>
    <p align="center"><button class="btn btn-primary" id="add-new-menu-links"><?= _("Add Custom Menu Link") ?></button></p>
<?php 
} else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= _('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>

<div class="box box-body">

<table class="table table-striped table-bordered" id="menulinksTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.personId  = <?= $personId ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sidebar/MenuLinksList.js" ></script>

<?php
 require $sRootDocument . '/Include/Footer.php';
?>
