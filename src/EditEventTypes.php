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


if (!$_SESSION['user']->isAdmin()) {
    header('Location: Menu.php');
}
$sPageTitle = gettext('Edit Event Types');
require 'Include/Header.php';

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
    }
} else {
  switch ($_POST['Action']) {
    case 'ADD':
      $eventCountName = new EventCountName();
      
      $eventCountName->setName(InputUtils::FilterString($_POST['newCountName']));
      $eventCountName->setTypeId($_POST['EN_tyid']);
      
      $eventCountName->save();

      break;
      
    case 'NAME':
      $editing = 'FALSE';
      
      $eventType = EventTypesQuery::Create()
                       ->findOneById($_POST['EN_tyid']);
                       
      $eventType->setName(InputUtils::FilterString($_POST['newEvtName']));
      
      $eventType->save();
      
                       
      $theID = '';
      $_POST['Action'] = '';
      break;

    case 'TIME':
      $editing = 'FALSE';
      
      $eventType = EventTypesQuery::Create()
                       ->findOneById($_POST['EN_tyid']);
                       
      $eventType->setDefStartTime($_POST['newEvtStartTime']);
      
      $eventType->save();
                  
      $theID = '';
      $_POST['Action'] = '';
      break;
  }
}

// Get data for the form as it now exists.
// Get data for the form as it now exists.
$eventType = EventTypesQuery::Create()
                ->findOneById($tyid);
                
if (empty($eventType)) {
  Redirect('EventNames.php'); // clear POST
}

$aTypeID = $eventType->getId();
$aTypeName = $eventType->getName();
$aDefStartTime = $eventType->getDefStartTime()->format('h:i:s');
    $aStartTimeTokens = explode(':', $aDefStartTime);
    $aEventStartHour = $aStartTimeTokens[0];
    $aEventStartMins = $aStartTimeTokens[1];
$aDefRecurDOW = $eventType->getDefRecurDOW();
$aDefRecurDOM = $eventType->getDefRecurDOM();
$aDefRecurDOY = (empty($eventType->getDefRecurDOY()))?"":$eventType->getDefRecurDOY()->format(SystemConfig::getValue("sDateFormatNoYear"));
$aDefRecurType = $eventType->getDefRecurType();


switch ($aDefRecurType) {
    case 'none':
       $recur = gettext('None');
       break;
    case 'weekly':
       $recur = gettext('Weekly on').' '.gettext($aDefRecurDOW.'s');
       break;
    case 'monthly':
       $recur = gettext('Monthly on').' '.date('dS', mktime(0, 0, 0, 1, $aDefRecurDOM, 2000));
       break;
    case 'yearly':
       $recur = gettext('Yearly on').' '.$aDefRecurDOY;
       break;
    default:
       $recur = gettext('None');
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
          
// Construct the form
?>
<div class='box'>
  <div class='box-header'>
    <h3 class='box-title'><?= gettext('Edit Event Type') ?></h3>
  </div>

  <form method="POST" action="EditEventTypes.php" name="EventTypeEditForm">
  <input type="hidden" name="EN_tyid" value="<?= $aTypeID ?>">
  <input type="hidden" name="EN_ctid" value="<?= $cCountID[$c] ?>">

<table class='table'>
  <tr>
    <td class="LabelColumn" width="15%">
      <strong><?= gettext('Event Type').':'.$aTypeID ?></strong>
    </td>
    <td class="TextColumn" width="35%">
      <input type="text" class="form-control" name="newEvtName" value="<?= $aTypeName ?>" size="30" maxlength="35" autofocus />
    </td>
    <td class="TextColumn" width="50%">
      <button type="submit" Name="Action" value="NAME" class="btn btn-primary"><?= gettext('Save Name') ?></button>
    </td>
  </tr>
  <tr>
    <td class="LabelColumn" width="15%">
      <strong><?= gettext('Recurrence Pattern') ?></strong>
    </td>
    <td class="TextColumn" width="35%">
      <?= $recur ?>
    </td>
    <td class="TextColumn" width="50%">
      <select class='form-control' name="newEvtStartTime" size="1" onchange="javascript:$('#newEvtStartTimeSubmit').click()">
        <?php OutputUtils::createTimeDropdown(7, 18, 15, $aEventStartHour, $aEventStartMins); ?>
      </select>
      <button class='hidden' type="submit" name="Action" value="TIME" id="newEvtStartTimeSubmit"></button>
    </td>
  </tr>

   <tr>
      <td class="LabelColumn" width="15%" rowspan="<?= $nr ?>" colspan="1">
        <strong><?= gettext('Attendance Counts') ?></strong>
      </td>
    </tr>
    <?php
    for ($c = 0; $c < $numCounts; $c++) {
        ?>
      <tr>
        <td class="TextColumn" width="35%"><?= $cCountName[$c] ?></td>
        <td class="TextColumn" width="50%">
          <button type="submit" name="Action" value="DELETE_<?=  $cCountID[$c] ?>" class="btn btn-danger"><?= gettext('Remove') ?></button>
        </td>
      </tr>
     <?php
    }
     ?>
      <tr>
        <td class="TextColumn" width="35%">
           <input class='form-control' type="text" name="newCountName" length="20" placeholder="<?= gettext("New Attendance Count") ?>" />
        </td>
        <td class="TextColumn" width="50%">
           <button type="submit" name="Action" value="ADD" class="btn btn-success"><?= gettext('Add counter') ?></button>
        </td>
      </tr>
</table>
</form>
</div>

<div>
  <a href="EventNames.php" class='btn btn-default'>
    <i class='fa fa-chevron-left'></i>
    <?= gettext('Return to Event Types') ?>
  </a>
</div>

<?php require 'Include/Footer.php' ?>