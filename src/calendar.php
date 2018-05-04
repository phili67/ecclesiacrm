<?php

/*******************************************************************************
 *
 *  filename    : calendar.php
 *  last change : 2017-04-27
 *  description : manage the full calendar
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\EventTypesQuery;


// Set the page title and include HTML header
$sPageTitle = gettext('Church Calendar');
require 'Include/Header.php';
      
$eventTypes = EventTypesQuery::Create()
      ->orderByName()
      ->find();
      
?>


<style>
    @media print {
        a[href]:after {
            content: none !important;
        }
    }
    .fc-other-month .fc-day-number {
      display:none;
    }
</style>

<div class="col">
       <div class="row">
          <div class="col-sm-3">
            <div class="box box-info">
               <div class="box-header with-border">
                   <h3 class="box-title"><?= gettext("Filters") ?></h3>
               </div>
               <div class="row" style="padding:5px">
                 <div class="col-sm-4">  
                    <label><?= gettext("By Types")." : " ?>
                 </div>
                 <div class="col-sm-8">  
                   <select type="text" id="EventTypeFilter" value="0" class="form-control input-sm" size=1>
                     <option value='0' ><?= gettext("All") ?></option>
                       <?php
                         foreach ($eventTypes as $eventType) {
                         ?>
                            <option value="<?= $eventType->getID() ?>"><?= $eventType->getName() ?></option>
                        <?php
                         }
                       ?>
                   </select>
                </div>
               </div>
               <div class="row" style="padding-bottom:5px">
                 <div class="col-xs-12 col-sm-12">
                   <hr class="hr-separator">
                   <table width=100%>
                   <tr>
                     <td align="center">
                       <input data-size="mini" id="isWithLimit" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Limit") ?>" data-off="<?= gettext("No Limit") ?>"><br/> 
                     </td>
                     <td align="center">
                       <?php 
                         if ($_SESSION['bSeePrivacyData'] || $_SESSION['user']->isAdmin()) { 
                        ?>
                       <input data-size="mini" id="isBirthdateActive" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Birthdate") ?>" data-off="<?= gettext("Birthdate") ?>">
                       <?php 
                         } 
                        ?>
                     </td>
                     <td align="center">
                       <?php 
                         if ($_SESSION['bSeePrivacyData'] || $_SESSION['user']->isAdmin()) { 
                        ?>
                       <input data-size="mini" id="isAnniversaryActive" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Wedding") ?>" data-off="<?= gettext("Wedding") ?>">
                       <?php 
                         } 
                        ?>
                     </td>
                    </tr>
                   </table>
                  </div>
               </div>       
            </div>
            <div class="box box-info">
               <div class="box-header with-border">
                   <h3 class="box-title"><?= gettext("Calendars") ?></h3>
               </div>
               <div class="panel-group" id="accordion"> 
                  <div class="row panel panel-primary personal-collapse">
                    <div class="panel-heading">
                     <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true" class="" style="width:100%">
                         <?= gettext("Personals")?>
                       </a>
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true" class="" style="width:100%">
                          <i class="fa pull-right fa-chevron-up" style="font-size: 0.6em"></i>
                       </a>
                       <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="right" data-original-title="<?= gettext("Exclude/include Calendars") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-calendars"></i>&nbsp;
                       <i class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left" data-original-title="<?= gettext("Add New Calendar") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="add-calendar"></i>&nbsp;
                     </h1>
                   </div>
                   <div id="collapse1" class="panel-collapse collapse in" aria-expanded="true" style="padding: 0px;">
                       <div class="panel-body" style="padding: 0px;">
                         <div class="row" style="padding: 0px;">
                           <div class="col-md-12 col-xs-12">
                             <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                 <ul class="list-group" id="cal-list">
                                 </ul>
                             </div>
                           </div>  
                        </div>
                     </div>
                 </div>
                 </div>
               <?php
                 if ($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled()) {
                 // only an administrator can manage the groups
               ?>
                 <div class="row panel panel-primary personal-collapse">
                    <div class="panel-heading">
                     <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse2" aria-expanded="false" class="" style="width:100%">
                          <?= gettext("Groups")?> 
                       </a>
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse2" aria-expanded="false" class="" style="width:100%">
                          <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                       </a>
                       <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="right" data-original-title="<?= gettext("Exclude/include Groups. To add a Group Calendar, Add a new group.") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-groups"></i>&nbsp;
                     </h1>
                   </div>
                   <div id="collapse2" class="panel-collapse collapse" aria-expanded="false" style="padding: 0px;">
                       <div class="panel-body" style="padding: 0px;">
                         <div class="row" style="padding: 0px;">
                           <div class="col-md-12 col-xs-12">
                             <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                 <ul class="list-group" id="group-list">
                                 </ul>
                               </div>
                           </div>  
                        </div>
                     </div>
                 </div>
                 </div>
               <?php
                 }
               ?>
                 <div class="row panel panel-primary personal-collapse">
                    <div class="panel-heading">
                     <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" class="collapsed" style="width:100%">
                          <?= gettext("Shared")?> 
                       </a>
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" class="collapsed" style="width:100%">
                          <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                       </a>
                       <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left" data-original-title="<?= gettext("Exclude/include the Shared") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-shared"></i>&nbsp;
                     </h1>
                   </div>
                   <div id="collapse3" class="panel-collapse collapse" aria-expanded="false" style="padding: 0px;">
                       <div class="panel-body" style="padding: 0px;">
                         <div class="row" style="padding: 0px;">
                           <div class="col-md-12 col-xs-12">
                             <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                 <ul class="list-group" id="share-list">
                                 </ul>
                               </div>
                           </div>  
                        </div>
                     </div>
                 </div>
                 </div>
             </div>
            </div>
          </div>
          <div class="col-sm-9">
          <div class="box box-info">
            <!-- THE CALENDAR -->
            <div id="calendar"></div>
            <!-- /.box-body -->
            </div>
         </div>
    </div>
    <!-- /. box -->
</div>
<!-- /.col -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>
<link href="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.css" rel="stylesheet">

<!-- fullCalendar 2.2.5 -->
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var isModifiable  = <?php
  //if ($_SESSION['bAddEvent'] ||  $_SESSION['user']->isAdmin()) {
        echo "true";
  /*} else {
        echo "false";
  }*/
