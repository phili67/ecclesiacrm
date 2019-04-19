<?php

/*******************************************************************************
 *
 *  filename    : templates/Calendar.php
 *  last change : 2019-02-5
 *  description : manage the full Calendar
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';
?>

<div class="col">
    <div class="row">
        <div class="col-sm-3">
            <div class="box box-info">
               <div class="box-header with-border">
                   <h3 class="box-title"><?= _("Filters") ?></h3>
               </div>
               <div class="row" style="padding:5px">
                 <div class="col-sm-12">
                   <div class="fc-event-container fc-day-grid-event" style="background-color:#f39c12;border-color:#f19a10;color: white;line-height:33px">
                    <table width=100%>
                      <tr>
                        <td>
                          <center><?= _("By Types") ?></center>
                        </td>
                        <td>
                           <select type="text" id="EventTypeFilter" value="0" class="form-control input-sm" size=1>
                             <option value='0' ><?= _("All") ?></option>
                               <?php
                                 foreach ($eventTypes as $eventType) {
                                 ?>
                                    <option value="<?= $eventType->getID() ?>"><?= $eventType->getName() ?></option>
                                <?php
                                 }
                               ?>
                           </select>
                       </td>
                      </tr>
                     </table>
                   </div>
                </div>
               </div>
               <div class="row" style="padding-bottom:5px">
                 <div class="col-xs-12 col-sm-12">
                   <hr class="hr-separator">
                   <table width=100%>
                   <tr>
                     <td align="center">
                       <input data-size="mini" id="isWithLimit" type="checkbox" checked data-toggle="toggle" data-on="<?= _("Limit") ?>" data-off="<?= _("No Limit") ?>" data-onstyle="info"><br/> 
                     </td>
                     <td align="center">
                       <?php 
                         if ($sessionUsr->isSeePrivacyDataEnabled()) { 
                        ?>
                       <input data-size="mini" id="isBirthdateActive" type="checkbox" checked data-toggle="toggle" data-on="<?= _("Birthdate") ?>" data-off="<?= _("Birthdate") ?>" data-onstyle="danger">
                       <?php 
                         } 
                        ?>
                     </td>
                     <td align="center">
                       <?php 
                         if ($sessionUsr->isSeePrivacyDataEnabled()) { 
                        ?>
                       <input data-size="mini" id="isAnniversaryActive" type="checkbox" checked data-toggle="toggle" data-on="<?= _("Wedding") ?>" data-off="<?= _("Wedding") ?>">
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
                   <h3 class="box-title"><?= _("Calendars") ?></h3>
               </div>
               <div class="panel-group" id="accordion"> 
                  <div class="row panel panel-primary personal-collapse">
                    <div class="panel-heading">
                     <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true" class="" style="width:100%">
                         <i class="fa fa-user"></i>&nbsp;<?= _("Personals")?>
                       </a>
                       <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" aria-expanded="true" class="" style="width:100%">
                          <i class="fa pull-right fa-chevron-up" style="font-size: 0.6em"></i>
                       </a>
                       <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="right" data-original-title="<?= _("Exclude/include Calendars") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-calendars"></i>&nbsp;
                       <i class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left" data-original-title="<?= _("Add New Calendar") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="add-calendar"></i>&nbsp;
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
                   <div class="row panel panel-primary personal-collapse">
                      <div class="panel-heading">
                       <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa fa-building"></i>&nbsp;<i class="fa fa-windows"></i>&nbsp;<i class="fa fa-video-camera"></i>&nbsp;<?= _("Resources").(!($sessionUsr->isAdmin() || $sessionUsr->isManageGroupsEnabled())?"  ("._("Shared").")":"") ?> 
                         </a>
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                         </a>
                         <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left" data-original-title="<?= _("Exclude/include Resource") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-reservation"></i>&nbsp;
                         <?php
                           if ($sessionUsr->isAdmin()) {
                         ?>
                         <i class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left" data-original-title="<?= _("Add New Resource Calendar") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="add-reservation-calendar"></i>&nbsp;
                         <?php
                           }
                         ?>
                       </h1>
                     </div>
                     <div id="collapse3" class="panel-collapse collapse" aria-expanded="false" style="padding: 0px;">
                         <div class="panel-body" style="padding: 0px;">
                           <div class="row" style="padding: 0px;">
                             <div class="col-md-12 col-xs-12">
                               <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                   <ul class="list-group" id="reservation-list">
                                   </ul>
                                 </div>
                             </div>  
                          </div>
                       </div>
                   </div>
                 </div>
                  <div class="row panel panel-primary personal-collapse">
                    <div class="panel-heading">
                       <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse2" aria-expanded="false" class="" style="width:100%">
                            <i class="fa fa-users"></i><?= !($sessionUsr->isManageGroupsEnabled())?'&nbsp;<i class="fa  fa-share"></i>&nbsp;':"&nbsp;"?><?= _("Groups").(!($sessionUsr->isAdmin() || $sessionUsr->isManageGroupsEnabled())?"  ("._("Shared").")":"") ?> 
                         </a>
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse2" aria-expanded="false" class="" style="width:100%">
                            <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                         </a>
                         <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="right" data-original-title="<?= _("Exclude/include Groups. To add a Group Calendar, Add a new group.") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-groups"></i>&nbsp;
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
                   <div class="row panel panel-primary personal-collapse">
                      <div class="panel-heading">
                       <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa  fa-share"></i>&nbsp;<?= _("Shared")."  ("._("Users").")"?> 
                         </a>
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                         </a>
                         <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left" data-original-title="<?= _("Exclude/include the Shared") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-shared"></i>&nbsp;
                       </h1>
                     </div>
                     <div id="collapse4" class="panel-collapse collapse" aria-expanded="false" style="padding: 0px;">
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
          </div>
        </div>
    </div>
    <!-- /. box -->
</div>
<!-- /.col -->

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.isModifiable  = true;
  
  window.CRM.churchloc = {
      lat: <?= $coordinates['lat'] ?>,
      lng: <?= $coordinates['lng'] ?>};
  window.CRM.mapZoom   = <?= $iLittleMapZoom ?>;
</script>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>
<script src="<?= $sRootPath ?>/skin/external/fullcalendar/fullcalendar.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/fullcalendar/locale-all.js"></script>

<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

<script src="<?= $sRootPath ?>/skin/js/calendar/CalendarSideBar.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js" ></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/CalendarV2.js" ></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<?php
  if ($sMapProvider == 'OpenStreetMap') {
?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
<?php
  } else if ($sMapProvider == 'GoogleMaps'){
?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $sGoogleMapKey ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
<?php
  } else if ($sMapProvider == 'BingMaps') {
?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
<?php
  }
?>