$(document).ready(function () {
  
  // EDrive
  var selected     = [];// the selected rows
  var uploadWindow = null;
  var oldTextField = null;

  
  window.CRM.dataEDriveTable = $("#edrive-table").DataTable({
    ajax:{
      url: window.CRM.root + "/api/filemanager/" + window.CRM.currentPersonID,
      type: 'POST',
      contentType: "application/json",
      dataSrc: "files"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    searching: true,
    select: true,
    columns: [
      {
        width: '5%',
        title:i18next.t('Icon'),
        data:'icon',
        render: function(data, type, full, meta) {
          if (!full.dir) {
            return '<span class="drag drag-file" id="' + full.name + '" type="file" data-path="' + full.path + '" data-perid="' + full.perID + '">' + data + '</span>';
          } else {
            return '<a class="change-folder" data-personid="' + window.CRM.currentPersonID + '" data-folder="' + full.name + '"><span class="drag drop" id="'+ full.name +'" type="folder">' + data + '</span>';
          }
        }
      },
      {
        width: '50%',
        title:i18next.t('Name'),
        data:'name',
        type : 'column-name',
        render: function(data, type, full, meta) {
          if (full.dir) {
            var fileName = data.substring(1);
            
            return '<input type="text" value="' + fileName + '" class="fileName" data-name="' + data + '" data-type="folder" readonly>';
          } else {
            var fileName = data;
            fileName = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
            
            return '<input type="text" value="' + fileName + '" class="fileName" data-name="' + data + '" data-type="file" readonly>';
          }
        }
      },
      {
        width: '5%',
        title:i18next.t('Actions'),
        data:'id',
        render: function(data, type, full, meta) {
          if (!full.dir) {
            var ret = '';
            
            ret  += '<a href="' + window.CRM.root + '/api/filemanager/getFile/' + full.perID + '/' + full.path + '">'
                 + '<span class="fa-stack">'
                 + '   <i class="fa fa-square fa-stack-2x" style="color:blue"></i>'
                 + '   <i class="fa fa-download fa-stack-1x fa-inverse"></i>'
                 + '</span>'
                 + '</a>';

            ret  += '<span class="fa-stack shareFile" data-id="'+data+'" data-shared="'+full.isShared+'">'
                 + '   <i class="fa fa-square fa-stack-2x" style="color:'+((full.isShared)?'green':'#777')+'"></i>'
                 + '   <i class="fa fa-share-square-o fa-stack-1x fa-inverse"></i>'
                 + '</span>';
                 
            return ret;
          }
          
          return '';
        }
      },
      {
        width: '15%',
        title:i18next.t('Modification Date'),
        data:'date',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: '10%',
        title:i18next.t('Type'),
        data:'type',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: '10%',
        title:i18next.t('Size'),
        data:'size',
        type : 'file-size',
        render: function(data, type, full, meta) {
          return data
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("edriveRow");
      //$(row).attr('id', data.id)
      $(row).attr('id', data.name);
    },
    "rowCallback": function( row, data ) {
        if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
          $(row).addClass('selected');
        }
    },
    "initComplete": function(settings, json) {
      installDragAndDrop();
    }
  });

  
 $('#edrive-table tbody').on('click', 'td', function (e) {
 
  var id    = $(this).parent().attr('id');
  var col  = window.CRM.dataEDriveTable.cell( this ).index().column;
  
  if ( !(col == 2) ) {
    if (!e.shiftKey) {
      selected.length = 0;// no lines
      $('#edrive-table tbody tr').removeClass('selected');
    }
    
    var index = $.inArray(id, selected);
  
    if ( index === -1 ) {
        selected.push( id );
    } else {
        selected.splice( index, 1 );
    }

    $(this).parent().toggleClass('selected');
  
    var selectedRows = window.CRM.dataEDriveTable.rows('.selected').data().length;
  
    if (selectedRows == 0) {
      selected.length = 0;// no lines
    }
    
    if ( !(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) && selectedRows == 1) {
     
      window.CRM.APIRequest({
        method: 'POST',
        path: 'filemanager/getPreview',
        data: JSON.stringify({"personID": window.CRM.currentPersonID,"name" : id})
      }).done(function(data) {
        if (data && data.success) {
          $('.filmanager-left').removeClass( "col-md-12" ).addClass( "col-md-9" );
          $('.filmanager-right').show();
          $('.preview').html(data.path);
        }
      });
    } else {
      $('.preview').html('');
    }
  }
  
});
  

$("body").on('click', '.fileName', function(e) {
    if ( (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) ) {
          // we're on a SmartPhone
      var oldName  = $(this).data("name");
      var fileName = '';
      
      if (oldName[0] == '/') {
        fileName = oldName.substring(1);
      } else {
        fileName = oldName.substring(0, oldName.lastIndexOf('.')) || oldName;
      }

      var type    = $(this).data("type");

      bootbox.prompt({
        title : i18next.t("Set a File/Folder name"),
        value : fileName,
        callback: function(result){ 
          if (result != '') {
            window.CRM.APIRequest({
              method: 'POST',
              path: 'filemanager/rename',
              data: JSON.stringify({"personID": window.CRM.currentPersonID,"oldName" : oldName, "newName" : result, "type" : type})
            }).done(function(data) {
              if (data && data.success) {
                window.CRM.dataEDriveTable.ajax.reload(function(json) {
                 installDragAndDrop();
                });
              }
            });
          }
        }
      });
    }
});

$("body").on('dblclick', '.drag-file', function(e) {
    if ( !(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) ) {
      var perID = $(this).data("perid");
      var path  = $(this).data("path");
      
      window.location.href  = window.CRM.root + '/api/filemanager/getFile/' + perID + '/' + path;
    }
});
  
$("body").on('dblclick', '.fileName', function(e) {
    if ( !(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) ) {
      // we're on a computer
        if (oldTextField != null) {
            $(oldTextField).css("background", "transparent");
            $(oldTextField).attr('readonly');
        }
  
        $(this).css("background", "white");
        $(this).removeAttr('readonly');
  
        oldTextField = this;
    }
});

$("body").on('click', '.close-file-preview', function(e) {
  $('.filmanager-left').removeClass( "col-md-9" ).addClass( "col-md-12" );
  $('.filmanager-right').hide();
});


$("body").on('keypress', '.fileName', function(e) {
  var key  = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
  var newName  = $(this).val();
  var oldName = $(this).data("name");
  var type = $(this).data("type");
  
  switch (key) {
    case 13:// return
      window.CRM.APIRequest({
        method: 'POST',
        path: 'filemanager/rename',
        data: JSON.stringify({"personID": window.CRM.currentPersonID,"oldName" : oldName, "newName" : newName, "type" : type})
      }).done(function(data) {
        if (data && data.success) {
          window.CRM.dataEDriveTable.ajax.reload(function(json) {
            installDragAndDrop();
          });
        }
      });
      break;
    case 27:// ESC
      var fileName = oldName;
      
      if ( type == 'file') {
        fileName = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
      } else {
        fileName = fileName.substring(1);
      }
      
      $(this).attr('readonly');
      $(this).css("background", "transparent");
      $(this).val(fileName);
      oldTextField = null;
      break;
  }
});
  
  $('.trash-drop').click (function () {
      if (selected.length) {
        bootbox.confirm({
          title  : i18next.t("You're about to remove a folder and it's content"),
          message: i18next.t("This can't be undone !!!!"),
          buttons: {
            confirm: {
              label: i18next.t('Yes'),
                className: 'btn-success'
            },
            cancel: {
              label: i18next.t('No'),
              className: 'btn-danger'
            }
          },
          callback: function (result)
          {
            if (result)
            {
              window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/deleteFiles',
                data: JSON.stringify({"personID": window.CRM.currentPersonID,"files" : selected})
              }).done(function(data) {
                if (data && data.success) {
                  window.CRM.dataEDriveTable.ajax.reload(function(json) {
                    installDragAndDrop();
                    selected.length=0;
                  });
                }
              });
            }
          }
        });
      } else {
        window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("You've to select at least one line !!!"));
      }
  });

  $('.trash-drop').droppable({
    drop : function(event,ui){
      var len = selected.length;
      
      if (len > 1) {
        bootbox.confirm({
          title  : i18next.t("You're about to remove a folder and it's content"),
          message: i18next.t("This can't be undone !!!!"),
          buttons: {
            confirm: {
              label: i18next.t('Yes'),
                className: 'btn-success'
            },
            cancel: {
              label: i18next.t('No'),
              className: 'btn-danger'
            }
          },
          callback: function (result)
          {
            if (result)
            {
              window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/deleteFiles',
                data: JSON.stringify({"personID": window.CRM.currentPersonID,"files" : selected})
              }).done(function(data) {
                if (data && data.success) {
                  window.CRM.dataEDriveTable.ajax.reload(function(json) {
                    installDragAndDrop();
                    selected.length=0;
                  });
                }
              });
            }
          }
        });
        
        return;
      }
      
      var name = $(ui.draggable).attr('id');
      var type = $(ui.draggable).attr('type');
      
      if (type == 'folder') {
        bootbox.confirm({
          title  : i18next.t("You're about to remove a folder and it's content"),
          message: i18next.t("This can't be undone !!!!"),
          buttons: {
            confirm: {
              label: i18next.t('Yes'),
                className: 'btn-success'
            },
            cancel: {
              label: i18next.t('No'),
              className: 'btn-danger'
            }
          },
          callback: function (result)
          {
            if (result)
            {
              window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/deleteFiles',
                data: JSON.stringify({"personID": window.CRM.currentPersonID,"files" : [name]})
              }).done(function(data) {
                if (data && data.success) {
                  window.CRM.dataEDriveTable.ajax.reload(function(json) {
                    installDragAndDrop();
                    selected.length=0;
                  });
                }
              });
            }
          }
        });
      } else {// in the case of a file
        bootbox.confirm({
          title  : i18next.t("You're about to remove a file"),
          message: i18next.t("This can't be undone !!!!"),
          buttons: {
            confirm: {
              label: i18next.t('Yes'),
                className: 'btn-success'
            },
            cancel: {
              label: i18next.t('No'),
              className: 'btn-danger'
            }
          },
          callback: function (result)
          {
            if (result)
            {
              window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/deleteFiles',
                data: JSON.stringify({"personID": window.CRM.currentPersonID,"files" : [name]})
              }).done(function(data) {
                if (data && data.success) {
                  window.CRM.dataEDriveTable.ajax.reload(function(json) {
                    installDragAndDrop();
                    selected.length=0;
                  });
                }
              });
            }
          }
        });
      }
    }
  });
  
  $('.folder-back-drop').droppable({

    drop : function(event,ui){
      var name = $(ui.draggable).attr('id');
      var folderName = '/..';
      
      if (selected.length > 0) {// Drag in a folder
        window.CRM.APIRequest({
          method: 'POST',
          path: 'filemanager/movefiles',
          data: JSON.stringify({"personID": window.CRM.currentPersonID,"folder" : folderName,"files":selected})
        }).done(function(data) {
          if (data && !data.success) {
            window.CRM.DisplayAlert(i18next.t("Error"),data.message);
          }
          
          window.CRM.dataEDriveTable.ajax.reload(function(json) {
            installDragAndDrop();
            selected.length=0;
          });
        });
      } else {
        window.CRM.APIRequest({
          method: 'POST',
          path: 'filemanager/movefiles',
          data: JSON.stringify({"personID": window.CRM.currentPersonID,"folder" : folderName,"files":[name]})
        }).done(function(data) {
          if (data && !data.success) {
            window.CRM.DisplayAlert(i18next.t("Error"),data.message);
          }
          
          window.CRM.dataEDriveTable.ajax.reload(function(json) {
            installDragAndDrop();
            selected.length=0;
          });
        });
      }
    }

  });  

  function installDragAndDrop()
  {
    $('.drag').draggable({
       helper: 'clone',
       appendTo: 'body',
       zIndex: 1100
       //revert : true
    });

    $('.drop').droppable({
      drop : function(event,ui){
        var name = $(ui.draggable).attr('id');
        var folderName = $(event.target).attr('id');
        
        if (selected.length > 0) {// Drag in a folder
          window.CRM.APIRequest({
            method: 'POST',
            path: 'filemanager/movefiles',
            data: JSON.stringify({"personID": window.CRM.currentPersonID,"folder" : folderName,"files":selected})
          }).done(function(data) {
            if (data && !data.success) {
              window.CRM.DisplayAlert(i18next.t("Error"),data.message);
            }
          
            window.CRM.dataEDriveTable.ajax.reload(function(json) {
              installDragAndDrop();
              selected.length=0;
            });
          });
        } else {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'filemanager/movefiles',
            data: JSON.stringify({"personID": window.CRM.currentPersonID,"folder" : folderName,"files":[name]})
          }).done(function(data) {
            if (data && !data.success) {
              window.CRM.DisplayAlert(i18next.t("Error"),data.message);
            }
          
            window.CRM.dataEDriveTable.ajax.reload(function(json) {
              installDragAndDrop();
              selected.length=0;
            });
          });
        }
      }
    });
  }
  
  function openFolder (personID,folder) {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'filemanager/changeFolder',
      data: JSON.stringify({"personID": personID,"folder" : folder})
    }).done(function(data) {
      if (data && data.success) {
        window.CRM.dataEDriveTable.ajax.reload(function(json) {
          installDragAndDrop();
          $(".folder-back-drop").show();
          $("#currentPath").html(data.currentPath);
          selected.length = 0;// no more selected files
        });
      }
    });
  }
  
  $(document).on('click','.change-folder',function () {
    if ( (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) ) {
      var personID = $(this).data("personid");
      var folder   = $(this).data("folder");
      
      openFolder (personID,folder);
    }
  });
  
  $(document).on('dblclick','.change-folder',function () {
  //$(".change-folder").click (function () {
    var personID = $(this).data("personid");
    var folder   = $(this).data("folder");
    
    if ( !(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) ) {
      var personID = $(this).data("personid");
      var folder   = $(this).data("folder");
      
      openFolder (personID,folder);
    }
  });
  
  $(".new-folder").click (function () {
    var personID = $(this).data("personid");
    
    bootbox.prompt(i18next.t("Set your Folder name"), function(result){ 
      if (result != '') {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'filemanager/newFolder',
          data: JSON.stringify({"personID": personID,"folder" : result})
        }).done(function(data) {
          if (data && !data.success) {
            window.CRM.DisplayAlert(i18next.t("Error"),data.message);
          }
        
          window.CRM.dataEDriveTable.ajax.reload(function(json) {
            installDragAndDrop();
            selected.length=0;
          });
        });
      }
    });
  });

  $(".folder-back-drop").click (function () {
    var personID = $(this).data("personid");

    window.CRM.APIRequest({
      method: 'POST',
      path: 'filemanager/folderBack',
      data: JSON.stringify({"personID": personID})
    }).done(function(data) {
      if (data && data.success) {
        window.CRM.dataEDriveTable.ajax.reload(function(json) {
          installDragAndDrop();
          if (data.isHomeFolder) {
            $(".folder-back-drop").hide();
          } else {
            $(".folder-back-drop").show();
          }
        
          $("#currentPath").html(data.currentPath);
        });
        
      }
    });
  });
  
  
  function BootboxContentUploadFile(){
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Upload your Files")+'</h3>'
       + '<div>'
            +'<div class="row div-title">'
            +'  <form action="api/" method="post" id="formId" enctype="multipart/form-data">'
            +'     <p align="center" >'
            +'       <label for="noteInputFile">'+i18next.t("Files input")+" : " + '</label>'
            +'       <input type="file" id="noteInputFile" name="noteInputFile[]" multiple>'
            +'       '
            +'       '+i18next.t('Upload your files')
            +'       <input type="submit" class="btn btn-success" name="Submit" value="'+ i18next.t("Upload") + '">'
            +'     </p>'
            +'  </form>'
            +'</div>'
       +'</div>';
          
      var object = $('<div/>').html(frm_str).contents();

      return object
  }

  function CreateUploadFileWindow()
  {
     var modal = bootbox.dialog({
       message: BootboxContentUploadFile(),
       buttons: [
        {
         label: i18next.t("Cancel"),
         className: "btn btn-default",
         callback: function() {
           modal.modal("hide");
           return true;
         }
        },
       ],
       show: false,
       onEscape: function() {
          modal.modal("hide");
       }
     });
     
     return modal;
  }
    
  $(document).on('submit','#formId',function (e) {
    $.ajax( {
      url: window.CRM.root + "/api/filemanager/uploadFile/" + window.CRM.currentPersonID,
      type: 'POST',
      data: new FormData( this ),
      processData: false,
      contentType: false
    }).done(function (data) {
      window.CRM.dataEDriveTable.ajax.reload(function(json) {
        installDragAndDrop();
        uploadWindow.modal("hide");
      });
    });
    e.preventDefault();
  });  

  $("#uploadFile").click (function () {
    uploadWindow = CreateUploadFileWindow();
    
    uploadWindow.modal("show");
  });
  
  
  // the share files
  function BootboxContentShareFiles(){
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Share your File")+'</h3>'
       + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4">'
              + '<span style="color: red">*</span>' + i18next.t("With") + ":"                    
              +'</div>'
              +'<div class="col-md-8">'
              +'<select size="6" style="width:100%" id="select-share-persons" multiple>'
              +'</select>'
             +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Set Rights") + ":</div>"
              +'<div class="col-md-8">'
                +'<select name="person-group-Id" id="person-group-rights" class="form-control input-sm"'
                    +'style="width:100%" data-placeholder="text to place">'
                    +'<option value="0">'+i18next.t("Select your rights")+" [👀  ]"+i18next.t("or")+"[👀 ✐]"+' -- </option>'
                    +'<option value="1">'+i18next.t("[👀  ]")+' -- '+i18next.t("[R ]")+'</option>'
                    +'<option value="2">'+i18next.t("[👀 ✐]")+' -- '+i18next.t("[RW]")+'</option>'
                +'</select>'
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Send email notification") + ":</div>"
              +'<div class="col-md-8">'
                +'<input id="sendEmail" type="checkbox">'
              +'</div>'
            +'</div>'            
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Add persons/Family/groups") + ":</div>"
              +'<div class="col-md-8">'
                +'<select name="person-group-Id" id="person-group-Id" class="form-control select2"'
                    +'style="width:100%">'
                +'</select>'
              +'</div>'
            +'</div>'
          +'</div>';
          
          var object = $('<div/>').html(frm_str).contents();

        return object
  }
  
