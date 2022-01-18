<?php
/*******************************************************************************
 *
 *  filename    : Checkin.php
 *  last change : 2007-xx-x
 *  description : Quickly add attendees to an event
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2003 Phillip Hullquist, Deane Barker, Chris Gebhardt
 *  Copyright 2005 Todd Pillars
 *  Copyright 2012 Michael Wilt
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2020 Philippe Logel all right reserved
 *                This code can't be included in another software.
 *                Updated : 2020/06/18
 *
 ******************************************************************************/


// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';


$sPageTitle = _('Call the Register');
require 'Include/Header.php';

use EcclesiaCRM\EventQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\utils\RedirectUtils;


$EventID = 0;
$event = null;

if (array_key_exists('EventID', $_POST)) {
    // from ListEvents button=Attendees
    $EventID = InputUtils::FilterInt($_POST['EventID']);
    $_SESSION['EventID'] = $EventID;
} else if (isset ($_SESSION['EventID'])) {
    // from api/routes/events.php
    $EventID = InputUtils::FilterInt($_SESSION['EventID']);
} else {
    $Event = EventQuery::create()
        ->filterByStart(date("Y-m-d 00:00:00"), Criteria::GREATER_EQUAL)
        ->filterByEnd(date("Y-m-d 23:59:59"), Criteria::LESS_EQUAL)
        ->findOne();

    if (!is_null($Event)) {
        $_SESSION['EventID'] = $Event->getId();
        $EventID = $_SESSION['EventID'];
    }
}

if ($EventID > 0) {
    $event = EventQuery::Create()
        ->findOneById($EventID);

    if ($event == null) {
        $_SESSION['EventID'] = 0;
        $EventID = 0;
    } else {
        // for EditEventAttendees.php
        $_SESSION['Action'] = "EditEvent";
        $_SESSION['EID'] = $EventID;
        $_SESSION['EName'] = $event->getTitle();
        $_SESSION['EDesc'] = $event->getDesc();
        $_SESSION['EDate'] = $event->getStart()->format('YYYY-MM-DD');
    }
}

$bSundaySchool = false;

if (!is_null($event) && $event->getGroupId() > 0) {
    $bSundaySchool = GroupQuery::Create()->findOneById($event->getGroupId())->isSundaySchool();
}

if (isset($_SESSION['CartToEventEventID'])) {
    $EventID = InputUtils::LegacyFilterInput($_SESSION['CartToEventEventID'], 'int');
}


//
// process the action inputs
//


//Start off by first picking the event to check people in for
$activeEvents = EventQuery::Create()
    ->filterByInActive(1, Criteria::NOT_EQUAL)
    ->Where('MONTH(event_start) = ' . date('m') . ' AND YEAR(event_start)=' . date('Y'))// We filter only the events from the current month
    ->orderByStart('desc')
    ->find();

$searchEventInActivEvent = EventQuery::Create()
    ->filterByInActive(1, Criteria::NOT_EQUAL)
    ->Where('MONTH(event_start) = ' . date('m') . ' AND YEAR(event_start)=' . date('Y'))// We filter only the events from the current month
    ->findOneById($EventID);

if ($searchEventInActivEvent != null) {
    //get Event Details
    $event = EventQuery::Create()
        ->findOneById($EventID);

    $sTitle = $event->getTitle();
    $sNoteText = $event->getText();

    $eventCountNames = EventCountNameQuery::Create()
        ->leftJoinEventTypes()
        ->Where('type_id=' . $event->getType())
        ->find();
} /*else if ($activeEvents->count() == 0 && is_null($event)) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}*/

?>

<link href="https://fonts.googleapis.com/css?family=Ropa+Sans" rel="stylesheet">

<div class="card">
    <div class="card-body">
        <a class="btn btn-app" id="add-event"><i class="fa fa-ticket"></i><?= _('Add New Event') ?></a>
        <?php if (!is_null($searchEventInActivEvent)) {
            ?>
            <a class="btn btn-app" id="qrcode-call"><i class="fa fa-qrcode"></i><?= _("QR Code Call") ?></a>
            <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/v2/kioskmanager"><i
                    class="fa fa-plug"></i><?= _("Kiosk Manager") ?></a>
            <?php
        }
        if ($bSundaySchool) {
            ?>
            <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/EditEventAttendees.php"><i
                    class="fa fa-pencil"></i><?= _("Edit Attendees") ?></a>
            <?php
        }
        ?>
    </div>
</div>

<br>

<div id="errorcallout" class="alert alert-danger" hidden></div>

