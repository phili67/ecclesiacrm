<?php

use EcclesiaCRM\dto\SystemURLs;

// Set the page title and include HTML header
$sPageTitle = "CRM - Sunday School Device Kiosk";
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");

?>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/moment/moment-with-locales.min.js"></script>

<link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/Kiosk.css">

<div>
    <h1 id="noEvent"></h1>
</div>
<div id="event">
    <div class="container" id="eventDetails">
        <div class="row">
            <div class="col-12 col-sm-6 col-lg-3 mb-2">
                <div id="eventTitle"></div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-2">
                <div id="eventKiosk"></div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-2">
                <div id="startTime"></div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 mb-2">
                <div id="endTime"></div>
            </div>
        </div>
    </div>
    <div class="container" id="classMemberContainer"></div>
    <!-- TODO: Add a quick-entry screen for new people <a id="newStudent"><i class="fas fa-plus-circle" aria-hidden="true"></i></a>-->
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/KioskJSOM.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/Kiosk.js"></script>

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
?>
