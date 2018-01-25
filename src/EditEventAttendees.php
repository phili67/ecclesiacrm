<?php
/*******************************************************************************
 *
 *  filename    : EditEventAttendees.php
 *  last change : 2018-01-08
 *  description : Edit Event Attendees
 *
 *  http://www.ecclesiacrm.com/
 *        copyright 2018 Philippe Logel
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\dto\SystemURLs;
use Propel\Runtime\ActiveQuery\Criteria;


$sPageTitle = gettext('Church Event Editor');
require 'Include/Header.php';

if (isset($_POST['Action'])) {
  $sAction = $_POST['Action'];
  $EventID = $_POST['EID']; // from ListEvents button=Attendees
  $EvtName = $_POST['EName'];
  $EvtDesc = $_POST['EDesc'];
  $EvtDate = $_POST['EDate'];

  $_SESSION['Action'] = $sAction;
  $_SESSION['EID'] = $EventID;
  $_SESSION['EName'] = $EvtName;
  $_SESSION['EDesc'] = $EvtDesc;
  $_SESSION['EDate'] = $EvtDate;
} else if(isset($_SESSION['Action'])) {
  $sAction = $_SESSION['Action'];
  $EventID = $_SESSION['EID'];
  $EvtName = $_SESSION['EName'];
  $EvtDesc = $_SESSION['EDesc'];
  $EvtDate = $_SESSION['EDate'];
}

//
// process the action inputs
//
if ($sAction == gettext('Delete')) {
    $dpeEventID = $_POST['DelPerEventID'];
    $dpePerID = $_POST['DelPerID'];
    
    $eventAttend = EventAttendQuery::Create()->filterByEventId($dpeEventID)->filterByPersonId($dpePerID)->limit(1)->findOne();
    if ($eventAttend) {
      $eventAttend->delete();
    }
    
    $ShowAttendees = 1;
}
// Construct the form
?>

<div class='box-header'>
    <h3 class='box-title'><?= gettext('Attendees for Event ID:').' '.$EventID ?></h3>
</div>
<p style="margin-left:10px">
    <strong><?= gettext('Name')?>:</strong> <?= $EvtName ?><br/>
    <strong><?= gettext('Date')?>:</strong> <?= $EvtDate ?><br/>
    <strong><?= gettext('Description')?>:</strong> <?= $EvtDesc ?><br/>
</p>

<div class='box'>
  
  <div class='box-body'>
    <form method="post" action="EditEventAttendees.php" name="AttendeeEditor">
      <input type="hidden" name="EID" value="<?= $EventID  ?>">
      
  <table class="table table-striped table-bordered data-table  dataTable no-footer dtr-inline" id="eventsTable" style="width:100%">
  <thead>
  <tr class="TableHeader">
    <th width="35%"><strong><?= gettext('Name') ?></strong></td>
    <th width="25%"><strong><?= gettext('Email') ?></strong></td>
    <th width="25%"><strong><?= gettext('Home Phone') ?></strong></td>
    <th width="15%" nowrap><strong><?= gettext('Action') ?></strong></td>
  </tr>
  </thead>
  <tbody>
<?php

$ormOpps = EventAttendQuery::Create()->filterByEventId($EventID)->leftJoinPerson()->usePersonQuery()->orderByLastName()->orderByFirstName()->endUse()->find();


$numAttRows = count($ormOpps);

$countCheckout = 0;

if ($numAttRows != 0) {
  $sRowClass = 'RowColorA';
  foreach ($ormOpps as $ormOpp) {
    $person = $ormOpp->getPerson();

    $per_fam = PersonQuery::Create()->filterByPrimaryKey($person->getId())->joinWithFamily()->findOne();
    
    if ($ormOpp->getCheckoutId()) {
      $countCheckout++;
    }
   
    $fam = null;
   
    if($per_fam) {
      $fam = $per_fam->getFamily();
    }
   
   $sPhoneCountry = SelectWhichInfo($person->getCountry(), (!empty($fam))?$fam->getCountry():"", false);
   $sHomePhone = SelectWhichInfo(ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy), ExpandPhoneNumber((!empty($fam))?$fam->getHomePhone():"", (!empty($fam))?$fam->getCountry():"", $dummy), true);
   $sEmail = SelectWhichInfo($person->getEmail(), (!empty($fam))?$fam->getEmail():"", false);?>
    <tr>
        <td class="TextColumn"><?= FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) ?></td>
        <td class="TextColumn"><?= $sEmail ? '<a href="mailto:'.$sEmail.'" title="Send Email">'.$sEmail.'</a>' : gettext('Not Available') ?></td>
        <td class="TextColumn"><?= $sHomePhone ? '<a href="tel:'.$sHomePhone.'" title="Phone to">'.$sHomePhone.'</a>' : gettext('Not Available') ?></td>
    <td colspan="1" align="center">
      <form method="POST" action="EditEventAttendees.php" name="DeletePersonFromEvent">
          <input type="hidden" name="DelPerID" value="<?= $person->getId()?>">
          <input type="hidden" name="DelPerEventID" value="<?= $EventID ?>">
          <input type="hidden" name="EID" value="<?= $EventID ?>">
          <input type="hidden" name="EName" value="<?= $EvtName ?>">
          <input type="hidden" name="EDesc" value="<?= $EvtDesc ?>">
          <input type="hidden" name="EDate" value="<?= $EvtDate ?>">
          <input type="submit" name="Action" value="<?= gettext('Delete') ?>" class="btn btn-danger" onClick="return confirm("<?= gettext('Are you sure you want to DELETE this person from Event ID: ').$EventID ?>")">
      </form>
     </td>
    </tr>
    <?php
  }
} else {
    ?>
<tr><td colspan="4" align="center"><?= gettext('No Attendees Assigned to Event') ?></td></tr>
<?php
}

?>
<tbody>
</table>

<div class="row">
<div class="col-sm-6">
<form action="#" method="get" class="sidebar-form">
    <label for="addPersonMember"><?= gettext('Add Event Member') ?> :</label>
    <select class="form-control personSearch" name="addPersonMember" style="width:100%">
    </select>
</form>
</div>
<div class="col-sm-6">
<form action="#" method="get" class="sidebar-form">
    <label for="addGroupMember"><?= gettext('Add All Group Members') ?> :</label>
    <select class="form-control groupSearch" name="addGroupMember" style="width:100%">
    </select>
</form>
</div>
</div>
<br>
<center>
<?php if ($numAttRows-$countCheckout>0) { ?>
    <form action="<?= SystemURLs::getRootPath() ?>/Checkin.php" method="POST">
      <input type="hidden" name="EventID" value="<?= $EventID ?>">
      <button type="submit" name="Action" title="<?=gettext('Make Check-out') ?>" data-tooltip value="<?= gettext('Make Check-out') ?>" class="btn btn-success <?= ($numAttRows == 0)?"disabled":"" ?>">
        <i class='fa fa-check-circle'></i> <?=gettext('Make Check-out') ?>
      </button>
    </form>
<?php } else { ?>
      <button type="" data-tooltip value="<?= gettext('Make Check-out') ?>" class="btn btn-success disabled ?>">
        <i class='fa fa-check-circle'></i> <?=gettext('Make Check-out') ?>
      </button>
<?php } ?>
</center>                
</div>
</div>

<div>
  <a href="ListEvents.php" class='btn btn-default'>
    <i class='fa fa-chevron-left'></i>
    <?= gettext('Return to Events') ?>
  </a>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
//Added by @saulowulhynek to translation of datatable nav terms
  window.CRM.currentEvent = <?= $EventID ?>;

  $(document).ready(function () {
   <?php 
     if ($numAttRows != 0) {
     ?>
    
      window.CRM.DataTableEventView = $("#eventsTable").DataTable({
         "language": {
         "url": window.CRM.plugin.dataTable.language.url,
      },
      pageLength: 100,
      responsive: true
    });
  <?php
    }
    ?>
  });
</script>

<script src="skin/js/EditEventAttendees.js" ></script>

<?php require 'Include/Footer.php' ?>
