<?php
/*******************************************************************************
 *
 *  filename    : pastoralcarefamily.php
 *  last change : 2020-01-03
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\Utils\GeoUtils;

$family = \EcclesiaCRM\FamilyQuery::create()->findOneById($currentFamilyID);

require $sRootDocument . '/Include/Header.php';
?>

<?php
if ($ormPastoralCares->count() == 0) {
    ?>
    <div class="alert alert-info"><?= _("Please add some records with the button below.") ?></div>

    <?php
}
$sFamilyEmails = [];
?>

<br/>
<div class="margin">
    <img src="/api/families/<?= $currentFamilyID ?>/photo"
         class="initials-image profile-user-img img-responsive img-rounded img-circle"
         style="width:70px; height:70px;display:inline-block">
    <div class="btn-group">
        <?php
        foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
            $type_and_desc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? " (" . $ormPastoralTypeCare->getDesc() . ")" : "");
            ?>
            <a class="btn btn-app newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>"
               data-visible="<?= ($ormPastoralTypeCare->getVisible()) ? 1 : 0 ?>"
               data-typeDesc="<?= $type_and_desc ?>"><i
                    class="fas fa-sticky-note"></i><?= _("Add Pastoral Care Notes") ?></a>
            <?php
            break;
        }
        ?>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
            <span class="sr-only">Menu déroulant</span>
        </button>
        <div class="dropdown-menu" role="menu">
            <?php
            foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
                $type_and_desc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? " (" . $ormPastoralTypeCare->getDesc() . ")" : "");
                ?>
                <a class="dropdown-item newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>"
                   data-visible="<?= ($ormPastoralTypeCare->getVisible()) ? 1 : 0 ?>"
                   data-typeDesc="<?= $type_and_desc ?>"><?= $type_and_desc ?></a>
                <?php
            }
            ?>
        </div>
        &nbsp;
        <a class="btn btn-app bg-orange" id="add-event"><i class="far fa-calendar-plus"></i><?= _("Appointment") ?>
        </a>
    </div>
    <!--<a class="btn btn-app" href="<?= $sRootPath ?>/PrintPastoralCare.php?PersonID=<?= $currentFamilyID ?>"><i class="fas fa-print"></i> <?= _("Printable Page") ?></a>-->

    <div class="btn-group pull-right">
        <a class="btn btn-app filterByPastor" data-pastorId="<?= SessionUser::getUser()->getPerson()->getId() ?>"><i
                class="fas fa-sticky-note"></i><?= SessionUser::getUser()->getPerson()->getFullName() ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
            <span class="sr-only">Menu déroulant</span>
        </button>
        <div class="dropdown-menu" role="menu">
            <li><a class="dropdown-item filterByPastorAll"><?= _("Everyone") ?></a></li>
            <?php
            foreach ($ormPastors as $ormPastor) {
                ?>
                <a class="dropdown-item filterByPastor"
                   data-pastorId="<?= $ormPastor->getPastorId() ?>"><?= $ormPastor->getPastorName() ?></a>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="pull-right" style="margin-right:15px;margin-top:10px">
        <h4><?= _("Filters") ?></h4>
    </div>
</div>
<br/>

<div class="row">
    <div class="col-md-3">
        <div class="card direct-chat direct-chat-warning  card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><?= _("Informations") ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" title="<?= _("View Family Members") ?>" data-widget="chat-pane-toggle"
                            data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= _("View Family Members") ?>">
                        <i class="fas fa-users"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <!-- Conversations are loaded here -->
                <div class="direct-chat-messages" style="height: 100%;min-height:200px">

                    <ul class="fa-ul">
                        <?php
                        if ($can_see_privatedata) {
                        ?>
                        <li><strong><i class="fa-li fas fa-home"></i><?= _("Address") ?>:</strong>
                            <span>
                                <?= OutputUtils::GetLinkMapFromAddress($family->getAddress()) ?>
                                <?php if ($location_available) { ?>
                                    <div id="MyMap" style="height: 100%;min-height:200px"></div>
                                <?php } ?>
                            </span>
                            <br>

                            <?php
                            if ($family->getLatitude() && $family->getLongitude()) {
                                if (SystemConfig::getValue("iChurchLatitude") && SystemConfig::getValue("iChurchLongitude")) {
                                    $sDistance = GeoUtils::LatLonDistance(SystemConfig::getValue("iChurchLatitude"), SystemConfig::getValue("iChurchLongitude"), $family->getLatitude(), $family->getLongitude());
                                    $sDirection = GeoUtils::LatLonBearing(SystemConfig::getValue("iChurchLatitude"), SystemConfig::getValue("iChurchLongitude"), $family->getLatitude(), $family->getLongitude());
                                    echo OutputUtils::number_localized($sDistance) . " " . _(strtolower(SystemConfig::getValue("sDistanceUnit"))) . " " . _($sDirection) . " " . _(" of church<br>");
                                }
                            } else {
                                $bHideLatLon = true;
                            }
                            ?>
                            <?php
                            if (!$bHideLatLon && !SystemConfig::getBooleanValue('bHideLatLon')) { /* Lat/Lon can be hidden - General Settings */ ?>
                        <li><strong><i class="fa-li far fa-compass"></i><?= _("Latitude/Longitude") ?></strong>
                            <span><?= $family->getLatitude() . " / " . $family->getLongitude() ?></span>
                        </li>
                    <?php
                    }
                    ?>
                    </ul>
                    <hr/>
                    <ul class="fa-ul">
                        <?php
                        if (!SystemConfig::getBooleanValue("bHideFamilyNewsletter")) { /* Newsletter can be hidden - General Settings */
                            ?>
                            <li><strong><i class="fa-li fab fa-hacker-news"></i><?= _("Send Newsletter") ?>:</strong>
                                <span id="NewsLetterSend"></span>
                            </li>
                            <?php
                        }

                        if (!SystemConfig::getBooleanValue("bHideWeddingDate") && $family->getWeddingdate() != "") { /* Wedding Date can be hidden - General Settings */
                            ?>
                            <li>
                                <strong><i class="fa-li fas fa-magic"></i><?= _("Wedding Date") ?>:</strong>
                                <span><?= OutputUtils::FormatDate($family->getWeddingdate()->format('Y-m-d'), false) ?></span>
                            </li>
                            <?php
                        }
                        if (SystemConfig::getBooleanValue("bUseDonationEnvelopes")) {
                            ?>
                            <li><strong><i class="fa-li fas fa-phone"></i><?= _("Envelope Number") ?> : </strong>
                                <span><?= $family->getEnvelope() ?></span>
                            </li>
                            <?php
                        }
                        if ($sHomePhone != "") {
                            ?>
                            <li><strong><i class="fa-li fas fa-phone"></i><?= _("Home Phone") ?>:</strong> <span><a
                                        href="tel:<?= $sHomePhone ?>"><?= $sHomePhone ?></a></span></li>
                            <?php
                        }
                        if ($sWorkPhone != "") {
                            ?>
                            <li><strong><i class="fa-li fas fa-building"></i><?= _("Work Phone") ?>:</strong> <span>
          <a href="tel:<?= $sWorkPhone ?>"><?= $sWorkPhone ?></a></span>
                            </li>
                            <?php
                        }
                        if ($sCellPhone != "") {
                            ?>
                            <li><strong><i class="fa-li fas fa-mobile"></i><?= _("Mobile Phone") ?>:</strong> <span><a
                                        href="tel:<?= $sCellPhone ?>"><?= $sCellPhone ?></a></span></li>
                            <li><strong><i class="fa-li fas fa-mobile"></i><?= _('Text Message') ?>:
                                </strong><span><a
                                        href="sms:<?= $sCellPhone ?>&body=<?= _("EcclesiaCRM text message") ?>"><?= $sCellPhone ?></a></span>
                            </li>

                            <?php
                        }
                        if ($family->getEmail() != "") {
                            ?>
                            <li><strong><i class="fa-li far fa-envelope"></i><?= _("Email") ?>:</strong>
                                <a href="mailto:<?= $family->getEmail() ?>" target="_blank"><span><?= $family->getEmail() ?></span></a>
                            </li>
                            <?php
                            if ($mailchimp->isActive()) {
                                ?>
                                <li><strong><i class="fa-li fas fa-paper-plane"></i><?= _("MailChimp") ?>:</strong>
                                    <span id="mailChimpUserNormal"></span>
                                </li>
                                <?php
                            }
                        }

                        } else {
                            ?>
                            <?= _("Private Data") ?>
                            <?php
                        }// end of can_see_privatedata
                        ?>
                    </ul>
                    <hr/>
                    <ul class="fa-ul">
                        <?php

                        // Display the left-side custom fields
                        foreach ($ormFamCustomFields as $rowCustomField) {
                            if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
                                $currentData = trim($aFamCustomDataArr[$rowCustomField->getCustomField()]);

                                if (empty($currentData)) continue;

                                if ($rowCustomField->getTypeId() == 11) {
                                    $fam_custom_Special = $sPhoneCountry;
                                } else {
                                    $fam_custom_Special = $rowCustomField->getCustomSpecial();
                                }
                                ?>
                                <li><strong><i class="fa-li fas fa-tag"></i>
                                        <?= $rowCustomField->getCustomName() ?>:</strong>
                                    <span><?= OutputUtils::displayCustomField($rowCustomField->getTypeId(), $currentData, $fam_custom_Special) ?>
            </span>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>


                </div>
                <!--/.direct-chat-messages-->

                <!-- Contacts are loaded here -->
                <div class="direct-chat-contacts" style="height: 100%;padding: 10px">
                    <?php if (count($family->getActivatedPeople()) > 1) { ?>
                        <br>
                        <h3 class="card-title">
                            <?= _("Family Members") ?>
                        </h3>
                        <table class="table table-hover" width="100%">
                            <thead>
                            <tr>
                                <th><span><?= _("Members") ?></span></th>
                                <th class="text-center"><span><?= _("Role") ?></span></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($family->getActivatedPeople() as $person) {
                                ?>
                                <tr>
                                    <td>
                                        <img
                                            src="<?= SystemURLs::getRootPath() ?>/api/persons/<?= $person->getId() ?>/thumbnail"
                                            width="40" height="40"
                                            class="initials-image img-circle"/>
                                        <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/person/<?= $person->getId() ?>"
                                           class="user-link"><?= $person->getFullName() ?> </a>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $famRole = $person->getFamilyRoleName();
                                        $labelColor = 'label-default';
                                        if ($famRole == _('Head of Household')) {
                                        } elseif ($famRole == _('Spouse')) {
                                            $labelColor = 'label-info';
                                        } elseif ($famRole == _('Child')) {
                                            $labelColor = 'label-warning';
                                        }
                                        ?>
                                        <span class='label <?= $labelColor ?>'> <?= $famRole ?></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>

                    <?php } ?>
                </div>
                <!-- /.direct-chat-pane -->
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <?php
        if ($ormPastoralCares->count() > 0) {
            ?>
            <div class="timeline">
                <!-- timeline time label -->
                <div class="time-label">
        <span class="bg-red">
          <?= (new DateTime(''))->format($sDateFormatLong) ?>
        </span>
                </div>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <?php
                foreach ($ormPastoralCares as $ormPastoralCare) {
                    ?>
                    <div class="item-<?= $ormPastoralCare->getPastorId() ?> all-items">
                        <i class="fas fa-clock bg-blue"></i>
                        <div class="timeline-item">
                    <span class="time"><i
                            class="fas fa-clock"></i> <?= $ormPastoralCare->getDate()->format($sDateFormatLong . ' H:i:s') ?></span>

                            <h3 class="timeline-header">
                                <b><?= $ormPastoralCare->getPastoralCareType()->getTitle() . "</b>  : " ?><a
                                        href="<?= $sRootPath . "/PersonView.php?PersonID=" . $ormPastoralCare->getPastorId() ?>"><?= $ormPastoralCare->getPastorName() ?></a>
                            </h3>
                            <div class="timeline-body">
                                <?php if ($ormPastoralCare->getVisible()) {
                                    echo $ormPastoralCare->getText();
                                }
                                ?>
                            </div>

                            <?php
                            if ($ormPastoralCare->getPastorId() == $currentPastorId) {
                                ?>
                                <div class="timeline-footer">
                                    <a class="btn btn-primary btn-xs modify-pastoral"
                                       data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Modify") ?></a>
                                    <a class="btn btn-danger btn-xs delete-pastoral"
                                       data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Delete") ?></a>
                                </div>
                                <?php
                            } elseif (SessionUser::getUser()->isAdmin()) {
                                ?>
                                <div class="timeline-footer">
                                    <a class="btn btn-danger btn-xs delete-pastoral"
                                       data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Delete") ?></a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <!-- END timeline item -->
                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            </div>

            <?php
        } else {
            ?>
            <div class="alert alert-warning"><i class="fas fa-ban"></i> <?= _("None") ?></div>
        <?php
        }
        ?>
        <div class="text-center">
            <input type="button" class="btn btn-success" value="<?= _('Return to Family View') ?>" name="Cancel"
                   onclick="javascript:document.location='<?= $sRootPath . '/FamilyView.php?FamilyID=' . $currentFamilyID ?>';">

            <input type="button" class="btn btn-default" value="<?= _('Return To PastoralCare Dashboard') ?>"
                   name="Cancel"
                   onclick="javascript:document.location='<?= $sRootPath ?>/v2/pastoralcare/dashboard';">

            <input type="button" class="btn btn-default" value="<?= _('Return To PastoralCare Members List') ?>"
                   name="Cancel"
                   onclick="javascript:document.location='<?= $sRootPath ?>/v2/pastoralcare/membersList';">
        </div>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $sCSPNonce ?>">
    var currentFamilyID = <?= $currentFamilyID ?>;
    var currentPastorId = <?= $currentPastorId ?>;
    var sPageTitle = '<?= $sPageTitle ?>';

    window.CRM.churchloc = {
        lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>
    };
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCareFamily.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>

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

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    <?php if ($location_available){ ?>
    // location and MAP
    window.CRM.churchloc = {
        lat: <?= $lat ?>,
        lng: <?= $lng ?>
    };
    window.CRM.mapZoom = <?= $iLittleMapZoom ?>;

    initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, '<?= $family->getName() ?>', '', '');
    <?php } ?>
</script>
