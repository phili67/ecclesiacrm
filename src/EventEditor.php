<?php
/*******************************************************************************
 *
 *  filename    : EventEditor.php
 *  last change : 2005-09-10
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Todd Pillars
 *                Copyright 2012 Michael Wilt
 *                Copyright 2018 Philippe Logel all rights reserved
 *
 *  function    : Editor for Church Events
  *
 ******************************************************************************/
// table fields
//  event_id       int(11)
//  event_type     enum('CS', 'SS', 'VOL')
//  event_title    varchar(255)
//  event_desc     varchar(255)
//  event_text     text
//  event_start    datetime
//  event_end      datetime
//  inactive       int(1) default 0

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\EventCounts;

$sPageTitle = gettext('Church Event Editor');

if (!$_SESSION['user']->isAdmin() && !$_SESSION['bAddEvent']) {
    header('Location: Menu.php');
}

require 'Include/Header.php';

if (isset($_GET['calendarAction'])) {
    $sAction = 'Edit';
    $sOpp = $_GET['calendarAction'];
} else {
    if (array_key_exists('Action', $_POST)) {
        $sAction = $_POST['Action'];
    }

    if (array_key_exists('EID', $_POST)) {
        $sOpp = $_POST['EID'];
    } // from EDIT button on event listing

    if (array_key_exists('EN_tyid', $_POST)) {
        $tyid = $_POST['EN_tyid'];
    } // from event type list page
    else {
        $tyid = 0;
    }
}

$iEventID = 0;
$iErrors = 0;

