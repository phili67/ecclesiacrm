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
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 *
 ******************************************************************************/


// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';


$sPageTitle = _('Event Checkin');
require 'Include/Header.php';

use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\EventCounts;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


$EventID = 0;
$CheckoutOrDelete = false;
$event = null;
$iChildID = 0;
$iAdultID = 0;

if (array_key_exists('EventID', $_POST)) {
    // from ListEvents button=Attendees
    $EventID = InputUtils::FilterInt($_POST['EventID']);
    $_SESSION['EventID'] = $EventID;
} else if (isset ($_SESSION['EventID'])) {
    // from api/routes/events.php
    $EventID = InputUtils::FilterInt($_SESSION['EventID']);
} else {
    $Event = EventQuery::create()
        ->filterByStart('now', Criteria::LESS_EQUAL)
        ->filterByEnd('now', Criteria::GREATER_EQUAL)
        ->findOne();

    if (!is_null($Event)) {
        $_SESSION['EventID'] = $Event->getId();
        $EventID = $_SESSION['EventID'];
    }
}

$bSundaySchool = false;

if ($EventID > 0) {
    $event = EventQuery::Create()
        ->findOneById($EventID);

    if ($event == null) {
        $_SESSION['EventID'] = 0;
        $EventID = 0;
    }
}


if (!is_null($event) && $event->getGroupId() > 0) {
    $bSundaySchool = GroupQuery::Create()->findOneById($event->getGroupId())->isSundaySchool();
}

if (isset($_POST['CheckOutBtn']) || isset($_POST['DeleteBtn'])) {
    $CheckoutOrDelete = true;
}

if (isset($_POST['validateEvent']) && isset($_POST['NoteText'])) {
    $event = EventQuery::Create()
        ->findOneById($EventID);

    $event->setText($_POST['NoteText']);

    $event->save();


    $eventAttents = EventAttendQuery::Create()
        ->filterByEventId($EventID)
        ->find();

    foreach ($eventAttents as $eventAttent) {
        $eventAttent->setCheckoutId(SessionUser::getUser()->getPersonId());

        $eventAttent->save();
    }

    /*if (GroupQuery::Create()->findOneById($event->getGroupId())->isSundaySchool()) {
      // in the case you are in a sundayschool group we stay on the same page, for productivity
      //RedirectUtils::Redirect('sundayschool/SundaySchoolClassView.php?groupId='.$event->getGroupId());
    } else */
    if ($bSundaySchool == false && !is_null($event) && $event->getGroupId()) {
        //RedirectUtils::Redirect('v2/group/'.$event->getGroupId().'/view');
        RedirectUtils::Redirect('v2/calendar');
        exit;
    }
}

if (isset($_SESSION['CartToEventEventID'])) {
    $EventID = InputUtils::LegacyFilterInput($_SESSION['CartToEventEventID'], 'int');
}

if (isset($_POST['child-id'])) {
    $iChildID = InputUtils::LegacyFilterInput($_POST['child-id'], 'int');
}
if (isset($_POST['adult-id'])) {
    $iAdultID = InputUtils::LegacyFilterInput($_POST['adult-id'], 'int');
}

if (isset($_POST['FreeAttendees'])) {
    $FreeAttendees = 1;
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
} else if ($activeEvents->count() == 0 && is_null($event)) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

if ($FreeAttendees) {
    $eventCounts = EventCountsQuery::Create()
        ->findByEvtcntEventid($EventID);

    if (!empty($eventCounts)) {
        $eventCounts->delete();
    }

    foreach ($eventCountNames as $eventCountName) {
        $eventCount = new EventCounts;
        $eventCount->setEvtcntEventid($EventID);
        $eventCount->setEvtcntCountid($eventCountName->getId());
        $eventCount->setEvtcntCountname($eventCountName->getName());
        $eventCount->setEvtcntCountcount($_POST[$eventCountName->getId()]);
        $eventCount->setEvtcntNotes($_POST['desc']);
        $eventCount->save();
    }
}

?>

<div class='text-center'>
    <a class='btn btn-primary' id="add-event">
        <i class='fa fa-ticket'></i>
        <?= _('Add New Event') ?>
    </a>
</div>

<br>

<div id="errorcallout" class="alert alert-danger" hidden></div>

