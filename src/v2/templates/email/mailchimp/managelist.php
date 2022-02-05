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

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-list"></i> <?= _('Manage Email List') ?></h3>
                <div style="float:right">
                    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank"><img
                            class="logo-mailchimp"  src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled()?'Mailchimp_Logo-Horizontal_White.png':'Mailchimp_Logo-Horizontal_Black.png' ?>" height=25/></a>
                </div>
            </div>
            <div class="card-body">
                <div class="btn-group">
                    <button class="btn btn-app CreateCampaign" id="CreateCampaign" data-listid="<?= $listId ?>"
                            data-id="-1" data-name="">
                        <i class="fas fa-envelope-open-text"></i><?= _("Create a Campaign") ?>
                    </button>
                    <button type="button" id="addCreateCampaignTagDrop" class="btn btn-app dropdown-toggle"
                            data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu" id="allCampaignTags"></div>
                </div>
                <button id="deleteAllSubScribers" class="btn btn-app bg-orange" data-listid="<?= $listId ?>">
                    <i class="far fa-trash-alt"></i><?= _("Delete All Subscribers") ?>
                </button>
                <button id="deleteList" class="btn btn-app align-right bg-maroon" data-listid="<?= $listId ?>">
                    <i class="fas fa-trash-alt"></i><?= _("Delete") ?>
                </button>
                <button class="btn btn-app align-right bg-blue" id="modifyList" data-name="<?= $list['name'] ?>"
                        data-subject="<?= $list['campaign_defaults']['subject'] ?>"
                        data-permissionreminder="<?= $list['permission_reminder'] ?>">
                    <i class="fas fa-pencil-alt"></i>
                    <?= _('Modify Properties') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
if ($isMailchimpActiv) {
    ?>
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-users"></i> <?= _('Subscribers') ?></h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info"><i class="fas fa-info" aria-hidden="true"></i>
                        <?= _("To add all the newsletter users, type <b>NewLetter</b> in the search field, to add all members of the CRM, use <b>*</b>") ?>
                    </div>

                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-1">
                            <label for="check_all"><?= _("Add") ?></label>
                        </div>
                        <div class="col-md-5">
                            <select name="person-group-Id-Share" class="person-group-Id-Share"
                                    class="form-control select2" style="width:100%"
                                    data-listid="<?= $list['id'] ?>"></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-4" style="color:orange;font-size: 12px">
                            <?= _("Keywords") ?> : *, <?= _("Persons") ?>, <?= _("Families") ?>, newsletter, etc...<br>
                        </div>
                    </div>
                    <br>

                    <table class="table table-striped table-bordered" id="memberListTable" cellpadding="5"
                           cellspacing="0" width="100%"></table>

                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="sticky-top">
                <div class="card card-secondary shadow" id="container"></div>
            </div>
        </div>

        <br>
    </div>
    <?php
} else {
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="card box-body">
                <div class="alert alert-danger alert-dismissible">
                    <h4><i class="fas fa-ban"></i> MailChimp <?= _('is not configured') ?></h4>
                    <?= _('Please update the') ?> MailChimp <?= _('API key in Setting->') ?><a
                        href="<?= $sRootPath ?>/SystemSettings.php"><?= _('Edit General Settings') ?></a>,
                    <?= _('then update') ?> sMailChimpApiKey. <?= _('For more info see our ') ?><a
                        href="<?= $getSupportURL ?>"> MailChimp <?= _('support docs.') ?></a>
                </div>
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
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>
