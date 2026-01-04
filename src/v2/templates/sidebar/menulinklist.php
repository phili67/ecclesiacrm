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
?>

<div class="card card-body">
  <?php if ($addCustomLink) { ?>
    <p align="center"><button class="btn btn-primary" id="add-new-menu-links"><?= _("Add Custom Menu Link") ?></button></p>
  <?php } ?>
  <table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="menulinksTable" cellpadding="5" cellspacing="0" width="100%"></table>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.personId = <?= $personId ?>;
</script>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/MenuLinksList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>