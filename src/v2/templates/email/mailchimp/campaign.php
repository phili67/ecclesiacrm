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

?>

<?php
if ($campaign['status'] == 'sent') {
    ?>
    <div class="row mailchimp-campaign-main-send-infos">
        <div class="col-md-12">
            <h3 class="mailchimp-h3">
                <?= $campaign['recipients']['recipient_count'] ?>
                <span class="mailchimp-h2">
                <?= _("Recipients") ?>
            </span>
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <label><?= _("Audience") ?></label> : <?= $campaign['recipients']['list_name'] ?>
                </div>
                <div class="col-md-6">
                    <label><?= _("Delivered") ?></label> : <?= $campaign['send_time'] ?>

                </div>
            </div>


            <div class="row mailchimp-campaign-main-send-infos-sub">
                <div class="col-md-3 text-center mailchimp-campaign-main-send-infos-sub-cell">
                    <h3 class="mailchimp-h3"><?= $campaign['report_summary']['unique_opens'] ?></h3>
                    <h2 class="mailchimp-h2"><?= _("Opened") ?></h2>
                </div>
                <div class="col-md-3 text-center mailchimp-campaign-main-send-infos-sub-cell">
                    <h3 class="mailchimp-h3"><?= $campaign['report_summary']['subscriber_clicks'] ?></h3>
                    <h2 class="mailchimp-h2"><?= _("Clicked") ?></h2>
                </div>
                <div class="col-md-3 text-center mailchimp-campaign-main-send-infos-sub-cell">
                    <h3 class="mailchimp-h3"><?= 0 /*$campaign['report_summary']['subscriber_clicks']*/ ?></h3>
                    <h2 class="mailchimp-h2"><?= _("Bounced") ?></h2>
                </div>
                <div class="col-md-3 text-center">
                    <h3 class="mailchimp-h3"><?= 0 /*$campaign['report_summary']['subscriber_clicks']*/ ?></h3>
                    <h2 class="mailchimp-h2"><?= _("Unsubscribed") ?></h2>
                </div>
            </div>

            <div class="unit size1of2">
                <ul class="leaders">
                    <li><span><?= _("Successful deliveries") ?></span> <span class="fwb">
                                                    <span
                                                        data-mc-el="deliverCountStat"><?= $campaign['emails_sent'] ?></span> <span
                                data-mc-el="deliverRateStat"
                                class="percent-spacer alignr dim-el fwn"><?= round($campaign['emails_sent'] / $campaign['recipients']['recipient_count'] * 100.0, 2) ?>%</span>
                                            </span></li>
                    <li><span><?= _("Total opens") ?></span> <span class="fwb">
                                                    <?= $campaign['report_summary']['opens'] ?></span>
                    </li>
                    <!--<li><span><?= _("Last opened") ?></span>
                    <span data-mc-el="lastOpenDate">10/12/22 9:53PM</span>
                </li>
                <li><span><?= _("Forwarded") ?></span> <span class="fwb">
                                                    <span data-mc-el="forwardCountStat">0</span>
                                            </span></li>
                <li data-mc-el="forwardOpenCountBlock" style="display:none"><span><?= _("Forward opens") ?></span> <span
                        class="fwb">
                                                    <span data-mc-el="forwardOpenCountStat">0</span>
                                            </span></li>-->
                </ul>
            </div>

            <div class="lastUnit size1of2">
                <ul class="leaders">
                    <li><span><?= _("Clicks rate") ?></span> <span class="fwb">
                                                    <?= round($campaign['report_summary']['click_rate']*100.0,2) ?>%</span>
                    </li>
                    <li><span><?= _("Total clicks") ?></span> <span class="fwb">
                                                    <?= $campaign['report_summary']['clicks'] ?>                                             </span>
                    </li>
                    <!--<li><span>Last clicked</span>
                        <span data-mc-el="lastClickDate">10/12/22 9:44PM</span>
                    </li>
                    <li><span>Abuse reports</span> <span class="fwb">
                                                        <span data-mc-el="abuseCountStat">0</span>
                                                </span></li>
                                                -->
                </ul>
            </div>
        </div>
    </div>

    <?php
}
?>