// Share Files management
  function addPersonsFromNotes(noteId)
  {
      $('#select-share-persons').find('option').remove();
      
      window.CRM.APIRequest({
            method: 'POST',
            path: 'sharedocument/getallperson',
            data: JSON.stringify({"noteId": noteId})
      }).done(function(data) {    
        var elt = document.getElementById("select-share-persons");
        var len = data.length;
      
        for (i=0; i<len; ++i) {
          var option = document.createElement("option");
          // there is a groups.type in function of the new plan of schema
          option.text = data[i].name;
          //option.title = data[i].type;        
          option.value = data[i].id;
        
          elt.appendChild(option);
        }
      });  
      
      //addProfilesToMainDropdown();
  }

  function openShareFilesWindow (event) {
    var noteId = event.currentTarget.dataset.id;
    var isShared = event.currentTarget.dataset.shared;
    
    var button = $(this); //Assuming first tab is selected by default
        
    var modal = bootbox.dialog({
       message: BootboxContentShareFiles(),
       buttons: [
        {
         label: i18next.t("Delete"),
         className: "btn btn-warning",
         callback: function() {                        
            bootbox.confirm(i18next.t("Are you sure ? You're about to delete this Person ?"), function(result){ 
              if (result) {
                $('#select-share-persons :selected').each(function(i, sel){ 
                  var personID = $(sel).val();
                  
                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'sharedocument/deleteperson',
                     data: JSON.stringify({"noteId":noteId,"personID": personID})
                  }).done(function(data) {
                    $("#select-share-persons option[value='"+personID+"']").remove(); 
                    
                    if (data.count == 0) {
                      $(button).addClass("btn-default");
                      $(button).removeClass("btn-success");
                    }
                    
                    $("#person-group-Id").val("").trigger("change");
                  });
                });
              }
            });
            return false;
         }
        },
        {
         label: i18next.t("Stop sharing"),
         className: "btn btn-danger",
         callback: function() {
          bootbox.confirm(i18next.t("Are you sure ? You are about to stop sharing your document ?"), function(result){ 
            if (result) {
              window.CRM.APIRequest({
                 method: 'POST',
                 path: 'sharedocument/cleardocument',
                 data: JSON.stringify({"noteId":noteId})
              }).done(function(data) {
                addPersonsFromNotes(noteId);
                $(button).addClass("btn-default");
                $(button).removeClass("btn-success");
                modal.modal("hide");
                window.CRM.dataEDriveTable.ajax.reload(function(json) {
                  installDragAndDrop();
                });
              });
            }
          });
          return false;
         }
        },
        {
         label: i18next.t("Ok"),
         className: "btn btn-primary",
         callback: function() {
            window.CRM.dataEDriveTable.ajax.reload(function(json) {
              modal.modal("hide");
              installDragAndDrop();
            });
            return true;
         }
        },
       ],
       show: false,
       onEscape: function() {
          window.CRM.dataEDriveTable.ajax.reload(function(json) {
            modal.modal("hide");
            installDragAndDrop();
          });
       }
     });
     
     $("#person-group-Id").select2({ 
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- "+i18next.t("Person or Family or Group")+" -- ",
        allowClear: true, // This is for clear get the clear button if wanted 
        ajax: {
            url: function (params){
              return window.CRM.root + "/api/people/search/" + params.term;
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
      
     $("#person-group-rights").change(function() {
       var rightAccess = $(this).val();
       var deferreds = [];
       var i = 0;
       
       $('#select-share-persons :selected').each(function(i, sel){ 
          var personID = $(sel).val();
          var str = $(sel).text();
          
          deferreds.push(          
            window.CRM.APIRequest({
               method: 'POST',
               path: 'sharedocument/setrights',
               data: JSON.stringify({"noteId":noteId,"personID": personID,"rightAccess":rightAccess})
            }).done(function(data) {
              if (rightAccess == 1) {
                res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
              } else {
                res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
              }
            
              var elt = [personID,res];
              deferreds[i++] = elt;
            })
          );
          
        });
        
        $.when.apply($, deferreds).done(function(data) {
         // all images are now prefetched
         //addPersonsFromNotes(noteId);
         
         deferreds.forEach(function(element) {
           $('#select-share-persons option[value="'+element[0]+'"]').text(element[1]);
         }); 
         
         $("#person-group-rights option:first").attr('selected','selected');
        });
     });
     
     $("#select-share-persons").change(function() {
       $("#person-group-rights").val(0);
     });
          
      
     $("#person-group-Id").on("select2:select",function (e) { 
       var notification = ($("#sendEmail").is(':checked'))?1:0;
       
       if (e.params.data.personID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addperson',
                data: JSON.stringify({"noteId":noteId,"currentPersonID":window.CRM.currentPersonID,"personID": e.params.data.personID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromNotes(noteId);
             $(button).addClass("btn-success");
             $(button).removeClass("btn-default");
           });
        } else if (e.params.data.groupID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addgroup',
                data: JSON.stringify({"noteId":noteId,"currentPersonID":window.CRM.currentPersonID,"groupID": e.params.data.groupID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromNotes(noteId);
             $(button).addClass("btn-success");
             $(button).removeClass("btn-default");
           });
        } else if (e.params.data.familyID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addfamily',
                data: JSON.stringify({"noteId":noteId,"currentPersonID":window.CRM.currentPersonID,"familyID": e.params.data.familyID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromNotes(noteId);
             $(button).addClass("btn-success");
             $(button).removeClass("btn-default");
           });
        }
     });
     
     addPersonsFromNotes(noteId);
     modal.modal('show');
     
    // this will ensure that image and table can be focused
    $(document).on('focusin', function(e) {e.stopImmediatePropagation();});
  }
  
  var isOpened = false;

  $(document).on('click','.shareFile',function (event) {
    if (!isOpened) {
      openShareFilesWindow (event);
      isOpened = true;
    } else {
      isOpened = false;
    }
  });

    $.fn.dataTable.moment = function ( format, locale ) {
        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift( function ( d ) {
            // Removed true as the last parameter of the following moment
            return moment( d, format, locale ).isValid() ?
                'moment-'+format :
            null;
        } );

        // Add sorting method - use an integer for the sorting
        types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
           console.log("d");
            return moment ( d, format, locale, true ).unix();
        };
      };

$.fn.dataTable.ext.type.order['column-name-pre'] = function  ( data )
{
  var val = $(data).data("name");
  
  return val;
}  

$.fn.dataTable.ext.type.order['file-size-pre'] = function ( data ) {
    var units = data.replace( /[\d\.\,\ ]/g, '' ).toLowerCase();
    var multiplier = 1;
 
    if ( units === 'kb' ) {
        multiplier = 1000;
    }
    else if ( units === 'mb' ) {
        multiplier = 1000000;
    }
    else if ( units === 'gb' ) {
        multiplier = 1000000000;
    }
 
    return parseFloat( data ) * multiplier;
};
  // end of EDrive management
});