<?php
if (!empty($searchEventInActivEvent)) {
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
                                            ?>
                                            <option
                                                value="<?= $event->getId(); ?>" <?= ($EventID == $event->getId()) ? " Selected='selected'" : "" ?> >
                                                <?= $event->getTitle() . " (" . $event->getDesc() . ")"; ?></option>
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
                                <form name="addFreeAttendeesEvent" action="Checkin.php" method="POST">
                                    <input type="hidden" name="EventID" value="<?= $EventID ?>">
                                    <input type="hidden" name="FreeAttendees" value="1">
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
                                                       size="8" class="form-control input-sm" width="100%"
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
                                                   size="8" class="form-control input-sm" width="100%"
                                                   style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-12 text-right">
                                            <input type="submit" class="btn btn-primary"
                                                   value="<?= _('Add Free Attendees Count'); ?>"
                                                   name="Add" tabindex=4>
                                        </div>
                                    </div>
                                </form> <!-- end Add Free Attendees Form -->
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
if (!$CheckoutOrDelete && $EventID > 0) {
    ?>

    <form class="form-horizontal" method="post" action="Checkin.php" id="AddAttendees" data-toggle="validator"
          role="form">
        <input type="hidden" id="EventID" name="EventID" value="<?= $EventID; ?>">
        <input type="hidden" id="child-id" name="child-id">
        <input type="hidden" id="adult-id" name="adult-id">

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
                                        <input type="submit" class="btn btn-primary" value="<?= _('Add and Checkin'); ?>"
                                               name="CheckIn" tabindex=3>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="reset" class="btn btn-default" value="<?= _('Cancel'); ?>"
                                               name="Cancel" tabindex=4
                                               onClick="SetPersonHtml($('#childDetails'),null);SetPersonHtml($('#adultDetails'),null);">
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
    </form> <!-- end AddAttendees form -->

    <?php
}

// Checkin/Checkout Section update db
if ($EventID && isset($_POST['child-id']) && (isset($_POST['CheckIn']) || isset($_POST['CheckOut']) || isset($_POST['Delete']))) {
    //Fields -> event_id, person_id, checkin_date, checkin_id, checkout_date, checkout_id
    if (isset($_POST['CheckIn']) && !empty($iChildID)) {
        $attendee = EventAttendQuery::create()->filterByEventId($EventID)->findOneByPersonId($iChildID);
        if ($attendee) {
            ?>
            <script nonce="<?= SystemURLs::getCSPNonce() ?>">
                $('#errorcallout').text('<?= _("Person has been already checked in for this event") ?>').fadeIn();
            </script>
            <?php
        } else {
            $attendee = new EventAttend();
            $attendee->setEventId($EventID);
            $attendee->setPersonId($iChildID);
            $attendee->setCheckinDate(date("Y-m-d H:i:s"));
            if (!empty($iAdultID)) {
                $attendee->setCheckinId($iAdultID);
            }
            $attendee->save();
        }
    }

    //Checkout Update
    if (isset($_POST['CheckOut'])) {
        $values = "checkout_date=NOW(), checkout_id=" . ($iAdultID ? "'" . $iAdultID . "'" : 'null');
        $attendee = EventAttendQuery::create()
            ->filterByEventId($EventID)
            ->findOneByPersonId($iChildID);
        $attendee->setCheckoutDate(date("Y-m-d H:i:s"));
        if ($iAdultID) {
            $attendee->setCheckoutId($iAdultID);
        }
        $attendee->save();
    }


    //delete
    if (isset($_POST['Delete'])) {
        $attendDel = EventAttendQuery::create()
            ->filterByEventId($EventID)
            ->findOneByPersonId($iChildID);
        if (!empty($attendDel)) {
            $attendDel->delete();
        }
    }
}

//-- End checkin

//  Checkout / Delete section
if ($EventID > 0 && isset($_POST['child-id']) &&
    (isset($_POST['CheckOutBtn']) || isset($_POST['DeleteBtn']))
) {
    $iChildID = InputUtils::LegacyFilterInput($_POST['child-id'], 'int');

    $formTitle = (isset($_POST['CheckOutBtn']) ? _("CheckOut Person") : _("Delete Checkin in Entry")); ?>

    <form method="post" action="Checkin.php" id="CheckOut" data-toggle="validator" role="form">
        <input type="hidden" name="EventID" value="<?= $EventID ?>">
        <input type="hidden" name="child-id" value="<?= $iChildID ?>">

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header with-border">
                        <h3 class="card-title"><?= $formTitle ?></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div id="child" class="col-sm-4 text-center" onload="SetPersonHtml(this,perArr)">
                                <?php
                                loadperson($iChildID); ?>
                            </div>
                            <?php
                            if (isset($_POST['CheckOutBtn'])) {
                                $person = PersonQuery::Create()->findOneById(SessionUser::getUser()->getPersonId());
                                ?>
                                <div class="col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label><?= _('Adult Checking Out Person') ?>:</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" id="adultout" name="adult" class="form-control"
                                                   value="<?= $person->getFullName() ?>"
                                                   placeholder="<?= _('Adult Name (Optional)') ?>">
                                        </div>
                                        <input type="hidden" id="adultout-id" name="adult-id"
                                               value="<?= $person->getId() ?>">
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-primary"
                                               value="<?= _('CheckOut') ?>" name="CheckOut">
                                        <input type="submit" class="btn btn-default" value="<?= _('Cancel') ?>"
                                               name="CheckoutCancel">
                                    </div>
                                </div>

                                <div class="col-sm-4 text-center">
                                    <div id="adultoutDetails" class="card card-solid box-default">
                                        <div class="text-center"><a target="_top"
                                                                    href="PersonView.php?PersonID=<?= $person->getId() ?>">
                                                <h4><?= $person->getFullName() ?></h4></a>
                                            <img src="/api/persons/<?= $person->getId() ?>/thumbnail"
                                                 class="initials-image profile-user-img img-responsive img-circle">
                                            <br>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else { // DeleteBtn?>
                                <div class="form-group">
                                    <input type="submit" class="btn btn-danger"
                                           value="<?= _('Delete') ?>" name="Delete">
                                    <input type="submit" class="btn btn-default" value="<?= _('Cancel') ?>"
                                           name="DeleteCancel">
                                </div>
                                <?php
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
}
//End checkout
//**********************************************************************************************************

//Populate data table
if ($EventID > 0 || isset($_SESSION['CartToEventEventID'])) {
    ?>
    <div class="card card-success">
        <div class="card-header  with-border">
            <h3 class="card-title">
                <?= _('Listing') ?> :</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="checkedinTable" class="table data-table table-striped " style="width:100%">
                <thead>
                <tr>
                    <th></th>
                    <th><?= ($bSundaySchool) ? _('First Name') : _('Name') ?></th>
                    <th><?= ($bSundaySchool) ? _('Name') : _('First Name') ?></th>
                    <th><?= _("Gender") ?></th>
                    <th><?= _('Checked In Time') ?></th>
                    <th><?= _('Checked In By') ?></th>
                    <th><?= _('Checked Out Time') ?></th>
                    <th><?= _('Checked Out By') ?></th>
                    <th nowrap><?= _('Action') ?></th>
                    <?php
                    if ($bSundaySchool == false) {
                        ?>
                        <th><?= _('Delete') ?></th>
                        <?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>

                <?php
                //Get Event Attendees details
                $eventAttendees = EventAttendQuery::create()
                    ->joinWithPerson()
                    ->usePersonQuery()
                    ->orderByFirstName()
                    ->endUse()
                    ->findByEventId($EventID);


                if ($bSundaySchool) {
                    $genderMale = "Boy";
                    $genderFem = "Girl";
                } else {
                    $genderMale = "Man";
                    $genderFem = "Woman";
                }

                foreach ($eventAttendees as $per) {
                    //Get Person who is checked in
                    $checkedInPerson = PersonQuery::create()
                        ->findOneById($per->getPersonId());

                    if (is_null($checkedInPerson)) {// we have to avoid pure user and not persons
                        continue;
                    }

                    $sPerson = $checkedInPerson->getFullName();

                    //Get Person who checked person in
                    $sCheckinby = "";
                    if ($per->getCheckinId()) {
                        $checkedInBy = PersonQuery::create()
                            ->findOneById($per->getCheckinId());
                        $sCheckinby = $checkedInBy->getFullName();
                    }

                    //Get Person who checked person out
                    $sCheckoutby = "";
                    if ($per->getCheckoutId()) {
                        $checkedOutBy = PersonQuery::create()
                            ->findOneById($per->getCheckoutId());
                        $sCheckoutby = $checkedOutBy->getFullName();
                    } ?>
                    <tr>
                        <td><img
                                src="<?= SystemURLs::getRootPath() . '/api/persons/' . $per->getPersonId() . '/thumbnail' ?>"
                                class="direct-chat-img initials-image"></a></td>
                        <?php
                        if ($bSundaySchool) {
                            ?>
                            <td>
                                <a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getFirstName() ?></a>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>
                                <a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getLastName() ?></a>
                            </td>
                            <?php
                        }
                        ?>
                        <?php
                        if ($bSundaySchool) {
                            ?>
                            <td>
                                <a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getLastName() ?></a>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>
                                <a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getFirstName() ?></a>
                            </td>
                            <?php
                        }
                        ?>
                        <td><?= ($checkedInPerson->getGender() == 1) ? _($genderMale) : _($genderFem) ?></td>
                        <td><span
                                id="checkinDatePersonID<?= $per->getPersonId() ?>"><?= (!empty($per->getCheckinDate())) ? OutputUtils::FormatDate($per->getCheckinDate()->format("Y-m-d H:i:s"), 1) : "" ?></span>
                        </td>
                        <td><?= $sCheckinby ?></td>
                        <td><span
                                id="checkoutDatePersonID<?= $per->getPersonId() ?>"><?= (!empty($per->getCheckoutDate())) ? OutputUtils::FormatDate($per->getCheckoutDate()->format("Y-m-d H:i:s"), 1) : "" ?></span>
                        </td>
                        <td><span id="checkoutPersonID<?= $per->getPersonId() ?>"><?= $sCheckoutby ?></span></td>

                        <td align="center">
                            <form method="POST" action="Checkin.php" name="DeletePersonFromEvent">
                                <input type="hidden" name="child-id" value="<?= $per->getPersonId() ?>">
                                <input type="hidden" name="EventID" value="<?= $EventID ?>">
                                <label>
                                    <input <?= (!is_null($per->getCheckinDate())) ? "checked" : "" ?> type="checkbox"
                                                                                                      data-personid="<?= $per->getPersonId() ?>"
                                                                                                      data-eventid="<?= $EventID ?>"
                                                                                                      class="PersonCheckinChangeState"
                                                                                                      id="PersonCheckinChangeState">
                                    <span
                                        id="presenceID<?= $per->getPersonId() ?>"> <?= _("Checkin") ?></span>
                                </label>
                                <br/>
                                <label>
                                    <input <?= (!is_null($per->getCheckoutDate())) ? "checked" : "" ?> type="checkbox"
                                                                                                       data-personid="<?= $per->getPersonId() ?>"
                                                                                                       data-eventid="<?= $EventID ?>"
                                                                                                       class="PersonCheckoutChangeState"
                                                                                                       id="PersonCheckoutChangeState-<?= $per->getPersonId() ?>">
                                    <span
                                        id="presenceID<?= $per->getPersonId() ?>"> <?= _("Checkout") ?></span>
                                </label>
                            </form>
                        </td>
                        <?php
                        if ($bSundaySchool == false) {
                            ?>
                            <td align="center">
                                <form method="POST" action="Checkin.php" name="DeletePersonFromEvent">
                                    <input type="hidden" name="child-id" value="<?= $per->getPersonId() ?>">
                                    <input type="hidden" name="EventID" value="<?= $EventID ?>">
                                    <input class="btn btn-danger btn-sm" type="submit" name="DeleteBtn"
                                           value="<?= _('Delete') ?>">
                                </form>
                            </td>
                            <?php
                        }
                        ?>
                    </tr>
                    <?php
                } ?>
                </tbody>
            </table>
            <div class="row" style="margin:5px">
                <div class="col-md-1">
                    <label><?= _("Checkin") ?></label>
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-primary" type="submit" name="checkAllCheckin" id="checkAllCheckin"
                           data-id="<?= $EventID ?>" value="<?= _('Check all') ?>">
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-success" type="submit" name="uncheckAllCheckin" id="uncheckAllCheckin"
                           data-id="<?= $EventID ?>" value="<?= _('Uncheck all') ?>">
                </div>
                <div class="col-md-1">
                    <label><?= _("Checkout") ?></label>
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-primary" type="submit" name="checkAllCheckout" id="checkAllCheckout"
                           data-id="<?= $EventID ?>" value="<?= _('Check all') ?>">
                </div>
                <div class="col-sm-2" style="text-align:center">
                    <input class="btn btn-success" type="submit" name="uncheckAllCheckout" id="uncheckAllCheckout"
                           data-id="<?= $EventID ?>" value="<?= _('Uncheck all') ?>">
                </div>
            </div>

            <hr/>
            <div class="row" style="margin:5px">
                <div class="col-sm-2" style="text-align:right">
                    <label><?= _("Add some notes") ?> : </label>
                </div>
                <div class="col-sm-8">
                    <form method="POST" action="<?= SystemURLs::getRootPath() ?>/Checkin.php" name="validateEvent">
                        <input type="hidden" name="validateEvent" value="<?= $EventID ?>">
                        <input type="hidden" name="EventID" value="<?= $EventID ?>">
                        <textarea id="NoteText" name="NoteText" style="width: 100%;min-height: 300px;"
                                  rows="40"><?= $sNoteText ?></textarea>
                        <br>
                        <input class="btn btn-primary" type="submit" name="Validate"
                               value="<?= _("Validate Attendance") ?>">

                    </form>
                </div>
                <br>
            </div>
        </div>
    </div>
    <?php
}

?>

<div>
    <a href="ListEvents.php" class='btn btn-default'>
        <i class='fa fa-chevron-left'></i>
        <?= _('Return to Events') ?>
    </a>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    <?php if ($EventID > 0 ) { ?>
    var perArr;
    $(document).ready(function () {
        $('#checkedinTable').DataTable({
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
            pageLength: 100,
            responsive: true,
            order: [[1, "asc"]]
        });

        if (window.CRM.bEDrive) {
            var editor = CKEDITOR.replace('NoteText', {
                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                language: window.CRM.lang,
                extraPlugins: 'uploadfile,uploadimage,filebrowser',
                uploadUrl: window.CRM.root + '/uploader/upload.php?type=publicDocuments',
                imageUploadUrl: window.CRM.root + '/uploader/upload.php?type=publicImages',
                filebrowserUploadUrl: window.CRM.root + '/uploader/upload.php?type=publicDocuments',
                filebrowserBrowseUrl: window.CRM.root + '/browser/browse.php?type=publicDocuments'
            });
        } else {
            var editor = CKEDITOR.replace('NoteText', {
                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                language: window.CRM.lang
            });
        }

        add_ckeditor_buttons(editor);

        $('.collapse').on('shown.bs.collapse', function () {
            $(this).parent().find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up");
        }).on('hidden.bs.collapse', function () {
            $(this).parent().find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down");
        });

    });

    $(document).ready(function () {
        var $input = $("#child, #adult, #adultout");
        $input.autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.CRM.root + '/api/persons/search/' + request.term,
                    dataType: 'json',
                    type: 'GET',
                    success: function (data) {
                        console.log(data);
                        response($.map(data, function (item) {
                            return {
                                label: item.text,
                                value: item.objid,
                                obj: item
                            };
                        }));
                    }
                })
            },
            minLength: 2,
            select: function (event, ui) {
                $('[id=' + event.target.id + ']').val(ui.item.obj.text);
                $('[id=' + event.target.id + '-id]').val(ui.item.obj.objid);
                SetPersonHtml($('#' + event.target.id + 'Details'), ui.item.obj);
                return false;
            }
        });

    });

    function SetPersonHtml(element, perArr) {
        if (perArr) {
            element.html(
                '<div class="text-center">' +
                '<a target="_top" href="PersonView.php?PersonID=' + perArr.objid + '"><h4>' + perArr.text + '</h4></a>' +
                '<img src="' + window.CRM.root + '/api/persons/' + perArr.objid + '/thumbnail"' +
                'class="initials-image profile-user-img img-responsive img-circle"> </div>'
            );
            element.removeClass('hidden');
        } else {
            element.html('');
            element.addClass('hidden');
        }
    }
    <?php } ?>
