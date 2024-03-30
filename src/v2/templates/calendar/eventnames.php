<?php

/*******************************************************************************
 *
 *  filename    : templates/eventnames.php
 *  last change : 2023-06-23
 *  description : manage the full Calendar
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use EcclesiaCRM\EventTypes;
use EcclesiaCRM\EventCountName;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';

if (isset($_POST['Action'])) {
    switch (InputUtils::LegacyFilterInput($_POST['Action'])) {
        case 'CREATE':
            // Insert into the event_name table
            $eName = $_POST['newEvtName'];
            $eColor = $_POST['newEvtColor'];
            $eTime = $_POST['newEvtStartTime'];
            $eDOM = (empty($_POST['newEvtRecurDOM'])) ? "0" : $_POST['newEvtRecurDOM'];
            $eDOW = (empty($_POST['newEvtRecurDOW'])) ? "Sunday" : $_POST['newEvtRecurDOW'];
            $eDOY = (empty($_POST['newEvtRecurDOY'])) ? date('Y-m-d') : InputUtils::FilterDate($_POST['newEvtRecurDOY']);
            $eRecur = $_POST['newEvtTypeRecur'];
            $eCntLst = $_POST['newEvtTypeCntLst'];
            $eCntArray = array_filter(array_map('trim', explode(',', $eCntLst)));
            $eCntArray[] = 'Total';
            $eCntNum = count($eCntArray);
            $theID = $_POST['theID'];

            $eventType = new EventTypes();

            $eventType->setName(InputUtils::LegacyFilterInput($eName));
            $eventType->setDefStartTime(InputUtils::LegacyFilterInput($eTime));
            $eventType->setColor($eColor);
            /*$eventType->setDefRecurType(InputUtils::LegacyFilterInput($eRecur));
            $eventType->setDefRecurDOW(InputUtils::LegacyFilterInput($eDOW));
            $eventType->setDefRecurDOY(InputUtils::LegacyFilterInput($eDOY));*/

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
            RedirectUtils::Redirect('v2/calendar/events/names'); // clear POST
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
$aDefColorType = array();


