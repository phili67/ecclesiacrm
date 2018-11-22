
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
       add_ckeditor_buttons_merge_tag_mailchimp(editor);
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
    
    $(document).on("click","#deleteCampaign", function(){
      
      bootbox.confirm({
        message: i18next.t("You're about to delete your campaign! Are you sure ?"),
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
                  path: 'mailchimp/campaign/actions/delete',
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
