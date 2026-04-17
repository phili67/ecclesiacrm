<?php
/*******************************************************************************
 *
 *  filename    : managelist.php
 *  last change : 2014-11-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019
 *                Philippe Logel not MIT
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-1"><i class="fas fa-list mr-2 text-success"></i><?= _('Manage Email List') ?></h3>
        <p class="text-muted mb-0"><?= _('Manage subscribers and campaigns for this audience.') ?></p>
    </div>
    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
        <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>" height="25"/>
    </a>
</div>

<div class="card card-outline card-success shadow-sm mb-3">
    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
        <div class="mb-2 mb-md-0 text-muted"><i class="fas fa-rocket mr-1"></i><?= _('Quick actions') ?></div>
        <div class="d-flex flex-wrap">
            <div class="btn-group mr-2 mb-2 mb-md-0">
                <button class="btn btn-success CreateCampaign" id="CreateCampaign" data-listid="<?= $listId ?>" data-id="-1" data-name="">
                    <i class="fas fa-envelope-open-text mr-1"></i><?= _('Create a Campaign') ?>
                </button>
                <button type="button" id="addCreateCampaignTagDrop" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu" id="allCampaignTags"></div>
            </div>
            <button id="deleteAllSubScribers" class="btn btn-outline-warning mr-2 mb-2 mb-md-0" data-listid="<?= $listId ?>">
                <i class="far fa-trash-alt mr-1"></i><?= _('Delete All Subscribers') ?>
            </button>
            <button class="btn btn-outline-primary mr-2 mb-2 mb-md-0" id="modifyList" data-name="<?= $list['name'] ?>" data-subject="<?= $list['campaign_defaults']['subject'] ?>" data-permissionreminder="<?= $list['permission_reminder'] ?>">
                <i class="fas fa-pencil-alt mr-1"></i><?= _('Modify Properties') ?>
            </button>
            <button id="deleteList" class="btn btn-outline-danger mb-2 mb-md-0" data-listid="<?= $listId ?>">
                <i class="fas fa-trash-alt mr-1"></i><?= _('Delete') ?>
            </button>
        </div>
    </div>
</div>

<?php
if ($isMailchimpActiv) {
    ?>
    <div class="row">
        <div class="col-lg-9">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-1"></i><?= _('Subscribers') ?></h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3"><i class="fas fa-info-circle mr-1"></i>
                        <?= _("To add all the newsletter users, type <b>NewLetter</b> in the search field, to add all members of the CRM, use <b>*</b>") ?>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-md-2">
                            <label class="mb-0 font-weight-bold"><?= _('Add') ?></label>
                        </div>
                        <div class="col-md-6">
                            <select name="person-group-Id-Share" class="person-group-Id-Share form-control select2" style="width:100%" data-listid="<?= $list['id'] ?>"></select>
                        </div>
                        <div class="col-md-4">
                            <small class="text-warning"><i class="fas fa-tag mr-1"></i><?= _('Keywords') ?> : *, <?= _('Persons') ?>, <?= _('Families') ?>, newsletter...</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm" id="memberListTable" cellpadding="5" cellspacing="0" width="100%"></table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="sticky-top">
                <div class="card card-outline card-secondary shadow-sm" id="container"></div>
            </div>
        </div>

        <br>
    </div>
    <?php
} else {
    ?>
    <div class="card card-outline card-danger shadow-sm">
        <div class="card-body">
            <div class="alert alert-danger mb-0">
                <h5><i class="fas fa-ban mr-1"></i>MailChimp <?= _('is not configured') ?></h5>
                <?= _('Please update the') ?> MailChimp <?= _('API key in Setting->') ?>
                <a href="<?= $sRootPath ?>/v2/systemsettings/integration"><?= _('Edit General Settings') ?></a>,
                <?= _('then update') ?> sMailChimpApiKey.
                <?= _('For more info see our ') ?>
                <a href="<?= $getSupportURL ?>" target="_blank">MailChimp <?= _('support docs.') ?></a>
            </div>
        </div>
    </div>

    <?php
}
?>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/AutomaticDarkMode.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.list_ID = "<?= $listId ?>";
    window.CRM.mailchimpIsActive = <?= ($isMailchimpActiv) ? 1 : 0 ?>;
    window.CRM.bWithAddressPhone = <?= ($bWithAddressPhone) ? 'true' : 'false' ?>;
    window.CRM.sDateFormatLong = "<?= $sDateFormatLong ?>";
    window.CRM.canSeePrivacyData = <?= (SessionUser::getUser()->isSeePrivacyDataEnabled())?1:0 ?>;
    window.CRM.contentsExternalCssFont = '<?= $contentsExternalCssFont ?>';
    window.CRM.extraFont = '<?= $extraFont ?>';
</script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/ManageList.js"></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>
