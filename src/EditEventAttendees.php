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
<div class='box'>
  <div class='box-header'>
    <h3 class='box-title'><?= gettext('Attendees for Event ID:').' '.$EventID ?></h3>
  </div>
  <div class='box-body'>
    <strong><?= gettext('Name')?>:</strong> <?= $EvtName ?><br/>
    <strong><?= gettext('Date')?>:</strong> <?= $EvtDate ?><br/>
    <strong><?= gettext('Description')?>:</strong><br/>
    <?= $EvtDesc ?>
    <p/>
    <form method="post" action="EditEventAttendees.php" name="AttendeeEditor">
      <input type="hidden" name="EID" value="<?= $EventID  ?>">
  <table class="table">
  <tr class="TableHeader">
    <td width="35%"><strong><?= gettext('Name') ?></strong></td>
    <td width="25%"><strong><?= gettext('Email') ?></strong></td>
    <td width="25%"><strong><?= gettext('Home Phone') ?></strong></td>
	  <td width="15%" nowrap><strong><?= gettext('Action') ?></strong></td>
  </tr>
<?php

$ormOpps = EventAttendQuery::Create()->filterByEventId($EventID)->leftJoinPerson()->usePersonQuery()->orderByLastName()->orderByFirstName()->endUse()->find();

$numAttRows = count($ormOpps);

if ($numAttRows != 0) {
  $sRowClass = 'RowColorA';
  foreach ($ormOpps as $ormOpp) {
   $person = $ormOpp->getPerson();

   $fam = PersonQuery::Create()->filterByPrimaryKey($person->getId())->joinWithFamily()->findOne()->getFamily();
   
   $sPhoneCountry = SelectWhichInfo($person->getCountry(), $fam->getCountry(), false);
   $sHomePhone = SelectWhichInfo(ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy), ExpandPhoneNumber($fam->getHomePhone(), $fam->getCountry(), $dummy), true);
   $sEmail = SelectWhichInfo($person->getEmail(), $fam->getEmail(), false);?>
    <tr class="<?= $sRowClass ?>">
        <td class="TextColumn"><?= FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) ?></td>
        <td class="TextColumn"><?= $sEmail ? '<a href="mailto:'.$sEmail.'" title="Send Email">'.$sEmail.'</a>' : 'Not Available' ?></td>
        <td class="TextColumn"><?= $sHomePhone ? $sHomePhone : 'Not Available' ?></td>
    <td  class="TextColumn" colspan="1" align="center">
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
</table>
</div>
<?php require 'Include/Footer.php' ?>
