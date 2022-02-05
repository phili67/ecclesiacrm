<?php
/*******************************************************************************
 *
 *  filename    : EventNames.php
 *  last change : 2005-09-10
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Todd Pillars
 *
 *  function    : List all Church Events
 *
 *  Modified by Stephen Shaffer, Oct 2006
 *  Modified by Philippe Logel, Oct 2018-01-08 and copyright
 *  feature changes - added recurring defaults and customizable attendance count
 *  fields
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\EventTypes;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\EventCountName;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

if ( !SessionUser::getUser()->isAdmin() ) {
    RedirectUtils::Redirect('v2/dashboard');
}

$sPageTitle = _('Edit Event Types');

require 'Include/Header.php';

//
//  process the ACTION button inputs from the form page
//

if (isset($_POST['Action'])) {
  switch (InputUtils::LegacyFilterInput($_POST['Action'])) {
    case 'CREATE':
    // Insert into the event_name table
      $eName = $_POST['newEvtName'];
      $eTime = $_POST['newEvtStartTime'];
      $eDOM = (empty($_POST['newEvtRecurDOM']))?"0":$_POST['newEvtRecurDOM'];
      $eDOW = (empty($_POST['newEvtRecurDOW']))?"Sunday":$_POST['newEvtRecurDOW'];
      $eDOY = (empty($_POST['newEvtRecurDOY']))?date('Y-m-d'):InputUtils::FilterDate($_POST['newEvtRecurDOY']);
      $eRecur = $_POST['newEvtTypeRecur'];
      $eCntLst = $_POST['newEvtTypeCntLst'];
      $eCntArray = array_filter(array_map('trim', explode(',', $eCntLst)));
      $eCntArray[] = 'Total';
      $eCntNum = count($eCntArray);
      $theID = $_POST['theID'];

      $eventType = new EventTypes();

      $eventType->setName(InputUtils::LegacyFilterInput($eName));
      $eventType->setDefStartTime(InputUtils::LegacyFilterInput($eTime));
      $eventType->setDefRecurType(InputUtils::LegacyFilterInput($eRecur));
      $eventType->setDefRecurDOW(InputUtils::LegacyFilterInput($eDOW));
      $eventType->setDefRecurDOM(InputUtils::LegacyFilterInput($eDOM));
      $eventType->setDefRecurDOY(InputUtils::LegacyFilterInput($eDOY));

      $eventType->save();

      $theID = $eventType->getId();

      for ($j = 0; $j < $eCntNum; $j++) {
          $cCnt = ltrim(rtrim($eCntArray[$j]));

          try {
            $eventCountName = new EventCountName();

            $eventCountName->setTypeId(InputUtils::LegacyFilterInput($theID));
            $eventCountName->setName(InputUtils::LegacyFilterInput($cCnt));

            $eventCountName->save();
          } catch (Exception $e) {
          }
      }

      $_POST = array();
      RedirectUtils::Redirect('EventNames.php'); // clear POST
      break;
    }
}

// Get data for the form as it now exists.
$eventTypes = EventTypesQuery::Create()
                ->orderById()
                ->find();

$numRows = count($eventTypes);

$aTypeID = array();
$aTypeName = array();
$aDefStartTime = array();
$aDefRecurDOW = array();
$aDefRecurDOM = array();
$aDefRecurDOY = array();
$aDefRecurType = array();


  foreach ($eventTypes as $eventType) {
      $aTypeID[] = $eventType->getId();
      $aTypeName[] = $eventType->getName();
      $aDefStartTime[] = $eventType->getDefStartTime()->format((SystemConfig::getValue('bTimeEnglish')?'h:i A':'H:i'));
      $aDefRecurDOW[] = $eventType->getDefRecurDOW();
      $aDefRecurDOM[] = $eventType->getDefRecurDOM();
      $aDefRecurDOY[] = $eventType->getDefRecurDOY();
      $aDefRecurType[] = $eventType->getDefRecurType();


      //echo "$row:::ID = $aTypeID[$row] DOW = $aDefRecurDOW[$row], DOM=$aDefRecurDOM[$row], DOY=$adefRecurDOY[$row] type=$aDefRecurType[$row]\n\r\n<br>";

      switch ($eventType->getDefRecurType()) {
            case 'none':
              $recur[] = _('None');
              break;
            case 'weekly':
              $recur[] = _('Weekly on').' '._($eventType->getDefRecurDOW().'s');
              break;
            case 'monthly':
              $recur[] = _('Monthly on').' '.date(SystemConfig::getBooleanValue("bTimeEnglish")?'dS':'d', mktime(0, 0, 0, 1, $eventType->getDefRecurDOM(), 2000));
              break;
            case 'yearly':
              $recur[] = _('Yearly on').' '.$eventType->getDefRecurDOY()->format(SystemConfig::getValue("sDateFormatNoYear"));
              break;
            default:
              $recur[] = _('None');
          }
      // recur types = 1-DOW for weekly, 2-DOM for monthly, 3-DOY for yearly.
      // repeats on DOW, DOM or DOY
      //
      // new - check the count definintions table for a list of count fields
      $eventCountNames = EventCountNameQuery::Create()
                         ->filterByTypeId($eventType->getId())
                         ->orderById()
                         ->find();

      $numCounts = count($eventCountNames);

      $cCountName = array();
      if ($numCounts) {
          foreach ($eventCountNames as $eventCountName) {
              $cCountID[] = $eventCountName->getId();
              $cCountName[] = $eventCountName->getName();
          }
          $cCountList[] = implode(', ', $cCountName);
      } else {
          $cCountList[] = '';
      }
  }

    /*print_r($aTypeID);
    print_r($recur);
    print_r($aDefStartTime);
    print_r($cCountList);
    print_r($cCountID);
    print_r($cCountName);*/


