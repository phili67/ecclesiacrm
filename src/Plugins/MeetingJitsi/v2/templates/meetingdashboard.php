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

<div class="card bg-gray-dark card-body">
    <div class="margin">
        <label><?= dgettext("messages-MeetingJitsi","Room names") ?></label>
        <div class="btn-group">
            <a class="btn btn-app" id="newRoom" data-toggle="tooltip" data-placement="bottom" title="<?= dgettext("messages-MeetingJitsi","Create first a room and manage theme with the arrow button") ?>"><i
                    class="fas fa-sticky-note"></i><?= dgettext("messages-MeetingJitsi","Create Room") ?></a>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Menu déroulant</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <?php foreach ($allRooms as $room) { ?>
                    <a class="dropdown-item selectRoom" data-roomid="<?= $room['Id'] ?>">
                        (<?= (new DateTime($room['CreationDate']))->format(SystemConfig::getValue('sDateFormatLong')) ?>)
                        <?= $room['Code'] ?>
                        </a>
                <?php } ?>
            </div>
            &nbsp;
            <a class="btn btn-app bg-danger" id="delete-all-rooms" data-toggle="tooltip"  data-placement="bottom" title="<?= dgettext("messages-MeetingJitsi","This action will delete all your rooms") ?>">
                <i class="fas fa-times-circle"></i><?= dgettext("messages-MeetingJitsi","Delete all Rooms") ?>
            </a>
            &nbsp;
            <a class="btn btn-app bg-orange" id="add-event"><i class="far fa-calendar-plus"></i><?= dgettext("messages-MeetingJitsi","Appointment") ?>
            </a>
        </div>
    </div>
</div>

<div class="row" style="height: 100%">
    <div class="col-md-12">
        <div class="card card-gray-dark">
            <div class="card-header  border-1">
                <div
                    class="card-title"><?= dgettext("messages-MeetingJitsi","Room Name") ?> : <?= $roomName ?>
                </div>
                <div style="float:right"><a href="https://jitsi.org/" target="_blank">
                        <img src="<?= $sRootPath ?>/Images/jitsi_logo.png" height="25/"></a>
                </div>
            </div>
            <div class="card-body" style="padding: 0px">
                <div id="meetingIframe" style="width:100%;height:600px">
                    <?php if ($roomName == '') { ?>
                        <div class="text-center">
                            <img src="<?= $sRootPath ?>/Images/jitsi_logo.png" height="85/"><br/>
                            <label> <?= dgettext("messages-MeetingJitsi","First create a Jitsi room with the button above !") ?></label>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>

