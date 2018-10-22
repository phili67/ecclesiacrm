<?php

/*******************************************************************************
 *
 *  filename    : Calendar.php
 *  last change : 2018-05-13
 *  description : manage the full Calendar
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Utils\OutputUtils;

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
    
    .input-group-addon {
      border: 2;
      padding:1px 1px;
    }

    .input-group-addon:last-child {
      border-left: 1;
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
                 <div class="col-sm-12">
                   <div class="fc-event-container fc-day-grid-event" style="background-color:#f39c12;border-color:#f19a10;color: white;line-height:33px">
                    <table width=100%>
                      <tr>
                        <td>
                          <center><?= gettext("By Types") ?></center>
                        </td>
                        <td>
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
                       <input data-size="mini" id="isWithLimit" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Limit") ?>" data-off="<?= gettext("No Limit") ?>" data-onstyle="info"><br/> 
                     </td>
                     <td align="center">
                       <?php 
                         if ($_SESSION['user']->isSeePrivacyDataEnabled()) { 
                        ?>
                       <input data-size="mini" id="isBirthdateActive" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Birthdate") ?>" data-off="<?= gettext("Birthdate") ?>" data-onstyle="danger">
                       <?php 
                         } 
                        ?>
                     </td>
                     <td align="center">
                       <?php 
                         if ($_SESSION['user']->isSeePrivacyDataEnabled()) { 
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
                         <i class="fa fa-user"></i>&nbsp;<?= gettext("Personals")?>
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
                   <div class="row panel panel-primary personal-collapse">
                      <div class="panel-heading">
                       <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa fa-building"></i>&nbsp;<i class="fa fa-windows"></i>&nbsp;<i class="fa fa-video-camera"></i>&nbsp;<?= gettext("Resources").(!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())?"  (".gettext("Shared").")":"") ?> 
                         </a>
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                         </a>
                         <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left" data-original-title="<?= gettext("Exclude/include Resource") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-reservation"></i>&nbsp;
                         <?php
                           if ($_SESSION['user']->isAdmin()) {
                         ?>
                         <i class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left" data-original-title="<?= gettext("Add New Resource Calendar") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="add-reservation-calendar"></i>&nbsp;
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
                            <i class="fa fa-users"></i><?= !($_SESSION['user']->isManageGroupsEnabled())?'&nbsp;<i class="fa  fa-share"></i>&nbsp;':"&nbsp;"?><?= gettext("Groups").(!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())?"  (".gettext("Shared").")":"") ?> 
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
                   <div class="row panel panel-primary personal-collapse">
                      <div class="panel-heading">
                       <h1 class="panel-title" style="line-height:0.6;font-size: 1em">
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa  fa-share"></i>&nbsp;<?= gettext("Shared")."  (".gettext("Users").")"?> 
                         </a>
                         <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" aria-expanded="false" class="collapsed" style="width:100%">
                            <i class="fa pull-right fa-chevron-down" style="font-size: 0.6em"></i>
                         </a>
                         <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left" data-original-title="<?= gettext("Exclude/include the Shared") ?>" style="font-size: 1em" style="color:gray;margin-right:10px;" id="manage-all-shared"></i>&nbsp;
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
            <!-- /.box-body -->
            </div>
         </div>
    </div>
    <!-- /. box -->
</div>
<!-- /.col -->

<?php require 'Include/Footer.php'; ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.css" rel="stylesheet">


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.isModifiable  = true;
  
  window.CRM.churchloc = {
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
  window.CRM.mapZoom   = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/CalendarSideBar.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/EventEditor.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/CalendarV2.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditorextension.js"></script>
<?php
  if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/OpenStreetMapEvent.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/GoogleMapEvent.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/BingMapEvent.js"></script>
<?php
  }
?>