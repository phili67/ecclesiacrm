<?php
/*******************************************************************************
 *
 *  filename    : Dashboard.php
 *  last change : 2014-11-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2014
 *
 ******************************************************************************/

require '../../Include/Config.php';
require '../../Include/Functions.php';

use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\dto\SystemURLs;

if (!($_SESSION['user']->isMailChimpEnabled())) {
    Redirect('Menu.php');
    exit;
}

if ( !isset($_GET['campaignId']) ) {
    Redirect('Menu.php');
    exit;
}

$campaign_Id = $_GET['campaignId'];

$mailchimp = new MailChimpService();

$campaign = $mailchimp->getCampaignFromId($campaign_Id);

//Set the page title
$sPageTitle = _('Manage Campaign').' : '.$campaign['settings']['title']." <b><span style=\"color:".(($campaign['status'] == "sent")?'green':'gray')."\">("._($campaign['status']).")</span></b>";

require '../../Include/Header.php';

?>

<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= _('Manage Mailing List') ?></h3><div style="float:right"><a href="https://mailchimp.com/en/"><img src="<?= SystemURLs::getRootPath() ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a></div>
      </div>
      <div class="box-body">
        <p>
          <button class="btn btn-app bg-blue" id="saveCampaign" data-listid="<?= $list_id ?>">
            <i class="fa fa-list-alt"></i><?= _("Save Campaign") ?>
          </button>
          <button id="deleteCampaign" class="btn btn-app align-right bg-maroon" data-listid="<?= $list_id ?>">
            <i class="fa fa-trash"></i><?= _("Delete") ?>
          </button>
          <button id="sendCampaign" class="btn btn-app align-right bg-green" data-listid="<?= $list_id ?>">
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


<?php
require '../../Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.campaign_Id = "<?= $campaign_Id ?>";
  window.CRM.mailchimpIsActive = <?= ($mailchimp->isActive())?1:0 ?>;
  window.CRM.list_Id = "<?= $campaign['recipients']['list_id'] ?>";


  $(document).ready(function () {
    var editor = null;
  
  
    // this will create the toolbar for the textarea
    if (editor == null) {
       editor = CKEDITOR.replace('campaignContent',{
        customConfig: window.CRM.root+'/skin/js/ckeditor/campaign_editor_config.js',
        language : window.CRM.lang,
        width : '100%'
       });
   
       add_ckeditor_buttons(editor);
    }
    
    window.CRM.APIRequest({
          method: 'GET',
          path: 'mailchimp/campaign/'+ window.CRM.campaign_Id +'/content'
    }).done(function(data) { 
       if (data.success) {
         editor.setData(data.content);
       } else if (data.error) {
         window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
       }
    });
    
    $(document).on("click","#saveCampaign", function(){
      var subject = $("#CampaignSubject").val();
      var content = CKEDITOR.instances['campaignContent'].getData();
      
      window.CRM.APIRequest({
        method: 'POST',
        path: 'mailchimp/campaign/actions/save',
        data: JSON.stringify({"campaign_id" : window.CRM.campaign_Id,"subject" : subject, "content" : content})
      }).done(function(data) {
         if (data.success == true) {
           window.CRM.DisplayAlert(i18next.t("Campaign"),i18next.t("saved successfully"));
         } else if (data.success == false && data.error) {
           window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
         }
      });
    });

    $(document).on("click","#sendCampaign", function(){
      
      bootbox.confirm({
        message: i18next.t("You're about to send your campaign! Are you sure ?"),
        buttons: {
            confirm: {
                label: i18next.t('Yes'),
                className: 'btn-success'
            },
            cancel: {
                label: i18next.t('No'),
                className: 'btn-default'
            }
        },
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/campaign/actions/send',
                  data: JSON.stringify({"campaign_id":window.CRM.campaign_Id})
            }).done(function(data) { 
               if (data.success) {
                 window.location.href = window.CRM.root + "/email/MailChimp/ManageList.php?list_id=" + window.CRM.list_Id;
               } else if (data.success == false && data.error) {
                 window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
               }
            });
          }
        }
      });
    });    

});  
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditorextension.js"></script>