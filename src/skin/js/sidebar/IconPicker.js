//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/28
//

// usage :
// modal = createImagePickerWindow ({
//    title:"A title Name",
//    firstLabel:"First Label",
//    message: "Select your icon below",
//    label:"name",
//    directory:"/your_path"
//  },
//  function(selectedName) {
//     console.log(selectedName);
//  },
//function(directory) {
//      window.CRM.APIRequest({
//          method: 'POST',
//          path: 'mapicons/getall',
//      }).done(function(data) {
//        var len = data.length;
//    
//        $('#here_table').append('<table width=100%></table>');
//        
//        var table = $('#here_table').children();
//        
//        for(i=0;i<len;i++){
//         if (i%8 == 0) {
//             if (i==0) {
//               var buff = '<tr>';
//             } else {
//               table.append( buff+'</tr>');
//               var buff = '<tr>';
//             }
//          }
//          buff += '<td><img src="' + directory+data[i] + '" class="imgCollection" data-name="'+data[i]+'" style="border:solid 1px white"></td>';
//        }
//    
//       if (buff != '') {
//          len = len%8;
//          for (i=0;i<len;i++) {
//            buff += '<td></td>';
//          }
//          table.append( buff+'</tr>');
//        }
//      });
//    }
//  );   
  
    var selectedName = '';

    function BootboxContent(title,firstLabel,label,message){          
       var frm_str = '<h3 style="margin-top:-5px">'+title+'</h3>'
          + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-5"><label>'+firstLabel+' : </label></div><div class="col-md-7">"'+label+'"</div></div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-12">'+message+'</div>'
            +'</div>'
            +'<div class="row">'
              +'<div class="col-md-12"><div id="here_table"></div></div>'
            +'</div>'
          +'</div>'

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
    $('body').on('click','.imgCollection', function(){ 
      var name = $(this).data("name");
      
      selectedName = name;
    
      $( ".imgCollection" ).each(function( index ) {
            $(this).css('border', "solid 1px white"); 
      });

      $(this).css('border', "solid 1px blue"); 
    });

    
    function createImagePickerWindow (options,callbackRes,callBackIcons) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
    {      
      var diag = bootbox.dialog({
         message: BootboxContent(options.title,options.firstLabel,options.label,options.message,options.directory),
         size: "small",
         buttons: [
          {
           label: i18next.t("Cancel"),
           className: "btn btn-default",
          },
          {
           label: i18next.t("Validate"),
           className: "btn btn-primary",
           callback: function() {
              if (callbackRes) {
                  return callbackRes(selectedName);
              }
            }
          }
         ],
         onEscape: function() {
            modal.modal("hide");
         }
       });
      
       if (callBackIcons) {
         callBackIcons(options.directory);
       }
       
       diag.modal("show");
              
       return diag;
    }