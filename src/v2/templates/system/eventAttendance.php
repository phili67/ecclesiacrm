<?php

/*******************************************************************************
 *
 *  filename    : templates/eventAttendance.php
 *  last change : 2023-06-26
 *  website     : http://www.ecclesiacrm.com
 *                © 2023 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
 
use Propel\Runtime\Propel;


$connection = Propel::getConnection();

if ($Action == 'Retrieve' && $Event != -1) {
    if ($Choice == 'Attendees') {
        $sSQL = 'SELECT t1.per_ID, t1.per_Title, t1.per_FirstName, t1.per_MiddleName, t1.per_LastName, t1.per_Suffix, t1.per_Email, t1.per_HomePhone, t1.per_Country, t1.per_MembershipDate, t4.fam_HomePhone, t4.fam_Country
                FROM person_per AS t1, events_event AS t2, event_attend AS t3, family_fam AS t4
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$Event." AND t1.per_fam_ID = t4.fam_ID AND per_cls_ID IN ('1','2','5') AND  t1.per_DateDeactivated IS NULL
                ORDER BY t1.per_LastName, t1.per_ID";
        $sPageTitle = _('Event Attendees');
    } elseif ($Choice == 'Nonattendees') {
        $aSQL = 'SELECT DISTINCT(person_id) FROM event_attend WHERE event_id = '.$Event;

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
                        WHERE t1.per_fam_ID = t2.fam_ID AND per_cls_ID IN ('1','2','5') AND  t1.per_DateDeactivated IS NULL
                        ORDER BY t1.per_LastName, t1.per_ID";
        }
        $sPageTitle = _('Event Nonattendees');
    } elseif ($Choice == 'Guests') {
        $sSQL = 'SELECT t1.per_ID, t1.per_Title, t1.per_FirstName, t1.per_MiddleName, t1.per_LastName, t1.per_Suffix, t1.per_HomePhone, t1.per_Country
                FROM person_per AS t1, events_event AS t2, event_attend AS t3
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$Event." AND per_cls_ID IN ('0','3') AND  t1.per_DateDeactivated IS NULL
                ORDER BY t1.per_LastName, t1.per_ID";
        $sPageTitle = _('Event Guests');
    }
} elseif ($Action == 'List' && $Event != -1) {
    $sSQL = 'SELECT * FROM events_event WHERE event_type = '.$Event.' ORDER BY event_start';

    //I change textt from All $Type Events to All Events of type . $Type, because it don´t work for protuguese, spanish, french and so on
    $sPageTitle = _('All Events of Type').': '.$Type;
}

require $sRootDocument . '/Include/Header.php';
?>
<table cellpadding="4" cellspacing="0" width="100%">
  <tr>
    <td>
        <div class="text-center">
            <input type="button" class="btn btn-primary" value="&#xab; <?= _('Back to Report Menu') ?>" Name="Exit" onclick="javascript:document.location='/v2/system/report/list';">
        </div>    
    </td>
  </tr>
</table>
<br/>
<?php
// Get data for the form as it now exists..
$statement = $connection->prepare($sSQL);
$statement->execute();

$numRows = $statement->rowCount();

// Create arrays of the attendees.
$row = 1;
while ($aRow = $statement->fetch( \PDO::FETCH_ASSOC )) {
    extract($aRow);

    if ($Action == 'List') {
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

<div class="card card-default">
    <div class="card-header">
        <h2 class="card-title"?><?= _("Results") ?></h2>
    </div>
    <div class="card-body">
        <table id="tableEventAttendance" cellpadding="4" cellspacing="0" width="60%" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline">

        <?php
        if ($Action == 'List' && $numRows > 0) {
            ?>
            <caption>
                    <h3><?= ($numRows == 1 ? _('There is') : _('There are')).' '.$numRows.' '.($numRows == 1 ? _('event') : _('events'))._(' in this category.') ?></h3>
            </caption>
                <tr class="TableHeader">
                <td width="33%"><strong><?= _('Event Title') ?></strong></td>
                <td width="33%"><strong><?= _('Event Date') ?></strong></td>
                <td colspan="3" width="34%"><strong><?= _('Generate Report') ?></strong></td>
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
                <td class="TextColumn">
                    <form name="Attend" action="<?= $sRootPath ?>/v2/system/event/attendance/Retrieve/<?= $aEventID[$row] ?>/<?= $Type ?>/Attendees" method="POST">
        <?php
        $cSQL = 'SELECT COUNT(per_ID) AS cCount
                FROM person_per as t1, events_event as t2, event_attend as t3
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$aEventID[$row]." AND per_cls_ID IN ('1','2','5') AND  t1.per_DateDeactivated IS NULL";

        $tSQL = "SELECT COUNT(per_ID) AS tCount
                FROM person_per
                WHERE per_cls_ID IN ('1','2','5') AND  per_DateDeactivated IS NULL";

        $cOpps = $connection->prepare($cSQL);
        $cOpps->execute();
        $cNumAttend = $cOpps->fetch( \PDO::FETCH_BOTH )['cCount'];

        $tOpps = $connection->prepare($tSQL);
        $tOpps->execute();
        $tNumTotal = $tOpps->fetch( \PDO::FETCH_BOTH )['tCount'];

        ?>
                    <input type="submit" name="Type" value="<?= _('Attending Members').' ['.$cNumAttend.']' ?>" class="btn btn-secondary">
                    </form>
                </td>
                <td class="TextColumn">
                    <form name="NonAttend" action="<?= $sRootPath ?>/v2/system/event/attendance/Retrieve/<?= $aEventID[$row] ?>/<?= $Type ?>/Nonattendees" method="POST">
        <?php
        ?>
                    <input type="submit" name="Type" value="<?= _('Non-Attending Members').' ['.($tNumTotal - $cNumAttend).']' ?>" class="btn btn-secondary">
                    </form>
                </td>
                <td class="TextColumn">
                    <form name="GuestAttend" action="<?= $sRootPath ?>/v2/system/event/attendance/Retrieve/<?= $aEventID[$row] ?>/<?= $Type ?>/Guests" method="POST">
        <?php
        $gSQL = 'SELECT COUNT(per_ID) AS gCount
                FROM person_per as t1, events_event as t2, event_attend as t3
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$aEventID[$row].' AND per_cls_ID = 3 AND  t1.per_DateDeactivated IS NULL';

                $gOpps = $connection->prepare($gSQL);
                $gOpps->execute();
                $gNumGuestAttend = $gOpps->fetch( \PDO::FETCH_BOTH )['gCount']; ?>
                    <input <?= ($gNumGuestAttend == 0 ? 'type="button"' : 'type="submit"') ?> name="Type" value="<?= _('Guests').' ['.$gNumGuestAttend.']' ?>" class="btn btn-secondary">
                    </form>
                </td>
                </tr>
        <?php
            } ?>
                <tr><td colspan="5">&nbsp;</td></tr>
        <?php
        } elseif ($Action == 'Retrieve' && $numRows > 0) {
                ?>
            <caption>
                <h3><?= _('There '.($numRows == 1 ? 'was '.$numRows.' '.$Choice : 'were '.$numRows.' '.$Choice)).' for this Event' ?></h3>
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
                <h5><?= $_GET ? _('There are no events in this category') : _('There are no Records') ?><br><br></h3>
            </caption>
            <tr><td>&nbsp;</td></tr>
        <?php
            }
        ?>
        </table> 
    </div>
</div>

<script nonce="<?= $CSPNonce ?>">
    $(document).ready(function () {
        //Added by @saulowulhynek to translation of datatable nav terms
        //$('#eventNames').DataTable(window.CRM.plugin.dataTable);
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
