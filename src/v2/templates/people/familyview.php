<?php

/*******************************************************************************
 *
 *  filename    : familyview.php
 *  last change : 2023-05-08
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Utils\GeoUtils;

require $sRootDocument . '/Include/Header.php';

$familyMembers = $family->getActivatedPeople();
$familyMemberCount = count($familyMembers);
$familyEmailCount = 0;
$can_see_privatedata = ($iCurrentUserFamID == $iFamilyID || SessionUser::getUser()->isSeePrivacyDataEnabled());
$familyQuickRoles = [];

foreach ($familyMembers as $familyMember) {
    if (!empty($familyMember->getEmail())) {
        $familyEmailCount++;
    }
}

foreach ($ormFamilyRoles as $ormFamilyRole) {
    if ((int)$ormFamilyRole->getOptionId() > 0) {
        $familyQuickRoles[] = $ormFamilyRole;
    }
}

$sFamilyEmails = [];
?>


<?php if (!is_null($family->getDateDeactivated())) {
?>
    <div class="alert alert-warning">
        <strong><?= _(" This Family is Deactivated") ?> </strong>
    </div>
<?php
} ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="sticky-top">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-body">
                        <div class="text-center">
                            <img src="<?= $sRootPath ?>/api/families/<?= $family->getId() ?>/photo"
                                class="initials-image profile-user-img img-responsive img-rounded img-circle" />
                            <?php
                            if ($bOkToEdit) {
                            ?>
                                <div class="after">
                                    <div class="buttons">
                                        <a class="hide" id="view-larger-image-btn" href="#"
                                            title="<?= _("View Photo") ?>">
                                            <i class="fas fa-search-plus"></i>
                                        </a>&nbsp;
                                        <a href="#" data-toggle="modal" data-target="#upload-image"
                                            title="<?= _("Upload Photo") ?>">
                                            <i class="fas fa-camera"></i>
                                        </a>&nbsp;
                                        <a href="#" data-toggle="modal" data-target="#confirm-delete-image"
                                            title="<?= _("Delete Photo") ?>">
                                            <i class="far fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        <h3 class="profile-username text-center"><?= _('Family') . ': ' . $family->getName() ?></h3>
                        <div class="text-center mb-3">
                            <span class="badge badge-primary mr-1 mb-1"><i class="fas fa-users mr-1"></i><?= $familyMemberCount . ' ' . _('Members') ?></span>
                            <?php if ($familyEmailCount > 0) { ?>
                                <span class="badge badge-light mr-1 mb-1"><i class="far fa-envelope mr-1"></i><?= $familyEmailCount . ' ' . _('Emails') ?></span>
                            <?php } ?>
                            <?php if (!is_null($family->getDateDeactivated())) { ?>
                                <span class="badge badge-warning mb-1"><i class="fas fa-user-slash mr-1"></i><?= _('Deactivated') ?></span>
                            <?php } ?>
                        </div>
                        <?php
                        if ($bOkToEdit) {
                        ?>
                            <a href="<?= $sRootPath ?>/v2/people/family/editor/<?= $family->getId() ?>"
                                class="btn btn-sm btn-outline-primary btn-block"><i class="fas fa-pen mr-1"></i><?= _("Edit") ?></a>
                        <?php
                        }
                        ?>
                        <hr />
                        <?php
                        ?>
                        <ul class="fa-ul mb-0">
                            <?php
                            if ($can_see_privatedata) {
                                $adresses = explode('<br>', $family->getAddress());
                                $count = count($adresses);
                            ?>
                                <li><strong><i class="fa-li fas fa-home"></i><?= _("Address") ?>:<?= $count > 1 ? '<br>' : '' ?></strong>
                                    <span>
                                        <?php foreach ($adresses as $adress) { ?>
                                            <?= $count > 1 ? '•' : '' ?> <?= OutputUtils::GetLinkMapFromAddress($adress) ?><br>
                                        <?php } ?>
                                        <?php if ($location_available) { ?>
                                            <div id="MyMap" style="width:100%"></div>
                                        <?php } ?>
                                    </span>
                                    <br>

                                    <?php
                                    if ($family->getLatitude() && $family->getLongitude()) {
                                        if (SystemConfig::getValue("iEntityLatitude") && SystemConfig::getValue("iEntityLongitude")) {
                                            $sDistance = GeoUtils::LatLonDistance(SystemConfig::getValue("iEntityLatitude"), SystemConfig::getValue("iEntityLongitude"), $family->getLatitude(), $family->getLongitude());
                                            $sDirection = GeoUtils::LatLonBearing(SystemConfig::getValue("iEntityLatitude"), SystemConfig::getValue("iEntityLongitude"), $family->getLatitude(), $family->getLongitude());
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
                        <hr />
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
                                    <span><?= OutputUtils::FormatDateOrUnknown($family->getWeddingdate()) ?></span>
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
                                            href="sms:<?= $sCellPhone ?>&body=<?= _("CRM text message") ?>"><?= $sCellPhone ?></a></span>
                                </li>

                            <?php
                                }
                                if ($family->getEmail() != "") {
                            ?>
                                <li><strong><i class="fa-li far fa-envelope"></i><?= _("Email") ?>:</strong>
                                    <a href="mailto:<?= $family->getEmail() ?>"><span><?= $family->getEmail() ?></span></a>
                                </li>
                                <?php
                                    if ($isMailChimpActive) {
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
                            } // end of can_see_privatedata
                        ?>
                        </ul>
                        <hr />
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
                                        <span>
                                            <?= OutputUtils::displayCustomField($rowCustomField->getTypeId(), $currentData, $fam_custom_Special) ?>
                                        </span>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row align-items-lg-center">
                        <div class="col-lg-5 mb-3 mb-lg-0">
                            <div class="text-muted text-uppercase small"><?= _('Overview') ?></div>
                            <h2 class="h4 mb-2"><?= $family->getName() ?></h2>
                            <div class="mb-2">
                                <span class="badge badge-primary mr-1 mb-1"><i class="fas fa-users mr-1"></i><?= $familyMemberCount . ' ' . _('Members') ?></span>
                                <span class="badge badge-light mr-1 mb-1"><i class="far fa-envelope mr-1"></i><?= $familyEmailCount . ' ' . _('Reachable by email') ?></span>
                                <span class="badge badge-light mr-1 mb-1"><i class="fas fa-shield-alt mr-1"></i><?= $can_see_privatedata ? _('Private access granted') : _('Private access restricted') ?></span>
                            </div>
                            <!--
                            <div class="text-muted small">
                                <?= $can_see_privatedata ? _('Use the quick actions to manage this family and keep the member list up to date.') : _('Some details remain hidden because this family contains private data.') ?>
                            </div>
                            -->
                        </div>
                        <div class="col-lg-7">
                            <div class="d-flex flex-wrap align-items-center justify-content-lg-end">
                                <?php
                                $buttons = 0;

                                if (Cart::FamilyInCart($iFamilyID) && SessionUser::getUser()->isShowCartEnabled()) {
                                    $buttons++;
                                ?>
                                    <a class="btn btn-sm btn-outline-info RemoveFromFamilyCart mr-2 mb-2" id="AddToFamilyCart"
                                        data-cartfamilyid="<?= $iFamilyID ?>"> <i class="fas fa-times"></i> <span
                                            class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
                                <?php
                                } else if (SessionUser::getUser()->isShowCartEnabled()) {
                                ?>
                                    <a class="btn btn-sm btn-outline-info AddToFamilyCart mr-2 mb-2" id="AddToFamilyCart"
                                        data-cartfamilyid="<?= $iFamilyID ?>">
                                        <i class="fas fa-cart-plus"></i> <span
                                            class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
                                <?php
                                }

                                if (SessionUser::getUser()->isEmailEnabled()) {
                                    $buttons++;
                                    $emails = "";
                                    foreach ($family->getActivatedPeople() as $person) {
                                        $emails .= $person->getEmail() . SessionUser::getUser()->MailtoDelimiter();
                                    }

                                    $emails = mb_substr($emails, 0, -1)
                                ?>
                                    <a class="btn btn-sm btn-outline-success mr-2 mb-2" href="mailto:<?= urlencode($emails) ?>"><i
                                            class="far fa-paper-plane"></i><?= _('Email') ?></a>
                                    <a class="btn btn-sm btn-outline-info mr-2 mb-2" href="mailto:?bcc=<?= urlencode($emails) ?>"><i
                                            class="fas fa-paper-plane"></i><?= _('Email (BCC)') ?></a>
                                <?php
                                }
                                if (SessionUser::getUser()->isPastoralCareEnabled()) {
                                    $buttons++;
                                ?>
                                    <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
                                        href="<?= $sRootPath ?>/v2/pastoralcare/family/<?= $iFamilyID ?>"><i
                                            class="far fa-question-circle"></i> <?= _("Pastoral Care") ?></a>
                                <?php
                                }

                                if (SessionUser::getUser()->isAdmin()) {
                                    $buttons++;
                                ?>
                                    <a class="btn btn-sm btn-outline-primary mr-2 mb-2" href="#" data-toggle="modal" data-target="#confirm-verify"><i
                                            class="fas fa-check-square"></i> <?= _("Verify Info") ?></a>
                                <?php
                                }

                                if (SessionUser::getUser()->isNotesEnabled() || $iCurrentUserFamID == $iFamilyID) {
                                    $buttons++;
                                ?>
                                    <a class="btn btn-sm btn-success mr-2 mb-2" href="#" id="createDocument" data-toggle="tooltip"
                                        data-placement="top" title="<?= _("Create a document") ?>"><i
                                            class="fas fa-file"></i><?= _("Create a document") ?></a>
                                <?php
                                }

                                if (SessionUser::getUser()->isManageGroupsEnabled()) {
                                    $buttons++;
                                ?>
                                    <a class="btn btn-sm btn-outline-warning mr-2 mb-2 <?= (mb_strlen($family->getAddress1()) == 0) ? 'disabled' : '' ?>"
                                        data-toggle="tooltip" data-placement="bottom" title="<?= _("Get the vCard of the family") ?>"
                                        href="<?= $sRootPath ?>/api/families/addressbook/extract/<?= $iFamilyID ?>"><i
                                            class="far fa-id-card">
                                        </i> <?= _("vCard") ?></a>
                                <?php
                                }

                                if ($bOkToEdit && SessionUser::getUser()->isGdrpDpoEnabled()) {
                                    $buttons++;
                                ?>
                                    <button class="btn btn-sm btn-warning mr-2 mb-2" id="activateDeactivate">
                                        <i class="fa <?= (is_null($family->getDateDeactivated()) ? 'fa-times-circle' : 'fa-check-circle') ?> "></i><?php echo ((is_null($family->getDateDeactivated()) ? _('Deactivate') : _('Activate')) . _(' this Family')); ?>
                                    </button>
                                <?php
                                }

                                if (SessionUser::getUser()->isDeleteRecordsEnabled()) {
                                    $buttons++;
                                ?>
                                    <a class="btn btn-sm btn-danger mr-2 mb-2"
                                        href="<?= $sRootPath ?>/v2/people/family/delete/<?= $iFamilyID ?>"><i
                                            class="far fa-trash-alt"></i><?= _('Delete this Family') ?></a>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <!--
                    <?php if (SessionUser::getUser()->isAddRecordsEnabled() || $iCurrentUserFamID == $iFamilyID) { ?>
                        <div class="border-top pt-3 mt-3">
                            <div class="row align-items-end">
                                <div class="col-lg-6 mb-3 mb-lg-0">
                                    <div class="text-muted text-uppercase small"><?= _('Quick action') ?></div>
                                    <div class="font-weight-bold"><?= _('Add a family member') ?></div>
                                    <div class="text-muted small"><?= _('Choose a role to prefill the person editor and keep data entry focused.') ?></div>
                                </div>
                                <div class="col-lg-6">
                                    <form method="get" action="<?= $sRootPath ?>/v2/people/person/editor/AddToFamily/<?= $iFamilyID ?>" class="mb-0">
                                        <div class="input-group input-group-sm">
                                            <select name="FamilyRole" class="form-control form-control-sm" aria-label="<?= _('Family role') ?>">
                                                <option value="0"><?= _('Choose a role') ?></option>
                                                <?php foreach ($familyQuickRoles as $familyQuickRole) { ?>
                                                    <option value="<?= $familyQuickRole->getOptionId() ?>"><?= $familyQuickRole->getOptionName() ?></option>
                                                <?php } ?>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-user-plus mr-1"></i><?= _('Add New Member') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                -->
                </div>
            </div>


            <?php
            if ($iCurrentUserFamID == $iFamilyID || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
            ?>
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header border-0">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <h3 class="card-title mb-1">
                                    <?= _("Family Members") ?>
                                </h3>
                                <div class="text-muted small">
                                    <?= $familyMemberCount > 0 ? _('Review members, roles and contacts at a glance.') : _('No members linked yet. Use the quick action above to create the first member.') ?>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0">
                                <span class="badge badge-primary mr-2 mb-2"><i class="fas fa-users mr-1"></i><?= $familyMemberCount . ' ' . _('Members') ?></span>
                                <span class="badge badge-light mr-2 mb-2"><i class="far fa-envelope mr-1"></i><?= $familyEmailCount . ' ' . _('Emails') ?></span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($familyMemberCount === 0) { ?>
                            <div class="alert alert-light border mb-0">
                                <i class="fas fa-user-plus mr-2 text-primary"></i><?= _('This family has no active members yet.') ?>
                            </div>
                        <?php } else { ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-borderless mb-0" width="100%">
                                    <thead>
                                        <tr class="border-bottom">
                                            <th class="text-muted small font-weight-bold"><i class="fas fa-users mr-2 text-info"></i><?= _("Members") ?></th>
                                            <th class="text-center text-muted small font-weight-bold"><i class="fas fa-tag mr-1"></i><?= _("Role") ?></th>
                                            <th class="text-muted small font-weight-bold"><i class="fas fa-layer-group mr-1"></i><?= _("Classification") ?></th>
                                            <th class="text-muted small font-weight-bold"><i class="fas fa-birthday-cake mr-1"></i><?= _("Birthday") ?></th>
                                            <th class="text-muted small font-weight-bold"><i class="far fa-envelope mr-1"></i><?= _("Email") ?></th>
                                            <th class="text-center text-muted small font-weight-bold"><?= _("Actions") ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($familyMembers as $person) {
                                        ?>
                                            <tr class="align-middle border-bottom">
                                                <td class="py-2">
                                                    <div class="d-flex align-items-center">
                                                        <?= $person->getJPGPhotoDatas() ?>
                                                        <a href="<?= $person->getViewURI() ?>"
                                                            class="user-link font-weight-500 ml-2"><?= $person->getFullName() ?></a>
                                                    </div>
                                                </td>
                                                <td class="text-center py-2">
                                                    <?php
                                                    $famRole = $person->getFamilyRoleName();
                                                    $badgeClass = 'badge badge-secondary';
                                                    $badgeIcon = 'fa-user';
                                                    if ($famRole == _('Head of Household')) {
                                                        $badgeClass = 'badge badge-primary';
                                                        $badgeIcon = 'fa-crown';
                                                    } elseif ($famRole == _('Spouse')) {
                                                        $badgeClass = 'badge badge-info';
                                                        $badgeIcon = 'fa-heart';
                                                    } elseif ($famRole == _('Child')) {
                                                        $badgeClass = 'badge badge-warning';
                                                        $badgeIcon = 'fa-child';
                                                    }
                                                    ?>
                                                    <span class='<?= $badgeClass ?>' title="<?= $famRole ?>">
                                                        <i class="fas <?= $badgeIcon ?> mr-1"></i><?= $famRole ?>
                                                    </span>
                                                </td>
                                                <td class="py-2 text-muted small">
                                                    <?= $person->getClassification() ? $person->getClassification()->getOptionName() : '<em class="text-secondary">' . _('N/A') . '</em>' ?>
                                                </td>
                                                <td class="py-2 text-muted small">
                                                    <?= OutputUtils::FormatBirthDate(
                                                        $person->getBirthYear(),
                                                        $person->getBirthMonth(),
                                                        $person->getBirthDay(),
                                                        '-',
                                                        $person->getFlags()
                                                    ) ?>
                                                </td>
                                                <td class="py-2">
                                                    <?php $tmpEmail = $person->getEmail();
                                                    if ($tmpEmail != "") {
                                                        array_push($sFamilyEmails, $tmpEmail);
                                                    ?>
                                                        <a href="mailto:<?= $tmpEmail ?>" class="text-primary small" title="<?= $tmpEmail ?>"><?= $tmpEmail ?></a>
                                                    <?php
                                                    } else {
                                                        echo '<span class="text-muted small">—</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="py-2 text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <?php
                                                        if (SessionUser::getUser()->isShowCartEnabled()) {
                                                        ?>
                                                            <a class="AddToPeopleCart btn btn-outline-secondary" data-cartpersonid="<?= $person->getId() ?>"
                                                                title="<?= _('Add to Cart') ?>" data-toggle="tooltip">
                                                                <i class="fas fa-cart-plus"></i>
                                                            </a>
                                                        <?php
                                                        }
                                                        ?>
                                                        <?php
                                                        if ($bOkToEdit) {
                                                        ?>
                                                            <a href="<?= $sRootPath ?>/v2/people/person/editor/<?= $person->getId() ?>"
                                                                class="btn btn-outline-primary"
                                                                title="<?= _('Edit') ?>" data-toggle="tooltip">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a class="delete-person btn btn-outline-danger"
                                                                data-person_name="<?= $person->getFullName() ?>"
                                                                data-person_id="<?= $person->getId() ?>"
                                                                data-view="family"
                                                                title="<?= _('Delete') ?>" data-toggle="tooltip">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php
            if ($iCurrentUserFamID == $iFamilyID || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
            ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-secondary shadow-sm">
                            <div class="card-header border-0 card-header-custom">
                                <!-- Nav tabs -->
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a href="#timeline" aria-controls="timeline" role="tab"
                                            data-toggle="tab" class="nav-link <?= ($mode == "TimeLine") ? "active" : "" ?>">
                                            <i class="fas fa-clock"></i> <?= _("Timeline") ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#properties" aria-controls="properties" role="tab"
                                            data-toggle="tab" class="nav-link <?= ($mode == "Properties") ? "active" : "" ?>">
                                            <i class="fas fa-user-cog"></i> <?= _("Assigned Properties") ?>
                                        </a>
                                    </li>
                                    <?php
                                    if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
                                    ?>
                                        <li class="nav-item">
                                            <a href="#finance" aria-controls="finance" role="tab"
                                                data-toggle="tab" class="nav-link <?= ($mode == "Finance") ? "active" : "" ?>">
                                                <i class="far fa-credit-card"></i> <?= _("Automatic Payments") ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#pledges" aria-controls="pledges" role="tab"
                                                data-toggle="tab" class="nav-link <?= ($mode == "Pledges") ? "active" : "" ?>">
                                                <i class="fas fa-university"></i> <?= _("Pledges and Payments") ?>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                    <li role="presentation" class="nav-item">
                                        <a href="#notes" aria-controls="notes" role="tab"
                                            data-toggle="tab" class="nav-link <?= ($mode == "Documents") ? "active" : "" ?>">
                                            <i class="far fa-copy"></i> <?= _("Documents") ?>
                                        </a>
                                    </li>
                                </ul>

                            </div>
                            <div class="card-body">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane fade <?= ($mode == "TimeLine") ? "show active" : "" ?>" id="timeline">
                                        <div class="timeline">
                                            <!-- timeline time label -->
                                            <div class="time-label">
                                                <span class="bg-primary px-3 py-1 rounded-pill">
                                                    <?= $curYear ?>
                                                </span>
                                            </div>
                                            <!-- /.timeline-label -->

                                            <!-- timeline item -->
                                            <?php
                                            $countMainTimeLine = 0;  // number of items in the MainTimeLines

                                            foreach ($timelineServiceItems as $item) {
                                                $countMainTimeLine++;

                                                if ($countMainTimeLine > $maxMainTimeLineItems) break; // we break after 20 $items
                                                if ($curYear != $item['year']) {
                                                    $curYear = $item['year'];
                                            ?>
                                                    <div class="time-label">
                                                        <span class="bg-secondary px-3 py-1 rounded-pill">
                                                            <?= $curYear ?>
                                                        </span>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                                <div>
                                                    <!-- timeline icon -->
                                                    <i class="fa <?= $item['style'] ?>"></i>

                                                    <div class="timeline-item shadow-sm border-0">
                                                        <span class="time text-muted small"><i class="fas fa-clock mr-1"></i><?= $item['datetime'] ?>
                                                            <?php
                                                            if ((SessionUser::getUser()->isNotesEnabled()) && (isset($item["editLink"]) || isset($item["deleteLink"])) && $item['slim']) {
                                                            ?>
                                                                &nbsp;
                                                                <?php
                                                                if (isset($item["editLink"])) {
                                                                ?>
                                                                    <?= $item["editLink"] ?>
                                                                    <span class="btn btn-sm btn-outline-primary py-0 px-1"><i class="fas fa-edit"></i></span>
                                                                    </a>
                                                                <?php
                                                                }

                                                                if (isset($item["deleteLink"])) {
                                                                ?>
                                                                    <?= $item["deleteLink"] ?>
                                                                    <span class="btn btn-sm btn-outline-danger py-0 px-1"><i class="fas fa-trash-alt"></i></span>
                                                            <?php
                                                                }
                                                            } ?>
                                                        </span>

                                                        <h3 class="timeline-header border-0 font-weight-bold">
                                                            <?php
                                                            if (array_key_exists('headerlink', $item)) {
                                                            ?>
                                                                <a href="<?= $item['headerlink'] ?>"><?= $item['header'] ?></a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <?= _($item['header']) ?>
                                                            <?php
                                                            }
                                                            ?>
                                                        </h3>

                                                        <div class="timeline-body">
                                                            <span><?= $item['text'] ?></span>
                                                        </div>

                                                        <?php
                                                        if ((SessionUser::getUser()->isNotesEnabled()) && (isset($item["editLink"]) || isset($item["deleteLink"])) && !$item['slim']) {
                                                        ?>
                                                            <div class="timeline-footer">
                                                                <?php
                                                                if (isset($item["editLink"])) {
                                                                ?>
                                                                    <?= $item["editLink"] ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit mr-1"></i><?= _('Edit') ?></button>
                                                                    </a>
                                                                <?php
                                                                }

                                                                if (isset($item["deleteLink"])) {
                                                                ?>
                                                                    <?= $item["deleteLink"] ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt mr-1"></i><?= _('Delete') ?></button>
                                                                    </a>
                                                                <?php
                                                                }
                                                                ?>
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
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade <?= ($mode == "Properties") ? "show active" : "" ?>" id="properties">
                                        <div class="card card-outline card-info shadow-sm mb-0">
                                            <div class="card-body">
                                                <?php
                                                $sAssignedProperties = ",";
                                                ?>
                                                <table width="100%" cellpadding="4" id="assigned-properties-table"
                                                    class="table table-sm table-hover dt-responsive dataTable no-footer dtr-inline"></table>
                                                <?php
                                                if ($bOkToEdit) {
                                                ?>
                                                    <div class="mt-3 p-3 border rounded bg-light">
                                                        <h5 class="mb-3"><i class="fas fa-tag mr-2 text-info"></i><?= _("Assign a New Property") ?></h5>

                                                        <div class="row">
                                                            <div class="form-group col-xs-12 col-md-7">
                                                                <select name="PropertyId" id="input-family-properties"
                                                                    class="input-family-properties form-control form-control-sm select2"
                                                                    style="width:100%"
                                                                    data-placeholder="<?= _("Select") ?> ..."
                                                                    data-familyID="<?= $iFamilyID ?>">
                                                                    <option selected disabled>
                                                                        -- <?= _('select an option') ?> --
                                                                    </option>
                                                                    <?php
                                                                    foreach ($ormProperties as $ormProperty) {
                                                                        //If the property doesn't already exist for this Person, write the <OPTION> tag
                                                                        if (strlen(strstr($sAssignedProperties, "," . $ormProperty->getProId() . ",")) == 0) {
                                                                    ?>
                                                                            <option value="<?= $ormProperty->getProId() ?>"
                                                                                data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>"
                                                                                data-pro_Value=""><?= $ormProperty->getProName() ?></option>
                                                                    <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div id="prompt-box" class="col-xs-12 col-md-7"></div>
                                                            <div class="form-group col-xs-12 col-md-7 mb-0">
                                                                <button type="button" class="btn btn-sm btn-primary assign-property-btn">
                                                                    <i class="fas fa-plus mr-1"></i><?= _("Assign") ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    if (SessionUser::getUser()->isFinanceEnabled()) {
                                    ?>
                                        <div role="tabpanel" class="tab-pane fade <?= ($mode == "Finance") ? "show active" : "" ?>" id="finance">
                                            <div class="card card-outline card-info shadow-sm mb-0">
                                                <div class="card-body">
                                                    <?php
                                                    if ($ormAutoPayments->count() > 0) {
                                                    ?>
                                                        <table class="table table-sm table-hover"
                                                            id="automaticPaymentsTable" cellpadding="5" cellspacing="0"
                                                            width="100%"></table>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <div class="alert alert-light border mb-3">
                                                            <i class="far fa-credit-card mr-1 text-info"></i><?= _("No automatic payments configured yet.") ?>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>
                                                    <p class="text-center">
                                                        <a class="btn btn-sm btn-primary"
                                                            href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/-1/<?= $family->getId() ?>/v2-people-family-view-<?= $iFamilyID ?>"><i class="fas fa-plus mr-1"></i> <?= _("Add a new automatic payment") ?></a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane fade <?= ($mode == "Pledges") ? "show active" : "" ?>" id="pledges">
                                            <div class="card card-outline card-secondary shadow-sm">
                                                <div class="card-body">
                                                    <input type="checkbox" name="ShowPledges" id="ShowPledges"
                                                        value="1" <?= ($_SESSION['sshowPledges']) ? " checked" : "" ?>><?= _("Show Pledges") ?>
                                                    <div class="row">
                                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                                            <input type="checkbox" name="ShowPayments" id="ShowPayments"
                                                                value="1" <?= ($_SESSION['sshowPayments']) ? " checked" : "" ?>><?= _("Show Payments") ?>
                                                        </div>
                                                        <div class="col-lg-1 col-md-1 col-sm-1">
                                                            <label for="ShowSinceDate"><?= _("From") ?>:</label>
                                                        </div>
                                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                                            <input class=" form-control  form-control-sm date-picker" type="text" id="Min"
                                                                Name="ShowSinceDate"
                                                                value="<?= SessionUser::getUser()->getShowSince()->format(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                                                placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                                        </div>
                                                        <div class="col-lg-1 col-md-1 col-sm-1">
                                                            <label for="ShowToDate"><?= _("To") ?>:</label>
                                                        </div>
                                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                                            <input class=" form-control  form-control-sm date-picker" type="text" id="Max"
                                                                Name="ShowToDate"
                                                                value="<?= SessionUser::getUser()->getShowTo()->format(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                                                placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                                        </div>
                                                    </div>
                                                    <?php
                                                    $tog = 0;
                                                    if ($_SESSION['sshowPledges'] || $_SESSION['sshowPayments']) {
                                                    ?>

                                                        <table id="pledgePaymentTable"
                                                            class="table table-sm table-hover"
                                                            cellspacing="0" width="100%"></table>
                                                    <?php
                                                    } // if bShowPledges
                                                    ?>

                                                    <p class="text-center">
                                                        <a class="btn btn-sm btn-primary"
                                                            href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $family->getId() ?>/Pledge/v2-people-family-view-<?= $iFamilyID ?>"><i class="fa fa-plus"></i> <?= _("Add a new pledge") ?></a>
                                                        <a class="btn btn-sm btn-outline-secondary"
                                                            href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $family->getId() ?>/Payment/v2-people-family-view-<?= $iFamilyID ?>"><i class="fa fa-plus"></i> <?= _("Add a new payment") ?></a>
                                                    </p>

                                                    <?php
                                                    if (SessionUser::getUser()->isCanvasserEnabled()) {
                                                    ?>
                                                        <p class="text-center">
                                                            <a class="btn btn-sm btn-outline-secondary"
                                                                href="<?= $sRootPath ?>/v2/people/canvass/editor/<?= $family->getId() ?>/<?= $_SESSION['idefaultFY'] ?>/v2-people-family-view-<?= $iFamilyID ?>"><i class="fa fa-eye"></i> <?= MiscUtils::MakeFYString($_SESSION['idefaultFY']) . _(" Canvass Entry") ?></a>
                                                        </p>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div role="tabpanel" class="tab-pane fade <?= ($mode == "Documents") ? "show active" : "" ?>" id="notes">
                                        <div class="timeline">
                                            <!-- note time label -->
                                            <div class="time-label">
                                                <span class="bg-yellow px-3 py-1 rounded-pill">
                                                    <?php echo date_create()->format(SystemConfig::getValue('sDateFormatLong')) ?>
                                                </span>
                                            </div>
                                            <!-- /.note-label -->

                                            <!-- note item -->
                                            <?php
                                            foreach ($timelineNotesServiceItems as $item) {
                                            ?>
                                                <div>
                                                    <!-- timeline icon -->
                                                    <i class="fa <?= $item['style'] ?>"></i>
                                                    <div class="timeline-item">
                                                        <span class="time">
                                                            <i class="fas fa-clock"></i> <?= $item['datetime'] ?>
                                                            &nbsp;

                                                            <div class="btn-group">
                                                                <?php
                                                                if ($item['slim']) {
                                                                    if ($item['editLink'] != '') {
                                                                ?>
                                                                        <a href="#" data-id="<?= $item['id'] ?>" data-perid="<?= $item['perID'] ?>"
                                                                            data-famid="<?= $item['famID'] ?>" class="btn btn-primary btn-sm editDocument">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                        </span>
                                                        </a>
                                                    <?php
                                                                    }

                                                                    if ($item['deleteLink'] != '') {
                                                    ?>
                                                        <a href="#" data-id="<?= $item['id'] ?>" data-perid="<?= $item['perID'] ?>"
                                                            data-famid="<?= $item['famID'] ?>" class="btn btn-danger btn-sm deleteDocument">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                <?php
                                                                    }
                                                                }
                                                ?>
                                                    </div>
                                                    </span>
                                                    <h3 class="timeline-header">
                                                        <?php
                                                        if (array_key_exists('headerlink', $item)) {
                                                        ?>
                                                            <a href="<?= $item['headerlink'] ?>"><?= $item['header'] ?></a>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <?= $item['header'] ?>
                                                        <?php
                                                        }
                                                        ?>
                                                    </h3>

                                                    <div class="timeline-body">
                                                        <?= $item['text'] ?>
                                                    </div>

                                                    <?php if ((SessionUser::getUser()->isNotesEnabled()) && ($item['editLink'] != '' || $item['deleteLink'] != '')) { ?>
                                                        <div class="timeline-footer">
                                                            <?php
                                                            if (!$item['slim']) {
                                                            ?>
                                                                <div class="btn-group">
                                                                    <?php
                                                                    if ($item['editLink'] != '') {
                                                                    ?>
                                                                        <a href="#" data-id="<?= $item['id'] ?>"
                                                                            data-perid="<?= $item['perID'] ?>"
                                                                            data-famid="<?= $item['famID'] ?>"
                                                                            class="btn btn-primary btn-sm editDocument">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                    <?php
                                                                    }

                                                                    if ($item['deleteLink'] != '') {
                                                                    ?>
                                                                        <a href="#" data-id="<?= $item['id'] ?>"
                                                                            data-perid="<?= $item['perID'] ?>"
                                                                            data-famid="<?= $item['famID'] ?>"
                                                                            class="btn btn-primary btn-sm  deleteDocument">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </a>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </div>

                                                            <?php
                                                            }
                                                            ?>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    <?php
            } else {
    ?>
        <div class="card  card-primary">
            <div class="card-header  border-0">
                <h3 class="card-title"><?= _("Informations") ?></h3>
            </div>
            <div class="card-body">
                <?= _("Private Data") ?>
            </div>
        </div>
    <?php
            }
    ?>
    </div>
</div>
</div>
</div>

cocuou

<!-- Modal -->
<div id="photoUploader"></div>

<div class="modal fade" id="confirm-delete-image" tabindex="-1" role="dialog" aria-labelledby="delete-Image-label"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="delete-Image-label"><?= _("Confirm Delete") ?></h4>
                <button type="button" class="bootbox-close-button close" aria-hidden="true" data-dismiss="modal">&times;
                </button>
            </div>
            <div class="modal-body">
                <p><?= _("You are about to delete the profile photo, this procedure is irreversible.") ?></p>
                <p><?= _("Do you want to proceed?") ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><?= _("Cancel") ?></button>
                <button class="btn btn-danger danger" id="deletePhoto"><?= _("Delete") ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-verify" tabindex="-1" role="dialog" aria-labelledby="confirm-verify-label"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="confirm-verify-label"><?= _("Request Family Info Verification") ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <p>
                    <b><?= _("Select how do you want to request the family information to be verified") ?></b>
                </p>
                <?php
                if (count($sFamilyEmails) > 0) {
                ?>
                    <?= _("You are about to email copy of the family information in pdf to the following emails") ?>

                    <ul>
                        <?php
                        foreach ($sFamilyEmails as $tmpEmail) {
                        ?>
                            <li><?= $tmpEmail ?></li>
                        <?php
                        }
                        ?>
                    </ul>
                <?php
                }
                ?>

            </div>
            <div class="modal-footer text-center">
                <?php
                if (count($sFamilyEmails) > 0 && !empty(SystemConfig::getValue('sSMTPHost'))) {
                ?>
                    <button type="button" id="onlineVerify" class="btn btn-warning warning">
                        <i class="far fa-envelope"></i>
                        <?= _("Online Verification") ?>
                    </button>
                    <button type="button" id="onlineVerifyPDF" class="btn btn-danger danger">
                        <i class="far fa-envelope"></i> <i class="fas fa-file-pdf"></i>
                        <?= _("Online Verification") ?>
                    </button>
                <?php
                }
                ?>
                <button type="button" id="verifyDownloadPDF" class="btn btn-info">
                    <i class="fas fa-download"></i>
                    <?= _("PDF Report") ?>
                </button>
                <button type="button" id="verifyNow" class="btn btn-success">
                    <i class="fas fa-check"></i>
                    <?= _("Verified In Person") ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/external/jquery-photo-uploader/PhotoUploader.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/FamilyView.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/MemberView.js"></script>

<!-- Document editor -->
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= $sRootPath ?>/skin/js/document.js"></script>
<!-- !Document editor -->

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
}
?>

<script nonce="<?= $sCSPNonce ?>">
    window.CRM.currentPersonID = 0;
    window.CRM.currentFamily = <?= $iFamilyID ?>;
    window.CRM.docType = 'family';
    window.CRM.currentActive = <?= (is_null($family->getDateDeactivated()) ? 'true' : 'false') ?>;
    window.CRM.fam_Name = "<?= $family->getName() ?>";
    window.CRM.iPhotoHeight = <?= SystemConfig::getValue("iPhotoHeight") ?>;
    window.CRM.iPhotoWidth = <?= SystemConfig::getValue("iPhotoWidth") ?>;
    window.CRM.familyMail = "<?= $family->getEmail() ?>";

    var dataT = 0;
    var dataPaymentTable = 0;
    var pledgePaymentTable = 0;

    <?php if ($location_available) { ?>
        // location and MAP
        window.CRM.churchloc = {
            lat: parseFloat(<?= $lat ?>),
            lng: parseFloat(<?= $lng ?>)
        };
        window.CRM.mapZoom = <?= $iLittleMapZoom ?>;

        initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, "<?= $family->getName() ?>", '', '');
    <?php } ?>
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>