<div class="row">
    <div class="col-lg-9">
        <div class="card card-mailchimp">
            <div class="card-header   border-1">
                <h3 class="card-title"><i class="fas fa-envelope-open-text"></i> <?= _('Email Campaign Management') ?>
                </h3>
                <div style="float:right">
                    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank"><img
                            class="logo-mailchimp"
                            src="<?= $sRootPath ?>/Images/<?= \EcclesiaCRM\Theme::isDarkModeEnabled() ? 'Mailchimp_Logo-Horizontal_White.png' : 'Mailchimp_Logo-Horizontal_Black.png' ?>"
                            height=25/></a>
                </div>
            </div>
            <div class="card-body">
                <p>
                    <button class="btn btn-app btn-app-mailchimp" id="saveCampaign"
                            data-listid="<?= $list_id ?>" <?= (($campaign['status'] == "sent") ? 'disabled' : '') ?>>
                        <i class="fas fa-save"></i> <?= _("Save Campaign") ?>
                    </button>
                    <button id="deleteCampaign" class="btn btn-app btn-app-mailchimp align-right"
                            data-listid="<?= $list_id ?>">
                        <i class="fas fa-trash-alt"></i><?= _("Delete") ?>
                    </button>
                    <button id="sendCampaign"
                            class="btn btn-app btn-app-mailchimp align-right <?= (($campaign['status'] == "sent" || $campaign['status'] == "schedule") ? 'hidden' : '') ?>"
                            data-listid="<?= $list_id ?>">
                        <i class="far fa-paper-plane"></i><?= _("Send") ?>
                    </button>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-mailchimp">
                    <div class="card-header border-1">
                        <h3 class="card-title"><i class="far fa-file-alt"></i> <?= _('Mail Subject') ?>
                            & <?= _("Content") ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2"><span style="color: red">*</span>
                                <label><?= _('Subject') ?> :</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" id="CampaignSubject" placeholder="<?= _("Your Mail Subject") ?>"
                                       size="30"
                                       maxlength="100" class="form-control form-control-sm" style="width: 100%"
                                       value="<?= $campaign['settings']['subject_line'] ?>">
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-lg-12" style="padding-left:15px;padding-right:15px;">
                                <div class="alert alert-info"><i class="fas fa-info" aria-hidden="true"></i>
                                    <?= _("You can use the button \"Merge Tags\" below, to customize your content") ?> :
                                    <img
                                        src="<?= $sRootPath ?>/Images/merge_tags.png">.
                                </div>
                                <textarea name="campaignContent" cols="80"
                                          class="form-control form-control-sm campaignContent"
                                          id="campaignContent" width="100%"
                                          style="margin-top:0px;width: 100%;height: 14em;"></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-info">
                    <div class="card-header   border-1">
                        <h3 class="card-title"><i class="fas fa-tags"></i> <?= _('Tags') ?></h3>
                        <div class="card-tools pull-right">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= $campaign['recipients']['segment_text'] ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-gray">
                    <div class="card-header   border-1">
                        <?php
                        if ($campaign['status'] == "schedule") {
                            ?>
                            <h3 class="card-title"><input type="checkbox" id="checkboxaCampaignSchedule"
                                                          name="checkboxaCampaignSchedule" checked> <i
                                    class="fas fa-calendar-check"></i> <label
                                    for="checkboxaCampaignSchedule"><?= _('Schedule') ?></label></h3>
                            <?php
                        } else {
                            ?>
                            <h3 class="card-title"><input type="checkbox" id="checkboxaCampaignSchedule"
                                                          name="checkboxaCampaignSchedule" <?= ($campaign['status'] == "sent" && !($campaign['status'] == "schedule")) ? "disabled" : "" ?>>
                                <i class="fas fa-calendar-times-o"></i> <label
                                    for="checkboxaCampaignSchedule"><?= _('Schedule') ?></label></h3>
                            <?php
                        }
                        ?>
                        <div class="card-tools pull-right">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning alert-mailchimp"><i class="fas fa-exclamation-triangle"
                                                                            aria-hidden="true"></i>
                            <?= _("You've first to create a content below to schedule a campaign.") ?>
                            <br/>
                            <i class="fas fa-exclamation-triangle"
                               aria-hidden="true"></i> <?= _("Campaigns may only be scheduled to send on the quarter-hour (:00, :15, :30, :45).") ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3"><span style="color: red">*</span>
                                        <label><?= _('Date') ?> :</label>
                                    </div>
                                    <div class="form-group col-md-9">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            </div>
                                            <!-- Philippe Logel -->
                                            <input class=" form-control  form-control-sm date-picker form-control-sm"
                                                   type="text" id="dateCampaign"
                                                   name="dateCampaign"
                                                   value="<?= (isset($campaign['send_time'])) ? OutputUtils::change_date_for_place_holder($campaign['send_time']) : "" ?>"
                                                   maxlength="10" id="sel1" size="11"
                                                   placeholder="<?= SystemConfig::getValue('sDatePickerPlaceHolder') ?>
                                    " <?= ($campaign['status'] == "schedule") ? "" : "disabled" ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3"><span style="color: red">*</span>
                                        <label><?= _('Time') ?> :</label>
                                    </div>
                                    <div class="form-group col-md-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                            </div>
                                            <!-- Philippe Logel -->
                                            <input type="text" class="form-control timepicker form-control-sm"
                                                   id="timeCampaign"
                                                   name="timeCampaign"
                                                   value="<?= (isset($campaign['send_time'])) ? OutputUtils::change_time_for_place_holder($campaign['send_time']) : "00:00" ?>" <?= ($campaign['status'] == "schedule") ? "" : "disabled" ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= _("To validate, save your campaign with the <b>\"Save Campaign\"</b> button over.") ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

