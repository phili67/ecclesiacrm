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
use EcclesiaCRM\EventQuery;

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;

use EcclesiaCRM\Map\EventTableMap;

require $sRootDocument . '/Include/Header.php';
?>

<div class='text-center'>
    <a class='btn btn-primary' id="add-event">
        <i class='fa fa-ticket'></i>
        <?= _('Add New Event') ?>
    </a>
</div>


<div class="row">
    <div class="col-sm-4">
        <label><?= _('Select Event Types To Display') ?></label>
        <select name="WhichType" onchange="javascript:this.form.submit()" class='form-control'>
            <option value="All" <?= ($eType == 'All')?'selected':'' ?>><?= _('All') ?></option>
            <?php
            foreach ($eventTypes as $eventType) {
                if ($eventType->getId() == null) {
                    ?>
                    <option value="0" <?= ($eType == '0' && $eType !='All')?'selected':'' ?>><?= _("Personal Calendar") ?></option>
                <?php } else { ?>
                    <option value="<?= $eventType->getId() ?>" <?= ($eventType->getId() == $eType)?'selected':'' ?>><?= $eventType->getName() ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php
        // year selector

        if ($eType == 'All') {
            $years = EventQuery::Create()
                ->addAsColumn('year','YEAR('.EventTableMap::COL_EVENT_START.')')
                ->select('year')
                ->setDistinct()
                ->where('YEAR('.EventTableMap::COL_EVENT_START.')')
                ->find();

        } else {
            $years = EventQuery::Create()
                ->filterByType ($eType)
                ->addAsColumn('year','YEAR('.EventTableMap::COL_EVENT_START.')')
                ->select('year')
                ->setDistinct()
                ->where('YEAR('.EventTableMap::COL_EVENT_START.')')
                ->find();

        }
        ?>
    </div>
    <div class="col-sm-4">
        <label><?= _('Display Events in Month') ?></label>
        <select name="WhichMonth" onchange="javascript:this.form.submit()" class='form-control'>
            <option value="0" <?= ($EventMonth == 0)?'selected':'' ?>><?= _("All") ?></option>
            <option value="-1" disabled="disabled">_________________________</option>
            <option value="1" <?= ($EventMonth == 1)?'selected':'' ?>><?= _("January") ?></option>
            <option value="2" <?= ($EventMonth == 2)?'selected':'' ?>><?= _("February") ?></option>
            <option value="3" <?= ($EventMonth == 3)?'selected':'' ?>><?= _("March") ?></option>
            <option value="4" <?= ($EventMonth == 4)?'selected':'' ?>><?= _("April") ?></option>
            <option value="5" <?= ($EventMonth == 5)?'selected':'' ?>><?= _("May") ?></option>
            <option value="6" <?= ($EventMonth == 6)?'selected':'' ?>><?= _("June") ?></option>
            <option value="7" <?= ($EventMonth == 7)?'selected':'' ?>><?= _("July") ?></option>
            <option value="8" <?= ($EventMonth == 8)?'selected':'' ?>><?= _("August") ?></option>
            <option value="9" <?= ($EventMonth == 9)?'selected':'' ?>><?= _("September") ?></option>
            <option value="10" <?= ($EventMonth == 10)?'selected':'' ?>><?= _("October") ?></option>
            <option value="11" <?= ($EventMonth == 11)?'selected':'' ?>><?= _("November") ?></option>
            <option value="12" <?= ($EventMonth == 12)?'selected':'' ?>><?= _("December") ?></option>
        </select>
    </div>
    <div class="col-sm-4"><label><?= _('Display Events in Year') ?></label>
        <select name="WhichYear" onchange="javascript:this.form.submit()" class='form-control'>
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

<br/>


<div class="card">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _("Events in Year") ?></h3>
    </div>
    <div class="card-body">
        <table id="DataEventsListTable" width="100%"></table>
    </div>
</div>


<div>
    <a href="<?= SystemURLs::getRootPath() ?>/v2/calendar" class='btn btn-default'>
        <i class='fa fa-chevron-left'></i>
        <?= _('Return to Calendar') ?>
    </a>
</div>

<link href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/EventEditor.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/ListEvent.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/publicfolder.js"></script>

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
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
    ?>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isModifiable  = "true";

    window.CRM.churchloc = {
        lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
    window.CRM.mapZoom   = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/calendar/EventsList.js"></script>