if (InputUtils::LegacyFilterInput($_POST['Action']) == 'NEW') {
    ?>
  <div class='card card-primary'>
    <div class='card-body'>
      <form name="UpdateEventNames" action="EventNames.php" method="POST" class='form-horizontal'>
        <input type="hidden" name="theID" value="<?= $aTypeID[$row] ?>">
        <div class='row form-group'>
          <div class='col-sm-4 control-label text-bold'>
            <?= _('EVENT TYPE NAME') ?>
          </div>
          <div class='col-sm-6'>
            <input class="form-control" type="text" name="newEvtName" value="<?= $aTypeName[$row] ?>" size="30" maxlength="35" autofocus>
          </div>
        </div>
        <!--<div class='row form-group'>
          <div class='col-sm-4 control-label text-bold'>
            <?= _('Recurrence Pattern') ?>
          </div>
          <div class='col-sm-6 event-recurrance-patterns'>
            <div class='row form-radio-list'>
              <div class='col-xs-12'>
                <input type="radio" name="newEvtTypeRecur" value="none" checked/> <?= _('None'); ?>
              </div>
            </div>
            <div class='row form-radio-list'>
              <div class='col-xs-5'>
                <input type="radio" name="newEvtTypeRecur" value="weekly"/> <?= _('Weekly') ?>
              </div>
              <div class='col-xs-7'>
                <select name="newEvtRecurDOW" size="1" class='form-control pull-left' disabled>
                  <option value=1><?= _('Sundays') ?></option>
                  <option value=2><?= _('Mondays') ?></option>
                  <option value=3><?= _('Tuesdays') ?></option>
                  <option value=4><?= _('Wednesdays') ?></option>
                  <option value=5><?= _('Thursdays') ?></option>
                  <option value=6><?= _('Fridays') ?></option>
                  <option value=7><?= _('Saturdays') ?></option>
                </select>
              </div>
            </div>
            <div class='row form-radio-list'>
              <div class='col-xs-5'>
                <input type="radio" name="newEvtTypeRecur" value="monthly"/> <?= _('Monthly')?>
              </div>
              <div class='col-xs-7'>
                <select name="newEvtRecurDOM" size="1" class='form-control pull-left' disabled>
                  <?php
                    for ($kk = 1; $kk <= 31; $kk++) {
                        $DOM = date((SystemConfig::getBooleanValue("bTimeEnglish"))?'dS':'d', mktime(0, 0, 0, 1, $kk, 2000)); ?>
                      <option class="SmallText" value=<?= $kk ?>><?= $DOM ?></option>
                      <?php
                    } ?>
                 </select>
               </div>
            </div>
            <div class='row form-radio-list'>
              <div class='col-xs-5'>
                <input type="radio" name="newEvtTypeRecur" value="yearly"/> <?= _('Yearly')?>
              </div>
              <div class='col-xs-7'>
                <input type="text" disabled class="form-control date-picker" name="newEvtRecurDOY"
                               value="<?= OutputUtils::change_date_for_place_holder($dMembershipDate) ?>" maxlength="10" id="sel1" size="11"
                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">

              </div>
            </div>
          </div>
        </div>-->
        <div class='row form-group'>
          <div class='col-sm-4 control-label text-bold'>
            <?= _('DEFAULT START TIME') ?>
          </div>
          <div class='col-sm-6'>
            <select class="form-control" name="newEvtStartTime">
              <?php OutputUtils::createTimeDropdown(7, 22, 15, '', ''); ?>
            </select>
          </div>
        </div>
        <div class='row form-group'>
          <div class='col-sm-4 control-label text-bold'>
            <?= _('ATTENDANCE COUNTS') ?>
          </div>
          <div class='col-sm-6'>
            <input class="form-control" type="Text" name="newEvtTypeCntLst" value="<?= $cCountList[$row] ?>" Maxlength="50" id="nETCL" size="30" placeholder="<?= _('Optional') ?>">
            <div class='text-sm'><?= _('Enter a list of the attendance counts you want to include with this event.')?></div>
            <div class='text-sm'><?= _('Separate each count_name with a comma. e.g. Members, Visitors, Campus, Children'); ?></div>
            <div class='text-sm'><?= _('Every event type includes a Total count, you do not need to include it.') ?></div>
          </div>
        </div>
        <div class='row form-group'>
          <div class='col-sm-8 col-sm-offset-4'>
            <a href="EventNames.php" class='btn btn-default'>
              <?= _('Cancel') ?>
            </a>
            <button type="submit" Name="Action" value="CREATE" class="btn btn-primary">
              <?= _('Save Changes') ?>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php
}

