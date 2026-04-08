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
    <div class='card card-outline card-primary shadow-sm'>
        <div class='card-header py-2'>
            <h3 class='card-title mb-0'><i class='fas fa-calendar-plus mr-1'></i><?= _('New Event Type') ?></h3>
        </div>
        <form name="UpdateEventNames" action="<?= $sRootPath ?>/v2/calendar/events/names" method="POST" class='form-horizontal'>
            <input type="hidden" name="theID" value="<?= $aTypeID[$row] ?>">

            <!-- Event Type Name Section -->
            <div class='card-body py-3 border-bottom'>
                <div class='row form-group mb-0'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('Event Type Name') ?>
                    </div>
                    <div class='col-sm-8'>
                        <input class="form-control form-control-sm" type="text" name="newEvtName"
                               value="<?= $aTypeName[$row] ?>" size="30" maxlength="35" autofocus>
                    </div>
                </div>
            </div>

            <!-- Default Color Section -->
            <div class='card-body py-3 border-bottom'>
                <div class='row form-group mb-0'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('Default Color') ?>
                    </div>
                    <div class='col-sm-8'>
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
            </div>

            <!-- Default Start Time Section -->
            <div class='card-body py-3 border-bottom'>
                <div class='row form-group mb-0'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('Default Start Time') ?>
                    </div>
                    <div class='col-sm-8'>
                        <select class="form-control form-control-sm" name="newEvtStartTime">
                            <?php OutputUtils::createTimeDropdown(7, 22, 15, '', ''); ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Attendance Counts Section -->
            <div class='card-body py-3 border-bottom'>
                <div class='row form-group mb-0'>
                    <div class='col-sm-4 control-label text-bold'>
                        <?= _('Attendance Counts') ?>
                    </div>
                    <div class='col-sm-8'>
                        <input class="form-control form-control-sm" type="Text" name="newEvtTypeCntLst"
                               value="<?= $cCountList[$row] ?>" Maxlength="50" id="nETCL" size="30"
                               placeholder="<?= _('Optional') ?>">
                        <div class='text-sm mt-2'><?= _('Enter a list of the attendance counts you want to include with this event.') ?></div>
                        <div class='text-sm'><?= _('Separate each count_name with a comma. e.g. Members, Visitors, Campus, Children'); ?></div>
                        <div class='text-sm'><?= _('Every event type includes a Total count, you do not need to include it.') ?></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Section -->
            <div class='card-body py-3'>
                <div class='row form-group mb-0'>
                    <div class='col-sm-8 col-sm-offset-4'>
                        <a href="<?= $sRootPath ?>/v2/calendar/events/names" class='btn btn-sm btn-outline-secondary mr-2'>
                            <i class='fas fa-times mr-1'></i><?= _('Cancel') ?>
                        </a>
                        <button type="submit" Name="Action" value="CREATE" class="btn btn-sm btn-success">
                            <i class='fas fa-save mr-1'></i><?= _('Save Changes') ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

// Construct the form
?>
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-calendar mr-1"></i><?= _('Event Types Management') ?></h3>
        <form name="AddEventNames" action="<?= $sRootPath ?>/v2/calendar/events/names" method="POST" class="m-0">
            <button type="submit" Name="Action" value="NEW" class="btn btn-sm btn-success">
                <i class="fas fa-plus mr-1"></i><?= _('Add Event Type') ?>
            </button>
        </form>
    </div>
    <div class="card-body py-3">
        <p class="text-muted mb-0"><?= _('Manage your event types and their configurations') ?></p>
    </div>
</div>

<div class="card card-outline card-info shadow-sm">
    <div class="card-header py-2">
        <?php if ($numRows > 0) {
            ?>
            <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i><?= ($numRows == 1 ? _('There currently is') : _('There currently are')) . ' ' . $numRows . ' ' . ($numRows == 1 ? _('custom event type') : _('custom event types')) ?></h3>
            <?php
        } ?>
    </div>

    <div class='card-body'>
        <?php
        if ($numRows > 0) {
            ?>
            <table id="eventNames" class="table table-striped table-hover table-bordered table-sm data-table" width="100%">
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
                                        <td>
                                            <span title="<?= _('Event Color') ?>"
                                                  style="display:inline-block;background-color:<?= $aDefColorType[$row] ?>;width:18px;height:18px;border-radius:6px;border:1px solid rgba(0,0,0,.2);box-shadow:inset 0 0 0 1px rgba(255,255,255,.35),0 1px 2px rgba(0,0,0,.15);vertical-align:middle;"></span>
                                        </td>
                                        <td><?= $aTypeName[$row] ?></td>
                                    </tr>
                            </table>
                        </td>
                        <!--<td><?= $recur[$row] ?></td>-->
                        <td><?= $aDefStartTime[$row] ?></td>
                        <td><?= $cCountList[$row] ?></td>
                        <td>
                            <form name="ProcessEventType<?= $aTypeID[$row] ?>" action="<?= $sRootPath ?>/v2/calendar/events/types/edit" method="POST" style="display:none;">
                                <input type="hidden" name="EN_tyid" value="<?= $aTypeID[$row] ?>">
                                <input type="hidden" name="Action" value="edit">
                            </form>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary add-event"
                                        data-typeid="<?= $aTypeID[$row] ?>">
                                    <i class="fas fa-plus mr-1"></i><?= _('Create') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                        title="<?= _('Edit') ?>" data-toggle="tooltip"
                                        onclick="document.forms['ProcessEventType<?= $aTypeID[$row] ?>'].submit();">
                                    <i class='fas fa-edit mr-1'></i><?= _('Edit') ?>
                                </button>
                                <button type="button" class="btn btn-outline-danger delete-event" title="<?= _('Delete') ?>"
                                        data-toggle="tooltip"
                                        data-typeid="<?= $aTypeID[$row] ?>">
                                    <i class='fas fa-trash-alt mr-1'></i><?= _('Delete') ?>
                                </button>
                            </div>
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
