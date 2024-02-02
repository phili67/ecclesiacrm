<?php

/*******************************************************************************
 *
 *  filename    : ident/templates/verify/verifiy-person-info.php
 *  last change : 2024-01-31 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;

use EcclesiaCRM\Service\ConfirmReportService;

// Set the page title and include HTML header
$sPageTitle = _("Family Verification");

require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");

$doShowMap = !(empty($person->getFamily()->getLatitude()) && empty($person->getFamily()->getLongitude()));

$famAddress1 = $person->getFamily()->getAddress1();
$famAddress2 = $person->getFamily()->getAddress2();
$famCity = $person->getFamily()->getCity();
$famSate = $person->getFamily()->getState();
$famZip = $person->getFamily()->getZip();
$famCountry = $person->getFamily()->getCountry();

?>

<!-- Leaflet -->
<link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/external/leaflet/leaflet.css">
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/leaflet/leaflet-src.js"></script>

<div class="row">
    <div id="right-buttons" class="btn-group" role="group">
        <button type="button" id="verify" class="btn btn-sm" data-toggle="modal" data-target="#confirm-Verify" style="margin-top: -70px">
            <div class="btn-txt"><?= _("Confirm") ?></div>
            <i class="fas fa-check fa-5x"></i></button>
    </div>
</div>
<div class="card card-info" id="verifyBox" style="margin-top:40px">
    <div class="card-body">
        <div class="row">
            <div class="person-container-<?= $person->getId() ?> col-md-5">
                <?php $photo = base64_encode($person->getPhoto()->getThumbnailBytes()); ?>
                <?= ConfirmReportService::getPersonStandardInfos($person, $photo) ?>
            </div>
            <div class="col-md-1">                
            </div>
            <div class="col-md-6">     
                <h3><?= _("Custom Person Fields") ?></h3>
                <div class="person-container-custom-<?= $person->getId() ?>">
                    <?= ConfirmReportService::getPersonCustomFields($person) ?>
                </div>
            </div>
        </div>
        <br>
        <hr/>
        <div class="text-left text-center">
            <button class="btn btn-danger btn-sm deleteFamily" data-id="<?= $person->getId() ?>" style="height: 30px;padding-top: 5px;background-color: red"><i class="fas fa-trash"></i> <?= _("Delete") ?></button>
            <button class="btn btn-sm modifyPerson" data-id="<?= $person->getId() ?>" style="height: 30px;padding-top: 5px;"><i class="fas fa-edit"></i> <?= _("Modify") ?></button>
            <button class="btn btn-success btn-sm exitSession" style="height: 30px;padding-top: 5px;background-color: green"><i class="fas fa-sign-out-alt"></i> <?= _("Exit") ?></button>
        </div>
    </div>
    <div class="border-right border-left">
        <?php if ($doShowMap) { ?>
            <section id="map">
                <div id="MyMap"></div>
            </section>
        <?php } ?>
    </div>
</div>

<div class="modal fade" id="confirm-Verify" tabindex="888" role="dialog" aria-labelledby="Verify-label"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="delete-Image-label"><?= _("Confirm") ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="confirm-modal-collect">
                <p><?= _("Please let us know what information to update if any") ?></p>
                <textarea id="confirm-info-data" class= "form-control form-control-sm" rows="10"></textarea>
            </div>

            <div class="modal-body" id="confirm-modal-done">
                <p><?= _("Your verification request is complete") ?></p>
            </div>

            <div class="modal-body" id="confirm-modal-error">
                <p><?= _("We encountered an error submitting with your verification data") ?></p>
            </div>

            <div class="modal-footer">
                <button id="onlineVerifyCancelBtn" type="button" class="btn btn-default"
                        data-dismiss="modal"><i class="fas fa-times"></i> <?= _("Cancel") ?></button>
                <button id="onlineVerifyBtn" class="btn btn-success"><i class="fas fa-paper-plane"></i> <?= _("Send") ?></button>
                <a href="<?= ChurchMetaData::getChurchWebSite() ?>" id="onlineVerifySiteBtn"
                   class="btn btn-success"><?= _("Visit our Site") ?></a>
            </div>
        </div>
    </div>
</div>


<style>
    #verifyBox {
        padding: 5px;
        width: 80%
    }

    #MyMap {
        height: 300px;
    }

    .btn-sm {
        vertical-align: center;
        position: relative;
        margin: 0px;
        padding: 20px 20px;
        font-size: 4px;
        color: white;
        text-align: center;
        background: #62b1d0;
    }

    .btn-txt {
        font-size: 15px;
    }

    #right-buttons {
        z-index: 999;
        position: fixed;
        left: 45%;
    }

    #success-alert, #error-alert {
        z-index: 888;
    }

    body {
        margin-top: 45px;
    }

</style>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/PersonVerify.js"></script>

<?php
$sMapProvider = SystemConfig::getValue('sMapProvider');

$lat = str_replace(",", ".", $person->getFamily()->getLatitude());
$lng = str_replace(",", ".", $person->getFamily()->getLongitude());

$iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
$sMapProvider = SystemConfig::getValue('sMapProvider');
$sGoogleMapKey = SystemConfig::getValue('sGoogleMapKey');
?>

<?php if ($doShowMap){ ?>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
<?php } ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
<?php if ($doShowMap){ ?>
    // location and MAP
    window.CRM.churchloc = {
    lat: <?= $lat ?>,
    lng: <?= $lng ?>
    };
    window.CRM.mapZoom = <?= $iLittleMapZoom ?>;
    window.CRM.iLittleMapZoom = <?= $iLittleMapZoom ?>;
    window.CRM.token = '<?= $realToken ?>';

    $('body,html').css('margin-top','20px');

    initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, '<?= $person->getLastName(). " " . $person->getFirstName() ?>', '', '');
<?php } ?>
    window.CRM.token = '<?= $token->getToken()?>';
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/moment/moment-with-locales.min.js"></script>

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
