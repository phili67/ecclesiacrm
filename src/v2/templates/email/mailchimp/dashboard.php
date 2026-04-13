<?php
/*******************************************************************************
 *
 *  filename    : dashboard.php
 *  last change : 2019/2/6
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 all right reserved Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

if ($mailChimpStatus['title'] == 'Forbidden') {
    ?>
    <div class="alert alert-danger">
        <h4><i class="fas fa-ban"></i> <?= _('MailChimp Problem') ?></h4>
        <?= _("Mailchimp Status") ?> :<?= _("Title") ?> : <?= $mailChimpStatus['title'] ?> status
        : <?= $mailChimpStatus['status'] ?> detail : <?= $mailChimpStatus['detail'] ?>
        <?php
        if (!empty($mailChimpStatus['errors'])) {
            ?>
            <ul>
                <?php
                foreach ($mailChimpStatus['errors'] as $error) {
                    ?>
                    <li>
                        <?= _("field") ?> : <?= $error['field'] ?>  <?= _("Message") ?> : <?= $error['message'] ?>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <?php
        }
        ?>
    </div>
    <?php
} else {
    ?>
    <div class="mailchimp-message-is-activated" style="display: <?= $isMailChimpLoaded ? 'block' : 'none' ?>">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h3 class="h4 mb-1"><i class="far fa-envelope mr-2 text-success"></i><?= _('MailChimp Management') ?></h3>
                <p class="text-muted mb-0"><?= _('Create audiences, sync contacts, and manage campaign data.') ?></p>
            </div>
            <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
                <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>" height="25"/>
            </a>
        </div>

        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle mr-1"></i><?= _('MailChimp is working correctly') ?>
        </div>
    </div>

    <div class="mailchimp-dashboard-list-visibility">
        <div class="card card-outline card-success shadow-sm mb-3">           
            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2 mb-md-0 text-muted"><i class="fas fa-rocket mr-1"></i><?= _('Quick actions') ?></div>
                <div class="d-flex flex-wrap">
                    <button class="btn btn-success mr-2 mb-2 mb-md-0" id="CreateList" <?= ($isMailChimpActiv) ? '' : 'disabled' ?> data-toggle="tooltip" data-placement="bottom" title="<?= _('Create an audience or List') ?>">
                        <i class="fas fa-list-alt mr-1"></i><?= _('Create list') ?>
                    </button>
                    <a class="btn btn-outline-secondary mr-2 mb-2 mb-md-0" href="<?= $sRootPath ?>/Reports/MemberEmailExport.php">
                        <i class="fas fa-table mr-1"></i><?= _('Generate CSV') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/duplicateemails" class="btn btn-outline-warning mr-2 mb-2 mb-md-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i><?= _('Find Duplicate Emails') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/notinmailchimpemailspersons" class="btn btn-outline-info mr-2 mb-2 mb-md-0">
                        <i class="far fa-bell-slash mr-1"></i><?= _('Persons Not In MailChimp') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/notinmailchimpemailsfamilies" class="btn btn-outline-info mb-2 mb-md-0">
                        <i class="far fa-bell-slash mr-1"></i><?= _('Families Not In MailChimp') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="alert alert-light border mb-3">
            <i class="fas fa-info-circle text-success mr-1"></i>
            <?= _('You can import the generated CSV file to external email system.') ?>
            <?= _('For MailChimp see') ?>
            <a href="http://kb.mailchimp.com/lists/growth/import-subscribers-to-a-list" target="_blank"><?= _('import subscribers to a list.') ?></a>
        </div>

        <div class="card card-outline card-secondary shadow-sm">
             <div class="card-header border-1 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list-alt text-success me-2"></i> <?= _('All your lists') ?>
                </h3>
            </div>
            <div class="card-body">
                <div id="container"></div>
            </div>
        </div>
    </div>

    <script src="<?= $sRootPath ?>/skin/js/email/MailChimp/AutomaticDarkMode.js"></script>
    
    <script nonce="<?= $sCSPNonce ?>">
        window.CRM.mailchimpIsActive = <?= $isMailChimpActiv ?>;
        window.CRM.getSupportURL = "<?= $getSupportURL ?>";
        window.CRM.isMailChimpLoaded = <?= $isMailChimpLoaded ?>;
    </script>

    <script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Dashboard.js"></script>
    

    <?php
    }
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