?>;

  
  var maxHeight = 230;
  $( window ).resize(function() {   
    //(document.body.clientHeight); n'a pas l'air top
    var hscreen = $(window).height(),
    height = hscreen > maxHeight ? maxHeight : hscreen;
    $('#mon_rectangle').height(height);
  });

  $('.collapse').on('shown.bs.collapse', function(){
      $(this).parent().find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up");
  }).on('hidden.bs.collapse', function(){
      $(this).parent().find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down");
  });
  
  // for the calendar
  $('body').on('click','.check-calendar', function(){ 
    var calIDs = $(this).data("id");
    var isChecked  = ($(this).is(':checked'))?1:0;
    
    window.CRM.APIRequest({
      method: 'POST',
      path: 'calendar/setckecked',
      data: JSON.stringify({"calIDs":calIDs,"isChecked":isChecked})
    }).done(function(data) {    
      // we reload all the events
      $('#calendar').fullCalendar( 'refetchEvents' );   
    });
  });
  
  $("#manage-all-calendars").click('focus', function (e) {
    alert("manage all calendars");
  });

  $("#manage-all-groups").click('focus', function (e) {
    alert("manage all groups");
  });
  
  $("#manage-all-shared").click('focus', function (e) {
    alert("manage all shared");
  });
  
  
  $("#add-calendar").click('focus', function (e) {
    bootbox.prompt({
      title: i18next.t("Set Calendar Name"),
      inputType: 'text',
      callback: function (title) {
        if (title !== null && title !== '') {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/new',
            data: JSON.stringify({"title":title})
          }).done(function(data) {             
             addPersonalCalendars();
          });
        }
      }
    });
  });
  