foreach ($eventTypes as $eventType) {
    $aTypeID[] = $eventType->getId();
    $aTypeName[] = $eventType->getName();
    $aDefStartTime[] = $eventType->getDefStartTime()->format((SystemConfig::getValue('bTimeEnglish') ? 'h:i A' : 'H:i'));
    $aDefRecurDOW[] = $eventType->getDefRecurDOW();
    $aDefRecurDOM[] = $eventType->getDefRecurDOM();
    $aDefRecurDOY[] = $eventType->getDefRecurDOY();
    $aDefRecurType[] = $eventType->getDefRecurType();
    $aDefColorType[] = $eventType->getColor();


    //echo "$row:::ID = $aTypeID[$row] DOW = $aDefRecurDOW[$row], DOM=$aDefRecurDOM[$row], DOY=$adefRecurDOY[$row] type=$aDefRecurType[$row]\n\r\n<br>";

    switch ($eventType->getDefRecurType()) {
        case 'none':
            $recur[] = _('None');
            break;
        case 'weekly':
            $recur[] = _('Weekly on') . ' ' . _($eventType->getDefRecurDOW() . 's');
            break;
        case 'monthly':
            $recur[] = _('Monthly on') . ' ' . date(SystemConfig::getBooleanValue("bTimeEnglish") ? 'dS' : 'd', mktime(0, 0, 0, 1, $eventType->getDefRecurDOM(), 2000));
            break;
        case 'yearly':
            $recur[] = _('Yearly on') . ' ' . $eventType->getDefRecurDOY()->format(SystemConfig::getValue("sDateFormatNoYear"));
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


if (isset($_POST['Action']) and InputUtils::LegacyFilterInput($_POST['Action']) == 'NEW') {
    ?>
    <div class='card card-primary'>
        <div class='card-body'>
            <form name="UpdateEventNames" action="<?= $sRootPath ?>/v2/calendar/events/names" method="POST" class='form-horizontal'>
                <input type="hidden" name="theID" value="<?= $aTypeID[$row] ?>">
                <div class='row form-group'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('EVENT TYPE NAME') ?>
                    </div>
                    <div class='col-sm-6'>
                        <input class="form-control form-control-sm" type="text" name="newEvtName"
                               value="<?= $aTypeName[$row] ?>" size="30" maxlength="35" autofocus>
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
                <input type="radio" name="newEvtTypeRecur" value="monthly"/> <?= _('Monthly') ?>
              </div>
              <div class='col-xs-7'>
                <select name="newEvtRecurDOM" size="1" class='form-control pull-left' disabled>
                  <?php
                for ($kk = 1; $kk <= 31; $kk++) {
                    $DOM = date((SystemConfig::getBooleanValue("bTimeEnglish")) ? 'dS' : 'd', mktime(0, 0, 0, 1, $kk, 2000)); ?>
                      <option class="SmallText" value=<?= $kk ?>><?= $DOM ?></option>
                      <?php
                } ?>
                 </select>
               </div>
            </div>
            <div class='row form-radio-list'>
              <div class='col-xs-5'>
                <input type="radio" name="newEvtTypeRecur" value="yearly"/> <?= _('Yearly') ?>
              </div>
              <div class='col-xs-7'>
                <input type="text" disabled class=" form-control  form-control-sm date-picker" name="newEvtRecurDOY"
                               value="<?= OutputUtils::change_date_for_place_holder($dMembershipDate) ?>" maxlength="10" id="sel1" size="11"
                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">

              </div>
            </div>
          </div>
        </div>-->
                <div class='row form-group'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('DEFAULT COLOR') ?>
                    </div>
                    <div class='col-sm-6'>
                        <div class="input-group my-colorpicker-event colorpicker-element">
                            <input id="checkBox" type="hidden" name="newEvtColor" class="check-calendar" checked=""
                                   value="#000000">&nbsp;
                            <span class="editCalendarName" data-id="38,44"></span>
                            <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                                <i style="background-color: rgb(0, 0, 0);"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='row form-group'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('DEFAULT START TIME') ?>
                    </div>
                    <div class='col-sm-6'>
                        <select class="form-control form-control-sm" name="newEvtStartTime">
                            <?php OutputUtils::createTimeDropdown(7, 22, 15, '', ''); ?>
                        </select>
                    </div>
                </div>
                <div class='row form-group'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('ATTENDANCE COUNTS') ?>
                    </div>
                    <div class='col-sm-6'>
                        <input class="form-control form-control-sm" type="Text" name="newEvtTypeCntLst"
                               value="<?= $cCountList[$row] ?>" Maxlength="50" id="nETCL" size="30"
                               placeholder="<?= _('Optional') ?>">
                        <div
                            class='text-sm'><?= _('Enter a list of the attendance counts you want to include with this event.') ?></div>
                        <div
                            class='text-sm'><?= _('Separate each count_name with a comma. e.g. Members, Visitors, Campus, Children'); ?></div>
                        <div
                            class='text-sm'><?= _('Every event type includes a Total count, you do not need to include it.') ?></div>
                    </div>
                </div>
                <div class='row form-group'>
                    <div class='col-sm-8 col-sm-offset-4'>
                        <a href="<?= $sRootPath ?>/v2/calendar/events/names" class='btn btn-default'>
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
    <div class="card-header  border-1">
        <?php if ($numRows > 0) {
            ?>
            <h3 class="card-title"><?= ($numRows == 1 ? _('There currently is') : _('There currently are')) . ' ' . $numRows . ' ' . ($numRows == 1 ? _('custom event type') : _('custom event types')) ?></h3>
            <?php
        } ?>
    </div>

    <div class='card-body'>
        <?php
        if ($numRows > 0) {
            ?>
            <table id="eventNames" class="table table-striped table-bordered data-table" width="100%">
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
                        <td>
                            <table class='table-simple-padding outer'>
                                    <tr class="no-background-theme">
                                        <td><div style="background-color:<?= $aDefColorType[$row] ?>;width:30px;height:30px;border: 2px solid black;"></div></td>
                                        <td><?= $aTypeName[$row] ?></td>
                                    </tr>
                            </table>
                        </td>
                        <!--<td><?= $recur[$row] ?></td>-->
                        <td><?= $aDefStartTime[$row] ?></td>
                        <td><?= $cCountList[$row] ?></td>
                        <td>
                            <table class='table-simple-padding outer'>
                                <tr class="no-background-theme">
                                    <td>
                                        <button value="<?= _('Create Event') ?>"
                                                class="btn btn-primary btn-sm add-event"
                                                data-typeid="<?= $aTypeID[$row] ?>">
                                            <i class="fas fa-ticket-alt"></i> <?= _('Create Event') ?>
                                        </button>
                                    </td>
                                    <td>
                                        <form name="ProcessEventType" action="<?= $sRootPath ?>/v2/calendar/events/types/edit" method="POST"
                                              class="pull-left">
                                            <input type="hidden" name="EN_tyid" value="<?= $aTypeID[$row] ?>">
                                            <button type="submit" class="btn btn-success btn-sm" name="Action"
                                                    title="<?= _('Edit') ?>" data-tooltip value="<?= _('Edit') ?>">
                                                <i class='fas fa-pencil-alt'></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm delete-event" title="<?= _('Delete') ?>"
                                                data-tooltip name="Action" data-typeid="<?= $aTypeID[$row] ?>">
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

<div class="text-center">
    <form name="AddEventNames" action="<?= $sRootPath ?>/v2/calendar/events/names" method="POST">
        <button type="submit" Name="Action" value="NEW" class="btn btn-primary">
            <?= _('Add Event Type') ?>
        </button>
    </form>
</div>

<script
    src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css"
      rel="stylesheet">

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        //Added by @saulowulhynek to translation of datatable nav terms
        $('#eventNames').DataTable(window.CRM.plugin.dataTable);

        $(".my-colorpicker-event").colorpicker({
            inline: false,
            horizontal: true,
            right: true
        });
    });

</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.isModifiable = true;

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
    };
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;

</script>

<script
    src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/event/EventNames.js"></script>
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
    ?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>
