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

$sAction = $_POST['Action'];
$EventID = $_POST['EID']; // from ListEvents button=Attendees
$EvtName = $_POST['EName'];
$EvtDesc = $_POST['EDesc'];
$EvtDate = $_POST['EDate'];
//
// process the action inputs
//
if ($sAction == gettext('Delete')) {
    $dpeEventID = $_POST['DelPerEventID'];
    $dpePerID = $_POST['DelPerID'];
    
    $eventAttend = EventAttendQuery::Create()->filterByEventId($dpeEventID)->filterByPersonId($dpePerID)->limit(1)->findOne();
    $eventAttend->delete();
    
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
      
  <table class="table data-table table-striped dataTable no-footer dtr-inline" id="eventsTable" style="width:100%">
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

if ($numAttRows != 0) {
  $sRowClass = 'RowColorA';
  foreach ($ormOpps as $ormOpp) {
   $person = $ormOpp->getPerson();

   $per_fam = PersonQuery::Create()->filterByPrimaryKey($person->getId())->joinWithFamily()->findOne();
   
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
<center>
<form action="<?= SystemURLs::getRootPath() ?>/Checkin.php" method="POST">
                      <input type="hidden" name="EventID" value="<?= $EventID ?>">
                      <button type="submit" name="Action" title="<?=gettext('Make Check-out') ?>" data-tooltip value="<?=gettext('Make Check-out') ?>" class="btn btn-success">
                        <i class='fa fa-check-circle'></i> <?=gettext('Make Check-out') ?>
                      </button>
                     </form>
</center>                
</div>
<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
//Added by @saulowulhynek to translation of datatable nav terms
  $(document).ready(function () {
    $("#eventsTable").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true
    });
  });
</script>
<?php require 'Include/Footer.php' ?>
