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


$sPageTitle = gettext('Event Checkin');
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
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\EventCounts;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Group;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;


$EventID = 0;
$CheckoutOrDelete = false;
$event = null;
$iChildID = 0 ;
$iAdultID = 0;

if (array_key_exists('EventID', $_POST)) {
   // from ListEvents button=Attendees
   $EventID = InputUtils::LegacyFilterInput($_POST['EventID'], 'int');
   $_SESSION['EventID'] = $EventID;
} else if (isset ($_SESSION['EventID'])) {
   // from api/routes/events.php
   $EventID = InputUtils::LegacyFilterInput($_SESSION['EventID'], 'int');
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
    $CheckoutOrDelete =  true;
} 

if (isset($_POST['validateEvent']) && isset($_POST['NoteText']) ) {
  $event = EventQuery::Create()
        ->findOneById($EventID);
        
  $event->setText($_POST['NoteText']);
  
  $event->save();
  
  
  $eventAttents = EventAttendQuery::Create()
            ->filterByEventId($EventID)
            ->find();
            
  foreach ($eventAttents as $eventAttent) {
    $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());    
    
    $eventAttent->save();
  }

  /*if (GroupQuery::Create()->findOneById($event->getGroupId())->isSundaySchool()) {
    // in the case you are in a sundayschool group we stay on the same page, for productivity
    //Redirect('sundayschool/SundaySchoolClassView.php?groupId='.$event->getGroupId());
  } else */
  if ($bSundaySchool == false && !is_null($event) && $event->getGroupId()) {
    //Redirect('GroupView.php?GroupID='.$event->getGroupId());
    Redirect('Calendar.php');
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
    ->Where('MONTH(event_start) = '.date('m').' AND YEAR(event_start)='.date('Y'))// We filter only the events from the current month
    ->find();
    
$searchEventInActivEvent = EventQuery::Create()
    ->filterByInActive(1, Criteria::NOT_EQUAL)
    ->Where('MONTH(event_start) = '.date('m').' AND YEAR(event_start)='.date('Y'))// We filter only the events from the current month
    ->findOneById($EventID);        

if ($searchEventInActivEvent != null) {
    //get Event Details
    $event = EventQuery::Create()
        ->findOneById($EventID);
    
    $sTitle = $event->getTitle();
    $sNoteText = $event->getText();        
        
    $eventCountNames = EventCountNameQuery::Create()
        ->leftJoinEventTypes()
        ->Where('type_id='.$event->getType())
        ->find();
} else if ($activeEvents->count() == 0 && is_null($event) ) {
  Redirect('Menu.php');
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
    <?= gettext('Add New Event') ?>
  </a>
</div>

<br>

<div id="errorcallout" class="callout callout-danger" hidden></div>

<?php 
  if (!empty($searchEventInActivEvent)) {
?>

<!--Select Event Form -->
<div class="box box-primary">
   <div class="box-header  with-border">
       <h3 class="box-title">
        <?= gettext('Select the event to which you would like to check people in for') ?> :</h3>
    </div>
    <div class="row">
        <div class="col-md-10 col-xs-12">
                <div class="box-body">
                    <?php if ($sGlobalMessage): ?>
                        <p><?= $sGlobalMessage ?></p>
                    <?php endif; ?>
                   <form name="selectEvent" action="Checkin.php" method="POST">
                    <div class="form-group">
                        <label class="col-md-2 control-label"><?= gettext('Select Event'); ?></label>
                        <div class="col-md-10 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar-check-o"></i></span>
                                <select name="EventID" class="form-control" onchange="this.form.submit()">
                                    <option value="<?= $EventID; ?>"
                                            disabled <?= ($EventID == 0) ? " Selected='selected'" : "" ?> ><?= gettext('Select event') ?></option>
                                    <?php foreach ($activeEvents as $event) {
    ?>
                                        <option
                                            value="<?= $event->getId(); ?>" <?= ($EventID == $event->getId()) ? " Selected='selected'" : "" ?> >
                                            <?= $event->getTitle()." (".$event->getDesc().")"; ?></option>
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
<div class="box box-primary">
    <div class="row">
        <div class="col-md-10 col-xs-12">
                <div class="box-header">
                    <h3 class="box-title"><?= "<b>".$event->getTitle()."</b> (".$event->getDesc().")"." ".gettext("From")." : <b>".OutputUtils::FormatDate($event->getStart()->format("Y-m-d H:i:s"),1)."</b> ".gettext("To")." : <b>".OutputUtils::FormatDate($event->getEnd()->format("Y-m-d H:i:s"),1)."</b>" ?> :</h3>
                </div>
                <div class="box-body">
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
 <div class="panel panel-primary">
      <div class="panel-heading">
        <h4 class="panel-title">
          <a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><?= gettext('Set your free attendees') ?> <i class="fa fa-chevron-down pull-right"></i></a>
        </h4>
      </div>
      <div id="collapse1" class="panel-collapse collapse">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-12 col-xs-12">
            <div class="box-header">
                    <h3 class="box-title"><?= gettext('You can set here the attendees for some group of persons.') ?></h3>
                </div>
                <div class="box-body">
                   <form name="addFreeAttendeesEvent" action="Checkin.php" method="POST">
                      <input type="hidden" name="EventID" value="<?= $EventID ?>">
                      <input type="hidden" name="FreeAttendees" value="1">
                      <div class="form-group row">
                        <label class="col-md-2 control-label"><?= gettext('Set your attendees Event'); ?></label>
                        <?php 
                           $desc = "";
                           foreach ($eventCountNames as $eventCountName) {
                        ?>                        
                           <div class="col-md-2">
                               <?= $eventCountName->getName();  ?>
                               
                               <?php
                                  $eventCount = EventCountsQuery::Create()
                                                ->filterByEvtcntEventid($EventID)
                                                ->findOneByEvtcntCountid($eventCountName->getId());
                                  
                                  $count = 0;
                                  if (!empty($eventCount)) {
                                    $count = $eventCount->getEvtcntCountcount();
                                    $desc =  $eventCount->getEvtcntNotes();
                                  }
                               ?>                               
                               <input type="text" id="field<?= $eventCountName->getId() ?>" name="<?= $eventCountName->getId() ?>" data-countid="<?= $eventCountName->getId() ?>" value="<?= $count ?>" size="8" class="form-control input-sm" width="100%" style="width: 100%">
                           </div>
                        <?php
                          }
                        ?>
                    </div>
                    <div class="row">
                        <label class="col-md-2 control-label"><?= gettext('Your description'); ?></label>
                          <div class="col-md-6">
                               <input type="text" id="fieldText" name="desc" data-countid="<?= $eventCountName->getId() ?>" value="<?= $desc ?>" size="8" class="form-control input-sm" width="100%" style="width: 100%">
                          </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12 text-right">
                           <input type="submit" class="btn btn-primary" value="<?= gettext('Add Free Attendees Count'); ?>"
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
<!-- End Add Free Attendees Form -->

<?php
}
?>

<!-- Add Attendees Form -->
<?php
// If event is known, then show 2 text boxes, person being checked in and the person checking them in.
// Show a verify button and a button to add new visitor in dbase.
if (!$CheckoutOrDelete &&  $EventID > 0) {
    ?>

<form class="form-horizontal" method="post" action="Checkin.php" id="AddAttendees" data-toggle="validator" role="form">
   <input type="hidden" id="EventID" name="EventID" value="<?= $EventID; ?>">
   <input type="hidden" id="child-id" name="child-id">
   <input type="hidden" id="adult-id" name="adult-id">

   <div class="panel panel-primary">
      <div class="panel-heading">
        <h4 class="panel-title">
          <a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><?= gettext('Add single child/parents Attendees') ?><i class="fa fa-chevron-down pull-right"></i></a>
        </h4>
      </div>
      <div id="collapse2" class="panel-collapse collapse">
        <div class="row">
            <div class="col-xs-12">
                    <div class="box-header">
                        <h3 class="box-title"><?= gettext('Add Attendees for Event'); ?>: <?= $event->getTitle() ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="child" class="col-sm-2 control-label"><?= gettext("Person's Name") ?></label>
                            <div class="col-sm-5 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-child"></i></span>
                                    <input type="text" class="form-control" id="child"
                                           placeholder="<?= gettext("Person's Name"); ?>" required tabindex=1>
                                </div>
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div id="childDetails" class="col-sm-5 text-center"></div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label for="adult"
                                   class="col-sm-2 control-label"><?= gettext('Adult Name(Optional)') ?></label>
                            <div class="col-sm-5 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control" id="adult"
                                           placeholder="<?= gettext('Checked in By(Optional)'); ?>" tabindex=2>
                                </div>
                            </div>
                            <div id="adultDetails" class="col-sm-5 text-center"></div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <input type="submit" class="btn btn-primary" value="<?= gettext('CheckIn'); ?>"
                                       name="CheckIn" tabindex=3>
                            </div>
                            <div class="col-md-4">
                                <input type="reset" class="btn btn-default" value="<?= gettext('Cancel'); ?>"
                                       name="Cancel" tabindex=4 onClick="SetPersonHtml($('#childDetails'),null);SetPersonHtml($('#adultDetails'),null);">
                            </div>
                            <div class="col-md-4">
                                <input type="Add" class="btn btn-success" value="<?= gettext('Add Visitor'); ?>"
                                       name="Add" tabindex=4 onClick="javascript:document.location = '<?= SystemURLs::getRootPath() ?>/PersonEditor.php';">
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
                $('#errorcallout').text('<?= gettext("Person has been already checked in for this event") ?>').fadeIn();
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
    (isset($_POST['CheckOutBtn']) || isset($_POST['DeleteBtn']) )
) {
    $iChildID = InputUtils::LegacyFilterInput($_POST['child-id'], 'int');

    $formTitle = (isset($_POST['CheckOutBtn']) ? gettext("CheckOut Person") : gettext("Delete Checkin in Entry")); ?>

    <form method="post" action="Checkin.php" id="CheckOut" data-toggle="validator" role="form">
        <input type="hidden" name="EventID" value="<?= $EventID ?>">
        <input type="hidden" name="child-id" value="<?= $iChildID ?>">

        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= $formTitle ?></h3>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div id="child" class="col-sm-4 text-center" onload="SetPersonHtml(this,perArr)">
                                <?php
                                loadperson($iChildID); ?>
                            </div>
                            <?php
                            if (isset($_POST['CheckOutBtn'])) {
                                $person = PersonQuery::Create()->findOneById($_SESSION['user']->getPersonId());
                                ?>
                                <div class="col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label><?= gettext('Adult Checking Out Person') ?>:</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" id="adultout" name="adult" class="form-control" value="<?= $person->getFullName() ?>"
                                               placeholder="<?= gettext('Adult Name (Optional)') ?>">
                                            </div>
                                        <input type="hidden" id="adultout-id" name="adult-id" value="<?= $person->getId() ?>">
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-primary"
                                               value="<?= gettext('CheckOut') ?>" name="CheckOut">
                                        <input type="submit" class="btn btn-default" value="<?= gettext('Cancel') ?>"
                                               name="CheckoutCancel">
                                    </div>
                                </div>

                                <div class="col-sm-4 text-center">                                    
                                    <div id="adultoutDetails" class="box box-solid box-default">
                                        <div class="text-center"><a target="_top" href="PersonView.php?PersonID=<?= $person->getId() ?>">
                                          <h4><?= $person->getFullName() ?></h4></a>
                                          <img src="/api/persons/<?= $person->getId() ?>/thumbnail" class="initials-image profile-user-img img-responsive img-circle"> 
                                          <br>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else { // DeleteBtn?>
                                <div class="form-group">
                                    <input type="submit" class="btn btn-danger"
                                           value="<?= gettext('Delete') ?>" name="Delete">
                                    <input type="submit" class="btn btn-default" value="<?= gettext('Cancel') ?>"
                                           name="DeleteCancel">
                                </div>
                                <?php
                            }?>
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
    <div class="box box-primary">
       <div class="box-header  with-border">
         <h3 class="box-title">
           <?= gettext('Listing') ?> :</h3>
        </div>
        <div class="box-body table-responsive">
            <table id="checkedinTable" class="table data-table table-striped " style="width:100%">
                <thead>
                <tr>
                    <th></th>
                    <th><?= ($bSundaySchool)?gettext('First Name'):gettext('Name') ?></th>
                    <th><?= ($bSundaySchool)?gettext('Name'):gettext('First Name') ?></th>
                    <th><?= gettext("Gender") ?></th>
                    <th><?= gettext('Checked In Time') ?></th>
                    <th><?= gettext('Checked In By') ?></th>
                    <th><?= gettext('Checked Out Time') ?></th>
                    <th><?= gettext('Checked Out By') ?></th>
                    <th nowrap><?= gettext('Action') ?></th>
                    <?php
                      if ($bSundaySchool == false) {
                    ?>
                      <th><?= gettext('Delete') ?></th>
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
                        <td><img src="<?= SystemURLs::getRootPath() . '/api/persons/' . $per->getPersonId() . '/thumbnail' ?>"
                                 class="direct-chat-img initials-image"></a></td>
                    <?php
                      if ($bSundaySchool) {
                      ?>
                        <td><a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getFirstName() ?></a></td>
                    <?php
                      } else {
                    ?>
                        <td><a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getLastName() ?></a></td>
                    <?php
                      }
                    ?>
                    <?php
                      if ($bSundaySchool) {
                    ?>
                        <td><a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getLastName() ?></a></td>
                    <?php
                      } else {
                    ?>
                        <td><a href="PersonView.php?PersonID=<?= $per->getPersonId() ?>"><?= $checkedInPerson->getFirstName() ?></a></td>
                    <?php
                      }
                    ?>
                        <td><?= ($checkedInPerson->getGender() == 1)?gettext($genderMale):gettext($genderFem) ?></td>
                        <td><?= (!empty($per->getCheckinDate()))?OutputUtils::FormatDate($per->getCheckinDate()->format("Y-m-d H:i:s"),1):"" ?></td>
                        <td><?= $sCheckinby ?></td>
                        <td><span id="checkoutDatePersonID<?= $per->getPersonId() ?>"><?= (!empty($per->getCheckoutDate()))?OutputUtils::FormatDate($per->getCheckoutDate()->format("Y-m-d H:i:s"),1):"" ?></span></td>
                        <td><span id="checkoutPersonID<?= $per->getPersonId() ?>"><?= $sCheckoutby ?></span></td>

                        <td align="center">
                            <form method="POST" action="Checkin.php" name="DeletePersonFromEvent">
                                <input type="hidden" name="child-id" value="<?= $per->getPersonId() ?>">
                                <input type="hidden" name="EventID" value="<?= $EventID ?>">
                                <label>
                                  <input <?= ($per->getCheckoutDate())?"checked":"" ?> type="checkbox" data-personid="<?= $per->getPersonId() ?>" data-eventid="<?= $EventID ?>" class="PersonChangeState" id="PersonChangeState"> <span id="presenceID<?= $per->getPersonId() ?>"> <?= ($per->getCheckoutDate())?gettext("Present"):gettext("Absent") ?></span>
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
                            <input class="btn btn-danger btn-sm" type="submit" name="DeleteBtn" value="<?= gettext('Delete') ?>">
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
              <center>
                <div class="col-sm-6" style="text-align:center">
                   <input class="btn btn-success" type="submit" name="uncheckAll" id="uncheckAll" data-id="<?= $EventID ?>" value="<?= gettext('Uncheck all') ?>">
                </div>
                <div class="col-sm-6" style="text-align:center">
                   <input class="btn btn-primary" type="submit" name="checkAll" id="checkAll" data-id="<?= $EventID ?>" value="<?= gettext('Check all') ?>">
                </div>
              </center>
            </div>

            <hr/>
            <div class="row" style="margin:5px"> 
            <label><?= gettext("Add some notes") ?> : </label>     
            <form method="POST" action="<?= SystemURLs::getRootPath() ?>/Checkin.php" name="validateEvent">
            <input type="hidden" name="validateEvent" value="<?= $EventID ?>">
            <input type="hidden" name="EventID" value="<?= $EventID ?>">
            <center>
              <textarea id="NoteText" name="NoteText" style="width: 100%;min-height: 300px;" rows="40"><?= $sNoteText ?></textarea>
              <br>
              <input class="btn btn-primary" type="submit" name="Validate" value="<?= gettext("Validate Attendance") ?>">
            </center>
            </form>
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
    <?= gettext('Return to Events') ?>
  </a>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditorextension.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
<?php if ($EventID > 0 ) { ?>
    var perArr;
    $(document).ready(function () {
        $('#checkedinTable').DataTable({
         "language": {
           "url": window.CRM.plugin.dataTable.language.url
         },
         pageLength: 100,
         responsive: true,
         order: [[ 1, "asc" ]]
       });
     
     var editor = CKEDITOR.replace('NoteText',{
       customConfig: '<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/note_editor_config.js',
       language : window.CRM.lang
     });  
     
     add_ckeditor_buttons(editor);
     
     $('.collapse').on('shown.bs.collapse', function(){
        $(this).parent().find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up");
     }).on('hidden.bs.collapse', function(){
        $(this).parent().find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down");
     });

    });

    $(document).ready(function() {
        var $input = $("#child, #adult, #adultout");
        $input.autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.CRM.root + '/api/persons/search/'+request.term,
                    dataType: 'json',
                    type: 'GET',
                    success: function (data) {
                        console.log(data);
                        response($.map(data, function (item) {
                            return {
                                label: item.text,
                                value: item.objid,
                                obj:item
                            };
                        }));
                    }
                })
            },
            minLength: 2,
            select: function(event,ui) {
                $('[id=' + event.target.id + ']' ).val(ui.item.obj.text);
                $('[id=' + event.target.id + '-id]').val(ui.item.obj.objid);
                SetPersonHtml($('#' + event.target.id + 'Details'),ui.item.obj);
                return false;
            }
        });

    });

    function SetPersonHtml(element, perArr) {
        if(perArr) {
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
    $familyRole="(";
    if ($person->getFamId()) {
        if ($person->getFamilyRole()) {
            $familyRole .= $person->getFamilyRoleName();
        } else {
            $familyRole .=  gettext('Member');
        }
        $familyRole .= gettext(' of the').' <a href="FamilyView.php?FamilyID='. $person->getFamId().'">'.$person->getFamily()->getName().'</a> '.gettext('family').' )';
    } else {
        $familyRole = gettext('(No assigned family)');
    }


    $html = '<div class="text-center">' .
        '<img src="' . SystemURLs::getRootPath() . '/api/persons/' . $iPersonID . '/thumbnail" class="initials-image profile-user-img img-responsive img-circle"> </div>'.
        '<a target="_top" href="PersonView.php?PersonID=' . $iPersonID . '"><h4>' . $person->getTitle(). ' ' . $person->getFullName() . '</h4></a>' .
        '<div class="">' . $familyRole . '</div>' .
        '<div class="text-center">' . $person->getAddress() . '</div><br>';
    echo $html;
}
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.isModifiable  = true;
  
  window.CRM.churchloc = {
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
  window.CRM.mapZoom   = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/js/EventEditor.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/Checkin.js" ></script>
<?php
  if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/OpenStreetMapEvent.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/GoogleMapEvent.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/BingMapEvent.js"></script>
<?php
  }
?>
