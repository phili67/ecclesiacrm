$(document).ready(function () {
 var dialogLoading = null;

 function dialogLoadingFunction (message) {
    dialogLoading = bootbox.dialog({ message: '<div class="text-center"><i class="fa fa-spin fa-spinner"></i> ' + message + '</div>' });
 }
 
 function closeDialogLoadingFunction () {
   if (dialogLoading != null) {
      dialogLoading.modal('hide');
    }
 }
 
 function render_container ()
 {
   if (window.CRM.mailchimpIsActive) {     
      window.CRM.APIRequest({
        method: 'GET',
        path: 'mailchimp/list/' + window.CRM.list_ID
      }).done(function(data) {
        closeDialogLoadingFunction();
        
        // we set correctly the buttons
        if (data.membersCount == 0) {
          $("#CreateCampaign").prop("disabled",true);
          $("#deleteAllSubScribers").prop("disabled",true);
        } else {
          $("#CreateCampaign").prop("disabled",false);
          $("#deleteAllSubScribers").prop("disabled",false);
        }
        // we empty first the container
        $("#container").html( i18next.t( i18next.t("Loading resources ...")) );
      
        var listItems  = "";
    
        var list = data.MailChimpList;
      
        var  listView = '<div class="box-header   with-border">'
          +'      <h3 class="box-title">'+i18next.t('MailChimp List') + ' : '+ list.name + '</h3><button class="btn btn-xs btn-primary" id="modifyList" style="float:right" data-name="' + list.name + '" data-subject="' + list.campaign_defaults.subject + '">' + i18next.t('Modify Properties') + '</button>'
          +'    </div>'
          +'    <div class="box-body">'
          +'      <div class="row" style="100%">'
          +'        <div class="col-lg-6">'
          +'          <table width="350px">'
          +'            <tr><td><b>' + i18next.t('List Details') + '</b> </td><td></td></tr>'
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
          
          var lenCampaigns = data.MailChimpCampaign.length;

          listView += '          <table width="300px">';

          for (j=0;j<lenCampaigns;j++) {
            if (data.membersCount == 0) {
              listView += '<tr><td>• ' + data.MailChimpCampaign[j].settings.title + '</td></tr>';
            } else {
              listView += '<tr><td>• <a href="' + window.CRM.root + '/email/MailChimp/Campaign.php?campaignId='+ data.MailChimpCampaign[j].id + '">' + data.MailChimpCampaign[j].settings.title +'</td><td>' + ' <b><span style="color:' + ((data.MailChimpCampaign[j].status == 'sent')?'green':'gray') + '">(' + i18next.t(data.MailChimpCampaign[j].status) + ')</span></b>  </td></tr>';
            }
          }
          
          if (lenCampaigns == 0) {
            listView += '<tr><td>&nbsp;&nbsp;0 ' + i18next.t('Campaign') + '</td></tr>';
          }

          listView += '          </table>';
          
          listView += '        </div>'
          +'      </div>'
          +'    </div>';
        
          listItems += '<li><a href="' + window.CRM.root + '/email/MailChimp/ManageList.php?list_id=' + list.id + '"><i class="fa fa-circle-o"></i>'+ list.name + '</a>';
    
        $("#container").html(listView);
      });
    }
  }
  
  render_container();
// end of container


    var editor = null;

    $(document).on("click","#CreateCampaign", function(){
      if (editor != null) {
        editor.destroy(false);
        editor = null;              
      }
    
      var modal = createCampaignEditorWindow();
    
      // this will create the toolbar for the textarea
       if (editor == null) {
         editor = CKEDITOR.replace('campaignNotes',{
          customConfig: window.CRM.root+'/skin/js/ckeditor/campaign_editor_config.js',
          language : window.CRM.lang,
          width : '100%'
         });
   
         add_ckeditor_buttons(editor);
         add_ckeditor_buttons_merge_tag_mailchimp(editor);
       }
    
      modal.modal("show");
    }); 
  
    $(".person-group-Id-Share").select2({ 
        language: window.CRM.shortLocale,
        minimumInputLength: 1,
        placeholder: " -- "+i18next.t("Person or Family or Group")+" -- ",
        allowClear: true, // This is for clear get the clear button if wanted 
        ajax: {
            url: function (params){
              return window.CRM.root + "/api/mailchimp/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
              return {results: data};
            },
            cache: true
        }
    });


     $(".person-group-Id-Share").on("select2:select",function (e) { 
       var list_id=$(this).data("listid");
       
       if (e.params.data.personID !== undefined) {
           dialogLoadingFunction ( i18next.t("Loading subscriber") );
       
           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addperson',
                data: JSON.stringify({"list_id":list_id ,"personID": e.params.data.personID})
           }).done(function(data) { 
             if (data.success) {
                window.CRM.dataListTable.ajax.reload();
                render_container();
             } else if (data.error) {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                closeDialogLoadingFunction();
             }
           });
        } else if (e.params.data.groupID !== undefined) {
           dialogLoadingFunction ( i18next.t("Loading subscribers from Group") );
       
           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addgroup',
                data: JSON.stringify({"list_id":list_id ,"groupID": e.params.data.groupID})
           }).done(function(data) {
             if (data.success) {
                window.CRM.dataListTable.ajax.reload();
                render_container();
             } else if (data.error) {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                closeDialogLoadingFunction();
             }
           });
        } else if (e.params.data.familyID !== undefined) {
           dialogLoadingFunction ( i18next.t("Loading subscribers from family") );
       
           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addfamily',
                data: JSON.stringify({"list_id":list_id ,"familyID": e.params.data.familyID})
           }).done(function(data) { 
             if (data.success) {
                window.CRM.dataListTable.ajax.reload();
                render_container();
             } else if (data.error) {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                closeDialogLoadingFunction();
             }
           });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 1) {
           dialogLoadingFunction ( i18next.t("Loading all persons from EcclesiaCRM<br>This could take a while !") );

           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addallpersons',
                data: JSON.stringify({"list_id":list_id})
           }).done(function(data) { 
             if (data.success) {
                window.CRM.dataListTable.ajax.reload();
                render_container();
             } else if (data.error) {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                window.CRM.dataListTable.ajax.reload();
                render_container();
             }
           });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 2) {
           dialogLoadingFunction ( i18next.t("Loading all newsletter subscribers from EcclesiaCRM<br>This could take a while !") );

           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addallnewsletterpersons',
                data: JSON.stringify({"list_id":list_id})
           }).done(function(data) { 
             if (data.success) {
                window.CRM.dataListTable.ajax.reload();
                render_container();
             } else if (data.error) {
                //window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                window.CRM.dataListTable.ajax.reload();
                render_container();
             }
           });
        }
        
     });

