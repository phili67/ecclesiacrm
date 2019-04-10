$(document).ready(function () {
  window.CRM.editor = null;

  $(document).on("click","#createDocument", function(){
    if (window.CRM.editor) {
       CKEDITOR.remove(window.CRM.editor);
       window.CRM.editor = null;
    }
  
    var modal = DocumentEditorWindow('create',0);

    // this will create the toolbar for the textarea
    if (window.CRM.editor == null) {
       window.CRM.editor = CKEDITOR.replace('documentText',{
        customConfig: window.CRM.root+'/skin/js/ckeditor/configs/note_editor_config.js',
        language : window.CRM.lang,
        width : '100%',
        extraPlugins : 'uploadfile,uploadimage,filebrowser',
        uploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
        imageUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicImages',
        filebrowserUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
        filebrowserBrowseUrl: window.CRM.root+'/browser/browse.php?type=publicDocuments'
       });
 
       add_ckeditor_buttons(window.CRM.editor);
       add_ckeditor_buttons_merge_tag_mailchimp(window.CRM.editor);
    }
  
    modal.modal("show");
  }); 
  
  $(document).on("click",".editDocument", function(){
    var perID  = $(this).data('perid');
    var docID  = $(this).data('id');
    
    window.CRM.APIRequest({
      method: 'POST',
      path: 'document/create',
      data: JSON.stringify({"docID" : docID, "personID" : perID, "famID" : 0})
    }).done(function(data) {
      if (window.CRM.editor) {
         CKEDITOR.remove(window.CRM.editor);
         window.CRM.editor = null;
      }
  
      var modal = DocumentEditorWindow('edit',docID);

      // this will create the toolbar for the textarea
      if (window.CRM.editor == null) {
         window.CRM.editor = CKEDITOR.replace('documentText',{
          customConfig: window.CRM.root+'/skin/js/ckeditor/configs/note_editor_config.js',
          language : window.CRM.lang,
          width : '100%',
          extraPlugins : 'uploadfile,uploadimage,filebrowser',
          uploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
          imageUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicImages',
          filebrowserUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
          filebrowserBrowseUrl: window.CRM.root+'/browser/browse.php?type=publicDocuments'
         });
 
         add_ckeditor_buttons(window.CRM.editor);
         add_ckeditor_buttons_merge_tag_mailchimp(window.CRM.editor);
      }
  
      modal.modal("show");
      
      $('#documentTitle').val(data.note.Title);
      $("#documentType").val(data.note.Type);
      $("#private").prop("checked", data.note.Private);
      CKEDITOR.instances['documentText'].setData(data.note.Text);
    });
                
    
  });
  
  

  function BootboxContent(sTitleText, sDocType, sText){  
    
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Document Editor")+'</h3>'
      +'<form id="some-form">'
       + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Document Title') + ":</div>"
              +'<div class="col-md-9">'
                +'<input type="text" id="documentTitle" placeholder="' + i18next.t("Set your Document title") + '" size="30" maxlength="100" class="form-control input-sm"  width="100%" style="width: 100%" required>'
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Choose your Document Type') + ":</div>"
              +'<div class="col-md-9">'
              +'  <select name="documentType" class="form-control input-sm" id="documentType">'
              +'     <option value="note" selected>' + i18next.t("document") + '</option>'
              +'     <option value="video">' + i18next.t("video") + '</option>'
              +'     <option value="audio">' + i18next.t("audio") + '</option>'
              +'  </select>'
              +'</div>'
            +'</div>'
            +'<div class="row  eventNotes">'
              +'<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
                  +'<textarea name="documentText" cols="80" class="form-control input-sm" id="documentText"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
              +'</div>'
            +'</div>'
            +'<div class="row  eventNotes">'
              +'<div class="col-md-12">'
              +'   <center><input type="checkbox" value="1" id="private" name="private" echo "checked">&nbsp;<label for="private">'+ i18next.t('Private')+ '</label></center>'
              +'</div>'
          +'</div>'
       + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }
    
    function DocumentEditorWindow (mode,docID)
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
              var DocumentTitle = $('#documentTitle').val();
              
              if (DocumentTitle != "") {
                var Type     = $("#documentType").val();
                var Private  = $('#private').is(':checked');
                var htmlBody = CKEDITOR.instances['documentText'].getData();
                
                if (mode == 'create') {
                  window.CRM.APIRequest({
                    method: 'POST',
                    path: 'document/create',
                    data: JSON.stringify({"personID" : window.CRM.currentPersonID, "famID" : window.CRM.currentFamily, "title" : DocumentTitle, "type" : Type,"text" : htmlBody, "bPrivate" : Private})
                  }).done(function(data) {
                    if (data.success) {
                      location.href = window.CRM.root + '/PersonView.php?PersonID=' + window.CRM.currentPersonID + '&documents=true';
                    }
                  });
                } else if (mode == 'edit') {
                  window.CRM.APIRequest({
                    method: 'POST',
                    path: 'document/update',
                    data: JSON.stringify({"docID" : docID,"personID" : window.CRM.currentPersonID, "famID" : window.CRM.currentFamily, "title" : DocumentTitle, "type" : Type,"text" : htmlBody, "bPrivate" : Private})
                  }).done(function(data) {
                    if (data.success) {
                      location.href = window.CRM.root + '/PersonView.php?PersonID=' + window.CRM.currentPersonID + '&documents=true';
                    }
                  });
                }
              } else {
                  window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("You have to set a Title for your document"));
                
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
});