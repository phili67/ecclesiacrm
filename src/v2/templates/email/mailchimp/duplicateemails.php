<?php
/*******************************************************************************
 *
 *  filename    : duplicateemails.php
 *  last change : 2018-11-12
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-1"><i class="fas fa-copy mr-2 text-success"></i><?= _("Duplicate Emails") ?></h3>
        <p class="text-muted mb-0"><?= _("Review duplicates before syncing audiences.") ?></p>
    </div>
    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
        <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>" height="25"/>
    </a>
</div>

<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-table mr-1"></i><?= _("Duplicate Emails") ?></h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-bordered table-sm" id="duplicateTable" cellpadding="5" cellspacing="0" width="100%"></table>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/DuplicateEmails.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
