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

require $sRootDocument . '/Include/Header.php';
?>

<div class="col">
    <div class="row">
        <div class="col-md-3">
            <div class="sticky-top">
                <div class="card card-lightblue">
                    <div class="card-header with-border">
                        <h3 class="card-title"><?= _("Filters") ?></h3>
                    </div>
                    <div class="row" style="padding:5px">
                        <div class="col-md-3">
                            <p class="text-center"><?= _("By Types") ?></p>
                        </div>
                        <div class="col-md-9">
                            <select type="text" id="EventTypeFilter" value="0"
                                    class="form-control input-sm" size=1>
                                <option value='0'><?= _("All") ?></option>
                                <option disabled>──────────</option>
                                <?php
                                foreach ($eventTypes as $eventType) {
                                    ?>
                                    <option
                                        value="<?= $eventType->getID() ?>"><?= $eventType->getName() ?></option>
                                    <?php
                                }
                                ?>
                                <option disabled>──────────</option>
                                <option value='-1'><?= _("Personal") ?></option>
                                <option value='-2'><?= _("Group") ?></option>
                                <option disabled>──────────</option>
                                <option value='-3'><?= _("Room") ?></option>
                                <option value='-4'><?= _("Computer") ?></option>
                                <option value='-5'><?= _("Video") ?></option>
                                <option value='-6'><?= _("Shared") ?></option>
                            </select>
                        </div>
                    </div>
                    <hr class="hr-separator">
                    <div class="row" style="padding: 3px">
                        <div class="cold-4">
                            <div
                                class="custom-control custom-switch ustom-switch custom-switch-off-light custom-switch-on-info">
                                &nbsp;<input type="checkbox" class="custom-control-input" id="isWithLimit" checked>
                                <label class="custom-control-label"
                                       for="isWithLimit"><small><?= _("Limit") ?></small></label>
                            </div>

                        </div>
                        <div class="col-4">

                            <?php
                            if ($sessionUsr->isSeePrivacyDataEnabled()) {
                                ?>
                                <div
                                    class="custom-control custom-switch custom-switch-off-light custom-switch-on-danger">
                                    <input type="checkbox" class="custom-control-input"
                                           id="isBirthdateActive" checked>
                                    <label class="custom-control-label"
                                           for="isBirthdateActive"><small><?= _("Birthdate") ?></small></label>
                                </div>
                                <?php
                            }
                            ?>


                        </div>
                        <div class="col-4">

                            <?php
                            if ($sessionUsr->isSeePrivacyDataEnabled()) {
                                ?>
                                <div
                                    class="custom-control custom-switch custom-switch-off-light custom-switch-on-primary">
                                    <input type="checkbox" class="custom-control-input"
                                           id="isAnniversaryActive" checked>
                                    <label class="custom-control-label"
                                           for="isAnniversaryActive"><small><?= _("Wedding") ?></small></label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div id="accordion">
                    <div class="card card-primary card-calendar">
                        <div class="card-header card-header-calendar" id="headingOne">
                            <h3 class="card-title">
                                <button class="btn btn-link text-white" data-toggle="collapse"
                                        data-target="#collapseOne"
                                        aria-expanded="true" aria-controls="collapseOne">
                                    <i class="fa fa-user"></i>&nbsp;<?= _("Personals") ?>
                                </button>
                            </h3>
                            <div class="card-tools card-tools-calendar">
                                <button type="button" class="btn btn-tool" data-card-widget=""><i
                                        class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="right"
                                        title="<?= _("Exclude/include Calendars") ?>"
                                        id="manage-all-calendars"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget=""><i
                                        class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left"
                                        title="<?= _("Add New Calendar") ?>"
                                        id="add-calendar"></i>
                                </button>
                            </div>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                             data-parent="#accordion">
                            <div class="card-body" style="padding: 0px">
                                <div class="panel-body" style="padding: 0px;">
                                    <div class="row" style="padding: 0px;">
                                        <div class="col-md-12 col-xs-12">
                                            <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                                <ul class="list-group" id="cal-list">
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-primary card-calendar">
                        <div class="card-header card-header-calendar" id="headingTwo">
                            <h3 class="card-title">
                                <button class="btn btn-link btn-link text-white" data-toggle="collapse"
                                        data-target="#collapseTwo"
                                        aria-expanded="true" aria-controls="collapseTwo">
                                    <i class="fa fa-building"></i>&nbsp;<i class="fa fa-windows"></i>&nbsp;<i
                                        class="fa fa-video-camera"></i>&nbsp;<?= _("Resources") . (!($sessionUsr->isAdmin() || $sessionUsr->isManageGroupsEnabled()) ? "  (" . _("Shared") . ")" : "") ?>
                                </button>
                            </h3>
                            <div class="card-tools card-tools-calendar">
                                <button type="button" class="btn btn-tool" data-card-widget=""><i
                                        class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left"
                                        title="<?= _("Exclude/include Resource") ?>"
                                        id="manage-all-reservation"></i>
                                </button>
                                <?php
                                if ($sessionUsr->isAdmin()) {
                                    ?>
                                    <button type="button" class="btn btn-tool" data-card-widget=""><i
                                            class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left"
                                            title="<?= _("Add New Calendar") ?>"
                                        <i class="fa pull-right fa-plus" data-toggle="tooltip" data-placement="left"
                                           title="<?= _("Add New Resource Calendar") ?>"
                                           id="add-reservation-calendar"></i>
                                    </button>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body" style="padding: 0px">
                                <div class="row" style="padding: 0px;">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                            <ul class="list-group" id="reservation-list">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-primary card-calendar">
                        <div class="card-header card-header-calendar" id="headingThree">
                            <h5 class="mb-0">
                                <h3 class="card-title">
                                    <button class="btn btn-link btn-link text-white" data-toggle="collapse"
                                            data-target="#collapseThree"
                                            aria-expanded="true" aria-controls="collapseThree">
                                        <i class="fa fa-users"></i><?= !($sessionUsr->isManageGroupsEnabled()) ? '&nbsp;<i class="fa  fa-share"></i>&nbsp;' : "&nbsp;" ?>
                                        <?= _("Groups") . (!($sessionUsr->isAdmin() || $sessionUsr->isManageGroupsEnabled()) ? "  (" . _("Shared") . ")" : "") ?>
                                    </button>
                                </h3>
                                <div class="card-tools card-tools-calendar">
                                    <button type="button" class="btn btn-tool" data-card-widget="">
                                        <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="right"
                                           title="<?= _("Add New Calendar") ?>"
                                           id="manage-all-groups"></i>
                                    </button>
                                </div>
                            </h5>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                             data-parent="#accordion">
                            <div class="card-body" style="padding: 0px">
                                <div class="row" style="padding: 0px;">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                            <ul class="list-group" id="group-list">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-primary card-calendar">
                        <div class="card-header card-header-calendar" id="headingFour">
                            <h3 class="card-title">
                                <button class="btn btn-link text-white collapsed" data-toggle="collapse"
                                        data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="fa  fa-share"></i>&nbsp;<?= _("Shared") . "  (" . _("Users") . ")" ?>
                                </button>
                            </h3>

                            <div class="card-tools card-tools-calendar">
                                <button type="button" class="btn btn-tool" data-card-widget="">
                                    <i class="fa pull-right fa-gear" data-toggle="tooltip" data-placement="left"
                                       title="<?= _("Exclude/include the Shared") ?>"
                                       id="manage-all-shared"></i>&nbsp;
                                </button>
                            </div>
                        </div>
                        <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
                            <div class="card-body" style="padding: 0px">
                                <div class="row" style="padding: 0px;">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="well" style="max-height: 255px;overflow: auto;padding: 0px;">
                                            <ul class="list-group" id="share-list">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <!-- THE CALENDAR -->
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <!-- /. box -->
</div>
<!-- /.col -->

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.isModifiable  = true;
  window.CRM.calendarSignature = null;
  window.CRM.calendar = null;

  window.CRM.churchloc = {
      lat: <?= $coordinates['lat'] ?>,
      lng: <?= $coordinates['lng'] ?>};
  window.CRM.mapZoom   = <?= $iLittleMapZoom ?>;

  var wAgendaName = localStorage.getItem("wAgendaName");
  if (wAgendaName == null) {
      localStorage.setItem("wAgendaName", "dayGridMonth");
      wAgendaName = "dayGridMonth";
  }
</script>


<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<link href="<?= $sRootPath ?>/skin/external/fullcalendar/main.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/fullcalendar/main.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/fullcalendar/locales-all.min.js "></script>

<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

<script src="<?= $sRootPath ?>/skin/js/calendar/CalendarSideBar.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/CalendarV2.js"></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<?php
if ($sMapProvider == 'OpenStreetMap') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
    <?php
} else if ($sMapProvider == 'GoogleMaps') {
    ?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $sGoogleMapKey ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
    <?php
} else if ($sMapProvider == 'BingMaps') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
<?php
  }
?>
