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
use EcclesiaCRM\dto\Cart;
 
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
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <a class="btn btn-outline-secondary" href="<?= $sRootPath ?>/v2/system/report/list">
                <i class="fas fa-arrow-left mr-1"></i><?= _('Back to Report Menu') ?>
        </a>
    <span class="badge badge-secondary mt-2 mt-sm-0"><?= $sPageTitle ?></span>
</div>
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
    <div class="card-header border-1 d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-list-ul mr-1"></i><?= _("Results") ?></h3>
        <span class="badge badge-primary"><?= $numRows ?> <?= _("record(s)") ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table id="tableEventAttendance" class="table table-bordered table-striped table-hover mb-0">

        <?php
        if ($Action == 'List' && $numRows > 0) {
            ?>
            <caption>
                    <h5 class="mb-3 text-left"><?= ($numRows == 1 ? _('There is') : _('There are')).' '.$numRows.' '.($numRows == 1 ? _('event') : _('events'))._(' in this category.') ?></h5>
            </caption>
            <thead>
                <tr class="print-table-header">
                    <th class="align-middle"><strong><?= _('Event Title') ?></strong></th>
                    <th class="align-middle text-nowrap"><strong><?= _('Event Date') ?></strong></th>
                    <th colspan="3" class="align-middle text-center"><strong><?= _('Generate Report') ?></strong></th>
                </tr>
            </thead>
            <tbody>
                <?php
            for ($row = 1; $row <= $numRows; $row++) {
                //Display the row?>
                <tr>
                <td class="TextColumn"><?= $aEventTitle[$row] ?></td>
                <td class="TextColumn text-nowrap"><?= OutputUtils::FormatDate($aEventStartDateTime[$row], 1) ?></td>
                <td class="TextColumn text-center">
                    <form class="mb-0" name="Attend" action="<?= $sRootPath ?>/v2/system/event/attendance/Retrieve/<?= $aEventID[$row] ?>/<?= $Type ?>/Attendees" method="POST">
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
                    <input type="submit" name="Type" value="<?= _('Attending Members').' ['.$cNumAttend.']' ?>" class="btn btn-outline-primary btn-sm btn-block text-wrap">
                    </form>
                </td>
                <td class="TextColumn text-center">
                    <form class="mb-0" name="NonAttend" action="<?= $sRootPath ?>/v2/system/event/attendance/Retrieve/<?= $aEventID[$row] ?>/<?= $Type ?>/Nonattendees" method="POST">
        <?php
        ?>
                    <input type="submit" name="Type" value="<?= _('Non-Attending Members').' ['.($tNumTotal - $cNumAttend).']' ?>" class="btn btn-outline-secondary btn-sm btn-block text-wrap">
                    </form>
                </td>
                <td class="TextColumn text-center">
                    <form class="mb-0" name="GuestAttend" action="<?= $sRootPath ?>/v2/system/event/attendance/Retrieve/<?= $aEventID[$row] ?>/<?= $Type ?>/Guests" method="POST">
        <?php
        $gSQL = 'SELECT COUNT(per_ID) AS gCount
                FROM person_per as t1, events_event as t2, event_attend as t3
                WHERE t1.per_ID = t3.person_id AND t2.event_id = t3.event_id AND t3.event_id = '.$aEventID[$row].' AND per_cls_ID = 3 AND  t1.per_DateDeactivated IS NULL';

                $gOpps = $connection->prepare($gSQL);
                $gOpps->execute();
                $gNumGuestAttend = $gOpps->fetch( \PDO::FETCH_BOTH )['gCount']; ?>
                    <input <?= ($gNumGuestAttend == 0 ? 'type="button"' : 'type="submit"') ?> name="Type" value="<?= _('Guests').' ['.$gNumGuestAttend.']' ?>" class="btn btn-outline-info btn-sm btn-block text-wrap">
                    </form>
                </td>
                </tr>
        <?php
            } ?>
                <tr><td colspan="5" class="table-secondary">&nbsp;</td></tr>
        <?php
        } elseif ($Action == 'Retrieve' && $numRows > 0) {
                ?>
            <caption>
                <h5 class="mb-3 text-left"><?= _('There '.($numRows == 1 ? 'was '.$numRows.' '.$Choice : 'were '.$numRows.' '.$Choice)).' for this Event' ?></h5>
            </caption>
            <thead>
                <tr class="print-table-header">
                    <th><strong><?= _('Name') ?></strong></th>
                    <th><strong><?= _('Email') ?></strong></th>
                    <th><strong><?= _('Home Phone') ?></strong></th>
                    <th class="text-nowrap"><strong><?= _("Cart") ?>&nbsp;</strong></th>
                </tr>
            </thead>
            <tbody>
        <?php
                for ($row = 1; $row <= $numRows; $row++) {
                    //Display the row
                ?>
                <tr>
                <td class="TextColumn"><?= OutputUtils::FormatFullName($aTitle[$row], $aFistName[$row], $aMiddleName[$row], $aLastName[$row], $aSuffix[$row], 3) ?></td>
                <td class="TextColumn"><?= $aEmail[$row] ? '<a href="mailto:'.$aEmail[$row].'" title="Send Email" target="_blank">'.$aEmail[$row].'</a>' : _('Not Available') ?></td>
                <td class="TextColumn"><?= $aHomePhone[$row] ? $aHomePhone[$row] : _('Not Available') ?></td>
        <?php
        // AddToCart call to go here
        ?>
                <td class="TextColumn">
                    <?php
                        if (!Cart::PersonInCart($aPersonID[$row])) {
                            ?>
                                <a class="AddToPeopleCart" data-cartpersonid="<?= $aPersonID[$row] ?>">
                                     <span class="fa-stack">
                                     <i class="fas fa-square fa-stack-2x"></i>
                                     <i class="fas fa-cart-plus fa-stack-1x fa-inverse"></i>
                                     </span>
                                </a>
                            <?php
                        } else {
                            ?>
                                <a class="RemoveFromPeopleCart" data-cartpersonid="<?= $aPersonID[$row] ?>">
                                    <span class="fa-stack">
                                    <i class="fas fa-square fa-stack-2x"></i>
                                    <i class="fas fa-times fa-stack-1x fa-inverse"></i>
                                    </span>
                                </a>
                            <?php
                        }
                    ?>
                </td>
                </tr>
        <?php
                }
            } else {
                ?>
            <caption>
                <h5 class="text-left"><?= $_GET ? _('There are no events in this category') : _('There are no Records') ?></h5>
            </caption>
            <tr><td class="table-secondary">&nbsp;</td></tr>
        <?php
            }
        ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/people/AddRemoveCart.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
