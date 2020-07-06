<?php
/*******************************************************************************
 *
 *  filename    : pastoralcareMembersList.php
 *  last change : 2020-06-20
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-primary card-body">
    <div class="margin">
        <label><?= _("Classification") ?></label>
        <div class="btn-group">
            <a class="btn btn-app changeType" data-typeid="<?= $aMemberTypes[0]['OptionName'] ?>>"><i
                    class="fa fa-sticky-note"></i><?= $aMemberTypes[0]['OptionName'] ?></a>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Menu déroulant</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <?php foreach ($aMemberTypes as $aMemberType) {
                    ?>
                    <a class="dropdown-item changeType" data-typeid="<?= $aMemberType['OptionName'] ?>"><?= $aMemberType['OptionName'] ?></a>
                <?php
                }
                ?>
            </div>
            &nbsp;
            <a class="btn btn-app bg-orange" id="add-event"><i class="fa fa-calendar-plus-o"></i><?= _("Appointment") ?></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="row">
                            <span style="color: red">*</span><?= _("For") ?> &nbsp;&nbsp;
                            <input type="radio" name="type" value="1" checked="" class="typeSort"> <?= _("All persons") ?> &nbsp;&nbsp;
                            <input type="radio" name="type" value="2" class="typeSort"> <?= _("Only the visited/called persons") ?> &nbsp;&nbsp;
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class=" dataTable table table-striped table-condensed" id="pastoralCareMembersList"
                       width="100%"></table>
            </div>
        </div>
    </div>
</div>

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
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>