</script>

<?php require 'Include/Footer.php';

function loadPerson($iPersonID)
{
    if ($iPersonID == 0) {
        echo "";
    }
    $person = PersonQuery::create()
        ->findOneById($iPersonID);
    $familyRole = "(";
    if ($person->getFamId()) {
        if ($person->getFamilyRole()) {
            $familyRole .= $person->getFamilyRoleName();
        } else {
            $familyRole .= _('Member');
        }
        $familyRole .= _(' of the') . ' <a href="FamilyView.php?FamilyID=' . $person->getFamId() . '">' . $person->getFamily()->getName() . '</a> ' . _('family') . ' )';
    } else {
        $familyRole = _('(No assigned family)');
    }


    $html = '<div class="text-center">' .
        '<img src="' . SystemURLs::getRootPath() . '/api/persons/' . $iPersonID . '/thumbnail" class="initials-image profile-user-img img-responsive img-circle"> </div>' .
        '<a target="_top" href="PersonView.php?PersonID=' . $iPersonID . '"><h4>' . $person->getTitle() . ' ' . $person->getFullName() . '</h4></a>' .
        '<div class="">' . $familyRole . '</div>' .
        '<div class="text-center">' . $person->getAddress() . '</div><br>';
    echo $html;
}

?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isModifiable = true;

    window.CRM.churchloc = {
        lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<link href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css"
      rel="stylesheet">

<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"
        type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/EventEditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/Checkin.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/publicfolder.js"></script>

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
