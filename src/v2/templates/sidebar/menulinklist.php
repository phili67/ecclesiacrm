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

<div class="card">
  <div class="card-header border-1 d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-link mr-2" aria-hidden="true"></i> <?= _('Menu Links') ?></h3>
    <?php if ($addCustomLink) { ?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-menu-links">
        <i class="fas fa-plus-circle mr-2"></i> <?= _("Add Custom Menu Link") ?>
    </a>  
    <?php } ?>
  </div>
  <div class="card-body">
    <table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="menulinksTable" cellpadding="5" cellspacing="0" width="100%"></table>
  </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.personId = <?= $personId ?>;
</script>

<script type="module" src="<?= $sRootPath ?>/skin/js/sidebar/MenuLinksList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>