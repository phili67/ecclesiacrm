<?php
/*******************************************************************************
*
*  filename    : ListEvents.php
*  website     : http://www.ecclesiacrm.com
*  function    : List all Church Events
*
*  copyright   : Copyright 2005 Todd Pillars
*
*
*  Additional Contributors:
*  2007 Ed Davis
*  update 2018 Philippe Logel all right reserved
*
******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\EventAttendQuery;

$eType = 'All';
$ThisYear = date('Y');

if (isset($_POST['WhichType'])) {
    $eType = InputUtils::LegacyFilterInput($_POST['WhichType']);
} else {
    $eType = 'All';
}

if ($eType != 'All') {
    $sSQL = "SELECT * FROM event_types WHERE type_id=$eType";
    $rsOpps = RunQuery($sSQL);
    $aRow = mysqli_fetch_array($rsOpps, MYSQLI_BOTH);
    extract($aRow);
    $sPageTitle = gettext('Listing Events of Type = ').$type_name;
} else {
    $sPageTitle = gettext('Listing All Church Events');
}

// retrieve the year selector

if (isset($_POST['WhichYear'])) {
    $EventYear = InputUtils::LegacyFilterInput($_POST['WhichYear'], 'int');
} else {
    $EventYear = date('Y');
}

///////////////////////
require 'Include/Header.php';


if (isset($_POST['Action']) && isset($_POST['EID'])) {
    $eID = InputUtils::LegacyFilterInput($_POST['EID'], 'int');
    $action = InputUtils::LegacyFilterInput($_POST['Action']);
    if ($action == 'Delete' && $eID) {
        $sSQL = 'DELETE FROM events_event WHERE event_id = '.$eID.' LIMIT 1';
        RunQuery($sSQL);

        $sSQL = 'DELETE FROM eventcounts_evtcnt WHERE evtcnt_eventid = '.$eID;
        RunQuery($sSQL);
        
        $sSQL = 'DELETE FROM event_attend WHERE event_id = '.$eID;
        RunQuery($sSQL);
    } elseif ($action == 'Activate' && $eID) {
        $sSQL = 'UPDATE events_event SET inactive = 0 WHERE event_id = '.$eID.' LIMIT 1';
        RunQuery($sSQL);
    }
}

/// top of main form
//
$sSQL = 'SELECT DISTINCT event_types.* 
         FROM event_types 
         RIGHT JOIN events_event ON event_types.type_id=events_event.event_type 
         ORDER BY type_id ';
$rsOpps = RunQuery($sSQL);
$numRows = mysqli_num_rows($rsOpps);

?>


<div class='text-center'>
  <a href="calendar.php" class='btn btn-primary'>
    <i class='fa fa-ticket'></i>
    <?= gettext('Add New Event') ?>
  </a>
</div>

<table cellpadding="1" align="center" cellspacing="0" class='table'>
<tr>
<td align="center" width="50%"><p><strong><?= gettext('Select Event Types To Display') ?></strong></p>
    <form name="EventTypeSelector" method="POST" action="ListEvents.php">
       <select name="WhichType" onchange="javascript:this.form.submit()" class='form-control'>
        <option value="All"><?= gettext('All') ?></option>
        <?php
        for ($r = 1; $r <= $numRows; $r++) {
            $aRow = mysqli_fetch_array($rsOpps, MYSQLI_BOTH);
            extract($aRow);
            //          foreach($aRow as $t)echo "$t\n\r";?>
          <option value="<?php echo $type_id ?>" <?php if ($type_id == $eType) {
                echo 'selected';
            } ?>><?= $type_name ?></option>
          <?php
        }
         ?>
         </select>
</td>

<?php
// year selector
if ($eType == 'All') {
    $sSQL = 'SELECT DISTINCT YEAR(events_event.event_start) 
           FROM events_event 
           WHERE YEAR(events_event.event_start)';
} else {
    $sSQL = "SELECT DISTINCT YEAR(events_event.event_start) 
           FROM events_event 
           WHERE events_event.event_type = '$eType' AND YEAR(events_event.event_start)";
}
$rsOpps = RunQuery($sSQL);
$aRow = mysqli_fetch_array($rsOpps, MYSQLI_BOTH);
@extract($aRow); // @ needed to suppress error messages when no church events
$rsOpps = RunQuery($sSQL);
$numRows = mysqli_num_rows($rsOpps);
for ($r = 1; $r <= $numRows; $r++) {
    $aRow = mysqli_fetch_array($rsOpps, MYSQLI_BOTH);
    extract($aRow);
    $Yr[$r] = $aRow[0];
}

?>

<td align="center" width="50%"><p><strong><?= gettext('Display Events in Year') ?></strong></p>
       <select name="WhichYear" onchange="javascript:this.form.submit()" class='form-control'>
        <?php
        for ($r = 1; $r <= $numRows; $r++) {
            ?>
          <option value="<?php echo $Yr[$r] ?>" <?php if ($Yr[$r] == $EventYear) {
                echo 'selected';
            } ?>><?= $Yr[$r] ?></option>
          <?php
        }
         ?>
         </select>
    </form>
</td>
</tr>
</table>
<?php

// Get data for the form as it now exists..
// for this year
$currYear = date('Y');
$currMonth = date('m');
$allMonths = ['12', '11', '10', '9', '8', '7', '6', '5', '4', '3', '2', '1'];
if ($eType == 'All') {
    $eTypeSQL = ' ';
} else {
    $eTypeSQL = " AND t1.event_type=$eType";
}

$statisticaAvgRows = true;

foreach ($allMonths as $mKey => $mVal) {
    unset($cCountSum);
    $sSQL = 'SELECT * FROM events_event as t1, event_types as t2 ';
    if (isset($previousMonth)) {
        // $sSQL .= " WHERE previous month stuff";
    } elseif (isset($nextMonth)) {
        // $sSQL .= " WHERE next month stuff";
    } elseif (isset($showAll)) {
        $sSQL .= '';
    } else {
        //$sSQL .= " WHERE (TO_DAYS(event_start_date) - TO_DAYS(now()) < 30)";
        $sSQL .= ' WHERE t1.event_type = t2.type_id'.$eTypeSQL.' AND MONTH(t1.event_start) = '.$mVal." AND YEAR(t1.event_start)=$EventYear";
    }
    $sSQL .= ' ORDER BY t1.event_start ';

    $rsOpps = RunQuery($sSQL);
    $numRows = mysqli_num_rows($rsOpps);
    $aAvgRows = $numRows;
    
    $numAVGAtt = 0;
    $numAVG_CheckIn = 0;
    $numAVG_CheckOut = 0;
    
    // Create arrays of the fundss.
    for ($row = 1; $row <= $numRows; $row++) {
        $aRow = mysqli_fetch_array($rsOpps, MYSQLI_BOTH);
        extract($aRow);

        $aEventID[$row] = $event_id;
        $aEventType[$row] = $event_typename;
        $aEventTitle[$row] = htmlentities(stripslashes($event_title), ENT_NOQUOTES, 'UTF-8');
        $aEventDesc[$row] = htmlentities(stripslashes($event_desc), ENT_NOQUOTES, 'UTF-8');
        $aEventText[$row] = htmlentities(stripslashes($event_text), ENT_NOQUOTES, 'UTF-8');
        $aEventStartDateTime[$row] = $event_start;
        $aEventEndDateTime[$row] = $event_end;
        $aEventStatus[$row] = $inactive;
        
        // get the list of attend-counts that exists in event_attend for this
        $attendees = EventAttendQuery::create()->findByEventId($event_id);
        
        $attCheckOut[$row] = 0;
        
        if (!empty($attendees)) {
            
            foreach ($attendees as $attende) {
              if ($attende->getCheckoutId()) {
                $attCheckOut[$row]++;
              }          
            }
            
            if ($attCheckOut[$row] > 0) {
              // no statistic for the special counter
              $statisticaAvgRows = false;
            }
        
            $attNumRows[$row] = count($attendees);            
            $numAVG_CheckIn += $attNumRows[$row];
            $numAVG_CheckOut += $attCheckOut[$row];
        }
        
        if ($attNumRows[$row]) {
            $numAVGAtt++;            
        }
    }
    
    if ($numRows > 0) {
        ?>
  <div class='box'>
    <div class='box-header'>
      <h3 class='box-title'><?= ($numRows == 1 ? gettext('There is') : gettext('There are')).' '.$numRows.' '.($numRows == 1 ? gettext('event') : gettext('events')).' '.gettext('for').'  '.gettext(date('F', mktime(0, 0, 0, $mVal, 1, $currYear))) ?></h3>
    </div>
    <div class='box-body'>
  <table class="table table-striped table-bordered data-table"  id="eventsTable" style="width:100%">
    <thead>
      <tr class="TableHeader">
        <th><?= gettext("Action") ?></th>
        <th><?= gettext("Description") ?></th>
        <th><?= gettext("Event Type") ?></th>
        <th><?= gettext("Attendance Counts with real Attendees") ?></th>
        <th><?= gettext("Free Attendance Counts without Attendees") ?></th>
        <th><?= gettext("Start Date/Time") ?></th>
        <th><?= gettext("Active") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php
        for ($row = 1; $row <= $numRows; $row++) {
            ?>
          <tr>
            <td>
              <table class='table-responsive'>
                <tr>
                  <td>
                    <form name="EditEvent" action="EventEditor.php" method="POST">
                      <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
                      <button type="submit" name="Action" title="<?= gettext('Edit') ?>" value="Edit" data-tooltip class="btn btn-default btn-sm">
                        <i class='fa fa-pencil'></i>
                      </button>
                    </form>
                  </td>
                  <td>
                    <form name="EditAttendees" action="EditEventAttendees.php" method="POST">
                      <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
                      <input type="hidden" name="EName" value="<?= $aEventTitle[$row] ?>">
                      <input type="hidden" name="EDesc" value="<?= $aEventDesc[$row] ?>">
                      <input type="hidden" name="EDate" value="<?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?>">
                    </form>
                  </td>
                  <td>
                    <form name="DeleteEvent" class="DeleteEvent" action="ListEvents.php" method="POST">
                      <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
                      <input type="hidden" name="Action" value="Delete">
                      <button type="submit" name="Action" title="<?=gettext('Delete') ?>" data-tooltip value="Delete" class="btn btn-danger btn-sm">
                        <i class='fa fa-trash'></i>
                      </button>
                    </form>
                  </td>
                </tr>
              </table>
            </td>
            <td>
              <?= $aEventTitle[$row] ?>
              <?= ($aEventDesc[$row] == '' ? '&nbsp;' : "(".$aEventDesc[$row]).")" ?>
              <?php if ($aEventText[$row] != '') {
                ?>
                <div class='text-bold'><a href="javascript:popUp('GetText.php?EID=<?=$aEventID[$row]?>')" class="btn btn-info btn-sm"><?= gettext("Sermon Text") ?></a></div>
              <?php
            } ?>
            </td>
            <td><?= $aEventType[$row] ?></td>            
            <td>
            <center>
            <?php 
              if ($attNumRows[$row]) { 
            ?>
               <table width='100%' class='table-simple-padding' align="center">
                <tr>
                  <td><b><?= gettext("Check-in") ?></b></td>
                  <td><b><?= gettext("Check-out") ?></b></td>
                  <td><b><?= gettext("Rest") ?></b></td>
                </tr>
                <tr>
                  <td><?= $attNumRows[$row] ?></td>
                  <td><?= $attCheckOut[$row] ?></td>
                  <td><?= $attNumRows[$row]-$attCheckOut[$row] ?></td>
                </tr>
                <tr>
                   <td colspan="3">
                     <center>
                      <table>
                      <tr>
                      <td>
                      <form name="EditAttendees" action="EditEventAttendees.php" method="POST">
                        <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
                         <input type="hidden" name="EName" value="<?= $aEventTitle[$row] ?>">
                        <input type="hidden" name="EDesc" value="<?= $aEventDesc[$row] ?>">
                        <input type="hidden" name="EDate" value="<?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?>">
                        <input type="submit" name="Action" value="<?= gettext('Attendees').'('.$attNumRows[$row].')' ?>" class="btn btn-info btn-sm" >
                      </form>
                      </td>
                      <td>
                      <form action="<?= SystemURLs::getRootPath() ?>/Checkin.php" method="POST">
                        <input type="hidden" name="EventID" value="<?= $aEventID[$row] ?>">
                        <button type="submit" name="Action" title="<?=gettext('Make Check-out') ?>" data-tooltip value="<?=gettext('Make Check-out') ?>" class="btn btn-<?= ($attNumRows[$row]-$attCheckOut[$row] > 0)?"success":"default disabled" ?> btn-sm">
                          <i class='fa fa-check-circle'></i> <?=gettext('Make Check-out') ?>
                        </button>                      
                       </form>
                       </td>
                       </tr>
                       </table>
                     </center>
                   </td>
                </tr>
               </table>
              </center>
            <?php 
              } else {
            ?>
            <form name="EditAttendees" action="EditEventAttendees.php" method="POST">
              <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
              <input type="hidden" name="EName" value="<?= $aEventTitle[$row] ?>">
              <input type="hidden" name="EDesc" value="<?= $aEventDesc[$row] ?>">
              <input type="hidden" name="EDate" value="<?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?>">
              <?= gettext('No Attendance Recorded') ?><br>
              <input type="submit" name="Action" value="<?= gettext('Attendees').'('.$attNumRows[$row].')' ?>" class="btn btn-info btn-sm" >
            </form>
            <?php 
              }
            ?>
            </td>
            <td>
              <table width='100%' class='table-simple-padding'>
                <tr>
                  <?php
                    // RETRIEVE THE list of counts associated with the current event
                    $cvSQL = "SELECT * FROM eventcounts_evtcnt WHERE evtcnt_eventid='$aEventID[$row]' ORDER BY evtcnt_countid ASC";
                    $cvOpps = RunQuery($cvSQL);
                    $aNumCounts = mysqli_num_rows($cvOpps);

                    if ($aNumCounts) {
                        for ($c = 0; $c < $aNumCounts; $c++) {
                            $cRow = mysqli_fetch_array($cvOpps, MYSQLI_BOTH);
                            extract($cRow);
                            $cCountID[$c] = $evtcnt_countid;
                            $cCountName[$c] = $evtcnt_countname;
                            $cCount[$c] = $evtcnt_countcount;
                            $cCountNotes = $evtcnt_notes; ?>
                                <td>
                                  <div class='text-bold'><?= $evtcnt_countname ?></div>
                                  <div><?= $evtcnt_countcount ?></div>
                                </td>
                                <?php
                        }
                    } else {
                        ?>
                      <td>
                        <center>
	                        <?= gettext('No Attendance Recorded') ?>
	                      </center>
                      </td>
                      <?php
                    } ?>
                </tr>
              </table>
            </td>
            <td>
              <?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?>
            </td>
            <td>
              <?= ($aEventStatus[$row] != 0 ? _('No') : _('Yes')) ?>
            </td>

          </tr>
          <?php
        } // end of for loop for # rows for this month

        // calculate averages if this is a single type list
        if ($eType != 'All') {
            $avgSQL = "SELECT evtcnt_countid, evtcnt_countname, AVG(evtcnt_countcount) 
                         from eventcounts_evtcnt, events_event 
                         WHERE eventcounts_evtcnt.evtcnt_eventid=events_event.event_id 
                               AND events_event.event_type='$eType' 
                               AND MONTH(events_event.event_start)='$mVal' 
                               GROUP BY eventcounts_evtcnt.evtcnt_countid ASC ";
                               
            $avgOpps = RunQuery($avgSQL);
            $aAvgRows = mysqli_num_rows($avgOpps); ?>
          <tr>
            <td class="LabelColumn"><?= gettext(' Monthly Averages') ?></td>
            <td></td>
            <td></td>
            <td>
              <center>
              <?php
                if ($numAVGAtt > 0) {
              ?>
               <table width='100%' class='table-simple-padding' align="center">
                  <tr>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= gettext("AVG") ?><br><?= gettext("Check-in") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckIn/$numAVGAtt) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= gettext("AVG") ?><br><?= gettext("Check-out") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckOut/$numAVGAtt) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= gettext("AVG") ?><br><?= gettext("Rest") ?></strong>
                        <br><?= sprintf('%01.2f', ($numAVG_CheckIn-$numAVG_CheckOut)/$numAVGAtt) ?></span>
                     </td>
                  </tr>
               </table>
              <?php 
              } else {
                 echo  gettext('No Attendance Recorded');
              } 
              ?>
              </center>
            </td>
            <td>
              <div class='row'>
                <center>
                <?php 
                  if ($aAvgRows > 0) {
                ?>
                <table width=100%>
                  <tr>
                <?php
                   $count=0;
                // calculate and report averages
                for ($c = 0; $c < $aAvgRows; $c++) {
                   $count++;
                   if ($count == 0) {
                  ?>
                      </tr>
                      <tr>                      
                  <?php
                      
                   }
                   
                    $count%=3;
                    $avgRow = mysqli_fetch_array($avgOpps, MYSQLI_BOTH);
                    extract($avgRow);
                    $avgName = $avgRow['evtcnt_countname'];
                    $avgAvg = $avgRow[2]; ?>
                  <td align="center">
                    <span class="SmallText">
                    <strong><?= gettext("AVG") ?><br><?= $avgName ?></strong>
                    <br><?= sprintf('%01.2f', $avgAvg) ?></span>
                  </td>
                  <?php
                } ?>
                </tr>
                </table>
                <?php 
                } else {
                   echo  gettext('No Attendance Recorded');
                }
                ?>
                </center>
              </div>
            </td>            
            <td></td>
            <td></td>
          </tr>
          <?php
        } 
        
        // calculate averages if this is a single type list
        if ($eType != 'All' && $aNumCounts > 0) {
            $avgSQL = "SELECT evtcnt_countid, evtcnt_countname, SUM(evtcnt_countcount) 
                         from eventcounts_evtcnt, events_event 
                         WHERE eventcounts_evtcnt.evtcnt_eventid=events_event.event_id 
                               AND events_event.event_type='$eType' 
                               AND MONTH(events_event.event_start)='$mVal' 
                               GROUP BY eventcounts_evtcnt.evtcnt_countid ASC ";
                               
            $avgOpps = RunQuery($avgSQL);
            $aAvgRows = mysqli_num_rows($avgOpps); ?>
          <tr>
            <td class="LabelColumn"> <?= gettext('Monthly Counts') ?></td>
            <td></td>
            <td></td>            
            <td>
              <center>
              <?php 
                if ($numAVGAtt > 0) {
              ?>
               <table width='100%' class='table-simple-padding' align="center">
                  <tr>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= gettext("Total") ?><br><?= gettext("Check-in") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckIn) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= gettext("Total") ?><br><?= gettext("Check-out") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckOut) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= gettext("Total") ?><br><?= gettext("Rest") ?></strong>
                        <br><?= sprintf('%01.2f', ($numAVG_CheckIn-$numAVG_CheckOut)) ?></span>
                     </td>
                  </tr>
               </table>
              <?php
              } else {
                echo  gettext('No Attendance Recorded');
              } ?>
             </center>
            </td>
            <td>
              <div class='row'>
                <center>
                <?php 
                  if ($aAvgRows > 0) {
                ?>                
                <table width=100%>
                  <tr>
                <?php
                   $count=0;
                // calculate and report averages
                for ($c = 0; $c < $aAvgRows; $c++) {
                   $count++;
                   if ($count == 0) {
                  ?>
                      </tr>
                      <tr>                      
                  <?php
                      
                   }
                   
                    $count%=3;
                    $avgRow = mysqli_fetch_array($avgOpps, MYSQLI_BOTH);
                    extract($avgRow);
                    $avgName = $avgRow['evtcnt_countname'];
                    $avgAvg = $avgRow[2]; ?>
                  <td align="center">
                    <span class="SmallText">
                    <strong><?= gettext("Total") ?><br><?= $avgName ?></strong>
                    <br><?= sprintf('%01.2f', $avgAvg) ?></span>
                  </td>
                  <?php
                } ?>
                </tr>
                </table>
                <?php 
                } else {
                   echo  gettext('No Attendance Recorded');
                }
                ?>
                </center>
              </div>
            </td>
            <td></td>
            <td></td>
          </tr>
          <?php
        } ?>
      </tbody>
    </table>
  </div>
  </div>
  <?php
    }
} // end for-each month loop
?>

<div>
  <a href="calendar.php" class='btn btn-default'>
    <i class='fa fa-chevron-left'></i>
    <?= gettext('Return to Calendar') ?>
  </a>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
//Added by @saulowulhynek to translation of datatable nav terms
  $(document).ready(function () {
    $("#eventsTable").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true
    });
    
    $('.listEvents').DataTable({"language": {
      "url": window.CRM.plugin.dataTable.language.url
    }});
    
    $('.DeleteEvent').submit(function(e) {
        var currentForm = this;
        e.preventDefault();
        bootbox.confirm({
        title:  i18next.t("Deleting an event will also delete all attendance counts for that event."),
        message:i18next.t("Are you sure you want to DELETE the event ?"),
        buttons: {
          confirm: {
              label: i18next.t('Yes'),
              className: 'btn-danger'
          },
          cancel: {
              label: i18next.t('No'),
              className: 'btn-success'
          }
        },
        callback: function(result) {
            if (result) {
                currentForm.submit();
            }
        }});
    });
  });
</script>


<?php
require 'Include/Footer.php';
?>
