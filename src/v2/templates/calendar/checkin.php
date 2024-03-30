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
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;


require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
    <div class="col-md-7">
        <a class="btn btn-app" id="add-event"><i class="fas fa-ticket-alt"></i><?= _('Add New Event') ?></a>
        <?php if (!is_null($searchEventInActivEvent)) {
            ?>
            <a class="btn btn-app" id="qrcode-call"><i class="fas fa-qrcode"></i><?= _("QR Code Call") ?></a>
            <a class="btn btn-app" href="<?= $sRootPath ?>/v2/kioskmanager"><i
                    class="fas fa-plug"></i><?= _("Kiosk Manager") ?></a>
            <?php
        }
        if ($bSundaySchool) {
            ?>
            <a class="btn btn-app" href="<?= $sRootPath ?>/v2/calendar/events/Attendees/Edit"><i
                    class="fas fa-pencil-alt"></i><?= _("Edit Attendees") ?></a>
            <?php
        }
        ?>
    </div>
    <?php
    if (!is_null($searchEventInActivEvent)) {
        ?>

        <div class="col-md-1">
            <label class="control-label"><?= _('Select Event'); ?></label>
        </div>
        <div class="col-md-4">
            <?php if ($sGlobalMessage): ?>
                <p><?= $sGlobalMessage ?></p>
            <?php endif; ?>
            <form name="selectEvent" action="<?= $sRootPath ?>/v2/calendar/events/checkin" method="POST">
                <div class="form-group">
                    <div class="inputGroupContainer">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-calendar-check"></i></span></div>
                            <select name="EventID" class= "form-control form-control-sm" onchange="this.form.submit()">
                                <option value="<?= $EventID; ?>"
                                        disabled <?= ($EventID == 0) ? " Selected='selected'" : "" ?> ><?= _('Select event') ?></option>
                                <?php foreach ($activeEvents as $event) {
                                    $dateStart = $event->getStart()->format(SystemConfig::getValue('sDatePickerFormat'));
                                    ?>
                                    <option
                                        value="<?= $event->getId(); ?>" <?= ($EventID == $event->getId()) ? " Selected='selected'" : "" ?> >
                                        <?= $dateStart . " : " . $event->getTitle() . " (" . $event->getDesc() . ")"; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php
    }
    ?>
</div>

<br>

<div id="errorcallout" class="alert alert-danger" hidden></div>

<div class="row">
    <div class="col-md-8">
        <?php
        //Populate data table
        if ($EventID > 0) {
            ?>
            <div class="card card-success">
                <div class="card-header  border-1">
                    <h3 class="card-title">
                        <?= _('Listing') ?> :</h3>
                </div>
                <div class="card-body table-responsive">

                <div class="row" style="margin:5px">
                        <div class="col-md-1">
                            <label><?= _("Checkin") ?></label>
                        </div>
                        <div class="col-sm-3" style="text-align:center">
                            <div class="btn-group">
                                <button class="btn btn-primary" type="submit" name="checkAllCheckin" id="checkAllCheckin"
                                       data-id="<?= $EventID ?>" value="">
                                    <i class="far fa-check-square"></i>  <?= _('Check all') ?>
                                </button>
                                <button class="btn btn-default" type="submit" name="uncheckAllCheckin" id="uncheckAllCheckin"
                                       data-id="<?= $EventID ?>" value="">
                                    <i class="far fa-square"></i> <?= _('Uncheck all') ?>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label><?= _("Checkout") ?></label>
                        </div>
                        <div class="col-sm-3" style="text-align:center">
                            <div class="btn-group">
                                <button class="btn btn-success" type="submit" name="checkAllCheckout" id="checkAllCheckout"
                                    data-id="<?= $EventID ?>" >
                                    <i class="far fa-check-square"></i> <?= _('Check all') ?>
                                </button>
                                <button class="btn btn-default" type="submit" name="uncheckAllCheckout"
                                       id="uncheckAllCheckout"
                                       data-id="<?= $EventID ?>" >
                                    <i class="far fa-square"></i> <?= _('Uncheck all') ?>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="page-length-select"><?= _("Number of rows") ?></label>
                        </div>
                        <div class="col-md-2">
                            <select name="pets" id="page-length-select" class= "form-control form-control-sm">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                        </div>
                    </div>
                    <br/>
                    <table id="checkedinTable" class="table table-striped table-bordered data-table"
                           width="100%"></table>            
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
            

            <div class="row" style="margin:5px">
                <div class="col-md-12" style="text-align:left">
                    <label><?= _("Add some notes") ?></label>
                </div>
            </div>                

            <div class="row" style="margin:-7px">
                <div class="col-md-12">
                    <textarea id="NoteText" name="NoteText" style="width: 100%;min-height: 300px;"
                            rows="40"><?= $sNoteText ?></textarea>
                </div>
                </div>
                <div class="row" style="margin:5px">
                    <div class="col-md-12" style="text-align:center">
                        <br>

                        <input id="validateAttendees" class="btn btn-primary" name="Validate"
                            value="<?= _("Validate Attendance") ?>">
                    </div>
                    <br>
                </div>
            </div>

            <?php
        }
        ?>
</div>

<div>
    <a href="<?= $sRootPath ?>/v2/calendar/events/list" class='btn btn-default'>
        <i class='fas fa-chevron-left'></i>
        <?= _('Return to Events') ?>
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
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

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
