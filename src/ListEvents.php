<?php
/*******************************************************************************
*
*  filename    : ListEvents.php
*  website     : http://www.ecclesiacrm.com
*  function    : List all Church Events
*
*  This code is under copyright not under MIT Licence
*  copyright   : 2018 Philippe Logel all right reserved not MIT licence
*                This code can't be incoprorated in another software without any authorizaion
*  Updated     : 2018/05/13
*
******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;
use EcclesiaCRM\Map\EventCountsTableMap;
use EcclesiaCRM\Map\CalendarinstancesTableMap;
use EcclesiaCRM\Map\PrincipalsTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\SessionUser;

use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\VObject;
use EcclesiaCRM\MyVCalendar;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use Propel\Runtime\Propel;


$eType = 'All';
$ThisYear = date('Y');
$ThisMonth = date('m');

if (isset($_POST['WhichType'])) {
    $eType = InputUtils::LegacyFilterInput($_POST['WhichType']);
} else {
    $eType = 'All';
}

if ($eType == '0') {
    $sPageTitle = _('Listing Events of Type = ')._("Personal Calendar");
} elseif ($eType != 'All') {
    $eventType = EventTypesQuery::Create()->findOneById($eType);

    $sPageTitle = _('Listing Events of Type = ').$eventType->GetName();
} else {
    $sPageTitle = _('Listing All Church Events');
}

// retrieve the year selector

if (isset($_POST['WhichYear'])) {
    $yVal = InputUtils::LegacyFilterInput($_POST['WhichYear'], 'int');
} else {
    $yVal = date('Y');
}


if (isset($_POST['WhichMonth'])) {
    $EventMonth = InputUtils::LegacyFilterInput($_POST['WhichMonth'], 'int');
} else {
    $EventMonth = 0;//date('m');
}


///////////////////////
require 'Include/Header.php';


if (isset($_POST['Action']) && isset($_POST['EID'])) {
    $eID = InputUtils::LegacyFilterInput($_POST['EID'], 'int');
    $action = InputUtils::LegacyFilterInput($_POST['Action']);
    if ($action == 'Delete' && $eID) {
        $propel_event = EventQuery::Create()->findOneById($eID);

        $calendarId = [$propel_event->getEventCalendarid(),0];

        // new way to manage events
        // we get the PDO for the Sabre connection from the Propel connection
        $pdo = Propel::getConnection();

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        $event = $calendarBackend->getCalendarObjectById($calendarId,$eID);

        // We have to use the sabre way to ensure the event is reflected in external connection : CalDav
        $calendarBackend->deleteCalendarObject($calendarId, $event['uri']);
    } elseif ($action == 'Activate' && $eID) {
        $event = EventQuery::Create()->findOneById($eID);
        $event->setInActive (0);
        $event->save();
    }
}

/// top of main form
//
$eventTypes = EventTypesQuery::Create()
                  ->addJoin(EventTypesTableMap::COL_TYPE_ID, EventTableMap::COL_EVENT_TYPE,Criteria::RIGHT_JOIN)
                  ->setDistinct(EventTypesTableMap::COL_TYPE_ID)
                  ->orderById()
                  ->find();

?>


<div class='text-center'>
  <a class='btn btn-primary' id="add-event">
    <i class='fa fa-ticket'></i>
    <?= _('Add New Event') ?>
  </a>
</div>

<form name="EventTypeSelector" method="POST" action="ListEvents.php">
<div class="row">
<div class="col-sm-4">
<label><?= _('Select Event Types To Display') ?></label>
      <select name="WhichType" onchange="javascript:this.form.submit()" class='form-control'>
        <option value="All" <?= ($eType == 'All')?'selected':'' ?>><?= _('All') ?></option>
        <?php
        foreach ($eventTypes as $eventType) {
          if ($eventType->getId() == null) {
         ?>
          <option value="0" <?= ($eType == '0' && $eType !='All')?'selected':'' ?>><?= _("Personal Calendar") ?></option>
         <?php } else { ?>
          <option value="<?= $eventType->getId() ?>" <?= ($eventType->getId() == $eType)?'selected':'' ?>><?= $eventType->getName() ?></option>
        <?php
          }
        }
        ?>
      </select>
</td>

<?php
// year selector

if ($eType == 'All') {
    $years = EventQuery::Create()
                ->addAsColumn('year','YEAR('.EventTableMap::COL_EVENT_START.')')
                ->select('year')
                ->setDistinct()
                ->where('YEAR('.EventTableMap::COL_EVENT_START.')')
                ->find();

} else {
    $years = EventQuery::Create()
            ->filterByType ($eType)
            ->addAsColumn('year','YEAR('.EventTableMap::COL_EVENT_START.')')
            ->select('year')
            ->setDistinct()
            ->where('YEAR('.EventTableMap::COL_EVENT_START.')')
            ->find();

}
?>
</div>
<div class="col-sm-4">
<label><?= _('Display Events in Month') ?></label>
    <select name="WhichMonth" onchange="javascript:this.form.submit()" class='form-control'>
          <option value="0" <?= ($EventMonth == 0)?'selected':'' ?>><?= _("All") ?></option>
          <option value="-1" disabled="disabled">_________________________</option>
          <option value="1" <?= ($EventMonth == 1)?'selected':'' ?>><?= _("January") ?></option>
          <option value="2" <?= ($EventMonth == 2)?'selected':'' ?>><?= _("February") ?></option>
          <option value="3" <?= ($EventMonth == 3)?'selected':'' ?>><?= _("March") ?></option>
          <option value="4" <?= ($EventMonth == 4)?'selected':'' ?>><?= _("April") ?></option>
          <option value="5" <?= ($EventMonth == 5)?'selected':'' ?>><?= _("May") ?></option>
          <option value="6" <?= ($EventMonth == 6)?'selected':'' ?>><?= _("June") ?></option>
          <option value="7" <?= ($EventMonth == 7)?'selected':'' ?>><?= _("July") ?></option>
          <option value="8" <?= ($EventMonth == 8)?'selected':'' ?>><?= _("August") ?></option>
          <option value="9" <?= ($EventMonth == 9)?'selected':'' ?>><?= _("September") ?></option>
          <option value="10" <?= ($EventMonth == 10)?'selected':'' ?>><?= _("October") ?></option>
          <option value="11" <?= ($EventMonth == 11)?'selected':'' ?>><?= _("November") ?></option>
          <option value="12" <?= ($EventMonth == 12)?'selected':'' ?>><?= _("December") ?></option>
      </select>
</div>
<div class="col-sm-4"><label><?= _('Display Events in Year') ?></label>
    <select name="WhichYear" onchange="javascript:this.form.submit()" class='form-control'>
        <?php
          $current_Year = date('Y');

          $is_current_available = false;
          $is_option_selected   = false;

          foreach ($years as $year) {
            if ($year == $current_Year) {
              $is_current_available = true;
            }

            if ($year == $yVal && $year != $current_Year) {
              $is_option_selected = true;
            }
        ?>
          <option value="<?= $year ?>" <?= ($year == $yVal)?'selected':'' ?>><?= $year ?></option>
        <?php
          }
          if (!$is_current_available) {
        ?>
		        <option value="<?= $current_Year ?>" <?= (!$is_option_selected)?"selected":"" ?>><?= $current_Year ?></option>
        <?php
          }
        ?>
      </select>
</div>
</div>
</form>

<br>
<?php

// Get data for the form as it now exists..
// for this year
$currYear = date('Y');
$currMonth = date('m');

if ($EventMonth == 0) {
  $allMonths = ['12', '11', '10', '9', '8', '7', '6', '5', '4', '3', '2', '1'];
} else {
  $allMonths = [$EventMonth];
}


$statisticaAvgRows = true;

foreach ($allMonths as $mVal) {
    unset($cCountSum);

    $onlyUser = "";

    if (!(SessionUser::getUser()->isAdmin())) {
      $onlyUser = " AND ".PrincipalsTableMap::COL_URI."='principals/".strtolower(SessionUser::getUser()->getUserName())."'";
    }

    if ($eType == 'All') {
      $events = EventQuery::Create()
         ->orderByStart('DESC')
           ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID,Criteria::RIGHT_JOIN)
           ->addJoin(CalendarinstancesTableMap::COL_PRINCIPALURI, PrincipalsTableMap::COL_URI,Criteria::RIGHT_JOIN)
           ->addAsColumn('login',PrincipalsTableMap::COL_URI)
           ->addAsColumn('rights',CalendarinstancesTableMap::COL_ACCESS)
           ->addAsColumn('calendarName',CalendarinstancesTableMap::COL_DISPLAYNAME)
           ->where('MONTH('.EventTableMap::COL_EVENT_START.') = '.$mVal.' AND YEAR('.EventTableMap::COL_EVENT_START.')='.$yVal.$onlyUser)
         ->groupBy(EventTableMap::COL_EVENT_ID)
            ->find();

    } else {
      $events = EventQuery::Create()
         ->filterByType($eType)
         ->orderByStart('DESC')
           ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID,Criteria::RIGHT_JOIN)
           ->addJoin(CalendarinstancesTableMap::COL_PRINCIPALURI, PrincipalsTableMap::COL_URI,Criteria::RIGHT_JOIN)
           ->addAsColumn('login',PrincipalsTableMap::COL_URI)
           ->addAsColumn('rights',CalendarinstancesTableMap::COL_ACCESS)
           ->addAsColumn('calendarName',CalendarinstancesTableMap::COL_DISPLAYNAME)
         ->where('MONTH('.EventTableMap::COL_EVENT_START.') = '.$mVal.' AND YEAR('.EventTableMap::COL_EVENT_START.')='.$yVal.$onlyUser)
         ->groupBy(EventTableMap::COL_EVENT_ID)
         ->find();
    }




    $numRows = 0;
    if ( !empty($events) ) {
      $numRows = $events->count();
    }
    $aAvgRows = $numRows;


    $numAVGAtt = 0;
    $numAVG_CheckIn = 0;
    $numAVG_CheckOut = 0;

    $row=1;


    foreach ($events as $event) {
        // get the list of attend-counts that exists in event_attend for this
        $aEventID[$row] = $event->getId();

        if ( SessionUser::getUser()->isAdmin() ) {
          $aLogin[$row] = _("Name").":"."<b>".$event->getCalendarName()."</b><br>"._("login").":<b>".str_replace("principals/","",$event->getLogin())."</b>";
        } else {
          $aLogin[$row] = _("Name").":"."<b>".$event->getCalendarName()."</b>";
        }

        $aEventType[$row] = $event->getTypeName();
        $aEventTitle[$row] = htmlentities(stripslashes($event->getTitle()), ENT_NOQUOTES, 'UTF-8');
        $aEventDesc[$row] = htmlentities(stripslashes($event->getDesc()), ENT_NOQUOTES, 'UTF-8');
        $aEventText[$row] = htmlentities(stripslashes($event->getText()), ENT_NOQUOTES, 'UTF-8');
        $aEventStartDateTime[$row] = $event->getStart()->format(SystemConfig::getValue('sDateFormatLong'));//.' H:i:s');
        $aEventEndDateTime[$row] = $event->getEnd()->format(SystemConfig::getValue('sDateFormatLong'));//.' H:i:s');
        $aEventStatus[$row] = $event->getInactive();
        if (!(SessionUser::getUser()->isAdmin())) {
          $aEventRights[$row] = ($event->getRights() == 1 || $event->getRights() == 3)?true:false;
        } else {
          $aEventRights[$row] = true;
        }

        $attendees = EventAttendQuery::create()->findByEventId($event->getId());

        $attCheckOut[$row] = 0;
        $realAttCheckOut[$row] = 0;

        if (!empty($attendees)) {
            foreach ($attendees as $attende) {
              if ($attende->getCheckoutDate()) {
                $attCheckOut[$row]++;
              }

              if ($attende->getCheckoutId()) {
                $realAttCheckOut[$row]++;
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

        if ($attNumRows[$row++]) {
            $numAVGAtt++;
        }
    }

    if ($numRows > 0) {
        ?>
  <div class='card'>
    <div class='card-header'>
      <h3 class='card-title'><?= ($numRows == 1 ? _('There is') : _('There are')).' '.$numRows.' '.($numRows == 1 ? _('event') : _('events')).' '._('for').'  '._(date('F', mktime(0, 0, 0, $mVal, 1, $currYear))) ?></h3>
    </div>
    <div class='card-body'>
  <table class="table table-striped table-bordered data-table eventsTable" style="width:100%">
    <thead>
      <tr class="TableHeader">
        <th><?= _("Action") ?></th>
        <th><?= _("Title") ?></th>
        <th><?= _("Event Type") ?></th>
        <th><?= _("Calendar") ?></th>
        <th><?= _("Attendance Counts with real Attendees") ?></th>
        <th><?= _("Free Attendance Counts without Attendees") ?></th>
        <th><?= _("Start Date/Time") ?></th>
        <th><?= _("End Date/Time") ?></th>
        <th><?= _("Active") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php
        for ($row = 1; $row <= $numRows; $row++) {
            ?>
          <tr>
            <td>
               <table class='table-responsive'>
                <tr class="no-background-theme">
                  <td>
                    <a title="<?= _('Edit') ?>" value="Edit" data-id="<?= $aEventID[$row] ?>" data-tooltip class="<?= !($aEventRights[$row])?"disabled":" EditEvent" ?>">
                        <i class='fa fa-pencil'></i>
                    </a>
                  </td>
                  <td>
                    <?php
                      if ($aEventRights[$row]) {
                    ?>
                    <form name="DeleteEvent" class="DeleteEvent" action="ListEvents.php" method="POST">
                    <?php
                      }
                    ?>
                      <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
                      <input type="hidden" name="Action" value="Delete">
                      <button type="submit" name="Action" title="<?=_('Delete') ?>" data-tooltip value="Delete" class="<?= !($aEventRights[$row])?"disabled":"" ?>" style="background:none;border:0px;color:red">
                        <i class='fa fa-trash'></i>
                      </button>
                    <?php
                      if ($aEventRights[$row]) {
                    ?>
                    </form>
                    <?php
                      }
                    ?>
                  </td>
                </tr>
              </table>
            </td>
            <td>
              <?= $aEventTitle[$row]/*." ID=[".$aEventID[$row]."]"*/ ?>
              <?= ($aEventDesc[$row] == '' ? '&nbsp;' : ("(".$aEventDesc[$row].")")) ?>
              <?php if ($aEventText[$row] != '') {
                ?>
                <div class='text-bold'><a href="javascript:popUp('GetText.php?EID=<?=$aEventID[$row]?>')" class="btn btn-info btn-sm"><?= _("Notes") ?></a></div>
              <?php
            } ?>
            </td>
            <td><?= empty($aEventType[$row])?_("Personal Calendar"):$aEventType[$row] ?></td>
            <td>
               <?= $aLogin[$row] ?>
            </td>
            <td>
            <?php
              if ($attNumRows[$row]) {
            ?>
               <table width='100%' class='table-simple-padding' align="center">
                   <tr class="no-background-theme">
                       <td><b><?= _("Check-in") ?></b></td>
                       <td><b><?= _("Check-out") ?></b></td>
                       <td><b><?= _("Rest") ?></b></td>
                   </tr>
                    <tr class="no-background-theme">
                      <td><?= $attNumRows[$row] ?></td>
                      <td><?= $attCheckOut[$row] ?></td>
                      <td><?= $attNumRows[$row]-$attCheckOut[$row] ?></td>
                    </tr>
                    <tr class="no-background-theme">
                       <td colspan="3">
                            <table style="width:330px">
                                <tr class="no-background-theme">
                                    <td>
                            <?php
                              if ($aEventRights[$row]) {
                            ?>
                                        <form name="EditAttendees" action="EditEventAttendees.php" method="POST">
                            <?php
                              }
                            ?>
                                            <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
                                            <input type="hidden" name="EName" value="<?= $aEventTitle[$row] ?>">
                                            <input type="hidden" name="EDesc" value="<?= $aEventDesc[$row] ?>">
                                            <input type="hidden" name="EDate" value="<?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?>">
                                            <input type="submit" name="Action" value="<?= _('Attendees').'('.$attNumRows[$row].')' ?>" class="btn btn-info btn-sm <?= !($aEventRights[$row])?"disabled":"" ?>" >
                            <?php
                              if ($aEventRights[$row]) {
                            ?>
                                        </form>
                            <?php
                              }
                            ?>
                                    </td>
                                    <td>
                            <?php
                              if ($aEventRights[$row]) {
                            ?>
                                        <form action="<?= SystemURLs::getRootPath() ?>/Checkin.php" method="POST">
                            <?php
                              }
                            ?>
                                            <input type="hidden" name="EventID" value="<?= $aEventID[$row] ?>">
                                            <button type="submit" name="Action" title="<?=_('Make Check-out') ?>" data-tooltip value="<?=_('Make Check-out') ?>" class="btn btn-<?= ($attNumRows[$row]-$realAttCheckOut[$row] > 0)?"success":"default" ?> btn-sm <?= !($aEventRights[$row])?"disabled":"" ?>">
                                              <i class='fa fa-check-circle'></i> <?= _("Make Check-out") ?>
                                            </button>
                            <?php
                              if ($aEventRights[$row]) {
                            ?>
                                        </form>
                            <?php
                              }
                            ?>
                                    </td>
                               </tr>
                    </table>
                   </td>
                </tr>
               </table>

            <?php
              } else {
            ?>
            <form name="EditAttendees" action="EditEventAttendees.php" method="POST">
              <input type="hidden" name="EID" value="<?= $aEventID[$row] ?>">
              <input type="hidden" name="EName" value="<?= $aEventTitle[$row] ?>">
              <input type="hidden" name="EDesc" value="<?= $aEventDesc[$row] ?>">
              <input type="hidden" name="EDate" value="<?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?>">
              <?= _('No Attendance Recorded') ?><br>
              <input type="submit" name="Action" value="<?= _('Attendees').'('.$attNumRows[$row].')' ?>" class="btn btn-info btn-sm" >
            </form>
            <?php
              }
            ?>
            </td>
            <td>
              <table width='100%' class='table-simple-padding'>
                <tr class="no-background-theme">
                  <?php
                    // RETRIEVE THE list of counts associated with the current event
                    $eventCounts = EventCountsQuery::Create()->filterByEvtcntEventid($aEventID[$row])->orderByEvtcntCountid(Criteria::ASC)->find();

                    if (!empty($eventCounts)) {
                      $c=0;
                      $aNumCounts = $eventCounts->count();

                      foreach ($eventCounts as $eventCount) {
                          $cCountID[$c] = $eventCount->getEvtcntCountid();
                          $cCountName[$c] = $eventCount->getEvtcntCountname();
                          $cCount[$c] = $eventCount->getEvtcntCountcount();
                          $cCountNotes = $eventCount->getEvtcntNotes(); ?>
                          <td>
                              <div class='text-bold'><?= $eventCount->getEvtcntCountname() ?></div>
                              <div><?= $eventCount->getEvtcntCountcount() ?></div>
                          </td>
                      <?php
                         $c++;
                      }

                    } else {
                        ?>
                      <td>
                        <p class="text-center">
                          <?= _('No Attendance Recorded') ?>
                        </p>
                      </td>
                      <?php
                    } ?>
                </tr>
              </table>
            </td>
            <td>
              <?= $aEventStartDateTime[$row] ?>
            </td>
            <td>
              <?= $aEventEndDateTime[$row] ?>
            </td>
            <td style="color:<?= $aEventStatus[$row]?"red":"green" ?>;text-align:center">
              <?= ($aEventStatus[$row] != 0 ? _('No') : _('Yes')) ?>
            </td>

          </tr>
          <?php
        } // end of for loop for # rows for this month

        // calculate averages if this is a single type list

        if ($eType != 'All') {
            $real_counts = EventCountsQuery::Create()
                ->useEventQuery()
                  ->filterByType($eType)
                  ->addAsColumn('monthStart','MONTH('.EventTableMap::COL_EVENT_START.')')
                  ->addAsColumn('yearStart','YEAR('.EventTableMap::COL_EVENT_START.')')
                ->endUse()
                ->where('YEAR('.EventTableMap::COL_EVENT_START.')='.$yVal.' AND MONTH('.EventTableMap::COL_EVENT_START.')='.$mVal)
                ->addAsColumn('avg','AVG('.EventCountsTableMap::COL_EVTCNT_COUNTCOUNT.')')
                ->addAsColumn('sum','SUM('.EventCountsTableMap::COL_EVTCNT_COUNTCOUNT.')')
                ->groupByEvtcntCountid()
                ->find();

            ?>


          <tr class="no-background-theme">
            <td class="LabelColumn"><?= _(' Monthly Averages') ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <?php
                if ($numAVGAtt > 0) {
              ?>
               <table width='100%' class='table-simple-padding' align="center">
                  <tr class="no-background-theme">
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= _("AVG") ?><br><?= _("Check-in") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckIn/$numAVGAtt) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= _("AVG") ?><br><?= _("Check-out") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckOut/$numAVGAtt) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= _("AVG") ?><br><?= _("Rest") ?></strong>
                        <br><?= sprintf('%01.2f', ($numAVG_CheckIn-$numAVG_CheckOut)/$numAVGAtt) ?></span>
                     </td>
                  </tr>
               </table>
              <?php
              } else {
                 echo  _('No Attendance Recorded');
              }
              ?>

            </td>
            <td>
              <div class='row'>
                <?php
                  if ($aAvgRows > 0) {
                ?>
                <table width=100%>
                  <tr class="no-background-theme">
                <?php
                   $count=0;
                // calculate and report averages

                foreach ($real_counts as $real_count) {
                   $count++;
                   if ($count == 0) {
                  ?>
                      </tr>
                      <tr class="no-background-theme">
                  <?php

                   }

                    $count%=3;
                  ?>
                  <td align="center">
                    <span class="SmallText">
                    <strong><?= _("AVG") ?><br><?= $real_count->getEvtcntCountname() ?></strong>
                    <br><?= sprintf('%01.2f', $real_count->getAvg()) ?></span>
                  </td>
                  <?php
                } ?>
                </tr>
                </table>
                <?php
                } else {
                   echo  _('No Attendance Recorded');
                }
                ?>

              </div>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <?php
        }

        // calculate averages if this is a single type list
        if ($eType != 'All' && $aNumCounts > 0) {
      ?>
          <tr class="no-background-theme">
            <td class="LabelColumn"> <?= _('Monthly Counts') ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <?php
                if ($numAVGAtt > 0) {
              ?>
               <table width='100%' class='table-simple-padding' align="center">
                  <tr class="no-background-theme">
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= _("Total") ?><br><?= _("Check-in") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckIn) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= _("Total") ?><br><?= _("Check-out") ?></strong>
                        <br><?= sprintf('%01.2f', $numAVG_CheckOut) ?></span>
                     </td>
                     <td align="center">
                        <span class="SmallText">
                        <strong><?= _("Total") ?><br><?= _("Rest") ?></strong>
                        <br><?= sprintf('%01.2f', ($numAVG_CheckIn-$numAVG_CheckOut)) ?></span>
                     </td>
                  </tr>
               </table>
              <?php
              } else {
                echo  _('No Attendance Recorded');
              } ?>
            </td>
            <td>
              <div class='row'>
                <?php
                  if ($aAvgRows > 0) {
                ?>
                <table width=100%>
                  <tr class="no-background-theme">
                <?php
                   $count=0;
                // calculate and report averages
                foreach ($real_counts as $real_count) {
                   $count++;
                   if ($count == 0) {
                  ?>
                      </tr>
                      <tr class="no-background-theme">
                  <?php

                   }

                    $count%=3;
                  ?>
                  <td align="center">
                    <span class="SmallText">
                    <strong><?= _("Total") ?><br><?= $real_count->getEvtcntCountname() ?></strong>
                    <br><?= sprintf('%01.2f', $real_count->getSum()) ?></span>
                  </td>
                  <?php
                } ?>
                </tr>
                </table>
                <?php
                } else {
                   echo  _('No Attendance Recorded');
                }
                ?>
              </div>
            </td>
            <td></td>
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
  <a href="<?= SystemURLs::getRootPath() ?>/v2/calendar" class='btn btn-default'>
    <i class='fa fa-chevron-left'></i>
    <?= _('Return to Calendar') ?>
  </a>
</div>

<link href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/EventEditor.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/ListEvent.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/publicfolder.js"></script>

<?php
  if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/GoogleMapEvent.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/BingMapEvent.js"></script>
<?php
  }
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.isModifiable  = "true";

  window.CRM.churchloc = {
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
  window.CRM.mapZoom   = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<?php
require 'Include/Footer.php';
?>
