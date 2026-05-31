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
        <h4><i class="fas fa-ban"></i> <?= dgettext("messages-MailChimp", 'MailChimp Problem') ?></h4>
        <?= dgettext("messages-MailChimp", "Mailchimp Status") ?> :<?= dgettext("messages-MailChimp", "Title") ?> : <?= $mailChimpStatus['title'] ?> <?= dgettext("messages-MailChimp", "status") ?>
        : <?= $mailChimpStatus['status'] ?> <?= dgettext("messages-MailChimp", "detail") ?> : <?= $mailChimpStatus['detail'] ?>
        <?php
        if (!empty($mailChimpStatus['errors'])) {
            ?>
            <ul>
                <?php
                foreach ($mailChimpStatus['errors'] as $error) {
                    ?>
                    <li>
                        <?= dgettext("messages-MailChimp", "field") ?> : <?= $error['field'] ?>  <?= dgettext("messages-MailChimp", "Message") ?> : <?= $error['message'] ?>
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
                <h3 class="h4 mb-1"><i class="far fa-envelope mr-2 text-success"></i><?= dgettext("messages-MailChimp", 'MailChimp Management') ?></h3>
                <p class="text-muted mb-0"><?= dgettext("messages-MailChimp", 'Create audiences, sync contacts, and manage campaign data.') ?></p>
            </div>
            <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
                <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>" height="25"/>
            </a>
        </div>

        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle mr-1"></i><?= dgettext("messages-MailChimp", 'MailChimp is working correctly') ?>
        </div>
    </div>

    <div class="mailchimp-dashboard-list-visibility">
        <div class="card card-outline card-success shadow-sm mb-3">           
            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2 mb-md-0 text-muted"><i class="fas fa-rocket mr-1"></i><?= dgettext("messages-MailChimp", 'Quick actions') ?></div>
                <div class="d-flex flex-wrap">
                    <button class="btn btn-success mr-2 mb-2 mb-md-0" id="CreateList" <?= ($isMailChimpActiv) ? '' : 'disabled' ?> data-toggle="tooltip" data-placement="bottom" title="<?= dgettext("messages-MailChimp", 'Create an audience or List') ?>">
                        <i class="fas fa-list-alt mr-1"></i><?= dgettext("messages-MailChimp", 'Create list') ?>
                    </button>
                    <a class="btn btn-outline-secondary mr-2 mb-2 mb-md-0" href="<?= $sRootPath ?>/Reports/MemberEmailExport.php">
                        <i class="fas fa-table mr-1"></i><?= dgettext("messages-MailChimp", 'Generate CSV') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp2/duplicateemails" class="btn btn-outline-warning mr-2 mb-2 mb-md-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i><?= dgettext("messages-MailChimp", 'Find Duplicate Emails') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp2/notinmailchimpemailspersons" class="btn btn-outline-info mr-2 mb-2 mb-md-0">
                        <i class="far fa-bell-slash mr-1"></i><?= dgettext("messages-MailChimp", 'Persons Not In MailChimp') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp2/notinmailchimpemailsfamilies" class="btn btn-outline-info mb-2 mb-md-0">
                        <i class="far fa-bell-slash mr-1"></i><?= dgettext("messages-MailChimp", 'Families Not In MailChimp') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="alert alert-light border mb-3">
            <i class="fas fa-info-circle text-success mr-1"></i>
            <?= dgettext("messages-MailChimp", 'You can import the generated CSV file to external email system.') ?>
            <?= dgettext("messages-MailChimp", 'For MailChimp see') ?>
            <a href="http://kb.mailchimp.com/lists/growth/import-subscribers-to-a-list" target="_blank"><?= dgettext("messages-MailChimp", 'import subscribers to a list.') ?></a>
        </div>

        <div class="card card-outline card-secondary shadow-sm">
             <div class="card-header border-1 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list-alt text-success me-2"></i> <?= dgettext("messages-MailChimp", 'All your lists') ?>
                </h3>
            </div>
            <div class="card-body">
                <div id="container"></div>
            </div>
        </div>
    </div>

    <script src="<?= $sRootPath ?>/Plugins/MailChimp/skin/js/AutomaticDarkMode.js"></script>
    
    <script nonce="<?= $sCSPNonce ?>">
        window.CRM.mailchimpIsActive = <?= $isMailChimpActiv ?>;
        window.CRM.getSupportURL = "<?= $getSupportURL ?>";
        window.CRM.isMailChimpLoaded = <?= $isMailChimpLoaded ?>;
    </script>

    <script src="<?= $sRootPath ?>/Plugins/MailChimp/skin/js/Dashboard.js"></script>
    

    <?php
    }
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
