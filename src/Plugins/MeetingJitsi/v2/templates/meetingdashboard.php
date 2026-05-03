<?php
/*******************************************************************************
 *
 *  filename    : meetingdashboard.php
 *  last change : 2020-07-04
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2020 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';
?>

<section class="content pt-3">
    <div class="container-fluid">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="mb-0">
                        <i class="fas fa-video mr-2 text-info"></i><?= dgettext("messages-MeetingJitsi", "Jitsi Meeting") ?>
                    </h3>
                    <small class="text-muted"><?= dgettext("messages-MeetingJitsi", "Create, select and launch your rooms") ?></small>
                </div>
                <a href="https://jitsi.org/" target="_blank" rel="noopener noreferrer">
                    <img src="<?= $sRootPath ?>/Plugins/MeetingJitsi/skin/jitsi_logo.png" height="28" alt="Jitsi">
                </a>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-body d-flex flex-wrap align-items-center">
                    <span class="font-weight-bold mr-3"><?= dgettext("messages-MeetingJitsi", "Room names") ?></span>

                    <div class="btn-group mr-2 mb-2" role="group">
                        <a class="btn btn-info" id="newRoom" data-toggle="tooltip" data-placement="bottom" title="<?= dgettext("messages-MeetingJitsi", "Create first a room and manage theme with the arrow button") ?>">
                            <i class="fas fa-plus-circle mr-1"></i><?= dgettext("messages-MeetingJitsi", "Create Room") ?>
                        </a>
                        <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <span class="sr-only">Menu déroulant</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" role="menu">
                            <?php foreach ($allRooms as $room) { ?>
                                <a class="dropdown-item selectRoom" data-roomid="<?= $room['Id'] ?>">
                                    (<?= (new DateTime($room['CreationDate']))->format(SystemConfig::getValue('sDateFormatLong')) ?>) <?= $room['Code'] ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>

                    <a class="btn btn-outline-danger mr-2 mb-2" id="delete-all-rooms" data-toggle="tooltip" data-placement="bottom" title="<?= dgettext("messages-MeetingJitsi", "This action will delete all your rooms") ?>">
                        <i class="fas fa-trash-alt mr-1"></i><?= dgettext("messages-MeetingJitsi", "Delete all Rooms") ?>
                    </a>

                    <a class="btn btn-outline-warning mb-2" id="add-event">
                        <i class="far fa-calendar-plus mr-1"></i><?= dgettext("messages-MeetingJitsi", "Appointment") ?>
                    </a>
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <?= dgettext("messages-MeetingJitsi", "Room Name") ?> :
                        <span class="text-info"><?= $roomName == '' ? dgettext("messages-MeetingJitsi", "No room selected") : $roomName ?></span>
                    </h3>
                </div>
                <div class="card-body p-0" style="background:#f4f6f9;">
                    <div id="meetingIframe" style="width:100%;height:600px;background:#f4f6f9;">
                        <?php if ($roomName == '') { ?>
                            <div class="h-100 d-flex flex-column justify-content-center align-items-center text-center" style="min-height:600px;">
                                <img src="<?= $sRootPath ?>/Plugins/MeetingJitsi/skin/jitsi_logo.png" height="90" alt="Jitsi" class="mb-3"/>
                                <p class="mb-1"><?= dgettext("messages-MeetingJitsi", "First create a Jitsi room with the button above !") ?></p>
                                <small class="text-muted"><?= dgettext("messages-MeetingJitsi", "Then choose a room from the dropdown to launch the meeting") ?></small>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

    </div>
</section>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src='<?= $domainscriptpath ?>'></script>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>


<script nonce="<?= $sCSPNonce ?>">
    <?php if ($roomName != '') { ?>
    // jitsi code
    const domain = '<?= $domain ?>';
    const options = {
        roomName: "<?= $apiKey ?>/<?= $roomName ?>",
        width: '100%',
        height: '100%',        
        parentNode: document.querySelector('#meetingIframe'),
                        // Make sure to include a JWT if you intend to record,
                        // make outbound calls or use any other premium features!
                        // jwt: "eyJraWQiOiJ2cGFhcy1tYWdpYy1jb29raWUtYzEyZDEyZDJmMjc4NDIwZmExZjRiYTc0YjMzZWQ4ZWUvZjhmNmQ1LVNBTVBMRV9BUFAiLCJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJqaXRzaSIsImlzcyI6ImNoYXQiLCJpYXQiOjE3MDQzOTg1MzYsImV4cCI6MTcwNDQwNTczNiwibmJmIjoxNzA0Mzk4NTMxLCJzdWIiOiJ2cGFhcy1tYWdpYy1jb29raWUtYzEyZDEyZDJmMjc4NDIwZmExZjRiYTc0YjMzZWQ4ZWUiLCJjb250ZXh0Ijp7ImZlYXR1cmVzIjp7ImxpdmVzdHJlYW1pbmciOmZhbHNlLCJvdXRib3VuZC1jYWxsIjpmYWxzZSwic2lwLW91dGJvdW5kLWNhbGwiOmZhbHNlLCJ0cmFuc2NyaXB0aW9uIjpmYWxzZSwicmVjb3JkaW5nIjpmYWxzZX0sInVzZXIiOnsiaGlkZGVuLWZyb20tcmVjb3JkZXIiOmZhbHNlLCJtb2RlcmF0b3IiOnRydWUsIm5hbWUiOiJUZXN0IFVzZXIiLCJpZCI6Imdvb2dsZS1vYXV0aDJ8MTE3MDAxNjM2OTc2OTE5MTQ2ODQzIiwiYXZhdGFyIjoiIiwiZW1haWwiOiJ0ZXN0LnVzZXJAY29tcGFueS5jb20ifX0sInJvb20iOiIqIn0.Oh0USd1J2PZd4kAskLZGHiy-8wrAk2GcZIv9LLLACjRpLrM4pn_GBX-RCVL5ZgytiJ7YyVg9wQQrqOgIPFx2Qfq8oyh1L374bEAcR-HzprM0CyJZeTI5plEoNuDQU_YCXvojzcPjaaxzlGwlg4XqUfiy8-sRZqVLKb4TH3JZAVZt2xxgMmHUmTqu15kRJptLuUKTjtMIdrMk8JUFgAoRSrTDDwbwdc1X302hj8fza6CUZye4SRez-7AYgxUVxn-oFXCx_Ksuhoyhvj0i1ciCG60JDIUAKk31km9LRvK27jNIu3pxWQEtZuMkpy8-aBl_YYzAAz_f13XwsHj4BcmWhA"
        };
    const api = new JitsiMeetExternalAPI(domain, options);
    // end
    <?php } ?>

    // page construction
    var sPageTitle = '<?= $sPageTitle ?>';

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)};
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>


<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>
<script src="<?= $sRootPath ?>/Plugins/MeetingJitsi/skin/js/meeting.js"></script>

<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
<?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
?>
    <!--Google Map Scripts -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
<?php
} 
?>

