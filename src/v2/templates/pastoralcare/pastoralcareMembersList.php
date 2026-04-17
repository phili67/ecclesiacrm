<?php
/*******************************************************************************
 *
 *  filename    : pastoralcareMembersList.php
 *  last change : 2020-06-20
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be ibcluded in another software
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-filter mr-1"></i><?= _("Classification") ?></h3>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-primary changeType" data-typeid="<?= $aMemberTypes[0]['OptionName'] ?>" data-toggle="tooltip" data-placement="bottom" title="<?= _("Filter by member types") ?>">
                    <i class="fas fa-list-check mr-1"></i><?= $aMemberTypes[0]['OptionName'] ?>
                </button>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Menu déroulant</span>
                </button>
                <div class="dropdown-menu dropdown-menu-left">
                    <?php foreach ($aMemberTypes as $aMemberType) { ?>
                        <a class="dropdown-item changeType" href="#" data-typeid="<?= $aMemberType['OptionName'] ?>">
                            <i class="fas fa-tag mr-2"></i><?= $aMemberType['OptionName'] ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-warning" id="add-event" data-toggle="tooltip" data-placement="bottom" title="<?= _("Create an appointment") ?>">
                <i class="far fa-calendar-plus mr-1"></i><?= _("Appointment") ?>
            </button>
        </div>
    </div>
</div>

<?php if (SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) { ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0"><i class="fas fa-database mr-1"></i><?= _("Members List") ?></h3>
                </div>
                <div class="card-body py-3">
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="font-weight-semibold mb-2 d-block"><span class="text-danger">*</span><?= _("Display options") ?></label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="typeAll" name="type" value="1" checked class="custom-control-input typeSort">
                            <label class="custom-control-label" for="typeAll"><?= _("All persons") ?></label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="typeVisited" name="type" value="2" class="custom-control-input typeSort">
                            <label class="custom-control-label" for="typeVisited"><?= _("Only visited/called persons") ?></label>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm dataTable" id="pastoralCareMembersList" width="100%"></table>
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

<script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCareMembersList.js"></script>
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