// Construct the form
?>
<div class="card">
  <div class="card-header  border-0">
    <?php if ($numRows > 0) {
    ?>
      <h3 class="card-title"><?= ($numRows == 1 ? _('There currently is') : _('There currently are')).' '.$numRows.' '.($numRows == 1 ? _('custom event type') : _('custom event types')) ?></h3>
    <?php
} ?>
  </div>

  <div class='card-body'>
    <?php
    if ($numRows > 0) {
        ?>
      <table  id="eventNames" class="table table-striped table-bordered data-table" width="100%">
        <thead>
         <tr>
            <!--<th><?= _('Event Type') ?></th>-->
            <th><?= _('Name') ?></th>
            <!--<th><?= _('Recurrence Pattern') ?></th>-->
            <th><?= _('Start Time') ?></th>
            <th><?= _('Attendance Counts') ?></th>
            <th><?= _('Action') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
          for ($row = 0; $row < $numRows; $row++) {
              ?>
            <tr>
              <!--<td><?= $aTypeID[$row] ?></td>-->
              <td><?= $aTypeName[$row] ?></td>
              <!--<td><?= $recur[$row] ?></td>-->
              <td><?= $aDefStartTime[$row] ?></td>
              <td><?= $cCountList[$row] ?></td>
              <td>
                <table class='table-simple-padding'>
                  <tr class="no-background-theme">
                    <td>
                        <button value="<?= _('Create Event') ?>" class="btn btn-primary btn-sm add-event" data-typeid="<?= $aTypeID[$row] ?>">
                          <?= _('Create Event') ?>
                        </button>
                    </td>
                    <td>
                      <form name="ProcessEventType" action="EditEventTypes.php" method="POST" class="pull-left">
                        <input type="hidden" name="EN_tyid" value="<?= $aTypeID[$row] ?>">
                        <button type="submit" class="btn btn-success btn-sm" name="Action" title="<?= _('Edit') ?>" data-tooltip value="<?= _('Edit') ?>">
                          <i class='fas fa-pencil-alt'></i>
                        </button>
                      </form>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm delete-event" title="<?= _('Delete') ?>" data-tooltip name="Action" data-typeid="<?= $aTypeID[$row] ?>">
                          <i class='fas fa-trash-alt'></i>
                        </button>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <?php
          } ?>
        </tbody>
      </table>
      <?php
    }
    ?>
  </div>
</div>

<?php
if (InputUtils::LegacyFilterInput($_POST['Action']) != 'NEW') {
        ?>
  <div class="text-center">
    <form name="AddEventNames" action="EventNames.php" method="POST">
      <button type="submit" Name="Action" value="NEW" class="btn btn-primary">
        <?= _('Add Event Type') ?>
      </button
    </form>
  </div>
  <?php
    }
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
  $(document).ready(function () {
  //Added by @saulowulhynek to translation of datatable nav terms
    $('#eventNames').DataTable(window.CRM.plugin.dataTable);
  });
</script>

<?php require 'Include/Footer.php' ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.isModifiable  = true;

  window.CRM.churchloc = {
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
  window.CRM.mapZoom   = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/EventEditor.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/EventNames.js" ></script>
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
