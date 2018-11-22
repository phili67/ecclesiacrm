$(document).ready(function () {
  function render_container ()
   {
     if (window.CRM.mailchimpIsActive) {
        window.CRM.APIRequest({
          method: 'GET',
          path: 'mailchimp/lists'
        }).done(function(data) {
          var len = data.MailChimpLists.length;
    
          // we empty first the container
          $("#container").html( i18next.t("Loading resources ...") );
      
          // now we empty the menubar lists
          var lists_menu = $(".lists_class_menu").parent();
          var real_listMenu = $( lists_menu ).find (".treeview-menu");
      
          real_listMenu.html("");
    
          var listViews  = "";
          var listItems  = "";

          for (i=0;i<len;i++) {
            var list = data.MailChimpLists[i];
      
            listViews += '<div class="box">'
            +'    <div class="box-header   with-border">'
            +'      <h3 class="box-title">'+i18next.t('MailChimp List') + ' : '+ list.name + '</h3> <a href="'+ window.CRM.root + '/email/MailChimp/ManageList.php?list_id='+ list.id + '" style="float:right"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span></a>'
            +'    </div>'
            +'    <div class="box-body">'
            +'      <div class="row" style="100%">'
            +'        <div class="col-lg-5">'
            +'          <table width="350px">'
            +'            <tr><td><b>' + i18next.t('Details') + '</b> </td><td></td></tr>'
            +'            <tr><td>' + i18next.t('Subject') + '</td><td>"' + list.campaign_defaults.subject + '"</td></tr>'
            +'            <tr><td>' + i18next.t('Members:') + '</td><td>' + list.stats.member_count + '</td></tr>'
            //+'            <tr><td>' + i18next.t('Campaigns:') + '</td><td>' + list.stats.campaign_count + '</td></tr>'
            +'            <tr><td>' + i18next.t('Unsubscribed count:') + '</td><td>' + list.stats.unsubscribe_count + '</td></tr>'
            +'            <tr><td>' + i18next.t('Unsubscribed count since last send:') + '</td><td>' + list.stats.unsubscribe_count_since_send + '</td></tr>'
            +'            <tr><td>' + i18next.t('Cleaned count:') + '</td><td>' + list.stats.cleaned_count + '</td></tr>'
            +'            <tr><td>' + i18next.t('Cleaned count since last send:') + '</td><td>' + list.stats.cleaned_count_since_send + '</td></tr>'
            +'          </table>'
            +'        </div>'
            +'        <div class="col-lg-3">'
            +'           <b>' + i18next.t('Campaigns') + '</b><br>';
          
            var lenCampaigns = data.MailChimpCampaigns[i].length;

            listViews += '          <table width="300px">';

            for (j=0;j<lenCampaigns;j++) {
              listViews += '<tr><td>• <a href="' + window.CRM.root + '/email/MailChimp/Campaign.php?campaignId='+ data.MailChimpCampaigns[i][j].id + '">' + data.MailChimpCampaigns[i][j].settings.title +'</td><td>' + ' <b><span style="color:' + ((data.MailChimpCampaigns[i][j].status == 'sent')?'green':'gray') + '">(' + i18next.t(data.MailChimpCampaigns[i][j].status) + ')</span></b>  </td></tr>';
            }
          
            if (lenCampaigns == 0) {
              listViews += '<tr><td>&nbsp;&nbsp;0 ' + i18next.t('Campaign') + '</td></tr>';
            }

            listViews += '          </table>';
          
            listViews += '        </div>'
            +'      </div>'
            +'    </div>'
            +'  </div>';
        
            listItems += '<li><a href="' + window.CRM.root + '/email/MailChimp/ManageList.php?list_id=' + list.id + '"><i class="fa fa-circle-o"></i>'+ list.name + '</a>';
          }
    
          $("#container").html(listViews);
          real_listMenu.html(listItems);
        });
      } else {
        var container = '<div class="row">'
          +'<div class="col-lg-12">'
          +'  <div class="box box-body">'
          +'    <div class="alert alert-danger alert-dismissible">'
          +'      <h4><i class="fa fa-ban"></i> MailChimp ' + i18next.t('is not configured') + '</h4>'
          +'      ' + i18next.t('Please update the') + ' MailChimp ' + i18next.t('API key in Setting->') + '<a href="../../SystemSettings.php">' + i18next.t('Edit General Settings') + '</a>,'
          +'      ' + i18next.t('then update') + ' sMailChimpApiKey. ' + i18next.t('For more info see our ') + '<a href="<?= SystemURLs::getSupportURL() ?>"> MailChimp +' + i18next.t('support docs.') + '</a>'
          +'    </div>'
          +'  </div>'
          +'</div>'
          +'</div>';
        
        $("#container").html(container);
      }
    }
  
    render_container();
  
    // the List Creator
    function BootboxContent(){
      var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("List Creation")+'</h3><form id="some-form">'
         + '<div>'
              +'<div class="row div-title">'
                +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('List Title') + ":</div>"
                +'<div class="col-md-9">'
                  +"<input type='text' id='ListTitle' placeholder='" + i18next.t("Your List Title") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
                +'</div>'
              +'</div>'
              +'<div class="row div-title">'
                +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Subject') + ":</div>"
                +'<div class="col-md-9">'
                  +"<input type='text' id='Subject' placeholder='" + i18next.t("Your Subject") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
                +'</div>'
              +'</div>'
              +'<div class="row div-title">'
                +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Permission Reminder') + ":</div>"
                +'<div class="col-md-9">'
                  +"<textarea id='PermissionReminder' rows='3' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required placeholder='" + i18next.t("Permission Reminder") + "'></textarea>"
                +'</div>'
              +'</div>'
              +'<div class="row div-title">'
                +'<div class="col-md-5">'
                   +'<div class="checkbox">'
                     +'<label>'
                      +'<input type="checkbox" id="ArchiveBars"> '+ i18next.t('Archive Bars')
                    +'</label>'
                  +'</div>'
                +'</div>'
              +'</div>'
              +'<div class="row  div-title">'
                +'<div class="status-event-title">'
                  +'<span style="color: red">*</span>'+i18next.t('Status')
                +'</div>'
                +'<div class="status-event">'
                  +'<input type="radio" name="Status" value="prv" checked/> '+i18next.t('Private')
                +'</div>'
                +'<div class="status-event">'
                  +'<input type="radio" name="Status" value="pub" /> '+i18next.t('Public')
                +'</div>'
              +'</div>'
            +'</div>'
         + '</form>';

          var object = $('<div/>').html(frm_str).contents();

          return object
      }
    
      function createListEditorWindow ()
      {
      
        var modal = bootbox.dialog({
           message: BootboxContent(),
           buttons: [
            {
             label: i18next.t("Close"),
             className: "btn btn-default",
             callback: function() {
                console.log("just do something on close");
             }
            },
            {
             label: i18next.t("Save"),
             className: "btn btn-primary",
             callback: function() {
                var ListTitle =  $('form #ListTitle').val();
              
                if (ListTitle) {
                    var Subject      = $('form #Subject').val();
                    var PermReminder = $('form #PermissionReminder').val();
                    var ArchiveBars  = $('#ArchiveBars').is(":checked");
                    var Status       = $('input[name="Status"]:checked').val();

                    window.CRM.APIRequest({
                      method: 'POST',
                      path: 'mailchimp/createlist',
                      data: JSON.stringify({"ListTitle": ListTitle,"Subject" : Subject, "PermissionReminder":PermReminder,"ArchiveBars":ArchiveBars,"Status":Status})
                    }).done(function(data) {
                      if (data.success) {
                         render_container();
                         modal.modal("hide");
                      } else if (data.error) {
                        window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                      }
                    });

                    return add;  
                } else {
                    window.CRM.DisplayAlert("Error","You have to set a List Title for your eMail List");
                
                    return false;
                }    
              }
            }
           ],
           show: false/*,
           onEscape: function() {
              modal.modal("hide");
           }*/
         });
       
         // this will ensure that image and table can be focused
         $(document).on('focusin', function(e) {e.stopImmediatePropagation();});       
              
         return modal;
      }
    
    
    $(document).on("click","#CreateList", function(){
      var modal = createListEditorWindow();
    
      modal.modal("show");
    });  
});
