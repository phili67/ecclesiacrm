<?php

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Service\ConfirmReportService;
use EcclesiaCRM\Utils\OutputUtils;

// Set the page title and include HTML header
$sPageTitle = _("Family Verification");

require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");

$doShowMap = !(empty($family->getLatitude()) && empty($family->getLongitude()));
?>

<!-- Leaflet -->
<link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/external/leaflet/leaflet.css">
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/leaflet/leaflet-src.js"></script>

<div class="row">
    <div id="right-buttons" class="btn-group" role="group">
        <button type="button" id="verify" class="btn btn-sm" data-toggle="modal" data-target="#confirm-Verify" style="margin-top: -40px">
            <div class="btn-txt"><?= _("Confirm") ?></div>
            <i class="fas fa-check fa-5x"></i></button>
    </div>
</div>
<div class="card card-info" id="verifyBox">
    <div class="card-body">
        <img class="img-circle center-block pull-right img-responsive initials-image" width="200" height="200"
             src="data:image/png;base64,<?= base64_encode($family->getPhoto()->getThumbnailBytes()) ?>">
        <h2><?= $family->getName() ?></h2>
        <div class="text-muted font-bold m-b-xs family-info">
            <?= ConfirmReportService::getFamilyStandardInfos($family) ?>
        </div>
    </div>
    <div class="border-right border-left">
        <?php if ($doShowMap) { ?>
            <section id="map">
                <div id="MyMap"></div>
            </section>
        <?php } ?>
    </div>
    <div class="card card-solid">
        <div class="card-header  border-1">
            <h3 class="card-title"><i class="fas fa-users"></i> <?= _("Family Member(s)") ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($family->getPeopleSorted() as $person) { ?>
                    <div class="col-md-3 col-sm-4">
                        <div class="card card-primary">
                            <div class="card-body box-profile person-container-<?= $person->getId() ?>">
                                <?php $photo = base64_encode($person->getPhoto()->getThumbnailBytes()); ?>
                                <?= ConfirmReportService::getPersonForFamilyStandardInfos($person, $photo) ?>
                                <br/>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                <?php } ?>
            </div>
        </div>
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

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/FamilyVerify.js"></script>

<?php
$sMapProvider = SystemConfig::getValue('sMapProvider');

$lat = str_replace(",", ".", $family->getLatitude());
$lng = str_replace(",", ".", $family->getLongitude());

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
        lat: parseFloat(<?= $lat ?>),
        lng: parseFloat(<?= $lng ?>)
    };
    window.CRM.mapZoom = <?= $iLittleMapZoom ?>;
    window.CRM.iLittleMapZoom = <?= $iLittleMapZoom ?>;
    window.CRM.token = '<?= $realToken ?>';

    $('body,html').css('margin-top','20px');

    initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, '<?= $family->getName() ?>', '', '');
<?php } ?>
    window.CRM.token = '<?= $token->getToken()?>';
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/moment/moment-with-locales.min.js"></script>

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