//
// process the action inputs
//
if ($sAction = 'Edit' && !empty($sOpp)) {
    // Get data for the form as it now exists..
    $EventExists = 1;
    
    $event = EventQuery::Create()->findOneById($sOpp);
    $eventType = EventTypesQuery::Create()->findOneById($event->getType());

    $iEventID = $event->getId();
    $iTypeID = $event->getType();
    $sTypeName = $eventType->getName();
    $sEventTitle = $event->getTitle();
    $sEventDesc = $event->getDesc();
    $sEventText = $event->getText();
    $aStartTokens = explode(' ', $event->getStart()->format('Y-m-d H:i:s'));
    $sEventStartDate = $aStartTokens[0];
    $aStartTimeTokens = explode(':', $aStartTokens[1]);
    $iEventStartHour = $aStartTimeTokens[0];
    $iEventStartMins = $aStartTimeTokens[1];
    $aEndTokens = explode(' ', $event->getEnd()->format('Y-m-d H:i:s'));
    $sEventEndDate = $aEndTokens[0];
    $aEndTimeTokens = explode(':', $aEndTokens[1]);
    $iEventEndHour = $aEndTimeTokens[0];
    $iEventEndMins = $aEndTimeTokens[1];
    $iEventStatus = $event->getInActive();
    $nEventGroupId = $event->getGroupId();

    $ormOpps = EventCountsQuery::Create()->filterByEvtcntEventid($iEventID)->orderByEvtcntCountid()->find();
    
    $c = 0;
    $iNumCounts = $ormOpps->count();
    $nCnts= $iNumCounts;
    
    foreach ($ormOpps as $ormOpp) {
      $aCountID[$c] = $ormOpp->getEvtcntCountid();
      $aCountName[$c] = $ormOpp->getEvtcntCountname();
      $aCount[$c] = $ormOpp->getEvtcntCountcount();
      $sCountNotes = $ormOpp->getEvtcntNotes();
      $c++;
    }
    
} elseif (isset($_POST['SaveChanges'])) {
    // Does the user want to save changes to text fields?
    $iEventID = $_POST['EventID'];
    $iTypeID = $_POST['EventTypeID'];
    $EventExists = $_POST['EventExists'];
    $sEventTitle = $_POST['EventTitle'];
    $sEventDesc = $_POST['EventDesc'];
    if (empty($_POST['EventTypeID'])) {
        $bEventTypeError = true;
        $iErrors++;
    } else {
        $eventType = EventTypesQuery::Create()->findOneById(InputUtils::LegacyFilterInput($iTypeID));
        $sTypeName = $eventType->getName();
    }
    $sEventText = $_POST['EventText'];
    if ($_POST['EventStatus'] === null) {
        $bStatusError = true;
        $iErrors++;
    }
    
    $sEventRange = $_POST['EventDateRange'];
    
    // this part is in function of the format : ie the language
    $datePickerPlaceHolder =  SystemConfig::getValue("sDatePickerPlaceHolder");
    
    $datePickerPlaceHolder = str_replace("dd","d",$datePickerPlaceHolder);
    $datePickerPlaceHolder = str_replace("mm","m",$datePickerPlaceHolder);
    $datePickerPlaceHolder = str_replace("yyyy","Y",$datePickerPlaceHolder);
    
    $frmt = $datePickerPlaceHolder." H:i".((SystemConfig::getValue("sTimeEnglish"))?" a":"");
    
    $sEventStartDateTime = DateTime::createFromFormat($frmt, explode(' - ', $sEventRange)[0]);
    $sEventEndDateTime = DateTime::createFromFormat($frmt, explode(' - ', $sEventRange)[1]);
    $sEventStart = $sEventStartDateTime->format('Y-m-d H:i');
    $sEventStartDate = $sEventStartDateTime->format('Y-m-d');
    $iEventStartHour = $sEventStartDateTime->format('H');
    $iEventStartMins = $sEventStartDateTime->format('i');
    $sEventEnd = $sEventEndDateTime->format('Y-m-d H:i');
    $sEventEndDate = $sEventEndDateTime->format('Y-m-d');
    $iEventEndHour = $sEventEndDateTime->format('H');
    $iEventEndMins = $sEventEndDateTime->format('i');
    $iEventStatus = $_POST['EventStatus'];
    $nEventGroupId = $_POST['EventGroup'];

    $iNumCounts = $_POST['NumAttendCounts'];
    $nCnts = $iNumCounts;
    $aEventCountArry = $_POST['EventCount'];
    $aEventCountIDArry = $_POST['EventCountID'];
    $aEventCountNameArry = $_POST['EventCountName'];

    foreach ($aEventCountArry as $CCC) {
        $aCount[] = $CCC;
    }
    foreach ($aEventCountIDArry as $CID) {
        $aCountID[] = $CID;
    }
    foreach ($aEventCountNameArry as $CNM) {
        $aCountName[] = $CNM;
    }

    $sCountNotes = $_POST['EventCountNotes'];

    // If no errors, then update.
    if ($iErrors == 0) {
        if ($EventExists == 0) {
            $event = new Event();
            
            $event->setType(InputUtils::LegacyFilterInput($iTypeID));
            $event->setTitle(InputUtils::LegacyFilterInput($sEventTitle));
            $event->setDesc(InputUtils::LegacyFilterInput($sEventDesc));
            $event->setText(InputUtils::FilterHTML($sEventText));
            $event->setStart(InputUtils::LegacyFilterInput($sEventStart));
            $event->setEnd(InputUtils::LegacyFilterInput($sEventEnd));
            $event->setInActive(InputUtils::LegacyFilterInput($iEventStatus));
            $event->getTypeName(InputUtils::LegacyFilterInput($sTypeName));
            $event->setGroupId(InputUtils::LegacyFilterInput($nEventGroupId));
            
            $event->save();

            $iEventID = $event->getId();
            
            for ($c = 0; $c < $iNumCounts; $c++) {
                $cCnt = ltrim(rtrim($aCountName[$c]));
                
                $eventCount = EventCountsQuery::Create()->findOneByEvtcntCountcount($aCount[$c]);
                
                if (empty($eventCount)) {
                  $eventCount = new EventCounts();
                }
                
                $eventCount->setEvtcntEventid(InputUtils::LegacyFilterInput($iEventID));
                $eventCount->setEvtcntCountid(InputUtils::LegacyFilterInput($aCountID[$c]));
                $eventCount->setEvtcntCountname(InputUtils::LegacyFilterInput($aCountName[$c]));
                $eventCount->setEvtcntCountcount(InputUtils::LegacyFilterInput($aCount[$c]));
                $eventCount->setEvtcntNotes(InputUtils::LegacyFilterInput($sCountNotes));
                
                $eventCount->save();
            }
        } else {
            echo "coucou";
                        
            $event = EventQuery::Create()->findOneById(InputUtils::LegacyFilterInput($iEventID));
            
            $event->setType(InputUtils::LegacyFilterInput($iTypeID));
            $event->setTitle(InputUtils::LegacyFilterInput($sEventTitle));
            $event->setDesc(InputUtils::LegacyFilterInput($sEventDesc));
            $event->setText(InputUtils::FilterHTML($sEventText));
            $event->setStart(InputUtils::LegacyFilterInput($sEventStart));
            $event->setEnd(InputUtils::LegacyFilterInput($sEventEnd));
            $event->setInActive(InputUtils::LegacyFilterInput($iEventStatus));
            $event->getTypeName(InputUtils::LegacyFilterInput($sTypeName));
            $event->setGroupId(InputUtils::LegacyFilterInput($nEventGroupId));
            
            $event->save();
            
            
            for ($c = 0; $c < $iNumCounts; $c++) {
                $cCnt = ltrim(rtrim($aCountName[$c]));
                
                $eventCount = EventCountsQuery::Create()
                   ->filterByEvtcntEventid(InputUtils::LegacyFilterInput($iEventID))
                   ->findOneByEvtcntCountid(InputUtils::LegacyFilterInput($aCountID[$c]));
                
                if (empty($eventCount)) {
                  $eventCount = new EventCounts();
                  $eventCount->setEvtcntEventid(InputUtils::LegacyFilterInput($iEventID));
                  $eventCount->setEvtcntCountid(InputUtils::LegacyFilterInput($aCountID[$c]));
                  $eventCount->setEvtcntCountname(InputUtils::LegacyFilterInput($aCountName[$c]));
                }
                
                $eventCount->setEvtcntCountcount(InputUtils::LegacyFilterInput($aCount[$c]));
                $eventCount->setEvtcntNotes(InputUtils::LegacyFilterInput($sCountNotes));
                
                $eventCount->save();
            }
        }
        $EventExists = 1;
        header('Location: ListEvents.php');
    }
}