<?php
if (!is_null($searchEventInActivEvent)) {
    ?>

    <!--Select Event Form -->
    <div class="card card-primary">
        <div class="card-header  with-border">
            <h3 class="card-title">
                <?= _('Select the event to which you would like to check people in for') ?> :</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="control-label"><?= _('Select Event'); ?></label>
                </div>
                <div class="col-md-8">
                    <?php if ($sGlobalMessage): ?>
                        <p><?= $sGlobalMessage ?></p>
                    <?php endif; ?>
                    <form name="selectEvent" action="Checkin.php" method="POST">
                        <div class="form-group">
                            <div class="inputGroupContainer">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fa fa-calendar-check-o"></i></span></div>
                                    <select name="EventID" class="form-control" onchange="this.form.submit()">
                                        <option value="<?= $EventID; ?>"
                                                disabled <?= ($EventID == 0) ? " Selected='selected'" : "" ?> ><?= _('Select event') ?></option>
                                        <?php foreach ($activeEvents as $event) {
                                            $dateStart = $event->getStart()->format(SystemConfig::getValue('sDatePickerFormat'));
                                            ?>
                                            <option
                                                value="<?= $event->getId(); ?>" <?= ($EventID == $event->getId()) ? " Selected='selected'" : "" ?> >
                                                <?= $dateStart . " : " . $event->getTitle() . " (" . $event->getDesc() . ")"; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
} else if (!is_null($event)) {
    ?>

    <!-- short presentation -->
    <div class="card card-secondary">
        <div class="card-header">
            <h3 class="card-title"><?= "<b>" . $event->getTitle() . "</b> (" . $event->getDesc() . ")" . " " . _("From") . " : <b>" . OutputUtils::FormatDate($event->getStart()->format("Y-m-d H:i:s"), 1) . "</b> " . _("To") . " : <b>" . OutputUtils::FormatDate($event->getEnd()->format("Y-m-d H:i:s"), 1) . "</b>" ?>
                :</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-10 col-xs-12">
                    <?php if ($sGlobalMessage): ?>
                        <p><?= $sGlobalMessage ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
}
?>

<?php
if (!empty($eventCountNames) != null && $eventCountNames->count() > 0) {
    ?>
    <!-- Add Free Attendees Form -->
    <div class="card card-secondary collapsed-card">
        <div class="card-header">
            <h4 class="card-title">
                <?= _('Set your free attendees') ?>
            </h4>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><?= _('You can set here the attendees for some group of persons.') ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label
                                        class="col-md-2 control-label"><?= _('Set your attendees Event'); ?></label>
                                    <?php
                                    $desc = "";
                                    foreach ($eventCountNames as $eventCountName) {
                                        ?>
                                        <div class="col-md-2">
                                            <?= $eventCountName->getName(); ?>

                                            <?php
                                            $eventCount = EventCountsQuery::Create()
                                                ->filterByEvtcntEventid($EventID)
                                                ->findOneByEvtcntCountid($eventCountName->getId());

                                            $count = 0;
                                            if (!empty($eventCount)) {
                                                $count = $eventCount->getEvtcntCountcount();
                                                $desc = $eventCount->getEvtcntNotes();
                                            }
                                            ?>
                                            <input type="text" id="field<?= $eventCountName->getId() ?>"
                                                   name="<?= $eventCountName->getId() ?>"
                                                   data-countid="<?= $eventCountName->getId() ?>"
                                                   value="<?= $count ?>"
                                                   size="8" class="form-control input-sm freeAttendeesCount"
                                                   width="100%"
                                                   style="width: 100%">
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 control-label"><?= _('Your description'); ?></label>
                                    <div class="col-md-6">
                                        <input type="text" id="fieldText" name="desc"
                                               data-countid="<?= $eventCountName->getId() ?>" value="<?= $desc ?>"
                                               size="8" class="form-control input-sm " width="100%"
                                               style="width: 100%">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12 text-right">
                                        <input id="addFreeAttendees" class="btn btn-primary"
                                               value="<?= _('Add Free Attendees Count'); ?>"
                                               name="Add" tabindex=4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add Free Attendees Form -->

    <?php
}
?>

<!-- Add Attendees Form -->
<?php
// If event is known, then show 2 text boxes, person being checked in and the person checking them in.
// Show a verify button and a button to add new visitor in dbase.
if ($EventID > 0) {
    ?>

    <div class="card card-secondary collapsed-card">
        <div class="card-header">
            <h4 class="card-title">
                <?= _('Add single child/parents Attendees') ?>
            </h4>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= _('Add Attendees for Event'); ?>
                                : <?= $event->getTitle() ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="child" class="col-sm-12 control-label"><?= _("Person's Name") ?></label>
                                <div class="col-sm-5 inputGroupContainer">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-child"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="child"
                                               placeholder="<?= _("Person's Name"); ?>" required tabindex=1>
                                    </div>
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    <div class="help-block with-errors"></div>
                                </div>
                                <div id="childDetails" class="col-sm-5 text-center"></div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label for="adult"
                                       class="col-sm-12 control-label"><?= _('Adult Name(Optional)') ?></label>
                                <div class="col-sm-5 inputGroupContainer">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="adult"
                                               placeholder="<?= _('Checked in By(Optional)'); ?>" tabindex=2>
                                    </div>
                                </div>
                                <div id="adultDetails" class="col-sm-5 text-center"></div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <input id="addAndCheckIn" class="btn btn-primary"
                                           value="<?= _('Add and Checkin'); ?>"
                                           name="CheckIn" tabindex=3 data-childid="-1" data-adultid="-1">
                                </div>
                                <div class="col-md-4">
                                    <input type="reset" id="resetDetails" class="btn btn-default"
                                           value="<?= _('Cancel'); ?>"
                                           name="Cancel" tabindex=4
                                           onClick="">
                                </div>
                                <div class="col-md-4">
                                    <input type="Add" class="btn btn-success" value="<?= _('Add Visitor'); ?>"
                                           name="Add" tabindex=4
                                           id="addVisitor">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
}


//Populate data table
if ($EventID > 0) {
    ?>
    <div class="card card-success">
        <div class="card-header  with-border">
            <h3 class="card-title">
                <?= _('Listing') ?> :</h3>
        </div>
        <div class="card-body table-responsive">

            <div class="row">
                <div class="col-md-2">
                    <label for="page-length-select"><?= _("Number of rows") ?></label>
                </div>
                <div class="col-md-3">
                    <select name="pets" id="page-length-select" class="form-control">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                </div>
            </div>

            <br/>


            <table id="checkedinTable" class="table table-striped table-bordered data-table" width="100%"></table>

            <div class="row" style="margin:5px">
                <div class="col-md-1">
                    <label><?= _("Checkin") ?></label>
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-primary" type="submit" name="checkAllCheckin" id="checkAllCheckin"
                           data-id="<?= $EventID ?>" value="<?= _('Check all') ?>">
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-default" type="submit" name="uncheckAllCheckin" id="uncheckAllCheckin"
                           data-id="<?= $EventID ?>" value="<?= _('Uncheck all') ?>">
                </div>
                <div class="col-md-1">
                    <label><?= _("Checkout") ?></label>
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-success" type="submit" name="checkAllCheckout" id="checkAllCheckout"
                           data-id="<?= $EventID ?>" value="<?= _('Check all') ?>">
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-default" type="submit" name="uncheckAllCheckout" id="uncheckAllCheckout"
                           data-id="<?= $EventID ?>" value="<?= _('Uncheck all') ?>">
                </div>
            </div>

            <hr/>
            <div class="row" style="margin:5px">
                <div class="col-sm-2" style="text-align:right">
                    <label><?= _("Add some notes") ?> : </label>
                </div>
                <div class="col-sm-8">
                        <textarea id="NoteText" name="NoteText" style="width: 100%;min-height: 300px;"
                                  rows="40"><?= $sNoteText ?></textarea>
                    <br>
                    <input id="validateAttendees" class="btn btn-primary" name="Validate"
                           value="<?= _("Validate Attendance") ?>">
                </div>
                <br>
            </div>
        </div>
    </div>
    <?php
}

?>

<div>
    <a href="<?= SystemURLs::getRootPath() ?>/v2/calendar/events/list" class='btn btn-default'>
        <i class='fa fa-chevron-left'></i>
        <?= _('Return to Events') ?>
    </a>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isModifiable = true;
    window.CRM.EventID = <?= $EventID ?>;
    window.CRM.isSundaySchool = <?= ($bSundaySchool) ? 'true' : 'false' ?>;

    window.CRM.churchloc = {
        lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>
    };

    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/Checkin.js"></script>

<link href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css"
      rel="stylesheet">

<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"
        type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/EventEditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/publicfolder.js"></script>

<script src="./skin/external/jsqr/jsQR.js"></script>


<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
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

<?php require 'Include/Footer.php'; ?>
