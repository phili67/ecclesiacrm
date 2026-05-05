<?php
/*******************************************************************************
 *
 *  filename    : pastoralcareperson.php
 *  last change : 2022-11-24
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;

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


<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <img src="/api/persons/<?= $currentPersonID ?>/photo"
                 class="initials-image profile-user-img img-responsive img-rounded img-circle"
                 style="width:50px; height:50px;">
            <h3 class="card-title mb-0"><i class="fas fa-heart mr-1"></i><?= _("Pastoral Care Notes") ?></h3>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <div class="btn-group" role="group">
                <?php
                foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
                    $type_and_desc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? " (" . $ormPastoralTypeCare->getDesc() . ")" : "");
                    ?>
                    <button type="button" class="btn btn-sm btn-primary newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>"
                       data-visible="<?= ($ormPastoralTypeCare->getVisible()) ? 1 : 0 ?>"
                       data-typeDesc="<?= $type_and_desc ?>">
                        <i class="fas fa-plus mr-1"></i><?= _("Add Notes") ?>
                    </button>
                    <?php
                    break;
                }
                ?>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Menu déroulant</span>
                </button>
                <div class="dropdown-menu dropdown-menu-left">
                    <?php
                    foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
                        $type_and_desc = $ormPastoralTypeCare->getTitle() . ((!empty($ormPastoralTypeCare->getDesc())) ? " (" . $ormPastoralTypeCare->getDesc() . ")" : "");
                        ?>
                        <a class="dropdown-item newPastorCare" href="#" data-typeid="<?= $ormPastoralTypeCare->getId() ?>"
                           data-visible="<?= ($ormPastoralTypeCare->getVisible()) ? 1 : 0 ?>"
                           data-typeDesc="<?= $type_and_desc ?>">
                            <i class="fas fa-check mr-2"></i><?= $type_and_desc ?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/pastoralcare/person/print/<?= $currentPersonID ?>">
                <i class="fas fa-print mr-1"></i><?= _("Print") ?>
            </a>
            <button type="button" class="btn btn-sm btn-warning" id="add-event" data-toggle="tooltip" data-placement="bottom" title="<?= _("Create an appointment") ?>">
                <i class="far fa-calendar-plus mr-1"></i><?= _("Appointment") ?>
            </button>
            <div class="btn-group ml-auto" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary filterByPastor" data-pastorid="<?= SessionUser::getUser()->getPerson()->getId() ?>">
                    <i class="fas fa-user mr-1"></i><?= SessionUser::getUser()->getPerson()->getFullName() ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Menu déroulant</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item filterByPastorAll" href="#">
                        <i class="fas fa-users mr-2"></i><?= _("Everyone") ?>
                    </a>
                    <?php
                    foreach ($ormPastors as $ormPastor) {
                        ?>
                        <a class="dropdown-item filterByPastor" href="#"
                           data-pastorid="<?= $ormPastor->getPastorId() ?>">
                            <i class="fas fa-user mr-2"></i><?= $ormPastor->getPastorName() ?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <?php
                if ($person->getId() == SessionUser::getUser()->getPersonId() || $person->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isEditRecordsEnabled()) {
                    ?>
                    <p class="text-muted text-center">
                        <?php
                        if ($person->isMale()) {
                            ?>
                            <i class="fas fa-male fa-2x"></i>
                            <?php
                        } elseif ($person->isFemale()) {
                            ?>
                            <i class="fas fa-female fa-2x"></i>
                            <?php
                        }
                        ?> <?= empty($person->getFamilyRoleName()) ? _('Undefined') : _($person->getFamilyRoleName()); ?>
                    </p>
                    <?php
                }
                if ($person->getMembershipDate()) {
                    ?>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><?= _('Membership Date') ?></b> <a class="float-right"><?= OutputUtils::FormatDate($person->getMembershipDate()->format('Y-m-d'), false) ?></a>
                        </li>
                        <?php if (!is_null($person->getFriendDate()) and $person->getFriendDate()->format('Y-m-d') != '1900-01-01'): ?>
                        <li class="list-group-item">
                            <b><?= _('Friend Date') ?></b> <a class="float-right"><?= OutputUtils::FormatDate($person->getFriendDate()->format('Y-m-d'), false) ?></a>
                        </li>
                        <?php endif ?>  
                        <?php
                        if (!empty($person->getClassIcon())) {
                            ?>
                            <li class="list-group-item">
                                <b><img
                                        src="<?= SystemURLs::getRootPath() . "/skin/icons/markers/" . $person->getClassIcon() ?>"
                                        width="18" alt="">
                                    <?= _($person->getClassName()) ?>
                                </b>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <?php
                }
                ?>
                <h5><?= _("Groups") ?></h5>
                <ul class="list-group list-group-unbordered mb-3">
                    <?php
                    foreach ($ormAssignedGroups
                             as $groupAssigment) {
                        ?>
                        <li class="list-group-item">
                            <b>
                                <i class="fas fa-users"></i> <a
                                    href="<?= SystemURLs::getRootPath() ?>/v2/group/<?= $groupAssigment->getGroupId() ?>/view"><?= $groupAssigment->getGroupName() ?></a>
                            </b>

                            <div class="float-right">
                                <?= _($groupAssigment->getRoleName()) ?>
                            </div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
                if ($bOkToEdit) {
                    ?>
                    <a href="<?= SystemURLs::getRootPath() ?>/v2/people/person/editor/<?= $person->getId() ?>"
                       class="btn btn-primary btn-block"><b><?php echo _('Edit'); ?></b></a>
                    <?php
                }
                ?>
            </div>
            <!-- /.card-body -->
        </div>
        <div class="card direct-chat direct-chat-warning">
            <div class="card-header">
                <h3 class="card-title"><?= _("Informations") ?></h3>
                <div class="card-tools">
                    <?php if (!is_null($family) and count($family->getActivatedPeople()) > 1) { ?>
                        <button type="button" class="btn btn-tool" title="<?= _("View Family Members") ?>" data-widget="chat-pane-toggle"
                                data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= _("View Family Members") ?>">
                            <i class="fas fa-users"></i>
                        </button>
                    <?php } ?>
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
                        if (count($person->getOtherFamilyMembers()) > 0) {
                            ?>
                            <li style="left:-28px"><strong><i class="fa-solid fa-people-roof"></i> <?php echo _('Family:'); ?></strong>
                                <span>
            <?php
            if (!is_null($person->getFamily()) && $person->getFamily()->getId() != '') {
                ?>
                <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/family/<?= $person->getFamily()->getId() ?>"><?= $person->getFamily()->getName() ?> </a>
                <?php
            } else {
                ?>
                <?= _('(No assigned family)') ?>
                <?php
            }
            ?>
            </span>
                            </li>
                            <?php
                        }

                        if (!empty($formattedMailingAddress)) {
                            ?>
                            <li>
                                <strong>
                                    <i class="fa-li fas fa-home"></i><?php echo _('Address'); ?>:
                                </strong>
                                <span>
                                    <?= OutputUtils::GetLinkMapFromAddress($plaintextMailingAddress) ?>
                                </span>
                                <?php if ($location_available) { ?>
                                    <div id="MyMap" style="height: 100%;min-height:200px"></div>
                                <?php } ?>
                            </li>
                            <?php
                        }

                        if ($dBirthDate) {
                            ?>
                            <li>
                                <strong><i class="fa-li fas fa-calendar"></i><?= _('Birth Date') ?></strong>:
                                <br>
                                <p class="text-muted"><?= $dBirthDate ?>
                                    <?php
                                    if (!$person->hideAge()) {
                                        ?>
                                        (<span
                                            data-birth-date="<?= $person->getBirthDate()->format('Y-m-d') ?>"></span> <?= OutputUtils::FormatAgeSuffix($person->getBirthDate(), $person->getFlags()) ?>)
                                        <?php
                                    }
                                    ?>
                                </p>
                            </li>
                            <?php
                        }
                        if (!SystemConfig::getValue('bHideFriendDate') && $person->getFriendDate() != '') { /* Friend Date can be hidden - General Settings */
                            ?>
                            <li><strong><i class="fa-li fas fa-tasks"></i><?= _('Friend Date') ?>:</strong>
                                <span><?= OutputUtils::FormatDate($person->getFriendDate()->format('Y-m-d'), false) ?></span>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <hr/>
                    <ul class="fa-ul">
                        <?php

                        if ($sCellPhone) {
                            ?>
                            <li><strong><i class="fa-li fas fa-mobile"></i><?= _('Mobile Phone') ?>:</strong>
                                <span><a
                                        href="tel:<?= $sCellPhoneUnformatted ?>"><?= $sCellPhone ?></a></span></li>
                            <li><strong><i class="fa-li fas fa-mobile"></i><?= _('Text Message') ?>:</strong>
                                <span><a
                                        href="sms:<?= str_replace(' ', '', $sCellPhoneUnformatted) ?>&body=<?= _("CRM text message") ?>"><?= $sCellPhone ?></a></span>
                            </li>
                            <?php
                        }

                        if ($sHomePhone) {
                            ?>
                            <li><strong><i class="fa-li fas fa-phone"></i><?= _('Home Phone') ?>:</strong> <span><a
                                        href="tel:<?= $sHomePhoneUnformatted ?>"><?= $sHomePhone ?></a></span></li>
                            <?php
                        }

                        if (!SystemConfig::getBooleanValue("bHideFamilyNewsletter")) { /* Newsletter can be hidden - General Settings */
                            ?>
                            <li><strong><i class="fa-li fab fa-hacker-news"></i><?= _("Send Newsletter") ?>:</strong>
                                <span id="NewsLetterSend"></span>
                            </li>
                            <?php
                        }
                        if ($sEmail != '') {
                            ?>
                            <li><strong><i class="fa-li far fa-envelope"></i><?= _('Email') ?>:</strong> <span><a
                                        href="mailto:<?= $sUnformattedEmail ?>" target="_blank"><?= $sEmail ?></a></span></li>
                            <?php
                            if ($isMailChimpActive) {
                                ?>
                                <li><strong><i class="fa-li fas fa-paper-plane"></i>MailChimp:</strong> <span
                                        id="mailChimpUserNormal"></span>
                                </li>
                                <?php
                            }
                        }

                        if ($sWorkPhone) {
                            ?>
                            <li><strong><i class="fa-li fas fa-phone"></i><?= _('Work Phone') ?>:</strong> <span><a
                                        href="tel:<?= $sWorkPhoneUnformatted ?>"><?= $sWorkPhone ?></a></span></li>
                            <?php
                        }

                        if ($person->getWorkEmail() != '') {
                            ?>
                            <li><strong><i class="fa-li far fa-envelope"></i><?= _('Work/Other Email') ?>:</strong>
                                <span><a
                                        href="mailto:<?= $person->getWorkEmail() ?>" target="_blank"><?= $person->getWorkEmail() ?></a></span>
                            </li>
                            <?php
                            if ($isMailChimpActive) {
                                ?>
                                <li><i class="fa-li fas fa-paper-plane"></i>MailChimp: <span id="mailChimpUserWork"></span>
                                </li>
                                <?php
                            }
                        }

                        if ($person->getFacebookID() > 0) {
                            ?>
                            <li><strong><i class="fa-li fab fa-facebook"></i><?= _('Facebook') ?>:</strong>
                                <span><a
                                        href="https://www.facebook.com/<?= InputUtils::FilterInt($person->getFacebookID()) ?>"><?= _('Facebook') ?></a></span>
                            </li>
                            <?php
                        }

                        if (strlen($person->getTwitter()) > 0) {
                            ?>
                            <li><strong><i class="fa-li fas fa-twitter"></i><?= _('Twitter') ?>:</strong> <span><a
                                        href="https://www.twitter.com/<?= InputUtils::FilterString($person->getTwitter()) ?>"><?= _('Twitter') ?></a></span>
                            </li>
                            <?php
                        }

                        if (strlen($person->getLinkedIn()) > 0) {
                            ?>
                            <li><strong><i class="fa-li fab fa-linkedin"></i><?= _('LinkedIn') ?>:</strong> <span><a
                                        href="https://www.linkedin.com/in/<?= InputUtils::FiltersTring($person->getLinkedIn()) ?>"><?= _('LinkedIn') ?></a></span>
                            </li>
                            <?php
                        }

                        } else {
                            ?>
                            <?= _("Private Data") ?>
                            <?php
                        }// end of $can_see_privatedata

                        ?>

                    </ul>
                    <hr/>
                    <ul class="fa-ul">

                        <?php

                        // Display the right-side custom fields
                        foreach ($ormPersonCustomFields as $rowCustomField) {
                            if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
                                $currentData = trim($aCustomData[$rowCustomField->getCustomField()]);
                                if ($currentData != '') {
                                    if ($rowCustomField->getTypeId() == 11) {
                                        $custom_Special = $sPhoneCountry;
                                    } else {
                                        $custom_Special = $rowCustomField->getCustomSpecial();
                                    }

                                    echo '<li><strong><i class="fa-li ' . (($rowCustomField->getTypeId() == 11) ? 'fas fa-phone' : 'fas fa-tag') . '"></i>' . $rowCustomField->getCustomName() . ':</strong> <span>';
                                    $temp_string = nl2br(OutputUtils::displayCustomField($rowCustomField->getTypeId(), $currentData, $custom_Special));
                                    echo $temp_string;
                                    echo '</span></li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>                
                <!-- /.direct-chat-pane -->
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <?php
        if ($ormPastoralCares->count() > 0) {
            ?>
            <div class="timeline">
                <!-- timeline time label -->
                <div class="time-label">
        <span class="bg-red rounded-pill">
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
                  <span class="time">
                      <i class="fas fa-clock"></i> <?= $ormPastoralCare->getDate()->format($sDateFormatLong . ' H:i:s') ?>
                  </span>

                            <h3 class="timeline-header">
                                <b><?= $ormPastoralCare->getPastoralCareType()->getTitle() . "</b>  : " ?><a
                                        href="<?= $sRootPath . "/v2/people/person/view/" . $ormPastoralCare->getPastorId() ?>"><?= $ormPastoralCare->getPastorName() ?></a>
                            </h3>
                            <div class="timeline-body">
                                <?php if ($ormPastoralCare->getVisible() or $ormPastoralCare->getPastorId() == $currentPastorId): ?>
                                    <?= $ormPastoralCare->getText() ?>
                                <?php else: ?>
                                    <?= _("Private Data") ?>
                                <?php endif; ?>
                            </div>
                            <?php
                            if ($ormPastoralCare->getPastorId() == $currentPastorId) {
                                ?>
                                <div class="timeline-footer">
                                    <a class="btn btn-sm btn-primary modify-pastoral"
                                       data-id="<?= $ormPastoralCare->getId() ?>">
                                        <i class="fas fa-edit mr-1"></i><?= _("Modify") ?>
                                    </a>
                                    <a class="btn btn-sm btn-danger delete-pastoral"
                                       data-id="<?= $ormPastoralCare->getId() ?>">
                                        <i class="fas fa-trash-alt mr-1"></i><?= _("Delete") ?>
                                    </a>
                                </div>
                                <?php
                            } elseif (SessionUser::isAdmin()) {
                                ?>
                                <div class="timeline-footer">
                                    <a class="btn btn-sm btn-danger delete-pastoral"
                                       data-id="<?= $ormPastoralCare->getId() ?>">
                                        <i class="fas fa-trash-alt mr-1"></i><?= _("Delete") ?>
                                    </a>
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
        <div class="text-center mt-4 pt-3 border-top">
            <a class="btn btn-success" href="<?= $sRootPath . '/v2/people/person/view/' . $currentPersonID ?>">
                <i class="fas fa-arrow-left mr-1"></i><?= _('Return to Person View') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= $sRootPath ?>/v2/pastoralcare/dashboard">
                <i class="fas fa-home mr-1"></i><?= _('Dashboard') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= $sRootPath ?>/v2/pastoralcare/membersList">
                <i class="fas fa-list mr-1"></i><?= _('Members List') ?>
            </a>
        </div>
        <br/>
    </div>
    <div class="col-md-3">
        <!-- Contacts are loaded here -->
        <div class="card direct-chat direct-chat-warning  card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-people-roof"></i> <?= _("Family Members") ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <!-- Contacts are loaded here -->
                <?php if (!is_null($family) and count($family->getActivatedPeople()) > 1) : ?>
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
                            if ($person->getId() == $currentPersonID) continue;
                            ?>
                            <tr>
                                <td>
                                    <?= $person->getJPGPhotoDatas() ?>
                                    <a href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcare/person/<?= $person->getId() ?>"
                                        class="user-link"><?= $person->getFullName() ?> </a>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $famRole = $person->getFamilyRoleName();
                                    $labelColor = 'secondary';
                                    if ($famRole == _('Head of Household')) {
                                    } elseif ($famRole == _('Spouse')) {
                                        $labelColor = 'info';
                                    } elseif ($famRole == _('Child')) {
                                        $labelColor = 'warning';
                                    }
                                    ?>
                                    <span class='badge badge-<?= $labelColor ?>'> <?= $famRole ?></span>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <?= _("None") ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- /.direct-chat-pane -->
            </div>
        </div>
    </div>
</div>


<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $sCSPNonce ?>">
    var currentPersonID = <?= $currentPersonID ?>;
    var currentFamilyID = <?= (!is_null($person->getFamily()) && $person->getFamily()->getId() != '') ? (int)$person->getFamily()->getId() : 0 ?>;
    var currentPastorId = <?= $currentPastorId ?>;
    var sPageTitle = "<?= str_replace('"', "'", $sPageTitle) ?>";

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
    };
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCareBootboxContent.js"></script>
<script src="<?= $sRootPath ?>/skin/js/pastoralcare/PastoralCarePerson.js"></script>
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
}
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">

    <?php if ($location_available){ ?>
    // location and MAP
    window.CRM.churchloc = {
        lat: parseFloat(<?= $lat ?>),
        lng: parseFloat(<?= $lng ?>)
    };
    window.CRM.mapZoom = <?= $iLittleMapZoom ?>;

    initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, 'titre', "<?= str_replace('"', "'", $person->getFullName()) ?>", '');
    <?php } ?>
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
