//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/28
//

$('.checkOnlyPersonView').click('focus', function (e) {
  var ID        = $(this).data('id');
  var optionID  = $(this).data('optionid');
  var isChecked = $(this).is(':checked');

  window.CRM.APIRequest({
      method: 'POST',
      path: 'mapicons/checkOnlyPersonView',
      data: JSON.stringify({"lstID":ID,"lstOptionID":optionID,"onlyPersonView" : isChecked})
  }).done(function(data) {
     //location.reload();
  });
});

$('.RemoveClassification').click('focus', function (e) {
  var mode = $(this).data('mode');
  var order = $(this).data('order');
  var listID = $(this).data('listid');
  var ID = $(this).data('id');
  var name = $(this).data('name');
  
  
  bootbox.setDefaults({
    locale: window.CRM.shortLocale}),
    bootbox.confirm({
      title: i18next.t("Delete Classification"),
      message: '<p style="color: red">'+
        i18next.t("Please confirm deletion of this classification")+" : \""+name+"\" ?</p>"+
        "<p style='color: red'><b>"+
        i18next.t("This will also delete this Classification for all the associated persons.")+
        "</b><br></p><p style='color: red'><b>"+
        i18next.t("This can't be undone !!!!")+"</b></p>",
      callback: function (result) {
        if (result)
        {
            window.location.href = window.CRM.root+"/OptionManagerRowOps.php?mode="+mode+"&Order="+order+"&ListID="+listID+"&ID="+ID+"&Action=delete";
        }
      }
    });
});


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
  var lstID       = $(this).data('id');
  var lstOptionID = $(this).data('optionid');
  var name        = $(this).data('name');
  
  modal = createImagePickerWindow ({
    title:i18next.t("Map Icon GoogleMap"),
    firstLabel:i18next.t("Classification"),
    label:name,
    message: i18next.t("Select your classification icon"),
    directory:window.CRM.root+'/skin/icons/markers/'
  },
  function(selectedName) {
     window.CRM.APIRequest({
        method: 'POST',
        path: 'mapicons/setIconName',
        data: JSON.stringify({"name":selectedName,"lstID":lstID,"lstOptionID":lstOptionID})
    }).done(function(data) {
       location.reload();
    });
  },
  function(directory) {
      window.CRM.APIRequest({
          method: 'POST',
          path: 'mapicons/getall',
      }).done(function(data) {
        var len = data.length;
    
        $('#here_table').append('<table width=100%></table>');
        
        var table = $('#here_table').children();
        
        for(i=0;i<len;i++){
         if (i%8 == 0) {
             if (i==0) {
               var buff = '<tr>';
             } else {
               table.append( buff+'</tr>');
               var buff = '<tr>';
             }
          }
          buff += '<td><img src="' + directory+data[i] + '" class="imgCollection" data-name="'+data[i]+'" style="border:solid 1px white"></td>';
        }
    
        if (buff != '') {
          len = len%8;
          for (i=0;i<len;i++) {
            buff += '<td></td>';
          }
          table.append( buff+'</tr>');
        }
      });
    }
  );  
});