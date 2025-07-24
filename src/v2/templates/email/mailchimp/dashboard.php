<?php
/*******************************************************************************
 *
 *  filename    : dashboard.php
 *  last change : 2019/2/6
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 all right reserved Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\MailChimpService;

require $sRootDocument . '/Include/Header.php';

$mailchimp = new MailChimpService();

$isActive = $mailchimp->isActive();

if ($isActive == true) {
    $isLoaded = $mailchimp->isLoaded();
}

$load_Elements = false;

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
    <div class="alert alert-info mailchimp-message-is-activated" style="display: <?= $isLoaded?'block':'none' ?>">
        <h4><i class="fas fa-info"></i> <?= _('MailChimp is activated') ?></h4>
        <?= _('MailChimp is working correctly') ?>
    </div>
    <div class="row mailchimp-dashboard-list-visibility" style="display: <?= $isLoaded?'block':'none' ?>">
        <div class="col-lg-12">
            <div class="card card-mailchimp">
                <div class="card-header">
                    <h3 class="card-title"><i class="far fa-envelope"></i> <?= _('MailChimp Management') ?></h3>
                    <div style="float:right"><a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
                            <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled()?'Mailchimp_Logo-Horizontal_White.png':'Mailchimp_Logo-Horizontal_Black.png' ?>" height=25/></a>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        <button class="btn btn-app btn-app-mailchimp" id="CreateList" <?= ($mailchimp->isActive()) ? '' : 'disabled' ?> data-toggle="tooltip"  data-placement="bottom" title="<?= _("Create an audience or List") ?>">
                            <i class="fas fa-list-alt"></i><?= _("Create list") ?>
                        </button>
                        <a class="btn btn-app btn-app-mailchimp" href="<?= $sRootPath ?>/Reports/MemberEmailExport.php">
                            <i class="fas fas fa-table"></i> <?= _('Generate CSV') ?>
                        </a>
                        <a href="<?= $sRootPath ?>/v2/mailchimp/duplicateemails" class="btn btn-app btn-app-mailchimp">
                            <i class="fas fa-exclamation-triangle"></i> <?= _("Find Duplicate Emails") ?>
                        </a>
                        <a href="<?= $sRootPath ?>/v2/mailchimp/notinmailchimpemailspersons" class="btn btn-app btn-app-mailchimp">
                            <i class="far fa-bell-slash"></i> <?= _("Persons Not In MailChimp") ?>
                        </a>
                        <a href="<?= $sRootPath ?>/v2/mailchimp/notinmailchimpemailsfamilies" class="btn btn-app btn-app-mailchimp">
                            <i class="far fa-bell-slash"></i> <?= _("Families Not In MailChimp") ?>
                        </a>
                    </p>
                    <?= _('You can import the generated CSV file to external email system.') ?>
                    <?= _("For MailChimp see") ?> <a
                        href="http://kb.mailchimp.com/lists/growth/import-subscribers-to-a-list"
                        target="_blank"><?= _('import subscribers to a list.') ?></a>
                </div>
            </div>
        </div>
    </div>

    <div id="container"></div>

    <script src="<?= $sRootPath ?>/skin/js/email/MailChimp/AutomaticDarkMode.js"></script>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        window.CRM.mailchimpIsActive = <?= $isMailChimpActiv ?>;
        window.CRM.getSupportURL = "<?= $getSupportURL ?>";
        window.CRM.isMailChimpLoaded = <?= $isMailChimpLoaded ?>;
    </script>

    <script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Dashboard.js"></script>
    <script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

    <?php
    }
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