// Construct the form
?>

<div class='box'>
  <div class='box-header'>
    <h3 class='box-title'>
      <?= ($EventExists == 0) ? gettext('Create a new Event') : gettext('Editing Event ID: ').$iEventID ?>
    </h3>
  </div>
  <div class='box-header'>
    <?php
        if ($iErrors != 0) {
            echo "<div class='alert alert-danger'>".gettext('There were ').$iErrors.gettext(' errors. Please see below').'</div>';
        } else {
            echo '<div>'.gettext('Items with a ').'<span style="color: red">*</span>'.gettext(' are required').'</div>';
        }
        ?>
  </div>

<form method="post" action="EventEditor.php" name="EventsEditor">
<input type="hidden" name="EventID" value="<?= ($iEventID) ?>">
<input type="hidden" name="EventExists" value="<?= $EventExists ?>">

<div class="box-body">
<?php 
  if (empty($iTypeID)) {
?>

  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span><?= gettext('Event Type') ?>:</div>
    <div class="col-md-9">
      <select name='EN_tyid' class='form-control' id='event_type_id' width='100%' style='width: 100%'>
        <option><?= gettext('Select your event type'); ?></option>
        <?php
            $eventTypes = EventTypesQuery::Create()->find();
            
            foreach ($eventTypes as $eventType)
            {
        ?>
            <option value='<?= $eventType->getId() ?>' ><?= $eventType->getName()?></option>
        <?php
            }
        ?>
      </select>
      <?php if ($bEventTypeError) {
                echo '<div><span style="color: red;">'.gettext('You must pick an event type.').'</span></div>';
            } ?>
      <script nonce="<?= SystemURLs::getCSPNonce() ?>" >
        $('#event_type_id').on('change', function(e) {
          e.preventDefault();
          document.forms.EventsEditor.submit();
        });
      </script>
    </div>
  </div>

<?php
        } else { // if (empty($iTypeID))?>

  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span><?= gettext('Event Type') ?>:</div>
    <div class="col-md-9">
    <input type="hidden" name="EventTypeName" value="<?= ($sTypeName) ?>">
    <input type="hidden" name="EventTypeID" value="<?= ($iTypeID) ?>">
    <?= ($iTypeID.'-'.$sTypeName) ?>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span><?= gettext('Event Title') ?>:</div>
    <div class="col-md-9">
      <input type="text" name="EventTitle" value="<?= ($sEventTitle) ?>" size="30" maxlength="100" class='form-control' width="100%" style="width: 100%" required>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span><?= gettext('Event Desc') ?>:</div>
    <div class="col-md-9">
      <textarea name="EventDesc" rows="4" maxlength="100" class='form-control' required width="100%" style="width: 100%"><?= ($sEventDesc) ?></textarea>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span>
      <?= gettext('Date Range') ?>
    </div>
    <div class="col-md-9">
      <input type="text" name="EventDateRange" value=""
             maxlength="10" id="EventDateRange" size="50" class='form-control' width="100%" style="width: 100%" required>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span>
      <?= gettext('Event Group') ?>:
    </div>
    <div class="col-md-9">
      <select type="text" name="EventGroup" class='form-control input-sm' value="<?= $nEventGroupId ?>" width="100%" style="width: 100%">
         <option value="0" <?= ($nEventGroupId == 0 ? "Selected":"") ?>><?= gettext("None") ?></option>
        <?php
          $groups=  EcclesiaCRM\Base\GroupQuery::create()->find();
            foreach ($groups as $group) {
                ?>
         <option value="<?= $group->getId() ?>" <?= ($group->getId() == $nEventGroupId ? "Selected":"") ?>><?= $group->getName() ?></option>
            <?php
            } ?>
      </select>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-3"><?= gettext('Free Attendance Counts without Attendees') ?></div>
    <div class="col-md-9">
      <input type="hidden" name="NumAttendCounts" value="<?= $nCnts ?>">
      <?php
      if ($nCnts == 0) {
          echo gettext('No Attendance counts recorded');
      } else {
          ?>
    <table>
      <?php
      for ($c = 0; $c < $nCnts; $c++) {
          ?><tr>
          <td><strong><?= (gettext($aCountName[$c]).':') ?>&nbsp;</strong></td>
        <td>
        <input type="text" name="EventCount[]" value="<?= ($aCount[$c]) ?>" size="8" class='form-control'>
        <input type="hidden" name="EventCountID[]" value="<?= ($aCountID[$c]) ?>">
        <input type="hidden" name="EventCountName[]" value="<?= ($aCountName[$c]) ?>">
        </td>
        </tr>
      <?php
      } //end for loop
      ?>
      <tr>
      <td><strong><?= gettext('Attendance Notes: ') ?>&nbsp;</strong></td>
        <td><input type="text" name="EventCountNotes" value="<?= $sCountNotes ?>" class='form-control'>
        </td>
        </tr>
        </table>
        <?php
      } //endif
        ?>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-12"><?= gettext('Event Sermon') ?>:<br>
      <textarea id="#EventText" name="EventText" rows="5" cols="70" class='form-control'><?= ($sEventText) ?></textarea>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-3"><span style="color: red">*</span><?= gettext('Event Status') ?>:</div>
    <div class="col-md-9">
      <input type="radio" name="EventStatus" value="0" <?php if ($iEventStatus == 0) {
            echo 'checked';
        } ?>/> <?= _('Active')?>
      <input type="radio" name="EventStatus" value="1" <?php if ($iEventStatus == 1) {
            echo 'checked';
        } ?>/> <?= _('Inactive')?>
    </div>
  </div>
  <hr/>
  <div class="row">
    <div class="col-md-9">&nbsp;&nbsp;&nbsp;<input type="submit" name="SaveChanges" value="<?= gettext('Save Changes') ?>" class="btn btn-primary">
    </div>
    <div class="col-md-3"></div>
    </div>
<?php
        } // if (empty($iTypeID))?>
