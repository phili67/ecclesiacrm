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
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header   with-border">
                <h3 class="card-title"><i class="fa fa-newspaper-o"></i> <?= _('Email Campaign Management') ?></h3>
                <div style="float:right">
                    <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank"><img
                            src="<?= $sRootPath ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a>
                </div>
            </div>
            <div class="card-body">
                <p>
                    <button class="btn btn-app bg-blue" id="saveCampaign"
                            data-listid="<?= $list_id ?>" <?= (($campaign['status'] == "sent") ? 'disabled' : '') ?>>
                        <i class="fa fa-list-alt"></i><?= _("Save Campaign") ?>
                    </button>
                    <button id="deleteCampaign" class="btn btn-app align-right bg-maroon" data-listid="<?= $list_id ?>">
                        <i class="fa fa-trash"></i><?= _("Delete") ?>
                    </button>
                    <button id="sendCampaign"
                            class="btn btn-app align-right bg-green <?= (($campaign['status'] == "sent" || $campaign['status'] == "schedule") ? 'hidden' : '') ?>"
                            data-listid="<?= $list_id ?>">
                        <i class="fa fa-send-o"></i><?= _("Send") ?>
                    </button>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title"><?= _('Mail Subject') ?></h3>
            </div>
            <div class="card-body">
                <input type="text" id="CampaignSubject" placeholder="<?= _("Your Mail Subject") ?>" size="30"
                       maxlength="100" class="form-control input-sm" style="width: 100%"
                       value="<?= $campaign['settings']['subject_line'] ?>">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header   with-border">
                <h3 class="card-title"><i class="fa fa-tag"></i> <?= _('Tags') ?></h3>
            </div>
            <div class="card-body">
                <?= $campaign['recipients']['segment_text'] ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header   with-border">
                <?php
                if ($campaign['status'] == "schedule") {
                    ?>
                    <h3 class="card-title"><input type="checkbox" id="checkboxaCampaignSchedule"
                                                  name="checkboxaCampaignSchedule" checked> <i class="fa fa-calendar-check-o"></i> <label
                            for="checkboxaCampaignSchedule"><?= _('Schedule') ?></label></h3>
                    <?php
                } else {
                    ?>
                    <h3 class="card-title"><input type="checkbox" id="checkboxaCampaignSchedule"
                                                  name="checkboxaCampaignSchedule" <?= ($campaign['status'] == "sent" && !($campaign['status'] == "schedule")) ? "disabled" : "" ?>>
                        <i class="fa fa-calendar-times-o"></i> <label for="checkboxaCampaignSchedule"><?= _('Schedule') ?></label></h3>
                    <?php
                }
                ?>
            </div>
            <div class="card-body">
                <div class="alert alert-warning"><i class="fa fa-warning" aria-hidden="true"></i>
                    <?= _("You've first to create a content below to schedule a campaign.") ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-1"><span style="color: red">*</span>
                                <label><?= _('Date') ?> :</label>
                            </div>
                            <div class="form-group col-md-2">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    <!-- Philippe Logel -->
                                    <input class="form-control date-picker input-sm" type="text" id="dateCampaign"
                                           name="dateCampaign"
                                           value="<?= (isset($campaign[send_time])) ? OutputUtils::change_date_for_place_holder($campaign[send_time]) : "" ?>"
                                            maxlength="10" id="sel1" size="11"
                                            placeholder="<?= SystemConfig::getValue('sDatePickerPlaceHolder') ?>
                                    " <?= ($campaign['status'] == "schedule") ? "" : "disabled" ?>>
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                                    </div>
                                    <!-- Philippe Logel -->
                                    <input type="text" class="form-control timepicker input-sm" id="timeCampaign"
                                           name="timeCampaign"
                                           value="<?= (isset($campaign[send_time])) ? OutputUtils::change_time_for_place_holder($campaign[send_time]) : "00:00" ?>" <?= ($campaign['status'] == "schedule") ? "" : "disabled" ?>>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <?= _("To validate, save your campaign with the <b>\"Save Campaign\"</b> button over.") ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header   with-border">
                <h3 class="card-title"><i class="fa fa-file-text-o"></i> <?= _("Content") ?> </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12" style="padding-left:15px;padding-right:15px;">
                        <div class="alert alert-info"><i class="fa fa-info" aria-hidden="true"></i>
                            <?= _("You can use the button \"Merge Tags\" below, to customize your content") ?> : <img
                                src="<?= $sRootPath ?>/Images/merge_tags.png">.
                        </div>
                        <textarea name="campaignContent" cols="80" class="form-control input-sm campaignContent"
                                  id="campaignContent" width="100%"
                                  style="margin-top:0px;width: 100%;height: 14em;"></textarea></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isCampaignSent = <?= (($campaign['status'] == "sent") ? 1 : 0) ?>;
    window.CRM.campaign_Id = "<?= $campaignId ?>";
    window.CRM.mailchimpIsActive = <?= ($isMailchimpActiv) ? 1 : 0 ?>;
    window.CRM.list_Id = "<?= $campaign['recipients']['list_id'] ?>";
    window.CRM.status = "<?= $campaign['status'] ?>";
    window.CRM.bWithAddressPhone = <?= ($bWithAddressPhone) ? 'true' : 'false' ?>;
    window.CRM.sDateFormatLong = "<?= $sDateFormatLong ?>";

    //Timepicker
    $('.timepicker').timepicker({
        showInputs: false,
        showMeridian: (window.CRM.timeEnglish == true) ? true : false,
        minuteStep: 15
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Campaign.js"></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>
