<?php
/*******************************************************************************
 *
 *  filename    : EventAttendance.php
 *  last change : 2005-09-18
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Todd Pillars
 *
 *  function    : List all Church Events
  *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;

use Propel\Runtime\Propel;

if ( !SystemConfig::getBooleanValue('bEnabledSundaySchool') ) {
  RedirectUtils::Redirect('v2/dashboard');
  exit;
}

$connection = Propel::getConnection();

if (array_key_exists('Action', $_POST) && $_POST['Action'] == 'Retrieve' && !empty($_POST['Event'])) {
    if ($_POST['Choice'] == 'Attendees') {
        $sSQL = 'SELECT t1.per_ID, t1.per_Title, t1.per_FirstName, t1.per_MiddleName, t1.per_LastName, t1.per_Suffix, t1.per_Email, t1.per_HomePhone, t1.per_Country, t1.per_MembershipDate, t4.fam_HomePhone, t4.fam_Country
                FROM person_per AS t1, events_event AS t2, event_attend AS t3, family_fam AS t4
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$_POST['Event']." AND t1.per_fam_ID = t4.fam_ID AND per_cls_ID IN ('1','2','5')
                ORDER BY t1.per_LastName, t1.per_ID";
        $sPageTitle = _('Event Attendees');
    } elseif ($_POST['Choice'] == 'Nonattendees') {
        $aSQL = 'SELECT DISTINCT(person_id) FROM event_attend WHERE event_id = '.$_POST['Event'];

        $raOpps = $connection->prepare($aSQL);
        $raOpps->execute();

        $aArr = [];
        while ($aRow = $raOpps->fetch( \PDO::FETCH_ASSOC )) {
            $aArr[] = $aRow['person_id'];
        }

        if (count($aArr) > 0) {
            $aArrJoin = implode(',', $aArr);
            $sSQL = 'SELECT t1.per_ID, t1.per_Title, t1.per_FirstName, t1.per_MiddleName, t1.per_LastName, t1.per_Suffix, t1.per_Email, t1.per_HomePhone, t1.per_Country, t1.per_MembershipDate, t2.fam_HomePhone, t2.fam_Country
                        FROM person_per AS t1, family_fam AS t2
                        WHERE t1.per_fam_ID = t2.fam_ID AND t1.per_ID NOT IN ('.$aArrJoin.") AND per_cls_ID IN ('1','2','5')
                        ORDER BY t1.per_LastName, t1.per_ID";
        } else {
            $sSQL = "SELECT t1.per_ID, t1.per_Title, t1.per_FirstName, t1.per_MiddleName, t1.per_LastName, t1.per_Suffix, t1.per_Email, t1.per_HomePhone, t1.per_Country, t1.per_MembershipDate, t2.fam_HomePhone, t2.fam_Country
                        FROM person_per AS t1, family_fam AS t2
                        WHERE t1.per_fam_ID = t2.fam_ID AND per_cls_ID IN ('1','2','5')
                        ORDER BY t1.per_LastName, t1.per_ID";
        }
        $sPageTitle = _('Event Nonattendees');
    } elseif ($_POST['Choice'] == 'Guests') {
        $sSQL = 'SELECT t1.per_ID, t1.per_Title, t1.per_FirstName, t1.per_MiddleName, t1.per_LastName, t1.per_Suffix, t1.per_HomePhone, t1.per_Country
                FROM person_per AS t1, events_event AS t2, event_attend AS t3
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$_POST['Event']." AND per_cls_ID IN ('0','3')
                ORDER BY t1.per_LastName, t1.per_ID";
        $sPageTitle = _('Event Guests');
    }
} elseif (array_key_exists('Action', $_GET) && $_GET['Action'] == 'List' && !empty($_GET['Event'])) {
    $sSQL = 'SELECT * FROM events_event WHERE event_type = '.$_GET['Event'].' ORDER BY event_start';

    //I change textt from All $_GET['Type'] Events to All Events of type . $_GET['Type'], because it don´t work for protuguese, spanish, french and so on
    $sPageTitle = _('All Events of Type').': '.$_GET['Type'];
}
require 'Include/Header.php';
?>
<table cellpadding="4" align="center" cellspacing="0" width="100%">
  <tr>
    <td align="center"><input type="button" class="btn btn-default" value="<?= _('Back to Report Menu') ?>" Name="Exit" onclick="javascript:document.location='ReportList.php';"></td>
  </tr>
</table>
<?php
// Get data for the form as it now exists..
$statement = $connection->prepare($sSQL);
$statement->execute();

$numRows = $statement->rowCount();

// Create arrays of the attendees.
$row = 1;
while ($aRow = $statement->fetch( \PDO::FETCH_ASSOC )) {
    extract($aRow);

    if (array_key_exists('Action', $_GET) & $_GET['Action'] == 'List') {
        $aEventID[$row] = $event_id;
        $aEventTitle[$row] = htmlentities(stripslashes($event_title), ENT_NOQUOTES, 'UTF-8');
        $aEventStartDateTime[$row] = $event_start;
    } else {
        $aPersonID[$row] = $per_ID;
        $aTitle[$row] = $per_Title;
        $aFistName[$row] = $per_FirstName;
        $aMiddleName[$row] = $per_MiddleName;
        $aLastName[$row] = $per_LastName;
        $aSuffix[$row] = $per_Suffix;
        $aEmail[$row] = $per_Email;
        $aHomePhone[$row] = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($per_HomePhone, $per_Country, $dummy), MiscUtils::ExpandPhoneNumber($fam_HomePhone, $fam_Country, $dummy), true);
    }

    $row++;
}

// Construct the form
?>
<table cellpadding="4" align="center" cellspacing="0" width="60%" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline">

<?php
if (array_key_exists('Action', $_GET) && $_GET['Action'] == 'List' && $numRows > 0) {
    ?>
       <caption>
               <h3><?= ($numRows == 1 ? _('There is') : _('There are')).' '.$numRows.' '.($numRows == 1 ? _('event') : _('events'))._(' in this category.') ?></h3>
       </caption>
         <tr class="TableHeader">
           <td width="33%"><strong><?= _('Event Title') ?></strong></td>
           <td width="33%"><strong><?= _('Event Date') ?></strong></td>
           <td colspan="3" width="34%" align="center"><strong><?= _('Generate Report') ?></strong></td>
        </tr>
         <?php
         //Set the initial row color
         $sRowClass = 'RowColorA';

    for ($row = 1; $row <= $numRows; $row++) {

         //Alternate the row color
        $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

        //Display the row?>
         <tr class="<?= $sRowClass ?>">
           <td class="TextColumn"><?= $aEventTitle[$row] ?></td>
           <td class="TextColumn"><?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?></td>
           <td class="TextColumn" align="center">
             <form name="Attend" action="EventAttendance.php" method="POST">
               <input type="hidden" name="Event" value="<?= $aEventID[$row] ?>">
               <input type="hidden" name="Type" value="<?= $_GET['Type'] ?>">
               <input type="hidden" name="Action" value="Retrieve">
               <input type="hidden" name="Choice" value="Attendees">
<?php
$cSQL = 'SELECT COUNT(per_ID) AS cCount
         FROM person_per as t1, events_event as t2, event_attend as t3
         WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$aEventID[$row]." AND per_cls_ID IN ('1','2','5')";

$tSQL = "SELECT COUNT(per_ID) AS tCount
         FROM person_per
         WHERE per_cls_ID IN ('1','2','5')";

$cOpps = $connection->prepare($cSQL);
$cOpps->execute();
$cNumAttend = $cOpps->fetch( \PDO::FETCH_BOTH )['cCount'];

$tOpps = $connection->prepare($tSQL);
$tOpps->execute();
$tNumTotal = $tOpps->fetch( \PDO::FETCH_BOTH )['tCount'];

?>
               <input type="submit" name="Type" value="<?= _('Attending Members').' ['.$cNumAttend.']' ?>" class="btn btn-default">
             </form>
           </td>
           <td class="TextColumn">
             <form name="NonAttend" action="EventAttendance.php" method="POST">
               <input type="hidden" name="Event" value="<?= $aEventID[$row] ?>">
               <input type="hidden" name="Type" value="<?= $_GET['Type'] ?>">
               <input type="hidden" name="Action" value="Retrieve">
               <input type="hidden" name="Choice" value="Nonattendees">
<?php
?>
               <input type="submit" name="Type" value="<?= _('Non-Attending Members').' ['.($tNumTotal - $cNumAttend).']' ?>" class="btn btn-default">
             </form>
           </td>
           <td class="TextColumn">
             <form name="GuestAttend" action="EventAttendance.php" method="POST">
               <input type="hidden" name="Event" value="<?= $aEventID[$row] ?>">
               <input type="hidden" name="Type" value="<?= $_GET['Type'] ?>">
               <input type="hidden" name="Action" value="Retrieve">
               <input type="hidden" name="Choice" value="Guests">
<?php
$gSQL = 'SELECT COUNT(per_ID) AS gCount
         FROM person_per as t1, events_event as t2, event_attend as t3
         WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$aEventID[$row].' AND per_cls_ID = 3';

        $gOpps = $connection->prepare($gSQL);
        $gOpps->execute();
        $gNumGuestAttend = $gOpps->fetch( \PDO::FETCH_BOTH )['gCount']; ?>
               <input <?= ($gNumGuestAttend == 0 ? 'type="button"' : 'type="submit"') ?> name="Type" value="<?= _('Guests').' ['.$gNumGuestAttend.']' ?>" class="btn btn-default">
             </form>
           </td>
         </tr>
<?php
    } ?>
         <tr><td colspan="5">&nbsp;</td></tr>
<?php
} elseif ($_POST['Action'] == 'Retrieve' && $numRows > 0) {
        ?>
       <caption>
         <h3><?= _('There '.($numRows == 1 ? 'was '.$numRows.' '.$_POST['Choice'] : 'were '.$numRows.' '.$_POST['Choice'])).' for this Event' ?></h3>
       </caption>
         <tr class="TableHeader">
           <td width="35%"><strong><?= _('Name') ?></strong></td>
           <td width="25%"><strong><?= _('Email') ?></strong></td>
           <td width="25%"><strong><?= _('Home Phone') ?></strong></td>
           <td width="15%" nowrap><strong><?php /* echo _("Cart"); */ ?>&nbsp;</strong></td>
        </tr>
