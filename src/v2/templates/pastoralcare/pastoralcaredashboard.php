<?php
/*******************************************************************************
 *
 *  filename    : pastoralcaredashboard.php
 *  last change : 2020-06-20
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-primary card-body">
    <div class="margin">
        <label><?= _("Visit/Call randomly") ?></label>
        <div class="btn-group">
            <a class="btn btn-app newPastorCare" data-typeid="2" data-toggle="tooltip"  data-placement="bottom" title="<?= _("Pastoral care with a familly. You can validated all the persons together.") ?>"><i
                    class="fas fa-sticky-note"></i><?= _("Family") ?></a>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Menu déroulant</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <a class="dropdown-item newPastorCare" data-typeid="1"><?= _("Person") ?></a>
                <a class="dropdown-item newPastorCare" data-typeid="2"><?= _("Family") ?></a>
                <a class="dropdown-item newPastorCare" data-typeid="3"><?= _("Retired") ?></a>
                <a class="dropdown-item newPastorCare" data-typeid="4"><?= _("Young") ?></a>
                <a class="dropdown-item newPastorCare" data-typeid="5"><?= _("Single") ?></a>
            </div>
            &nbsp;
            &nbsp;
            <a class="btn btn-app bg-orange" id="add-event"><i class="far fa-calendar-plus"></i><?= _("Appointment") ?></a>
            &nbsp;
            &nbsp;
            <?php if ( !(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) && SessionUser::getId() == $currentPastorId) { ?>
                <a href="<?= $sRootPath ?>/v2/pastoralcare/listforuser/<?= $currentPastorId ?>"
                   class="btn btn-app bg-success"
                   data-toggle="tooltip" data-placement="bottom"
                   title="<?= _("Pastoral care list of members for")." ".SessionUser::getUser()->getPerson()->getFullName() ?>"><i class="fas fa-list"></i><?= _("Lists") ?></a>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div
                    class="card-title"><?= _("Period  from") . " : " . $Stats['startPeriod'] . " " . _("to") . " " . $Stats['endPeriod'] ?></div>
            </div>
            <div class="card-body">
                <div class="alert alert-default-info"><?= _("• Statistics about persons, families ... who remain to be contacted.") ?></div>
                <table class="table table-condensed">
                    <tr>
                        <th><?= _('Members') ?></th>
                        <th>% <?= _('of members').' '._('to contact') ?></th>
                        <th style="width: 40px"><?= _('Count') ?></th>
                    </tr>
                    <tr>
                        <td>
                            <?= _("Persons") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['personColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentNotViewPersons']) ?>%;" aria-valuenow="<?= $Stats['PercentNotViewPersons'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentNotViewPersons'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['personColor'] ?>"><?= $Stats['CountNotViewPersons'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= _("Families") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['familyColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentViewFamilies']) ?>%;" aria-valuenow="<?= $Stats['PercentViewFamilies'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentViewFamilies'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['familyColor'] ?>"><?= $Stats['CountNotViewFamilies'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= _("Singles") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['singleColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentPersonSingle']) ?>%;" aria-valuenow="<?= $Stats['PercentPersonSingle'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentPersonSingle'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['singleColor'] ?>"><?= $Stats['CountPersonSingle'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= _("Retired") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['retiredColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentRetiredViewPersons']) ?>%;" aria-valuenow="<?= $Stats['PercentRetiredViewPersons'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentRetiredViewPersons'] ?>%</div>
                            </div>
                        </td>
                        <td><span
                                class="badge bg-<?= $Stats['retiredColor'] ?>"><?= $Stats['CountNotViewRetired'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= _("Young People") ?>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['youngColor'] ?>" role="progressbar" style="width: <?= round($Stats['PercentViewYoung']) ?>%;" aria-valuenow="<?= $Stats['PercentViewYoung'] ?>" aria-valuemin="0" aria-valuemax="100"><?= $Stats['PercentViewYoung'] ?>%</div>
                            </div>
                        </td>
                        <td><span class="badge bg-<?= $Stats['youngColor'] ?>"><?= $Stats['CountNotViewYoung'] ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php if (SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) { ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= _("Pastoral Care Members") ?>
                        <?php if (SystemConfig::getBooleanValue("bPastoralcareStats")) { ?>
                            (<?= _("Period  from") . " : " . $Stats['startPeriod'] . " " . _("to") . " " . $Stats['endPeriod'] ?>)
                        <?php } ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class=" dataTable table table-striped table-condensed" id="pastoralcareMembers"
                           width="100%"></table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php if (SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) { ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= _("Persons not reached") ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class=" dataTable table table-striped table-condensed" id="personNeverBeenContacted"
                           width="100%"></table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= _("Families not reached") ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class=" dataTable table table-striped table-condensed" id="familyNeverBeenContacted"
                           width="100%"></table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= _("Single Persons not reached") ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class=" dataTable table table-striped table-condensed" id="singleNeverBeenContacted"
                           width="100%"></table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= _("Retired not reached") ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class=" dataTable table table-striped table-condensed" id="retiredNeverBeenContacted"
                           width="100%"></table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= _("Young People not reached") ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class=" dataTable table table-striped table-condensed" id="youngNeverBeenContacted"
                           width="100%"></table>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $sCSPNonce ?>">
    var sPageTitle = '<?= $sPageTitle ?>';

    window.CRM.churchloc = {
        lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCareDashboard.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>

<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
    ?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>
