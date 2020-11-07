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

require $sRootDocument . '/Include/Header.php';
?>

<?php
if ($mailChimpStatus['title'] == 'Forbidden') {
    ?>
    <div class="alert alert-danger">
        <h4><i class="fa fa-ban"></i> <?= _('MailChimp Problem') ?></h4>
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
    <div class="alert alert-info">
        <h4><i class="fa fa-info"></i> <?= _('MailChimp is activated') ?></h4>
        <?= _('MailChimp is working correctly') ?>
    </div>
    <?php
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header   with-border">
                <h3 class="card-title"><i class="fa fa-envelope"></i> <?= _('MailChimp Management') ?></h3>
                <div style="float:right"><a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
                        <img src="<?= $sRootPath ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a>
                </div>
            </div>
            <div class="card-body">
                <p>
                    <button class="btn btn-app" id="CreateList" <?= ($mailchimp->isActive()) ? '' : 'disabled' ?> data-toggle="tooltip"  data-placement="bottom" title="<?= _("Create an audience or List") ?>">
                        <i class="fa fa-list-alt"></i><?= _("Create list") ?>
                    </button>
                    <a class="btn btn-app bg-green" href="<?= $sRootPath ?>/Reports/MemberEmailExport.php">
                        <i class="fa fa fa-table"></i> <?= _('Generate CSV') ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/duplicateemails" class="btn btn-app">
                        <i class="fa fa-exclamation-triangle"></i> <?= _("Find Duplicate Emails") ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/notinmailchimpemailspersons" class="btn btn-app">
                        <i class="fa fa-bell-slash"></i><?= _("Persons Not In MailChimp") ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/notinmailchimpemailsfamilies" class="btn btn-app">
                        <i class="fa fa-bell-slash"></i><?= _("Families Not In MailChimp") ?>
                    </a>
                    <a href="<?= $sRootPath ?>/v2/mailchimp/debug" class="btn btn-app" data-toggle="tooltip"  data-placement="bottom" title="<?= _("To debug your email connection") ?>">
                        <i class="fa fa-stethoscope"></i><?= _("Debug") ?>
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

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.mailchimpIsActive = <?= $isMailChimpActiv ?>;
    window.CRM.getSupportURL = "<?= $getSupportURL ?>";
    window.CRM.isMailChimpLoaded = <?= $isMailChimpLoaded ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Dashboard.js"></script>
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>
