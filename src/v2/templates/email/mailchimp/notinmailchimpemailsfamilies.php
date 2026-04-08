<?php
/*******************************************************************************
 *
 *  filename    : notinmailchimpemailsfamilies.php
 *  last change : 2019/2/6
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-1"><i class="fas fa-users mr-2 text-success"></i><?= _("Family List") ?></h3>
        <p class="text-muted mb-0"><?= _("Families missing Mailchimp email matches.") ?></p>
    </div>
    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
        <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>" height="25"/>
    </a>
</div>

<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-table mr-1"></i><?= _("Family List") ?></h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-bordered table-sm" id="familiesWithoutEmailTable" cellpadding="5" cellspacing="0" width="100%"></table>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/AutomaticDarkMode.js"></script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/NotInMailChimpFamilies.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
