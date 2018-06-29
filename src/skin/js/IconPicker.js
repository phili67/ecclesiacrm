//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/28
//
  
    var selectedName = '';

    function BootboxContent(){          
       var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Choose a Map Icon")+'</h3>'
          + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-9"><div id="here_table"></div></div>'
            +'</div>'
          +'</div>'

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
    function createImagePickerWindow (lstID,lstOptionID) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
    {      
      var modal = bootbox.dialog({
         message: BootboxContent(),
         buttons: [
          {
           label: i18next.t("Close"),
           className: "btn btn-default",
          },
          {
           label: i18next.t("Save"),
           className: "btn btn-primary",
           callback: function() {
                                                            
                  window.CRM.APIRequest({
                      method: 'POST',
                      path: 'mapicons/setIconName',
                      data: JSON.stringify({"name":selectedName,"lstID":lstID,"lstOptionID":lstOptionID})
                  }).done(function(data) {
                     location.reload();
                  });

                  return true;  
            }
          }
         ],
         onEscape: function() {
            modal.modal("hide");
         }
       });
              
       return modal;
    }
    
    $('body').on('click','.imgCollection', function(){ 
      var name = $(this).data("name");
      
      selectedName = name;
    
      $( ".imgCollection" ).each(function( index ) {
            $(this).css('border', "solid 0px red"); 
      });

      $(this).css('border', "solid 1px red"); 
    });

    
    function AddIcons() {
      window.CRM.APIRequest({
          method: 'POST',
          path: 'mapicons/getall',
      }).done(function(data) {
        var len = data.length;
    
        $('#here_table').append('<table></table>');
        var table = $('#here_table').children();
        for(i=0;i<len;i++){
         if (i%17 == 0) {
             if (i==0) {
               var buff = '<tr>';
             } else {
               table.append( buff+'</tr>');
               var buff = '<tr>';
             }
          } else {
            buff += '<td><img src="' + window.CRM.root+'/skin/icons/markers/'+data[i] + '" class="imgCollection" data-name="'+data[i]+'" border="0"></td>';
          }
        }
    
        if (buff != '') {
          len = len%15;
          for (i=0;i<len;i++) {
            buff += '<td></td>';
          }
          table.append( buff+'</tr>');
        }
      });
    }


$('.RemoveImage').click('focus', function (e) {
  var lstID = $(this).data('id');
  var lstOptionID = $(this).data('optionid');
  
  window.CRM.APIRequest({
      method: 'POST',
      path: 'mapicons/removeIcon',
      data: JSON.stringify({"lstID":lstID,"lstOptionID":lstOptionID})
  }).done(function(data) {
     location.reload();
  });
});


$('.AddImage').click('focus', function (e) {
  var lstID = $(this).data('id');
  var lstOptionID = $(this).data('optionid');
  
  selectedName = '';
  modal = createImagePickerWindow (lstID,lstOptionID);  
  AddIcons();
  modal.modal("show");
});