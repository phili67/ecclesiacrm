<?php
/*******************************************************************************
 *
 *  filename    : EditEventAttendees.php
 *  last change : 2018-01-08
 *  description : Edit Event Attendees
 *
 *  http://www.ecclesiacrm.com/
 *        copyright 2018 Philippe Logel all right reserved
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;

$sPageTitle = _('Event Attendees');
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
} else if (isset($_SESSION['Action'])) {
    $sAction = $_SESSION['Action'];
    $EventID = $_SESSION['EID'];
    $EvtName = $_SESSION['EName'];
    $EvtDesc = $_SESSION['EDesc'];
    $EvtDate = $_SESSION['EDate'];
}

// Construct the form
?>

<div class="card">
    <div class='card-header'>
        <h3 class='card-title'><?= _('Event ID:') . ' ' . $EventID ?></h3>
    </div>
    <div class="card-body">
        <p style="margin-left:10px">
            <strong><?= _('Name') ?>:</strong> <?= $EvtName ?><br/>
            <strong><?= _('Date') ?>:</strong> <?= OutputUtils::FormatDate($EvtDate, 1) ?><br/>
            <strong><?= _('Description') ?>:</strong> <?= $EvtDesc ?><br/>
        </p>
    </div>
</div>

<div class='card'>
    <div class="card-header">
        <div class="card-title">
            <?= _("Attendees") ?>
        </div>
    </div>
    <div class='card-body'>

        <input type="hidden" name="EID" value="<?= $EventID ?>">

        <table class="table table-striped table-bordered data-table  dataTable no-footer dtr-inline"
               id="eventsTable" style="width:100%">
            <thead>
            <tr class="TableHeader">
                <th width="35%"><strong><?= _('Name') ?></strong>
                </td>
                <th width="25%"><strong><?= _('Email') ?></strong>
                </td>
                <th width="25%"><strong><?= _('Home Phone') ?></strong>
                </td>
                <th width="15%" nowrap><strong><?= _('Action') ?></strong>
                </td>
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

                    if ($per_fam) {
                        $fam = $per_fam->getFamily();
                    }

                    $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), (!empty($fam)) ? $fam->getCountry() : "", false);
                    $sHomePhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy), MiscUtils::ExpandPhoneNumber((!empty($fam)) ? $fam->getHomePhone() : "", (!empty($fam)) ? $fam->getCountry() : "", $dummy), true);
                    $sEmail = MiscUtils::SelectWhichInfo($person->getEmail(), (!empty($fam)) ? $fam->getEmail() : "", false); ?>
                    <tr>
                        <td class="TextColumn"><?= OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) ?></td>
                        <td class="TextColumn"><?= $sEmail ? '<a href="mailto:' . $sEmail . '" title="Send Email">' . $sEmail . '</a>' : _('Not Available') ?></td>
                        <td class="TextColumn"><?= $sHomePhone ? '<a href="tel:' . $sHomePhone . '" title="Phone to">' . $sHomePhone . '</a>' : _('Not Available') ?></td>
                        <td colspan="1" align="center">
                            <a class="btn btn-danger DeleleAttendees" data-personid="<?= $person->getId() ?>"
                               data-eventid="<?= $EventID ?>"> <?= _("Delete") ?></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4" align="center"><?= _('No Attendees Assigned to Event') ?></td>
                </tr>
                <?php
            }

            ?>
            <tbody>
        </table>

    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-6">
                <form action="#" method="get" class="sidebar-form">
                    <label for="addPersonMember"><?= _('Add Event Member') ?> :</label>
                    <select class="form-control personGroupSearch" name="addPersonGroupSearch" style="width:100%">
                    </select>
                </form>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-3">
                <a id="DeleleAllAttendees" class="btn btn-danger <?= ($numAttRows == 0) ? "disabled" : "" ?>"
                   data-eventid="<?= $EventID ?>"><?= _("Delele All Attendees") ?></a>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-3">
                <?php if ($numAttRows - $countCheckout > 0) { ?>
                <form action="<?= SystemURLs::getRootPath() ?>/Checkin.php" method="POST">
                    <input type="hidden" name="EventID" value="<?= $EventID ?>">
                    <?php } ?>
                    <button type="submit" name="Action" title="<?= _('Make Check-out') ?>"
                            data-tooltip <?= ($numAttRows - $countCheckout > 0) ? 'value="' . _('Make Check-out') . '"' : "" ?>
                            class="btn btn-<?= ($numAttRows - $countCheckout == 0) ? "default disabled" : "success" ?>">
                        <i class='fa fa-check-circle'></i>
                        <?= _('Make Check-out') ?>
                    </button>
                    <?php if ($numAttRows - $countCheckout > 0) { ?>
                </form>
            <?php } ?>
            </div>
        </div>
    </div>
</div>

<div>
    <a href="ListEvents.php" class='btn btn-default'>
        <i class='fa fa-chevron-left'></i>
        <?= _('Return to Events') ?>
    </a>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
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

<script src="<?= SystemURLs::getRootPath(); ?>/skin/js/event/EditEventAttendees.js"></script>

<?php require 'Include/Footer.php' ?>
