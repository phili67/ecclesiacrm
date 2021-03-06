<?php

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
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
        <button type="button" id="verify" class="btn btn-sm" data-toggle="modal" data-target="#confirm-Verify">
            <div class="btn-txt"><?= _("Confirm") ?></div>
            <i class="fa fa-check fa-5x"></i></button>
    </div>
</div>
<div class="card card-info" id="verifyBox">
    <div class="card-body">
        <img class="img-circle center-block pull-right img-responsive initials-image" width="200" height="200"
             src="data:image/png;base64,<?= base64_encode($family->getPhoto()->getThumbnailBytes()) ?>">
        <h2><?= $family->getName() ?></h2>
        <div class="text-muted font-bold m-b-xs">
            <i class="fa fa-fw fa-map-marker" title="<?= _("Home Address") ?>"></i><?= $family->getAddress() ?><br/>
            <?php if (!empty($family->getHomePhone())) { ?>
                <i class="fa fa-fw fa-phone" title="<?= _("Home Phone") ?>"> </i>(H) <?= $family->getHomePhone() ?><br/>
            <?php }
            if (!empty($family->getEmail())) { ?>
            <i class="fa fa-fw fa-envelope" title="<?= _("Family Email") ?>"></i><?= $family->getEmail() ?><br/>
                <?php
            }
            if ($family->getWeddingDate() !== null) {
                ?>
            <i class="fa fa-fw fa-heart"
               title="<?= _("Wedding Date") ?>"></i><?= $family->getWeddingDate()->format(SystemConfig::getValue("sDateFormatLong")) ?>
                <br/>
                <?php
            }
            ?>

            <i class="fa fa-fw fa-newspaper-o"
               title="<?= _("Send Newsletter") ?>"></i><?= _($family->getSendNewsletter()) ?><br/>
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
        <div class="card-header">
            <h3 class="card-title"><i class="fa fa-users"></i> <?= _("Family Member(s)") ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($family->getPeopleSorted() as $person) { ?>
                    <div class="col-md-3 col-sm-4">
                        <div class="card card-primary">
                            <div class="card-body box-profile">
                                <img class="profile-user-img img-responsive img-circle initials-image"
                                     src="data:image/png;base64,<?= base64_encode($person->getPhoto()->getThumbnailBytes()) ?>">

                                <h3 class="profile-username text-center"><?= $person->getFullName() ?></h3>

                                <p class="text-muted text-center"><i
                                        class="fa fa-fw fa-<?= ($person->isMale() ? "male" : "female") ?>"></i> <?= $person->getFamilyRoleName() ?>
                                </p>

                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item">
                                        <?php if (!empty($person->getHomePhone())) { ?>
                                            <i class="fa fa-fw fa-phone"
                                               title="<?= _("Home Phone") ?>"></i>(H) <?= $person->getHomePhone() ?>
                                            <br/>
                                        <?php }
                                        if (!empty($person->getWorkPhone())) { ?>
                                            <i class="fa fa-fw fa-briefcase"
                                               title="<?= _("Work Phone") ?>"></i>(W) <?= $person->getWorkPhone() ?>
                                            <br/>
                                        <?php }
                                        if (!empty($person->getCellPhone())) { ?>
                                            <i class="fa fa-fw fa-mobile"
                                               title="<?= _("Mobile Phone") ?>"></i>(M) <?= $person->getCellPhone() ?>
                                            <br/>
                                        <?php }
                                        if (!empty($person->getEmail())) { ?>
                                            <i class="fa fa-fw fa-envelope"
                                               title="<?= _("Email") ?>"></i>(H) <?= $person->getEmail() ?><br/>
                                        <?php }
                                        if (!empty($person->getWorkEmail())) { ?>
                                            <i class="fa fa-fw fa-envelope-o"
                                               title="<?= _("Work Email") ?>"></i>(W) <?= $person->getWorkEmail() ?>
                                            <br/>
                                        <?php } ?>
                                        <i class="fa fa-fw fa-birthday-cake" title="<?= _("Birthday") ?>"></i>
                                        <?php

                                        if ($person->hideAge()) {
                                            $birthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', 0);
                                            ?>
                                            <?= $birthDate ?>
                                            <i class="fa fa-fw fa-eye-slash" title="<?= _("Age Hidden") ?>"></i>
                                            <?php
                                        } else {
                                            $birthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', 0);
                                            ?>
                                            <?= $birthDate ?>
                                            <?php
                                        }
                                        ?>
                                        <br/>
                                    </li>
                                    <li class="list-group-item">
                                        <?php
                                        $classification = "";
                                        $cls = ListOptionQuery::create()->filterById(1)->filterByOptionId($person->getClsId())->findOne();
                                        if (!empty($cls)) {
                                            $classification = $cls->getOptionName();
                                        }
                                        ?>
                                        <b>Classification:</b> <?= $classification ?>
                                    </li>
                                    <?php if (count($person->getPerson2group2roleP2g2rs()) > 0) { ?>
                                        <li class="list-group-item">
                                            <h4><?= _("Groups") ?></h4>
                                            <?php foreach ($person->getPerson2group2roleP2g2rs() as $groupMembership) {
                                                if ($groupMembership->getGroup() != null) {
                                                    $listOption = ListOptionQuery::create()->filterById($groupMembership->getGroup()->getRoleListId())->filterByOptionId($groupMembership->getRoleId())->findOne()->getOptionName();
                                                    ?>
                                                    <b><?= $groupMembership->getGroup()->getName() ?></b>: <span
                                                        class="pull-right"><?= _($listOption) ?></span><br/>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </li>
                                    <?php } ?>
                                </ul>
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
                <textarea id="confirm-info-data" class="form-control" rows="10"></textarea>
            </div>

            <div class="modal-body" id="confirm-modal-done">
                <p><?= _("Your verification request is complete") ?></p>
            </div>

            <div class="modal-body" id="confirm-modal-error">
                <p><?= _("We encountered an error submitting with your verification data") ?></p>
            </div>

            <div class="modal-footer">
                <button id="onlineVerifyCancelBtn" type="button" class="btn btn-default"
                        data-dismiss="modal"><?= _("Cancel") ?></button>
                <button id="onlineVerifyBtn" class="btn btn-success"><?= _("Verify") ?></button>
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
    lat: <?= $lat ?>,
    lng: <?= $lng ?>
    };
    window.CRM.mapZoom = <?= $iLittleMapZoom ?>;
    window.CRM.iLittleMapZoom = <?= $iLittleMapZoom ?>;

    initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, '<?= $family->getName() ?>', '', '');
<?php } ?>
    var token = '<?= $token->getToken()?>';
</script

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
