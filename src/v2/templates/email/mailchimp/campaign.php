<?php
/*******************************************************************************
 *
 *  filename    : campaign.php
 *  last change : 2019-02-6
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019 Philippe Logel all rights reserved
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\utils\OutputUtils;

require $sRootDocument . '/Include/Header.php';

//print_r($campaign);

//print_r($reports['email-activity']);

?>

<?php
if ($campaign['status'] == 'sent') {
    $recipientCount = max(1, (int)$campaign['recipients']['recipient_count']);
    $openRate = round(($campaign['report_summary']['unique_opens'] / $recipientCount) * 100, 2);
    $clickRate = round(($campaign['report_summary']['subscriber_clicks'] / $recipientCount) * 100, 2);
    ?>
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="card card-outline card-success text-center h-100 mb-0">
                <div class="card-body py-3">
                    <div class="h3 mb-0 font-weight-bold text-success"><?= $campaign['report_summary']['unique_opens'] ?></div>
                    <div class="text-muted small"><?= _("Opened") ?> · <?= $openRate ?>%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-outline card-info text-center h-100 mb-0">
                <div class="card-body py-3">
                    <div class="h3 mb-0 font-weight-bold text-info"><?= $campaign['report_summary']['subscriber_clicks'] ?></div>
                    <div class="text-muted small"><?= _("Clicked") ?> · <?= $clickRate ?>%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-outline card-warning text-center h-100 mb-0">
                <div class="card-body py-3">
                    <div class="h3 mb-0 font-weight-bold text-warning">0</div>
                    <div class="text-muted small"><?= _("Bounced") ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-outline card-danger text-center h-100 mb-0">
                <div class="card-body py-3">
                    <div class="h3 mb-0 font-weight-bold text-danger"><?= count($reports['unsubscribed']['unsubscribes']) ?></div>
                    <div class="text-muted small"><?= _("Unsubscribed") ?></div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-1"><i class="fas fa-envelope-open-text mr-2 text-success"></i><?= _('Email Campaign Management') ?></h3>
        <p class="text-muted mb-0"><?= _('Compose content and schedule sending from one screen.') ?></p>
    </div>
    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank">
        <img class="logo-mailchimp" src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>" height="25"/>
    </a>
</div>

<div class="card card-outline card-success shadow-sm mb-3">
    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
        <div class="mb-2 mb-md-0 text-muted"><i class="fas fa-rocket mr-1"></i><?= _('Quick actions') ?></div>
        <div class="d-flex flex-wrap">
            <button class="btn btn-success mr-2 mb-2 mb-md-0" id="saveCampaign" data-listid="<?= $list_id ?>" <?= (($campaign['status'] == "sent") ? 'disabled' : '') ?>>
                <i class="fas fa-save mr-1"></i><?= _('Save Campaign') ?>
            </button>
            <button id="sendCampaign" class="btn btn-outline-primary mr-2 mb-2 mb-md-0 <?= (($campaign['status'] == "sent" || $campaign['status'] == "schedule") ? 'hidden' : '') ?>" data-listid="<?= $list_id ?>">
                <i class="far fa-paper-plane mr-1"></i><?= _('Send') ?>
            </button>
            <button id="deleteCampaign" class="btn btn-outline-danger mb-2 mb-md-0" data-listid="<?= $list_id ?>">
                <i class="fas fa-trash-alt mr-1"></i><?= _('Delete') ?>
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="far fa-file-alt mr-1"></i><?= _('Mail Subject') ?> & <?= _('Content') ?></h3>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-form-label"><span class="text-danger">*</span> <?= _('Subject') ?></label>
                    <div class="col-md-10">
                        <input type="text" id="CampaignSubject" placeholder="<?= _('Your Mail Subject') ?>" maxlength="100" class="form-control form-control-sm" value="<?= $campaign['settings']['subject_line'] ?>">
                    </div>
                </div>
                <div class="alert alert-light border mb-3">
                    <i class="fas fa-info-circle text-success mr-1"></i>
                    <?= _('You can use the button "Merge Tags" below, to customize your content') ?>
                    <img src="<?= $sRootPath ?>/Images/merge_tags.png" alt="Merge tags" class="ml-1"/>
                </div>
                <textarea name="campaignContent" cols="80" class="form-control form-control-sm campaignContent" id="campaignContent" style="width:100%;height:14em;"></textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-outline card-secondary shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags mr-1"></i><?= _('Tags') ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <?= $campaign['recipients']['segment_text'] ?>
            </div>
        </div>

        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="checkboxaCampaignSchedule" name="checkboxaCampaignSchedule" <?= ($campaign['status'] == "schedule") ? 'checked' : '' ?> <?= ($campaign['status'] == "sent" && !($campaign['status'] == "schedule")) ? "disabled" : "" ?>>
                        <label class="custom-control-label" for="checkboxaCampaignSchedule">
                            <i class="fas <?= ($campaign['status'] == "schedule") ? 'fa-calendar-check' : 'fa-calendar-times' ?> mr-1"></i><?= _('Schedule') ?>
                        </label>
                    </div>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i><?= _("You've first to create a content below to schedule a campaign.") ?><br>
                    <i class="fas fa-exclamation-triangle mr-1"></i><?= _("Campaigns may only be scheduled to send on the quarter-hour (:00, :15, :30, :45).") ?>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label"><span class="text-danger">*</span> <?= _('Date') ?></label>
                    <div class="col-md-9">
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar"></i></span></div>
                            <input class="form-control form-control-sm date-picker" type="text" id="dateCampaign" name="dateCampaign" value="<?= (isset($campaign['send_time'])) ? OutputUtils::change_date_for_place_holder($campaign['send_time']) : "" ?>" maxlength="10" size="11" placeholder="<?= SystemConfig::getValue('sDatePickerPlaceHolder') ?>" <?= ($campaign['status'] == "schedule") ? "" : "disabled" ?>>
                        </div>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <label class="col-md-3 col-form-label"><span class="text-danger">*</span> <?= _('Time') ?></label>
                    <div class="col-md-9">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-clock"></i></span></div>
                            <input type="text" class="form-control form-control-sm timepicker" id="timeCampaign" name="timeCampaign" value="<?= (isset($campaign['send_time'])) ? OutputUtils::change_time_for_place_holder($campaign['send_time']) : "00:00" ?>" <?= ($campaign['status'] == "schedule") ? "" : "disabled" ?>>
                        </div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2"><?= _('To validate, save your campaign with the <b>"Save Campaign"</b> button over.') ?></small>
            </div>
        </div>
    </div>
</div>


<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/AutomaticDarkMode.js"></script>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isCampaignSent = <?= (($campaign['status'] == "sent") ? 1 : 0) ?>;
    window.CRM.campaign_Id = "<?= $campaignId ?>";
    window.CRM.mailchimpIsActive = <?= ($isMailchimpActiv) ? 1 : 0 ?>;
    window.CRM.list_Id = "<?= $campaign['recipients']['list_id'] ?>";
    window.CRM.status = "<?= $campaign['status'] ?>";
    window.CRM.bWithAddressPhone = <?= ($bWithAddressPhone) ? 'true' : 'false' ?>;
    window.CRM.sDateFormatLong = "<?= $sDateFormatLong ?>";

    window.CRM.contentsExternalCssFont = '<?= $contentsExternalCssFont ?>';
    window.CRM.extraFont = '<?= $extraFont ?>';

    //Timepicker
    $('.timepicker').datetimepicker({
        format: 'LT',
        locale: window.CRM.lang,
        icons:
            {
                up: 'fas fa-angle-up',
                down: 'fas fa-angle-down'
            }
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Campaign.js"></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

