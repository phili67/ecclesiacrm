<?php

/*******************************************************************************
 *
 *  filename    : templates/checkin.php
 *  last change : 2023-05-19
 *  description : manage the full checkin
 *
 *  http://www.ecclesiacrm.com/
 *
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\EventCountsQuery;
 
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-clipboard-check mr-1"></i><?= _('Check-in Management') ?></h3>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-success" id="add-event">
                <i class="fas fa-plus mr-1"></i><?= _('Add Event') ?>
            </button>
            <?php if (!is_null($searchEventInActivEvent)) {
                ?>
                <a class="btn btn-sm btn-info" id="qrcode-call">
                    <i class="fas fa-qrcode mr-1"></i><?= _("QR Code") ?>
                </a>
                <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/kioskmanager">
                    <i class="fas fa-plug mr-1"></i><?= _("Kiosk") ?>
                </a>
                <?php
            }
            if ($bSundaySchool) {
                ?>
                <a class="btn btn-sm btn-warning" href="<?= $sRootPath ?>/v2/calendar/events/Attendees/Edit">
                    <i class="fas fa-edit mr-1"></i><?= _("Attendees") ?>
                </a>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-3">
                <label class="control-label mb-2"><strong><?= _('Select Event') ?></strong></label>
            </div>
            <div class="col-md-9">
                <form name="selectEvent" action="<?= $sRootPath ?>/v2/calendar/events/checkin" method="POST">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-check"></i></span></div>
                        <select name="EventID" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="<?= $EventID; ?>" disabled <?= ($EventID == 0) ? " selected" : "" ?>>><?= _('Select event') ?></option>
                            <?php foreach ($activeEvents as $event) {
                                $dateStart = $event->getStart()->format(SystemConfig::getValue('sDatePickerFormat'));
                                ?>
                                <option value="<?= $event->getId(); ?>" <?= ($EventID == $event->getId()) ? " selected" : "" ?>>
                                    <?= $dateStart . " : " . $event->getTitle() . " (" . $event->getDesc() . ")"; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="errorcallout" class="alert alert-danger" hidden></div>

<div class="row">
    <div class="col-md-8">
        <?php
        //Populate data table
        if ($EventID > 0) {
            ?>
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0"><i class="fas fa-list-check mr-1"></i><?= _('Attendance List') ?></h3>
                </div>
                <div class="card-body p-1">
                    <div class="py-3 px-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="mb-0"><strong><?= _("Check-in") ?></strong></label>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-sm btn-success w-100" type="button" id="toggleAllCheckin"
                                        data-id="<?= $EventID ?>"
                                        data-type="1"
                                        data-checked="0"
                                        data-label-all="<?= _('All') ?>"
                                        data-label-none="<?= _('None') ?>">
                                    <i class="fas fa-check-square mr-1 toggle-icon"></i>
                                    <span class="toggle-label"><?= _('All') ?></span>
                                </button>
                            </div>
                            <div class="col-md-3">
                                <label class="mb-0"><strong><?= _("Check-out") ?></strong></label>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-sm btn-info w-100" type="button" id="toggleAllCheckout"
                                        data-id="<?= $EventID ?>"
                                        data-type="2"
                                        data-checked="0"
                                        data-label-all="<?= _('All') ?>"
                                        data-label-none="<?= _('None') ?>">
                                    <i class="fas fa-check-square mr-1 toggle-icon"></i>
                                    <span class="toggle-label"><?= _('All') ?></span>
                                </button>
                            </div>
                        </div>                        
                    </div>
                    <br>
                    <div class="table-responsive">
                        <table id="checkedinTable" class="table table-striped table-hover table-sm"
                               width="100%"></table>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="col-md-4">
        <?php
        if (!is_null($eventCountNames) && $eventCountNames->count() > 0) {
            ?>
            <div class="row">
                <div class="col-md-12">
                    <!-- Add Free Attendees Form -->
                    <div class="card card-secondary collapsed-card">
                        <div class="card-header  border-1">
                            <h4 class="card-title">
                                <?= _('Set your free attendees') ?>
                            </h4>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding:0px">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card" style="margin:0px">
                                        <div class="card-header  border-1">
                                            <h3 class="card-title"><?= _('Set your attendees Event') ?></h3>
                                        </div>
                                        <div class="card-body">
                                            <?php
                                            $desc = "";
                                            foreach ($eventCountNames as $eventCountName) {
                                                ?>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="control-label"><?= $eventCountName->getName(); ?></label>

                                                        <?php
                                                        $eventCount = EventCountsQuery::Create()
                                                            ->filterByEvtcntEventid($EventID)
                                                            ->findOneByEvtcntCountid($eventCountName->getId());

                                                        $count = 0;
                                                        if (!empty($eventCount)) {
                                                            $count = $eventCount->getEvtcntCountcount();
                                                            $desc = $eventCount->getEvtcntNotes();
                                                        }
                                                        ?>
                                                        <input type="text" id="field<?= $eventCountName->getId() ?>"
                                                               name="<?= $eventCountName->getId() ?>"
                                                               data-countid="<?= $eventCountName->getId() ?>"
                                                               value="<?= $count ?>"
                                                               size="8" class="form-control form-control-sm freeAttendeesCount"
                                                               width="100%"
                                                               style="width: 100%">
                                                    </div>
                                                </div>
                                                <hr/>
                                                <?php
                                            }
                                            ?>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label
                                                        class="control-label"><?= _('Your description') ?></label>

                                                    <textarea id="fieldText" name="desc"
                                                              data-countid="<?= $eventCountName->getId() ?>"
                                                              rows="5" class="form-control form-control-sm " width="100%"
                                                              style="width: 100%"><?= $desc ?></textarea>
                                                </div>
                                            </div>
                                            <br/>
                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <input id="addFreeAttendees" class="btn btn-primary btn-sm"
                                                           value="<?= _('Add Free Attendees Count'); ?>"
                                                           name="Add" tabindex=4 style="width: 300px">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Add Free Attendees Form -->

                </div>
            </div>
            <?php
        }
        ?>
        <!-- Add Attendees Form -->
        <?php
        // If event is known, then show 2 text boxes, person being checked in and the person checking them in.
        // Show a verify button and a button to add new visitor in dbase.
        if ($EventID > 0) {
            ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary collapsed-card">
                        <div class="card-header  border-1">
                            <h4 class="card-title">
                                <?= _('Add single child/parents Attendees') ?>
                            </h4>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding:0px">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card" style="margin:0px">
                                        <div class="card-header  border-1">
                                            <h3 class="card-title"><?= _('Add Attendees for Event'); ?>
                                                : <?= $event->getTitle() ?></h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="col-md-5">
                                                    <label for="child"
                                                           class="col-sm-12 control-label"><?= _("Person's Name") ?></label>
                                                </div>
                                                <div class="col-md-12 inputGroupContainer">
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="fas fa-child"></i></span>
                                                        </div>
                                                        <input type="text" class= "form-control form-control-sm" id="child"
                                                               placeholder="<?= _("Person's Name"); ?>" required
                                                               tabindex=1>
                                                    </div>
                                                    <span class="glyphicon form-control-feedback"
                                                          aria-hidden="true"></span>
                                                    <div class="help-block with-errors"></div>
                                                </div>
                                                <div id="childDetails" class="col-sm-5 text-center"></div>
                                            </div>
                                            <hr>
                                            <div class="form-group">
                                                <label for="adult"
                                                       class="col-sm-12 control-label"><?= _('Adult Name(Optional)') ?></label>
                                                <div class="col-md-12 inputGroupContainer">
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="fas fa-user"></i></span>
                                                        </div>
                                                        <input type="text" class= "form-control form-control-sm" id="adult"
                                                               placeholder="<?= _('Checked in By(Optional)'); ?>"
                                                               tabindex=2>
                                                    </div>
                                                </div>
                                                <div id="adultDetails" class="col-sm-5 text-center"></div>
                                            </div>
                                            <hr>
                                            <div class="form-group row">
                                                <div class="col-md-5">
                                                    <input id="addAndCheckIn" class="btn btn-primary btn-sm"
                                                           value="<?= _('Add and Checkin'); ?>"
                                                           name="CheckIn" tabindex=3 data-childid="-1"
                                                           data-adultid="-1">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="reset" id="resetDetails" class="btn btn-default btn-sm"
                                                           value="<?= _('Cancel'); ?>"
                                                           name="Cancel" tabindex=4
                                                           onClick="">
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="Add" class="btn btn-success btn-sm"
                                                           value="<?= _('Add Visitor'); ?>"
                                                           name="Add" tabindex=4
                                                           id="addVisitor">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="card card-warning shadow-sm mt-3">
                <div class="card-header py-2 border-0">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-sticky-note mr-1"></i><?= _("Notes") ?>
                    </h3>
                </div>
                <div class="card-body bg-warning text-dark p-1">
                    <small><label for="NoteText" class="mb-2 text-white"><i class="fa fa-info-circle" style="font-size:0.6rem;"></i> <?= _("Add some notes") ?></label></small>
                    <textarea id="NoteText" name="NoteText" class="form-control" style="min-height: 300px; background-color: #fff9db; border-color: #e0c97f;"
                              rows="12"><?= $sNoteText ?></textarea>
                </div>
            </div>
            <div class="mt-2 d-flex justify-content-end">
                <button id="validateAttendees" class="btn btn-sm btn-primary" name="Validate" type="button">
                    <i class="fas fa-check-circle mr-1"></i><?= _("Validate Attendance") ?>
                </button>
            </div>

            <?php
        }
        ?>
    </div>
</div>


<div class="mt-4 pt-3 border-top">
    <a href="<?= $sRootPath ?>/v2/calendar/events/list" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i><?= _('Return to Events') ?>
    </a>
</div>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.isModifiable = true;
    window.CRM.EventID = <?= $EventID ?>;
    window.CRM.isSundaySchool = <?= ($bSundaySchool) ? 'true' : 'false' ?>;

    window.CRM.contentsExternalCssFont = '<?= $contentsExternalCssFont ?>';
    window.CRM.extraFont = '<?= $extraFont ?>';

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
    };

    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>


<script src="<?= $sRootPath ?>/skin/js/event/Checkin.js"></script>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css"
      rel="stylesheet">

<script
    src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/jquery-ui/jquery-ui.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>

<script src="<?= $sRootPath ?>/skin/external/jsqr/jsQR.js"></script>


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

<?php require $sRootDocument . '/Include/Footer.php'; ?>
