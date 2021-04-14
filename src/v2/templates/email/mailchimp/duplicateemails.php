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

<div class="card">
    <div class="card-header  with-border">
        <h3 class="card-title"><?= _("Duplicate Emails") ?></h3>
        <div style="float:right">
            <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank"><img
                    src="<?= $sRootPath ?>/Images/<?= !empty(\EcclesiaCRM\Theme::isDarkModeEnabled())?'Mailchimp_Logo-Horizontal_White.png':'Mailchimp_Logo-Horizontal_Black.png' ?>" height=25/></a>
        </div>
    </div>
    <div class=" card-body">
        <table class="table table-striped table-bordered" id="duplicateTable" cellpadding="5" cellspacing="0"
               width="100%"></table>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/DuplicateEmails.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