<?php
         //Set the initial row color
         $sRowClass = 'RowColorA';

        for ($row = 1; $row <= $numRows; $row++) {

         //Alternate the row color
            $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

            //Display the row
         ?>
         <tr class="<?= $sRowClass ?>">
           <td class="TextColumn"><?= OutputUtils::FormatFullName($aTitle[$row], $aFistName[$row], $aMiddleName[$row], $aLastName[$row], $aSuffix[$row], 3) ?></td>
           <td class="TextColumn"><?= $aEmail[$row] ? '<a href="mailto:'.$aEmail[$row].'" title="Send Email" target="_blank">'.$aEmail[$row].'</a>' : _('Not Available') ?></td>
           <td class="TextColumn"><?= $aHomePhone[$row] ? $aHomePhone[$row] : _('Not Available') ?></td>
<?php
// AddToCart call to go here
?>
           <td class="TextColumn"><?php /* echo '<a onclick="return AddToCart('.$aPersonID[$row].');" href="blank.html">'._("Add to Cart").'</a>'; */ ?>&nbsp;</td>
         </tr>
<?php
        }
    } else {
        ?>
       <caption>
         <h3><?= $_GET ? _('There are no events in this category') : _('There are no Records') ?><br><br></h3>
       </caption>
       <tr><td>&nbsp;</td></tr>
<?php
    }
?>
</table>

<?php require 'Include/Footer.php' ?>
