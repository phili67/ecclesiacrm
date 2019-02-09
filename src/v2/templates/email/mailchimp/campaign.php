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

require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= _('Manage Mailing List') ?></h3>
        <div style="float:right">
          <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank"><img src="<?= $sRootPath ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a>
        </div>
      </div>
      <div class="box-body">
        <p>
          <button class="btn btn-app bg-blue" id="saveCampaign" data-listid="<?= $list_id ?>" <?= (($campaign['status'] == "sent")?'disabled':'') ?>>
            <i class="fa fa-list-alt"></i><?= _("Save Campaign") ?>
          </button>
          <button id="deleteCampaign" class="btn btn-app align-right bg-maroon" data-listid="<?= $list_id ?>">
            <i class="fa fa-trash"></i><?= _("Delete") ?>
          </button>
          <button id="sendCampaign" class="btn btn-app align-right bg-green" data-listid="<?= $list_id ?>" <?= (($campaign['status'] == "sent")?'disabled':'') ?>>
            <i class="fa fa-send-o"></i><?= _("Send") ?>
          </button>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= _('Mail Subject') ?></h3>
      </div>
      <div class="box-body">
        <input type="text" id="CampaignSubject" placeholder="<?= _("Your Mail Subject") ?>" size="30" maxlength="100" class="form-control input-sm" style="width: 100%" value="<?= $campaign['settings']['subject_line'] ?>">
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12" style="padding-left:15px;padding-right:15px;">
    <textarea name="campaignContent" cols="80" class="form-control input-sm campaignContent" id="campaignContent"  width="100%" style="margin-top:0px;width: 100%;height: 14em;"></textarea></div>
  </div>
</div>


<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.campaign_Id       = "<?= $campaignId ?>";
  window.CRM.mailchimpIsActive = <?= ($isMailchimpActiv)?1:0 ?>;
  window.CRM.list_Id           = "<?= $campaign['recipients']['list_id'] ?>";
</script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Campaign.js"></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>