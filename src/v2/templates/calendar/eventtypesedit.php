<?php

/*******************************************************************************
 *
 *  filename    : templates/eventtypesedit.php
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


use EcclesiaCRM\EventCountName;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\InputUtils;

//
//  process the ACTION button inputs from the form page
//
$editing = 'FALSE';
$tyid = $_POST['EN_tyid'];

if (strpos($_POST['Action'], 'DELETE_', 0) === 0) {
    $ctid = mb_substr($_POST['Action'], 7);
    $eventCountName = EventCountNameQuery::Create()
        ->findOneById($ctid);

    if (!empty($eventCountName)) {
        $eventCountName->delete();

        $sGlobalMessageClass = 'success';        
        $sGlobalMessage = " "._("Event count name deleted");        
    }
} else {
    switch ($_POST['Action']) {
        case 'ADD':
            $eventCountName = new EventCountName();

            $eventCountName->setName(InputUtils::FilterString($_POST['newCountName']));
            $eventCountName->setTypeId($_POST['EN_tyid']);

            $eventCountName->save();

            $sGlobalMessageClass = 'success';
            $sGlobalMessage = " "._("Event count name added");

            break;

        case 'NAME':
            $editing = 'FALSE';

            $eventType = EventTypesQuery::Create()
                ->findOneById($_POST['EN_tyid']);

            $eventType->setName(InputUtils::FilterString($_POST['newEvtName']));

            $eventType->save();


            $theID = '';
            $_POST['Action'] = '';

            $sGlobalMessageClass = 'success';
            $sGlobalMessage = " "._("Name changed");

            break;

        case 'COLOR':
            $editing = 'FALSE';

            $eventType = EventTypesQuery::Create()
                ->findOneById($_POST['EN_tyid']);

            $eventType->setColor($_POST['newEvtColor']);

            $eventType->save();


            $theID = '';
            $_POST['Action'] = '';

            $sGlobalMessageClass = 'success';
            $sGlobalMessage = " "._("Color changed");
            break;

        case 'TIME':
            $editing = 'FALSE';

            $eventType = EventTypesQuery::Create()
                ->findOneById($_POST['EN_tyid']);

            $eventType->setDefStartTime($_POST['newEvtStartTime']);

            $eventType->save();

            //print_r($eventType->toArray());

            $theID = '';
            $_POST['Action'] = '';

            $sGlobalMessageClass = 'success';
            $sGlobalMessage = " "._("Event time modified");
            break;
    }
}

// Get data for the form as it now exists.
// Get data for the form as it now exists.
$eventType = EventTypesQuery::Create()
    ->findOneById($tyid);

if (empty($eventType)) {
    RedirectUtils::Redirect('v2/calendar/events/names'); // clear POST
}


$aTypeID = $eventType->getId();
$aTypeName = $eventType->getName();
$aTypeColor = $eventType->getColor();
$aDefStartTime = $eventType->getDefStartTime()->format('H:i:s');
$aStartTimeTokens = explode(':', $aDefStartTime);
$aEventStartHour = (int)$aStartTimeTokens[0];
$aEventStartMins = $aStartTimeTokens[1];
$aDefRecurDOW = $eventType->getDefRecurDOW();
$aDefRecurDOM = $eventType->getDefRecurDOM();
$aDefRecurDOY = (empty($eventType->getDefRecurDOY())) ? "" : $eventType->getDefRecurDOY()->format(SystemConfig::getValue("sDateFormatNoYear"));
$aDefRecurType = $eventType->getDefRecurType();

//echo $aDefStartTime."=>".$aEventStartHour.":".$aEventStartMins;


switch ($aDefRecurType) {
    case 'none':
        $recur = _('None');
        break;
    case 'weekly':
        $recur = _('Weekly on') . ' ' . _($aDefRecurDOW . 's');
        break;
    case 'monthly':
        $recur = _('Monthly on') . ' ' . date('dS', mktime(0, 0, 0, 1, $aDefRecurDOM, 2000));
        break;
    case 'yearly':
        $recur = _('Yearly on') . ' ' . $aDefRecurDOY;
        break;
    default:
        $recur = _('None');
}


// Get a list of the attendance counts currently associated with thisevent type
$eventCountNames = EventCountNameQuery::Create()
    ->filterByTypeId($aTypeID)
    ->orderById()
    ->find();

$numCounts = count($eventCountNames);

$nr = $numCounts + 2;

$cCountName = array();
$cCountID = array();

if ($numCounts) {
    foreach ($eventCountNames as $eventCountName) {
        $cCountID[] = $eventCountName->getId();
        $cCountName[] = $eventCountName->getName();
    }
}

/*print_r($cCountName);
print_r($cCountID);*/