// the DataTable     
  window.CRM.dataListTable = $("#memberListTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/mailchimp/listmembers/" + window.CRM.list_ID,
      type: 'GET',
      contentType: "application/json",
      dataSrc: "MailChimpMembers"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'id',
        render: function(data, type, full, meta) {
          return '<a class="edit-subscriber" data-id="'+full.email_address+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-subscriber" data-id="'+full.email_address+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
        }
      },      
      {
        width: 'auto',
        title:i18next.t('Email'),
        data:'email_address',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('First Name'),
        data:'merge_fields',
        render: function(data, type, full, meta) {
          return data.FNAME;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Last Name'),
        data:'merge_fields',
        render: function(data, type, full, meta) {
          return data.LNAME;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Email Marketing'),
        data:'status',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("duplicateRow");
    }
  });
  
  
    $(document).on("click",".edit-subscriber", function(){
      var email = $(this).data("id");
      
      bootbox.prompt({
        title: i18next.t("Select status for : ") + email,
        inputType: 'select',
        inputOptions: [
            {
                text: i18next.t('Subscribed'),
                value: 'subscribed',
            },
            {
                text: i18next.t('Unsubscribed'),
                value: 'unsubscribed',
            }
        ],
        callback: function (status) {
          if (status) {
            dialogLoadingFunction ( i18next.t("Changing status ...") );
          
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/status',
                  data: JSON.stringify({"list_id":window.CRM.list_ID ,"status": status,"email": email})
            }).done(function(data) { 
               if (data.success) {
                 window.CRM.dataListTable.ajax.reload();
                 render_container();
               } else if (data.success ==  false && data.error) {
                  closeDialogLoadingFunction();
                  window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
               }
            });
          }
        }
      });
    });

    $(document).on("click",".delete-subscriber", function(){
      var email = $(this).data("id");
      
      bootbox.confirm({
        message: i18next.t("You're about to delete a subscriber! Are you sure ?"),
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
          if (result) {
            dialogLoadingFunction( i18next.t('Deleting Subscriber...') );
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/suppress',
                  data: JSON.stringify({"list_id":window.CRM.list_ID ,"email": email})
            }).done(function(data) { 
               if (data.success) {
                 window.CRM.dataListTable.ajax.reload();
                 render_container();
               } else if (data.success ==  false && data.error) {
                  closeDialogLoadingFunction();
                  window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
               }
            });
          }
        }
      });
    });
    
    $(document).on("click","#deleteList", function(){
      var list_id = $(this).data("listid");
      
      bootbox.confirm({
        message: i18next.t("Do you really want to delete this mailing list ?"),
        buttons: {
            confirm: {
                label: i18next.t('Yes'),
                className: 'btn-danger'
            },
            cancel: {
                label: i18next.t('No'),
                className: 'btn-primary'
            }
        },
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/deletelist',
                  data: JSON.stringify({"list_id":window.CRM.list_ID})
            }).done(function(data) { 
               if (data.success) {
                 window.location.href = window.CRM.root + "/email/MailChimp/Dashboard.php";
               } else if (data.error) {
                 window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
               }
            });
          }
        }
      });
    });
    
    $(document).on("click","#deleteAllSubScribers", function(){
      var list_id = $(this).data("listid");
      
      bootbox.confirm({
        message: i18next.t("Are you sure you want to delete all the subscribers"),
        buttons: {
            confirm: {
                label: i18next.t('Yes'),
                className: 'btn-danger'
            },
            cancel: {
                label: i18next.t('No'),
                className: 'btn-primary'
            }
        },
        callback: function (result) {
          if (result) {
            dialogLoadingFunction( i18next.t('Deleting all subscribers...') );
            
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/deleteallsubscribers',
                  data: JSON.stringify({"list_id":window.CRM.list_ID})
            }).done(function(data) { 
               if (data.success) {
                 window.CRM.dataListTable.ajax.reload();
                 render_container();
               }
            });
          }
        }
      });
    });

  function BootboxContent(){  
    
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("MailChimp Campaign Creation")+'</h3><form id="some-form">'
       + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Campaign Title') + ":</div>"
              +'<div class="col-md-9">'
                +"<input type='text' id='CampaignTitle' placeholder='" + i18next.t("Your Campaign Title") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Mail Subject') + ":</div>"
              +'<div class="col-md-9">'
                +"<input type='text' id='Subject' placeholder='" + i18next.t("Your Mail Subject") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
              +'</div>'
            +'</div>'
            +'<div class="row  eventNotes">'
              +'<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
                  +'<textarea name="CampaignText" cols="80" class="form-control input-sm campaignNotes" id="campaignNotes"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
              +'</div>'
            +'</div>'
          +'</div>'
       + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    function createCampaignEditorWindow ()
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
              var campaignTitle =  $('form #CampaignTitle').val();
              
              if (campaignTitle) {
                  var Subject      = $('form #Subject').val();
                  var htmlBody     = CKEDITOR.instances['campaignNotes'].getData();//$('form #campaignNotes').val();
                  
                  
                  dialogLoadingFunction ( i18next.t("Adding Campaign ...") );

                  window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/campaign/actions/create',
                        data: JSON.stringify({"list_id":window.CRM.list_ID, "subject":Subject, "title" : campaignTitle,"htmlBody" : htmlBody})
                  }).done(function(data) { 
                     if (data.success) {
                       render_container();
                     }
                  });
              } else {
                  window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("You have to set a Campaign Title for your eMail Campaign"));
                
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
  
  
  $(document).on("click","#modifyList", function(){
    var name    = $(this).data('name');
    var subject = $(this).data('subject');
    
    bootbox.confirm('<form id="infos" action="#">'
      + i18next.t('List Name') + ':<input type="text" class="form-control" id="list_name" value="' + name +'"/><br/>'
      + i18next.t('Subject') + ':<input type="text" class="form-control" id="list_subject" value="' + subject + '"/>'
      + '</form>', function(result) {
          if(result) {
            name    = $("#list_name").val();
            subject = $("#list_subject").val();
            
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/modifylist',
                  data: JSON.stringify({"list_id":window.CRM.list_ID, "name" : name, "subject":subject})
            }).done(function(data) { 
               if (data.success) {
                 render_container();
                 $( '.listName' + window.CRM.list_ID ).html('<i class="fa fa-circle-o"></i>' + name );
               }
            });
            
          }
    });
  });

});