// the add people to calendar

  function addPersonsFromCalendar(calendarId)
  {
      $('#select-share-persons').find('option').remove();
      
      window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/getinvites',
            data: JSON.stringify({"calIDs": calendarId})
      }).done(function(data) {    
        var elt = document.getElementById("select-share-persons");
        var len = data.length;
      
        for (i=0; i<len; ++i) {
          if (data[i].access == 2) {
            var option = document.createElement("option");

            option.text = i18next.t("[R ]")+" "+data[i].principal.replace("principals/", "");
            option.value = data[i].principal;
        
            elt.appendChild(option);
          } else if (data[i].access == 3) {
            var option = document.createElement("option");

            option.text = i18next.t("[RW]")+" "+data[i].principal.replace("principals/", "");
            option.value = data[i].principal;
        
            elt.appendChild(option);
          }

        }
      });  
  }
  
  function BootboxContentShare(){
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Share your Calendar")+'</h3>'
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
                    +'<option value="0">'+i18next.t("Select your rights [R ] or [RW]")+' -- </option>'
                    +'<option value="1">'+i18next.t("[R ]")+' -- </option>'
                    +'<option value="2">'+i18next.t("[RW]")+' -- </option>'
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
  
  function createShareWindow (calIDs)
  {
    var modal = bootbox.dialog({
       message: BootboxContentShare(),
       buttons: [
        {
         label: i18next.t("Delete"),
         className: "btn btn-warning",
         callback: function() {                        
            bootbox.confirm(i18next.t("Are you sure, you want to delete this Share ?"), function(result){ 
              if (result) {
                $('#select-share-persons :selected').each(function(i, sel){ 
                  var principal = $(sel).val();
                  
                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'calendar/sharedelete',
                     data: JSON.stringify({"calIDs":calIDs,"principal": principal})
                  }).done(function(data) {
                    $("#select-share-persons option[value='"+principal+"']").remove(); 
                    $('#calendar').fullCalendar( 'refetchEvents' );   
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
          bootbox.confirm(i18next.t("Are you sure, you want to stop sharing your document ?"), function(result){ 
            if (result) {
              window.CRM.APIRequest({
                 method: 'POST',
                 path: 'calendar/sharestop',
                 data: JSON.stringify({"calIDs":calIDs})
              }).done(function(data) {
                addPersonsFromCalendar(calIDs);
                modal.modal("hide");
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
          var principal = $(sel).val();
          var str = $(sel).text();
          
          deferreds.push(          
            window.CRM.APIRequest({
               method: 'POST',
               path: 'calendar/setrights',
               data: JSON.stringify({"calIDs":calIDs,"principal": principal,"rightAccess":rightAccess})
            }).done(function(data) {
              if (rightAccess == 1) {
                res = str.replace('[RW]', '[R ]');
              } else {
                res = str.replace('[R]', '[RW]');
              }
            
              var elt = [principal,res];
              deferreds[i++] = elt;
            })
          );
          
        });
        
        $.when.apply($, deferreds).done(function(data) {
         // all images are now prefetched
         addPersonsFromCalendar(calIDs);
         
         deferreds.forEach(function(element) {
           $('#select-share-persons option[value="'+element[0]+'"]').text(element[1]);
         }); 
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
                path: 'calendar/shareperson',
                data: JSON.stringify({"calIDs":calIDs,"personID": e.params.data.personID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromCalendar();
           });
        } else if (e.params.data.groupID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/sharegroup',
                data: JSON.stringify({"calIDs":calIDs,"groupID": e.params.data.groupID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromCalendar(calIDs);
           });
        } else if (e.params.data.familyID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/sharefamily',
                data: JSON.stringify({"calIDs":calIDs,"familyID": e.params.data.familyID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromCalendar(calIDs);
           });
        }
     });
     
     addPersonsFromCalendar(calIDs);
     modal.modal('show');
     
    // this will ensure that image and table can be focused
    $(document).on('focusin', function(e) {e.stopImmediatePropagation();});  
  }
  
  $('body').on('click','#manage-cal-group', function(){ 
    var calIDs = $(this).data("id");
    var type   = $(this).data("type");
    
    window.CRM.APIRequest({
       method: 'POST',
       path: 'calendar/info',
       data: JSON.stringify({"calIDs":calIDs})
    }).done(function(data) {             
        var allButtons = {};
    
        var buttonDelete = {
          delete: {
            label: i18next.t("Delete"),
            className: 'btn-danger',
            callback: function(){  
                                    
              bootbox.confirm({
                title:i18next.t("Are you sure?"),
                message: i18next.t("You'll lose the calendar, the events and all the share calendars too. This cannot be undone."),
                callback: function(result) {
                  if (result) {
                     window.CRM.APIRequest({
                          method: 'POST',
                          path: 'calendar/delete',
                          data: JSON.stringify({"calIDs":calIDs})
                     }).done(function(data) { 
                       addPersonalCalendars(calIDs);
                       $('#calendar').fullCalendar( 'refetchEvents' );   
                     });
                  }
                }
              });
            }
           }
        };
    
        var buttonManage = {
          manage: {
              label: i18next.t("Manage"),
              className: 'btn-info',
              callback: function(){
                  createShareWindow (calIDs);
                  return true;
              }
          }
        };
        
        var buttonOk = {
          Ok: {
              label: i18next.t("Ok"),
              className: 'btn-primary',
              callback: function(){
                  return true;
              }
          }
        };
    
        if (type == "personal") {
          allButtons = $.extend(allButtons,buttonDelete,buttonManage,buttonOk);
        } else if (type == "group") {
          allButtons = $.extend(allButtons,buttonManage,buttonOk);
        } else if (type == "shared") {
          allButtons = $.extend(allButtons,buttonOk);
        }
    
    
        var dialog = bootbox.dialog({
          title: i18next.t("Calendar Management for"+" : "+data.title),
          message: i18next.t(data.message),
          buttons: allButtons
        });    
    });
    
  });
  
  
// end off add people to calendar
//

  
  $('body').on('click','.editCalendarName', function(){ 
    var calIDs = $(this).data("id");
    var name   = $(this).text();
    
    bootbox.prompt({
      title: i18next.t("Modify Calendar Name"),
      inputType: 'text',
      value:name,
      callback: function (title) {
        if (title !== null && title != '') {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/modifyname',
            data: JSON.stringify({"title":title,"calIDs":calIDs})
          }).done(function(data) {             
             addPersonalCalendars();
          });
        }
      }
    });    
  });    
  
  $('body').on('click','.editGroupName', function(){ 
    var calIDs = $(this).data("id");
    var name   = $(this).text();
    
    bootbox.prompt({
      title: i18next.t("Modify Group Name"),
      inputType: 'text',
      value:name,
      callback: function (title) {
        if (title !== null && title != '') {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/modifyname',
            data: JSON.stringify({"title":title,"calIDs":calIDs})
          }).done(function(data) {             
             addGroupCalendars();
          });
        }
      }
    });     
  }); 

  $('body').on('click','.editShareName', function(){ 
    var calIDs = $(this).data("id");
    var name   = $(this).text();
    
    bootbox.prompt({
      title: i18next.t("Modify Share Name"),
      inputType: 'text',
      value:name,
      callback: function (title) {
        if (title !== null && title != '') {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/modifyname',
            data: JSON.stringify({"title":title,"calIDs":calIDs})
          }).done(function(data) {             
             addShareCalendars();
          });
        }
      }
    });      
  }); 

  function addPersonalCalendars()
  {
    $('#cal-list').empty();
    
    window.CRM.APIRequest({
      method: 'POST',
      path: 'calendar/getallforuser',
      data: JSON.stringify({"type":"personal","onlyvisible":false})
    }).done(function(data) {    
      var len = data.length;
      
      for (i=0; i<len; ++i) {
        $('#cal-list').append('<li class="list-group-item" style="cursor: pointer;"><div class="input-group my-colorpicker-global my-colorpicker1'+i+' colorpicker-element" data-id="'+data[i].calendarID+'"><input id="checkBox" type="checkbox" class="check-calendar" data-id="'+data[i].calendarID+'"'+((data[i].visible)?"checked":"")+'><i class="fa pull-right fa-gear"  style="font-size: 1.2em" style="color:gray;padding-right:10px;" id="manage-cal-group" data-type="personal" data-id="'+data[i].calendarID+'"></i> <span class="editCalendarName"  data-id="'+data[i].calendarID+'">'+data[i].calendarName+'</span><div class="input-group-addon" style="border: 2;padding:1px 1px;"><i style="background-color:'+data[i].calendarColor+';"></i>');
        $(".my-colorpicker1"+i).colorpicker({
          color:data[i].calendarColor,
          inline:false,
          horizontal:true,
          right:true
        });
      }      
    });  
  }


  function addGroupCalendars()
  {
    $('#group-list').empty();
    
    window.CRM.APIRequest({
      method: 'POST',
      path: 'calendar/getallforuser',
      data: JSON.stringify({"type":"group","onlyvisible":false})
    }).done(function(data) {    
      var len = data.length;
      
      for (i=0; i<len; ++i) {
        $('#group-list').append('<li class="list-group-item" style="cursor: pointer;"><div class="input-group my-colorpicker-global my-colorpicker1'+i+' colorpicker-element" data-id="'+data[i].calendarID+'"><input id="checkBox" type="checkbox" class="check-calendar" data-id="'+data[i].calendarID+'"'+((data[i].visible)?"checked":"")+'><i class="fa pull-right fa-gear"  style="font-size: 1.2em" style="color:gray;padding-right:10px;" id="manage-cal-group" data-type="group" data-id="'+data[i].calendarID+'"></i> <span class="editGroupName"  data-id="'+data[i].calendarID+'">'+data[i].calendarName+'</span><div class="input-group-addon" style="border: 2;padding:1px 1px;"><i style="background-color:'+data[i].calendarColor+';"></i>');
        
        $(".my-colorpicker1"+i).colorpicker({
          color:data[i].calendarColor,          
          inline:false,
          horizontal:true,
          right:true
        });
      }      
    });  
  }
  
  function addShareCalendars()
  {
    $('#share-list').empty();
    
    window.CRM.APIRequest({
      method: 'POST',
      path: 'calendar/getallforuser',
      data: JSON.stringify({"type":"share","onlyvisible":false})
    }).done(function(data) {    
      var len = data.length;
      
      for (i=0; i<len; ++i) {
        $('#share-list').append('<li class="list-group-item" style="cursor: pointer;"><div class="input-group my-colorpicker-global my-colorpicker1'+i+' colorpicker-element" data-id="'+data[i].calendarID+'"><input id="checkBox" type="checkbox" class="check-calendar" data-id="'+data[i].calendarID+'"'+((data[i].visible)?"checked":"")+'><i class="fa pull-right fa-gear"  style="font-size: 1.2em" style="color:gray;padding-right:10px;" id="manage-cal-group" data-type="share" data-id="'+data[i].calendarID+'"></i> <span class="editShareName"  data-id="'+data[i].calendarID+'">'+data[i].calendarName+'</span><div class="input-group-addon" style="border: 2;padding:1px 1px;"><i style="background-color:'+data[i].calendarColor+';"></i>');
        
        $(".my-colorpicker1"+i).colorpicker({
          color:data[i].calendarColor,
          inline:false,
          horizontal:true,
          right:true
        });
      }      
    });  
  }
  
  $('body').colorpicker().on('changeColor','.my-colorpicker-global', function(e){
    var calIDs = $(this).data("id");
    var color = $(this).data('colorpicker').color.toHex();//.toString('hex');
          
    window.CRM.APIRequest({
      method: 'POST',
      path: 'calendar/setcolor',
      data: JSON.stringify({"calIDs":calIDs,"color":color})
    }).done(function(data) {    
      // we reload all the events
      $('#calendar').fullCalendar( 'refetchEvents' );   
    });
  });
  
  // Add all the calendars
  addPersonalCalendars();  
  addGroupCalendars();
  addShareCalendars();
  
</script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/Calendar.js" ></script>

<?php require 'Include/Footer.php'; ?>