require $sRootDocument . '/Include/Header.php';

// Construct the form
?>
<div class='card card-outline card-primary shadow-sm mb-3'>
    <div class='card-header py-2'>
        <h3 class='card-title mb-0'><i class='fas fa-calendar mr-1'></i><?= _('Edit Event Type') ?></h3>
    </div>

    <form method="POST" action="<?= $sRootPath ?>/v2/calendar/events/types/edit" name="EventTypeEditForm">
        <input type="hidden" name="EN_tyid" value="<?= $aTypeID ?>">
        <input type="hidden" name="EN_ctid" value="<?= $cCountID[$c] ?>">

        <!-- Event Name Section -->
        <div class='card-body py-3 border-bottom'>
            <div class='row form-group mb-0'>
                <div class='col-sm-4 control-label text-bold'>
                    <strong><?= _('Event Type Name') ?></strong>
                </div>
                <div class='col-sm-6'>
                    <input type="text" class="form-control form-control-sm" name="newEvtName" value="<?= $aTypeName ?>"
                           size="30" maxlength="35" autofocus/>
                </div>
                <div class='col-sm-2'>
                    <button type="submit" Name="Action" value="NAME"
                            class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i><?= _('Save') ?></button>
                </div>
            </div>
        </div>

        <!-- Event Color Section -->
        <div class='card-body py-3 border-bottom'>
            <div class='row form-group mb-0'>
                <div class='col-sm-4 control-label text-bold'>
                    <strong><?= _('Event Type Color') ?></strong>
                </div>
                <div class='col-sm-6'>
                    <div class="input-group my-colorpicker-event colorpicker-element">
                        <input id="checkBox" type="hidden" name="newEvtColor" class="check-calendar"
                               checked="" value="<?= $aTypeColor ?>">&nbsp;
                        <span class="editCalendarName" data-id="38,44"><?= _('Color') ?>:</span>
                        <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                            <i style="background-color: rgb(26, 43, 94);"></i>
                        </div>
                    </div>
                </div>
                <div class='col-sm-2'>
                    <button type="submit" Name="Action" value="COLOR"
                            class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i><?= _('Save') ?></button>
                </div>
            </div>
        </div>

        <!-- Start Time Section -->
        <div class='card-body py-3 border-bottom'>
            <div class='row form-group mb-0'>
                <div class='col-sm-4 control-label text-bold'>
                    <strong><?= _('Default Start Time') ?></strong>
                </div>
                <div class='col-sm-6'>
                    <select class="form-control form-control-sm" name="newEvtStartTime" size="1"
                            onchange="javascript:$('#newEvtStartTimeSubmit').click()">
                        <?php OutputUtils::createTimeDropdown(7, 18, 15, $aEventStartHour, $aEventStartMins); ?>
                    </select>
                    <button class='d-none' type="submit" name="Action" value="TIME" id="newEvtStartTimeSubmit"></button>
                </div>
            </div>
        </div>

        <!-- Attendance Counts Section -->
        <div class='card-body py-3'>
            <h5 class='mb-3'><i class='fas fa-users mr-1'></i><?= _('Attendance Counts') ?></h5>
            <?php
            if ($numCounts > 0) {
                ?>
                <div class='mb-3'>
                    <?php
                    for ($c = 0; $c < $numCounts; $c++) {
                        ?>
                        <div class='row form-group align-items-center mb-2'>
                            <div class='col-sm-6'>
                                <span><?= $cCountName[$c] ?></span>
                            </div>
                            <div class='col-sm-6'>
                                <button type="submit" name="Action" value="DELETE_<?= $cCountID[$c] ?>"
                                        class="btn btn-sm btn-danger"><i class="fas fa-trash-alt mr-1"></i><?= _('Remove') ?></button>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <hr/>
                <?php
            }
            ?>
            <div class='row form-group'>
                <div class='col-sm-6'>
                    <input class="form-control form-control-sm" type="text" name="newCountName" length="20"
                           placeholder="<?= _("New Attendance Count") ?>"/>
                </div>
                <div class='col-sm-6'>
                    <button type="submit" name="Action" value="ADD"
                            class="btn btn-sm btn-success"><i class="fas fa-plus mr-1"></i><?= _('Add Counter') ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="mt-4 pt-3 border-top">
    <a href="<?= $sRootPath ?>/v2/calendar/events/names" class='btn btn-outline-secondary'>
        <i class='fas fa-arrow-left mr-1'></i>
        <?= _('Return to Event Types') ?>
    </a>
</div>

<script
    src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css"
      rel="stylesheet">

<script nonce="<?= $CSPNonce ?>">
    $(".my-colorpicker-event").colorpicker({
        inline: false,
        horizontal: true,
        right: true
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

