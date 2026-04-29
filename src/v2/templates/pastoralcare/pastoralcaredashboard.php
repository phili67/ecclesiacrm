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
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-body py-3 px-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between">
            <div class="pr-3">
                <h2 class="h4 mb-1"><i class="fas fa-hands-helping mr-2 text-primary"></i><?= _('Pastoral Care Dashboard') ?></h2>
                <div class="text-muted small mb-0"><?= _('Track visits, launch quick pastoral actions and focus first on members who still need attention.') ?></div>
            </div>
            <div class="mt-2 mt-md-0 text-md-right">
                <span class="badge badge-primary mr-1 mb-1"><i class="fas fa-calendar-alt mr-1"></i><?= $Stats['startPeriod'] . ' - ' . $Stats['endPeriod'] ?></span>
                <span class="badge badge-info mr-1 mb-1"><i class="fas fa-user mr-1"></i><?= $Stats['CountNotViewPersons'] . ' ' . _('Persons pending') ?></span>
                <span class="badge badge-warning mb-1"><i class="fas fa-home mr-1"></i><?= $Stats['CountNotViewFamilies'] . ' ' . _('Families pending') ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h3 class="card-title mb-1"><i class="fas fa-heart mr-1"></i><?= _("Visit/Call randomly") ?></h3>
            <div class="small text-muted"><?= _('Start a pastoral action in one click and quickly choose the profile type you want to reach.') ?></div>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="btn-group mr-2 mb-2" role="group">
                <button type="button" class="btn btn-sm btn-primary newPastorCare" data-typeid="2" data-toggle="tooltip" data-placement="bottom" title="<?= _("Pastoral care with a familly. You can validated all the persons together.") ?>">
                    <i class="fas fa-sticky-note mr-1"></i><?= _("Family") ?>
                </button>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Menu déroulant</span>
                </button>
                <div class="dropdown-menu dropdown-menu-left">
                    <a class="dropdown-item newPastorCare" href="#" data-typeid="1"><i class="fas fa-user mr-2"></i><?= _("Person") ?></a>
                    <a class="dropdown-item newPastorCare" href="#" data-typeid="2"><i class="fas fa-users mr-2"></i><?= _("Family") ?></a>
                    <a class="dropdown-item newPastorCare" href="#" data-typeid="3"><i class="fas fa-user-tie mr-2"></i><?= _("Retired") ?></a>
                    <a class="dropdown-item newPastorCare" href="#" data-typeid="4"><i class="fas fa-graduation-cap mr-2"></i><?= _("Young") ?></a>
                    <a class="dropdown-item newPastorCare" href="#" data-typeid="5"><i class="fas fa-user-circle mr-2"></i><?= _("Single") ?></a>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-warning mr-2 mb-2" id="add-event" data-toggle="tooltip" data-placement="bottom" title="<?= _("Create an appointment") ?>">
                <i class="far fa-calendar-plus mr-1"></i><?= _("Appointment") ?>
            </button>
            <?php if ( !(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) && SessionUser::getId() == $currentPastorId) { ?>
                <a href="<?= $sRootPath ?>/v2/pastoralcare/listforuser/<?= $currentPastorId ?>" class="btn btn-sm btn-success mb-2" data-toggle="tooltip" data-placement="bottom" title="<?= _("Pastoral care list of members for")." ".SessionUser::getUser()->getPerson()->getFullName() ?>">
                    <i class="fas fa-list mr-1"></i><?= _("Lists") ?>
                </a>
            <?php } ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="alert alert-light border mb-0">
                    <div class="font-weight-bold mb-1"><?= _('Quick guidance') ?></div>
                    <div class="small text-muted mb-0"><?= _('Use Family when you want to validate several people at once, or pick a specific profile from the dropdown for a more focused outreach.') ?></div>
                </div>
            </div>
            <?php if ( SessionUser::getUser()->isAdmin() ) { ?>
                <div class="col-12 mt-3 text-lg-right">
                    <a href="<?= $sRootPath ?>/v2/systemsettings/pastoralcare" class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" data-placement="bottom" title="<?= _("Pastoral care Settings.") ?>">
                        <i class="fas fa-cog mr-1"></i><?= _("Settings") ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <div class="info-box bg-gradient-<?= $Stats['personGradientColor'] ?> shadow-sm">
            <span class="info-box-icon"><i class="fas fa-user"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Persons") ?></span>
                <span class="info-box-number" id="sundaySchoolClassesDasBoard"><?= $Stats['CountNotViewPersons'] ?> / <?= $Stats['CountAllPersons'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <div class="info-box bg-gradient-<?= $Stats['familyGradientColor'] ?> shadow-sm">
            <span class="info-box-icon"><small><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i></small></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Families") ?></span>
                <span class="info-box-number" id="sundaySchoolTeachersCNTDasBoard"><?= $Stats['CountNotViewFamilies'] ?> / <?= $Stats['CountAllFamilies'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <div class="info-box bg-gradient-<?= $Stats['singleGradientColor'] ?> shadow-sm">
            <span class="info-box-icon"><i class="fas fa-user"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Single Persons") ?></span>
                <span class="info-box-number" id="sundaySchoolFamiliesCNTDasBoard"><?= $Stats['CountPersonSingle'] ?> / <?= $Stats['CountAllPersonSingle'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <div class="info-box bg-gradient-<?= $Stats['retiredGradientColor'] ?> shadow-sm">
            <span class="info-box-icon"><i class="fas fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Retired Persons") ?></span>
                <span class="info-box-number" id="sundaySchoolKidsCNTDasBoard"><?= $Stats['CountNotViewRetired'] ?> / <?= $Stats['CountAllRetired'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-lg col-md-4 col-sm-6 col-12">
        <div class="info-box bg-gradient-<?= $Stats['youngGradientColor'] ?> shadow-sm">
            <span class="info-box-icon"><i class="fas fa-child"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Youngs") ?></span>
                <span class="info-box-number" id="sundaySchoolMaleKidsCNTDasBoard"><?= $Stats['CountNotViewYoung'] ?> / <?= $Stats['CountAllYoung'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline h-100 shadow-sm">
            <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <div class="card-title mb-1"><i class="fas fa-chart-line mr-2"></i><?= _('Coverage Overview') ?></div>
                    <div class="small text-muted"><?= _("Period  from") . " : " . $Stats['startPeriod'] . " " . _("to") . " " . $Stats['endPeriod'] ?></div>
                </div>
                <span class="badge badge-light mt-2 mt-md-0"><i class="fas fa-clock mr-1"></i><?= _('Current period') ?></span>
            </div>
            <div class="card-body p-3">
                <div class="alert alert-light border py-2 px-3 small mb-3">
                    <i class="fas fa-info-circle text-info mr-1"></i><?= _("Statistics about persons, families and categories that still need to be contacted.") ?>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <div class="small text-nowrap" style="width:120px"><i class="fas fa-user fa-fw"></i> <?= _("Persons") ?></div>
                    <div class="flex-grow-1 mx-2">
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['personColor'] ?>" role="progressbar" style="width:<?= round($Stats['PercentNotViewPersons']) ?>%;" aria-valuenow="<?= $Stats['PercentNotViewPersons'] ?>" aria-valuemin="0" aria-valuemax="100"><?= round($Stats['PercentNotViewPersons']) ?>%</div>
                        </div>
                    </div>
                    <span class="badge bg-<?= $Stats['personColor'] ?>" style="min-width:28px"><?= $Stats['CountNotViewPersons'] ?></span>
                </div>
                <br>
                <div class="d-flex align-items-center mb-1">
                    <div class="small text-nowrap" style="width:120px"><small><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i></small> <?= _("Families") ?></div>
                    <div class="flex-grow-1 mx-2">
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['familyColor'] ?>" role="progressbar" style="width:<?= round($Stats['PercentViewFamilies']) ?>%;" aria-valuenow="<?= $Stats['PercentViewFamilies'] ?>" aria-valuemin="0" aria-valuemax="100"><?= round($Stats['PercentViewFamilies']) ?>%</div>
                        </div>
                    </div>
                    <span class="badge bg-<?= $Stats['familyColor'] ?>" style="min-width:28px"><?= $Stats['CountNotViewFamilies'] ?></span>
                </div>
                <br>
                <div class="d-flex align-items-center mb-1">
                    <div class="small text-nowrap" style="width:120px"><i class="fas fa-user fa-fw"></i> <?= _("Singles") ?></div>
                    <div class="flex-grow-1 mx-2">
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['singleColor'] ?>" role="progressbar" style="width:<?= round($Stats['PercentPersonSingle']) ?>%;" aria-valuenow="<?= $Stats['PercentPersonSingle'] ?>" aria-valuemin="0" aria-valuemax="100"><?= round($Stats['PercentPersonSingle']) ?>%</div>
                        </div>
                    </div>
                    <span class="badge bg-<?= $Stats['singleColor'] ?>" style="min-width:28px"><?= $Stats['CountPersonSingle'] ?></span>
                </div>
                <br>
                <div class="d-flex align-items-center mb-1">
                    <div class="small text-nowrap" style="width:120px"><i class="fas fa-user fa-fw"></i> <?= _("Retired") ?></div>
                    <div class="flex-grow-1 mx-2">
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['retiredColor'] ?>" role="progressbar" style="width:<?= round($Stats['PercentRetiredViewPersons']) ?>%;" aria-valuenow="<?= $Stats['PercentRetiredViewPersons'] ?>" aria-valuemin="0" aria-valuemax="100"><?= round($Stats['PercentRetiredViewPersons']) ?>%</div>
                        </div>
                    </div>
                    <span class="badge bg-<?= $Stats['retiredColor'] ?>" style="min-width:28px"><?= $Stats['CountNotViewRetired'] ?></span>
                </div>
                <br>
                <div class="d-flex align-items-center mb-0">
                    <div class="small text-nowrap" style="width:120px"><i class="fas fa-child fa-fw"></i> <?= _("Young People") ?></div>
                    <div class="flex-grow-1 mx-2">
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $Stats['youngColor'] ?>" role="progressbar" style="width:<?= round($Stats['PercentViewYoung']) ?>%;" aria-valuenow="<?= $Stats['PercentViewYoung'] ?>" aria-valuemin="0" aria-valuemax="100"><?= round($Stats['PercentViewYoung']) ?>%</div>
                        </div>
                    </div>
                    <span class="badge bg-<?= $Stats['youngColor'] ?>" style="min-width:28px"><?= $Stats['CountNotViewYoung'] ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php if (SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) { ?>
        <div class="col-md-6">
            <div class="card card-primary card-outline h-100 d-flex flex-column shadow-sm">
                <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <div class="card-title mb-1">
                            <i class="fas fa-people-arrows mr-2"></i> <?= _("Pastoral Care Members") ?>
                        </div>
                        <?php if (SystemConfig::getBooleanValue("bPastoralcareStats")) { ?>
                            <div class="small text-muted"><?= _("Period  from") . " : " . $Stats['startPeriod'] . " " . _("to") . " " . $Stats['endPeriod'] ?></div>
                        <?php } ?>
                    </div>
                    <span class="badge badge-info mt-2 mt-md-0"><i class="fas fa-user-check mr-1"></i><?= _('Follow-up list') ?></span>
                </div>
                <div class="card-body p-3 flex-grow-1" style="overflow-y:auto;">
                    <table class="dataTable table table-striped table-condensed" id="pastoralcareMembers" width="100%"></table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php if (SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) { ?>

<br>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning shadow-sm">
                <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <h3 class="card-title mb-1"><i class="fas fa-exclamation-circle mr-2"></i><?= _('Members Still Not Reached') ?></h3>
                        <div class="small text-muted"><?= _('Switch by category to focus your follow-up list and start the next pastoral action faster.') ?></div>
                    </div>
                    <span class="badge badge-warning mt-2 mt-md-0"><i class="fas fa-filter mr-1"></i><?= _('Segmented view') ?></span>
                </div>
                <div class="card-header border-0 pt-0">
                    <ul class="nav nav-tabs card-header-tabs" id="notReachedTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-persons" data-toggle="tab" href="#pane-persons" role="tab" aria-controls="pane-persons" aria-selected="true">
                                <i class="fas fa-user"></i> <?= _("Persons") ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-families" data-toggle="tab" href="#pane-families" role="tab" aria-controls="pane-families" aria-selected="false">
                                <small><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i></small> <?= _("Families") ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-singles" data-toggle="tab" href="#pane-singles" role="tab" aria-controls="pane-singles" aria-selected="false">
                                <i class="fas fa-user"></i> <?= _("Singles") ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-retired" data-toggle="tab" href="#pane-retired" role="tab" aria-controls="pane-retired" aria-selected="false">
                                <i class="fas fa-user"></i> <?= _("Retired") ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-young" data-toggle="tab" href="#pane-young" role="tab" aria-controls="pane-young" aria-selected="false">
                                <i class="fas fa-child"></i> <?= _("Young People") ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body pt-2">
                    <div class="tab-content" id="notReachedTabsContent">
                        <div class="tab-pane fade show active" id="pane-persons" role="tabpanel" aria-labelledby="tab-persons">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 mb-2">
                                <h6 class="mb-0 text-muted"><?= _("Persons not reached") ?></h6>
                                <span class="badge badge-light"><?= $Stats['CountNotViewPersons'] . ' ' . _('remaining') ?></span>
                            </div>
                            <table class="dataTable table table-striped table-condensed" id="personNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-families" role="tabpanel" aria-labelledby="tab-families">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 mb-2">
                                <h6 class="mb-0 text-muted"><?= _("Families not reached") ?></h6>
                                <span class="badge badge-light"><?= $Stats['CountNotViewFamilies'] . ' ' . _('remaining') ?></span>
                            </div>
                            <table class="dataTable table table-striped table-condensed" id="familyNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-singles" role="tabpanel" aria-labelledby="tab-singles">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 mb-2">
                                <h6 class="mb-0 text-muted"><?= _("Single Persons not reached") ?></h6>
                                <span class="badge badge-light"><?= $Stats['CountPersonSingle'] . ' ' . _('remaining') ?></span>
                            </div>
                            <table class="dataTable table table-striped table-condensed" id="singleNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-retired" role="tabpanel" aria-labelledby="tab-retired">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 mb-2">
                                <h6 class="mb-0 text-muted"><?= _("Retired not reached") ?></h6>
                                <span class="badge badge-light"><?= $Stats['CountNotViewRetired'] . ' ' . _('remaining') ?></span>
                            </div>
                            <table class="dataTable table table-striped table-condensed" id="retiredNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-young" role="tabpanel" aria-labelledby="tab-young">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 mb-2">
                                <h6 class="mb-0 text-muted"><?= _("Young People not reached") ?></h6>
                                <span class="badge badge-light"><?= $Stats['CountNotViewYoung'] . ' ' . _('remaining') ?></span>
                            </div>
                            <table class="dataTable table table-striped table-condensed" id="youngNeverBeenContacted" width="100%"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $sCSPNonce ?>">
    var sPageTitle = '<?= $sPageTitle ?>';

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
    };
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
}
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
