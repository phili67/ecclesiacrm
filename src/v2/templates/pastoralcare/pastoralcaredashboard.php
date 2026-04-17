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
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-heart mr-1"></i><?= _("Visit/Call randomly") ?></h3>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group" role="group">
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
            <button type="button" class="btn btn-sm btn-warning" id="add-event" data-toggle="tooltip" data-placement="bottom" title="<?= _("Create an appointment") ?>">
                <i class="far fa-calendar-plus mr-1"></i><?= _("Appointment") ?>
            </button>
            <?php if ( !(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) && SessionUser::getId() == $currentPastorId) { ?>
                <a href="<?= $sRootPath ?>/v2/pastoralcare/listforuser/<?= $currentPastorId ?>" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="bottom" title="<?= _("Pastoral care list of members for")." ".SessionUser::getUser()->getPerson()->getFullName() ?>">
                    <i class="fas fa-list mr-1"></i><?= _("Lists") ?>
                </a>
            <?php } ?>
            <?php if ( SessionUser::getUser()->isAdmin() ) { ?>
                <a href="<?= $sRootPath ?>/v2/systemsettings/pastoralcare" class="btn btn-sm btn-outline-secondary ml-auto" data-toggle="tooltip" data-placement="bottom" title="<?= _("Pastoral care Settings.") ?>">
                    <i class="fas fa-cog mr-1"></i><?= _("Settings") ?>
                </a>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-2 col-sm-6 col-xs-12">
        <div class="info-box bg-gradient-<?= $Stats['personGradientColor'] ?>">
            <span class="info-box-icon"><i class="fas fa-user"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Persons") ?></span>
                <span class="info-box-number" id="sundaySchoolClassesDasBoard"><?= $Stats['CountNotViewPersons'] ?> / <?= $Stats['CountAllPersons'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <div class="info-box bg-gradient-<?= $Stats['familyGradientColor'] ?>">
            <span class="info-box-icon"><small><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i></small></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Families") ?></span>
                <span class="info-box-number" id="sundaySchoolTeachersCNTDasBoard"><?= $Stats['CountNotViewFamilies'] ?> / <?= $Stats['CountAllFamilies'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <div class="info-box bg-gradient-<?= $Stats['singleGradientColor'] ?>">
            <span class="info-box-icon"><i class="fas fa-user"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Single Persons") ?></span>
                <span class="info-box-number" id="sundaySchoolFamiliesCNTDasBoard"><?= $Stats['CountPersonSingle'] ?> / <?= $Stats['CountAllPersonSingle'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <div class="info-box bg-gradient-<?= $Stats['retiredGradientColor'] ?>">
            <span class="info-box-icon"><i class="fas fa-user"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= _("Not visited Retired Persons") ?></span>
                <span class="info-box-number" id="sundaySchoolKidsCNTDasBoard"><?= $Stats['CountNotViewRetired'] ?> / <?= $Stats['CountAllRetired'] ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
        <div class="info-box bg-gradient-<?= $Stats['youngGradientColor'] ?>">
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
        <div class="card card-primary card-outline h-100">
            <div class="card-header border-1 py-2">
                <div class="card-title small"><i class="fa-solid fa-clock"></i> <?= _("Period  from") . " : " . $Stats['startPeriod'] . " " . _("to") . " " . $Stats['endPeriod'] ?></div>
            </div>
            <div class="card-body p-2">
                <p class="text-info small mb-2"><i class="fas fa-info-circle"></i> <?= _("• Statistics about persons, families ... who remain to be contacted.") ?></p>
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
            <div class="card card-primary card-outline h-100 d-flex flex-column">
                <div class="card-header border-1 py-2">
                    <div class="card-title small">
                        <i class="fa-solid fa-people-group"></i> <?= _("Pastoral Care Members") ?>
                        <?php if (SystemConfig::getBooleanValue("bPastoralcareStats")) { ?>
                            <span class="text-muted">(<?= _("Period  from") . " : " . $Stats['startPeriod'] . " " . _("to") . " " . $Stats['endPeriod'] ?>)</span>
                        <?php } ?>
                    </div>
                </div>
                <div class="card-body p-2 flex-grow-1" style="overflow-y:auto;">
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
            <div class="card card-warning">
                <div class="card-header border-1">
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
                <div class="card-body">
                    <div class="tab-content" id="notReachedTabsContent">
                        <div class="tab-pane fade show active" id="pane-persons" role="tabpanel" aria-labelledby="tab-persons">
                            <h6 class="mt-2 mb-2 text-muted"><?= _("Persons not reached") ?></h6>
                            <table class="dataTable table table-striped table-condensed" id="personNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-families" role="tabpanel" aria-labelledby="tab-families">
                            <h6 class="mt-2 mb-2 text-muted"><?= _("Families not reached") ?></h6>
                            <table class="dataTable table table-striped table-condensed" id="familyNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-singles" role="tabpanel" aria-labelledby="tab-singles">
                            <h6 class="mt-2 mb-2 text-muted"><?= _("Single Persons not reached") ?></h6>
                            <table class="dataTable table table-striped table-condensed" id="singleNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-retired" role="tabpanel" aria-labelledby="tab-retired">
                            <h6 class="mt-2 mb-2 text-muted"><?= _("Retired not reached") ?></h6>
                            <table class="dataTable table table-striped table-condensed" id="retiredNeverBeenContacted" width="100%"></table>
                        </div>
                        <div class="tab-pane fade" id="pane-young" role="tabpanel" aria-labelledby="tab-young">
                            <h6 class="mt-2 mb-2 text-muted"><?= _("Young People not reached") ?></h6>
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