</div>
</form>
</div>
<div>
  <a href="ListEvents.php" class='btn btn-default'>
    <i class='fa fa-chevron-left'></i>
    <?= gettext('Return to Events') ?>
  </a>
</div>
<?php
$eventStart = $sEventStartDate.' '.$iEventStartHour.':'.$iEventStartMins;
$eventEnd = $sEventEndDate.' '.$iEventEndHour.':'.$iEventEndMins;
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $( document ).ready(function() {
        var startDate = moment("<?= $eventStart?>", "YYYY-MM-DD h:mm").format("<?= mb_strtoupper(SystemConfig::getValue("sDatePickerPlaceHolder")).((SystemConfig::getValue("sTimeEnglish"))?' h:mm A':' H:mm') ?>");
        var endDate = moment("<?= $eventEnd?>", "YYYY-MM-DD h:mm").format("<?= mb_strtoupper(SystemConfig::getValue("sDatePickerPlaceHolder")).((SystemConfig::getValue("sTimeEnglish"))?' h:mm A':' H:mm') ?>");
        $('#EventDateRange').val(startDate + " - " + endDate);
        $('#EventDateRange').daterangepicker({
            timePicker: true,
            timePickerIncrement: 30,
            linkedCalendars: true,
            showDropdowns: true,
            locale: {
                format: '<?= mb_strtoupper(SystemConfig::getValue("sDatePickerPlaceHolder")).((SystemConfig::getValue("sTimeEnglish"))?' h:mm A':' H:mm') ?>',
                applyLabel: i18next.t('Validate'),
                cancelLabel: i18next.t('Cancel'),
                fromLabel: i18next.t('From'),
                toLabel: i18next.t('to'),
                customRangeLabel: i18next.t('Custom')
            },
            timePicker24Hour:<?= ((SystemConfig::getValue("sTimeEnglish"))?'false':'true') ?>,
            minDate: 1 / 1 / 1900,
            startDate: startDate,
            endDate: endDate
        });
    });
</script>

<?php require 'Include/Footer.php' ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  CKEDITOR.replace('EventText',{
    customConfig: '<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/event_editor_config.js',
    language : window.CRM.lang,
    width : '100%'
  });
</script>
