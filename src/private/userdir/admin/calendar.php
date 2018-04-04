<?php

/*******************************************************************************
 *
 *  filename    : calendar.php
 *  last change : 2017-11-16
 *  description : manage the full calendar
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2017 Logel Philippe
  *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\EventTypesQuery;

// Set the page title and include HTML header
$sPageTitle = gettext('Church Calendar');
require 'Include/Header.php';

$groups = GroupQuery::Create()
      ->orderByName()
      ->find();
      
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
    <div class="box box-primary">
        <div class="box-body">
            <?php foreach (CalendarService::getEventTypes() as $type) {
    ?>
                <div class="col-xs-3 fc-event-container fc-day-grid-event"
                     style="background-color:<?= $type['backgroundColor'] ?>;border-color:<?= $type['backgroundColor'] ?>;color: white; ">
                    <div class="fc-title"><?= gettext($type['Name']) ?></div>
                </div>
                <?php
} ?>
        </div>
    </div>
</div>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Filter Settings') ?></h3>
  </div>
  <div class="box-body">
          <div class="col-sm-6"> <b><?= gettext("Event Group Filter") ?>:</b>
            <select type="text" id="EventGroupFilter" value="0"  class="form-control input-sm">
              <option value='0' ><?= gettext("None") ?></option>
                <?php
                  foreach ($groups as $group) {
                      echo "+\"<option value='".$group->getID()."'>".$group->getName()."</option>\"";
                  }
                ?>
            </select>
          </div>
  </div>
</div>


<div class="col">
       <div class="row">
          <div class="col-sm-3">
            <div class="box box-info">
               <div class="box-header with-border">
                   <h3 class="box-title">Réglages rapides</h3>
               </div>
               <div class="row" style="padding:5px">
                 <div class="col-sm-12">  
                   <select type="text" id="EventTypeFilter" value="0" class="form-control input-sm">
                     <option value='0' ><?= gettext("None") ?></option>
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
               <div class="row" style="padding:5px">
                 <div class="col-sm-4">                
                   <input data-size="small" id="isBirthdateActive" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Birthdate") ?>" data-off="<?= gettext("Birthdate") ?>">
                 </div>
                 <div class="col-sm-4">                
                   <input data-size="small" id="isAnniversaryActive" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Wedding") ?>" data-off="<?= gettext("Wedding") ?>">
                 </div>
                 <div class="col-sm-4">                
                   <input data-size="small" id="isWithLimit" type="checkbox" checked data-toggle="toggle" data-on="<?= gettext("Limit") ?>" data-off="<?= gettext("No Limit") ?>"><br/> 
                 </div>
               </div>
               <div class="box-header with-border">
                   <h3 class="box-title"><?= gettext("Choose Groups") ?></h3>
               </div>
               <div class="well" style="max-height: 355px;overflow: auto;padding: 0px;">
                <ul class="list-group checked-list-box">
                    <li class="list-group-item list-group-item-primary active" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-check"></span>Cras justo odio<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item list-group-item-primary active" data-checked="true" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-check"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;">
                    <div class="input-group my-colorpicker1 colorpicker-element">                                            
                        <span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden">
                            <div class="input-group-addon" style="border: 2;padding:1px 1px;">
                                <i style="background-color: rgb(221, 85, 85);"></i>
                            </div>
                    </div>
                    </li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Cras justo odio<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Dapibus ac facilisis in<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Morbi leo risus<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Porta ac consectetur ac<input type="checkbox" class="hidden"></li>
                    <li class="list-group-item" style="cursor: pointer;"><span class="state-icon glyphicon glyphicon-unchecked"></span>Vestibulum at eros<input type="checkbox" class="hidden"></li>
                  </ul>
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
    if ($_SESSION['bAddEvent'] || $_SESSION['bAdmin']) {
        echo "true";
    } else {
        echo "false";
    }
  ?>;
  
  
  //Colorpicker
  $(".my-colorpicker1").colorpicker();
  
  $(function () {
    $('.list-group.checked-list-box .list-group-item').each(function () {
        
        // Settings
        var $widget = $(this),
            $checkbox = $('<input type="checkbox" class="hidden" />'),
            color = ($widget.data('color') ? $widget.data('color') : "primary"),
            style = ($widget.data('style') == "button" ? "btn-" : "list-group-item-"),
            settings = {
                on: {
                    icon: 'glyphicon glyphicon-check'
                },
                off: {
                    icon: 'glyphicon glyphicon-unchecked'
                }
            };
            
        $widget.css('cursor', 'pointer')
        $widget.append($checkbox);

        // Event Handlers
        $widget.on('click', function () {
            $checkbox.prop('checked', !$checkbox.is(':checked'));
            $checkbox.triggerHandler('change');
            updateDisplay();
        });
        $checkbox.on('change', function () {
            updateDisplay();
        });
          

        // Actions
        function updateDisplay() {
            var isChecked = $checkbox.is(':checked');

            // Set the button's state
            $widget.data('state', (isChecked) ? "on" : "off");

            // Set the button's icon
            $widget.find('.state-icon')
                .removeClass()
                .addClass('state-icon ' + settings[$widget.data('state')].icon);

            // Update the button's color
            if (isChecked) {
                $widget.addClass(style + color + ' active');
            } else {
                $widget.removeClass(style + color + ' active');
            }
        }

        // Initialization
        function init() {
            
            if ($widget.data('checked') == true) {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
            }
            
            updateDisplay();

            // Inject the icon if applicable
            if ($widget.find('.state-icon').length == 0) {
                $widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
            }
        }
        init();
    });
    
    $('#get-checked-data').on('click', function(event) {
        event.preventDefault(); 
        var checkedItems = {}, counter = 0;
        $("#check-list-box li.active").each(function(idx, li) {
            checkedItems[counter] = $(li).text();
            counter++;
        });
        $('#display-json').html(JSON.stringify(checkedItems, null, '\t'));
    });
});
</script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/Calendar.js" ></script>

<?php require 'Include/Footer.php'; ?>