<?php

/*******************************************************************************
 *
 *  filename    : templates/Calendar.php
 *  last change : 2019-02-5
 *  description : manage the full Calendar
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\PersonQuery;

use EcclesiaCRM\Utils\MiscUtils;

require $sRootDocument . '/Include/Header.php';

?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class='card-header py-2'>
        <h3 class='card-title mb-0'><i class="fas fa-calendar-day mr-1"></i><?= _('Event') ?> #<?= $EventID ?></h3>
    </div>
    <div class="card-body">
        <p class="mb-0">
            <strong><?= _('Date') ?>:</strong> <?= $EvtDate ?><br/>
            <strong><?= _('Description') ?>:</strong> <?= $EvtDesc ?><br/>
        </p>
    </div>
</div>

<div class='card card-outline card-info shadow-sm'>
    <div class="card-header py-2">
        <h3 class="card-title mb-0"><i class="fas fa-users mr-1"></i><?= _("Attendees") ?></h3>
    </div>
    <div class='card-body p-1'>

        <input type="hidden" name="EID" value="<?= $EventID ?>">

        <table class="table table-striped table-hover table-sm mb-0 data-table dataTable no-footer dtr-inline"
               id="eventsTable" style="width:100%">
            <thead class="thead-light">
            <tr class="TableHeader">
                <th width="35%"><strong><?= _('Name') ?></strong></th>
                <th width="25%"><strong><?= _('Email') ?></strong></th>
                <th width="25%"><strong><?= _('Home Phone') ?></strong></th>
                <th width="15%" nowrap><strong><?= _('Action') ?></strong></th>
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
                    $sEmail = MiscUtils::SelectWhichInfo($person->getEmail(), (!empty($fam)) ? $fam->getEmail() : "", false);
                    ?>
                    <tr>
                        <td class="TextColumn"><?= OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) ?></td>
                        <td class="TextColumn"><?= $sEmail ? '<a href="mailto:' . $sEmail . '" title="Send Email" target="_blank">' . $sEmail . '</a>' : _('Not Available') ?></td>
                        <td class="TextColumn"><?= $sHomePhone ? '<a href="tel:' . $sHomePhone . '" title="Phone to">' . $sHomePhone . '</a>' : _('Not Available') ?></td>
                        <td align="center">
                            <a class="btn btn-sm btn-danger DeleleAttendees" data-personid="<?= $person->getId() ?>"
                               data-eventid="<?= $EventID ?>"><i class="fas fa-trash-alt mr-1"></i><?= _("Delete") ?></a>
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
            </tbody>
        </table>

    </div>
    <div class="card-footer">
        <div class="pb-3 mb-3 border-bottom">
            <form action="#" method="get" class="mb-0">
                <label for="personGroupSearch" class="mb-2"><strong><?= _('Add Event Member') ?></strong></label>
                <select class="form-control form-control-sm personGroupSearch" id="personGroupSearch" name="addPersonGroupSearch" style="width:100%">
                </select>
            </form>
        </div>

        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
            <div>
                <a id="DeleleAllAttendees" class="btn btn-sm btn-danger <?= ($numAttRows == 0) ? "disabled" : "" ?>"
                   data-eventid="<?= $EventID ?>"><i class="fas fa-trash mr-1"></i><?= _("Delele All Attendees") ?></a>
            </div>
            <div>
                <form action="<?= SystemURLs::getRootPath() ?>/v2/calendar/events/checkin" method="POST" class="mb-0">
                    <input type="hidden" name="EventID" value="<?= $EventID ?>">
                    <button type="submit" name="Action" title="<?= _('Make Check-out') ?>"
                            data-tooltip <?= ($numAttRows - $countCheckout > 0) ? 'value="' . _('Make Check-out') . '"' : "" ?>
                            class="btn btn-sm btn-<?= ($numAttRows - $countCheckout == 0) ? "outline-secondary disabled" : "success" ?>">
                        <i class='fas fa-check-circle mr-1'></i>
                        <?= _('Make Check-out') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 pt-3 border-top">
    <a href="<?= SystemURLs::getRootPath() ?>/v2/calendar/events/list" class='btn btn-outline-secondary btn-sm'>
        <i class='fas fa-arrow-left mr-1'></i>
        <?= _('Return to Events') ?>
    </a>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    //Added by @saulowulhynek to translation of datatable nav terms
    window.CRM.currentEvent = <?= $EventID ?>;

    $(function() {
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

<?php require $sRootDocument . '/Include/Footer.php'; ?>

