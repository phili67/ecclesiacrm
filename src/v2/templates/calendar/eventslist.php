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
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';

?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-calendar mr-1"></i><?= _('Events Management') ?></h3>
    </div>
    <div class="card-body py-3">
        <button type="button" class="btn btn-sm btn-success" id="add-event">
            <i class="fas fa-plus mr-1"></i><?= _('Add New Event') ?>
        </button>
    </div>
</div>


<div class="card card-outline card-info shadow-sm mb-3">
    <div class="card-header py-2">
        <h3 class="card-title mb-0"><i class="fas fa-filter mr-1"></i><?= _('Event Filters') ?></h3>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-sm-4">
                <label class="mb-2"><?= _('Select Event Types To Display') ?></label>
                <select name="WhichType" id="EventTypeSelector" class="form-control form-control-sm">
                    <option value="all" <?= ($eType == 'All')?'selected':'' ?>><?= _('All') ?></option>
                    <?php
                    foreach ($eventTypes as $eventType) {
                        if ($eventType->getId() == null) {
                            ?>
                            <option value="<?= _("Personal Calendar") ?>" <?= ($eType == '0' && $eType !='All')?'selected':'' ?>><?= _("Personal Calendar") ?></option>
                            <option value="<?= _("Group") ?>" <?= ($eType == '0' && $eType !='All')?'selected':'' ?>><?= _("Group") ?></option>
                        <?php } else { ?>
                            <option value="<?= $eventType->getName() ?>" <?= ($eventType->getId() == $eType)?'selected':'' ?>><?= $eventType->getName() ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-sm-4">
                <label class="mb-2"><?= _('Display Events in Month') ?></label>
                <select name="WhichMonth" id="MonthSelector" class="form-control form-control-sm">
                    <option value="all" <?= ($EventMonth == 0)?'selected':'' ?>><?= _("All") ?></option>
                    <option value="-1" disabled="disabled">_________________________</option>
                    <option value="<?= _("January") ?>" <?= ($EventMonth == 1)?'selected':'' ?>><?= _("January") ?></option>
                    <option value="<?= _("February") ?>" <?= ($EventMonth == 2)?'selected':'' ?>><?= _("February") ?></option>
                    <option value="<?= _("March") ?>" <?= ($EventMonth == 3)?'selected':'' ?>><?= _("March") ?></option>
                    <option value="<?= _("April") ?>" <?= ($EventMonth == 4)?'selected':'' ?>><?= _("April") ?></option>
                    <option value="<?= _("May") ?>" <?= ($EventMonth == 5)?'selected':'' ?>><?= _("May") ?></option>
                    <option value="<?= _("June") ?>" <?= ($EventMonth == 6)?'selected':'' ?>><?= _("June") ?></option>
                    <option value="<?= _("July") ?>" <?= ($EventMonth == 7)?'selected':'' ?>><?= _("July") ?></option>
                    <option value="<?= _("August") ?>" <?= ($EventMonth == 8)?'selected':'' ?>><?= _("August") ?></option>
                    <option value="<?= _("September") ?>" <?= ($EventMonth == 9)?'selected':'' ?>><?= _("September") ?></option>
                    <option value="<?= _("October") ?>" <?= ($EventMonth == 10)?'selected':'' ?>><?= _("October") ?></option>
                    <option value="<?= _("November") ?>" <?= ($EventMonth == 11)?'selected':'' ?>><?= _("November") ?></option>
                    <option value="<?= _("December") ?>" <?= ($EventMonth == 12)?'selected':'' ?>><?= _("December") ?></option>
                </select>
            </div>
            <div class="col-sm-4">
                <label class="mb-2"><?= _('Display Events in Year') ?></label>
                <select name="WhichYear" id="YearSelector" class= "form-control form-control-sm">
                    <?php
                    $current_Year = date('Y');

                    $is_current_available = false;
                    $is_option_selected   = false;

                    foreach ($years as $year) {
                        if ($year == $current_Year) {
                            $is_current_available = true;
                        }

                        if ($year == $yVal && $year != $current_Year) {
                            $is_option_selected = true;
                        }
                        ?>
                        <option value="<?= $year ?>" <?= ($year == $yVal)?'selected':'' ?>><?= $year ?></option>
                        <?php
                    }
                    if (!$is_current_available) {
                        ?>
                        <option value="<?= $current_Year ?>" <?= (!$is_option_selected)?"selected":"" ?>><?= $current_Year ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>


<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title mb-0" id="main-Title-events"><i class="fas fa-list mr-1"></i><?= _("Events in Year") ?> : <?= $yVal ?></h3>
        <div class="card-tools">
            <h3 class="in-progress" style="color:red"></h3>
        </div>
    </div>
    <div class="card-body p-0">
        <table width="100%" cellpadding="2"
               class="table table-striped table-bordered table-hover data-table dataTable no-footer dtr-inline"
            id="DataEventsListTable"></table>
    </div>
</div>


<div class="mt-4 pt-3 border-top">
    <a href="<?= SystemURLs::getRootPath() ?>/v2/calendar" class='btn btn-outline-secondary'>
        <i class='fas fa-arrow-left mr-1'></i>
        <?= _('Return to Calendar') ?>
    </a>
</div>

<link href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/EventEditor.js" ></script>

<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
    <?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
    ?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/GoogleMapEvent.js"></script>
    <?php
}
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isModifiable  = "true";
    window.CRM.yVal = '<?= $yVal ?>';

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
    };
    window.CRM.mapZoom   = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/calendar/EventsList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

