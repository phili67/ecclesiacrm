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
?>


<?php if (!empty($family->getDateDeactivated())) {
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
                <div class="card card-primary card-outline">
                <div class="card-body  box-profile">
                    <div class="text-center">
                        <img src="<?= $sRootPath ?>/api/families/<?= $family->getId() ?>/photo"
                             class="initials-image profile-user-img img-responsive img-rounded img-circle"/>
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
                    <?php
                    if ($bOkToEdit) {
                        ?>
                        <a href="<?= $sRootPath ?>/v2/people/family/editor/<?= $family->getId() ?>"
                           class="btn btn-primary btn-block"><b><?= _("Edit") ?></b></a>
                        <?php
                    }
                    ?>
                    <hr/>
                    <?php
                    $can_see_privatedata = ($iCurrentUserFamID == $iFamilyID || SessionUser::getUser()->isSeePrivacyDataEnabled()) ? true : false;
                    ?>
                    <ul class="fa-ul">
                        <?php
                        if ($can_see_privatedata) {
                            $adresses = explode('<br>',$family->getAddress());
                            $count = count($adresses);
                        ?>
                        <li><strong><i class="fa-li fas fa-home"></i><?= _("Address") ?>:<?= $count>1?'<br>':'' ?></strong>
                            <span>
                                <?php foreach ($adresses as $adress) { ?>
                                    <?= $count>1?'•':'' ?> <?= OutputUtils::GetLinkMapFromAddress($adress) ?><br>
                                <?php } ?>
                                <?php if ($location_available) { ?>
                                    <div id="MyMap" style="width:100%"></div>
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
                                <a href="mailto:<?= $family->getEmail() ?>"><span><?= $family->getEmail() ?></span></a>
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
                            <?=  _("Private Data") ?>
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
            <div class="card special-card">
                <div class="card-body special-card-body">
                    <?php
                    $buttons = 0;

                    if (Cart::FamilyInCart($iFamilyID) && SessionUser::getUser()->isShowCartEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app RemoveFromFamilyCart" id="AddToFamilyCart"
                           data-cartfamilyid="<?= $iFamilyID ?>"> <i class="fas fa-times"></i> <span
                                class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
                        <?php
                    } else if (SessionUser::getUser()->isShowCartEnabled()) {
                        ?>
                        <a class="btn btn-app AddToFamilyCart" id="AddToFamilyCart"
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
                        <a class="btn btn-app" href="mailto:<?= urlencode($emails) ?>"><i
                                class="far fa-paper-plane"></i><?= _('Email') ?></a>
                        <a class="btn btn-app" href="mailto:?bcc=<?= urlencode($emails) ?>"><i
                                class="fas fa-paper-plane"></i><?= _('Email (BCC)') ?></a>
                        <?php
                    }
                    if (SessionUser::getUser()->isPastoralCareEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-purple"
                           href="<?= $sRootPath ?>/v2/pastoralcare/family/<?= $iFamilyID ?>"><i
                                class="far fa-question-circle"></i> <?= _("Pastoral Care") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isAdmin()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-info" href="#" data-toggle="modal" data-target="#confirm-verify"><i
                                class="fas fa-check-square"></i> <?= _("Verify Info") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isAddRecordsEnabled() || $iCurrentUserFamID == $iFamilyID) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-blue"
                           href="<?= $sRootPath ?>/v2/people/person/editor/AddToFamily/<?= $iFamilyID ?>"><i
                                class="fas fa-plus-square"></i> <?= _('Add New Member') ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isNotesEnabled() || $iCurrentUserFamID == $iFamilyID) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-green" href="#" id="createDocument" data-toggle="tooltip"
                           data-placement="top" title="<?= _("Create a document") ?>"><i
                                class="fas fa-file"></i><?= _("Create a document") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isManageGroupsEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-yellow-gradient <?= (mb_strlen($family->getAddress1()) == 0)?'disabled':'' ?>"
                           data-toggle="tooltip" data-placement="bottom" title="<?= _("Get the vCard of the family") ?>"
                           href="<?= $sRootPath ?>/api/families/addressbook/extract/<?= $iFamilyID ?>"><i
                                class="far fa-id-card">
                            </i> <?= _("vCard") ?></a>
                        <?php
                    }

                    if ($bOkToEdit && SessionUser::getUser()->isGdrpDpoEnabled()) {
                        $buttons++;
                        ?>
                        <button class="btn btn-app bg-gradient-orange" id="activateDeactivate">
                            <i class="fa <?= (empty($family->getDateDeactivated()) ? 'fa-times-circle' : 'fa-check-circle') ?> "></i><?php echo((empty($family->getDateDeactivated()) ? _('Deactivate') : _('Activate')) . _(' this Family')); ?>
                        </button>
                        <?php
                    }

                    if (SessionUser::getUser()->isDeleteRecordsEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-maroon"
                           href="<?= $sRootPath ?>/v2/people/family/delete/<?= $iFamilyID ?>"><i
                                class="far fa-trash-alt"></i><?= _('Delete this Family') ?></a>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <?php
            if ($iCurrentUserFamID == $iFamilyID || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                ?>
                <div class="card card-default">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <?= _("Family Members") ?>
                        </h3>
                        <div class="card-tools pull-right">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table user-list table-hover data-person" width="100%">
                            <thead>
                            <tr>
                                <th><span><?= _("Members") ?></span></th>
                                <th class="text-center"><span><?= _("Role") ?></span></th>
                                <th><span><?= _("Classification") ?></span></th>
                                <th><span><?= _("Birthday") ?></span></th>
                                <th><span><?= _("Email") ?></span></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($family->getActivatedPeople() as $person) {
                                ?>
                                <tr>
                                    <td>
                                        <img
                                            src="<?= $sRootPath ?>/api/persons/<?= $person->getId() ?>/thumbnail"
                                            width="40" height="40"
                                            class="initials-image img-circle"/>
                                        <a href="<?= $person->getViewURI() ?>"
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
                                    <td>
                                        <?= $person->getClassification() ? $person->getClassification()->getOptionName() : "" ?>
                                    </td>
                                    <td>
                                        <?= OutputUtils::FormatBirthDate($person->getBirthYear(),
                                            $person->getBirthMonth(), $person->getBirthDay(), "-", $person->getFlags()) ?>
                                    </td>
                                    <td>
                                        <?php $tmpEmail = $person->getEmail();
                                        if ($tmpEmail != "") {
                                            array_push($sFamilyEmails, $tmpEmail);
                                            ?>
                                            <a href="mailto:<?= $tmpEmail ?>"><?= $tmpEmail ?></a>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                    <td style="width: 20%;">
                                        <?php
                                        if (SessionUser::getUser()->isShowCartEnabled()) {
                                            ?>
                                            <a class="AddToPeopleCart" data-cartpersonid="<?= $person->getId() ?>">
                                                <span class="fa-stack">
                                                <i class="fas fa-square fa-stack-2x"></i>
                                                <i class="fas fa-cart-plus fa-stack-1x fa-inverse"></i>
                                                </span>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                        <?php
                                        if ($bOkToEdit) {
                                            ?>
                                            <a href="<?= $sRootPath ?>/v2/people/person/editor/<?= $person->getId() ?>"
                                               class="table-link">
                                                <span class="fa-stack" style="color:green">
                                                <i class="fas fa-square fa-stack-2x"></i>
                                                <i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>
                                                </span>
                                            </a>
                                            <a class="delete-person" data-person_name="<?= $person->getFullName() ?>"
                                               data-person_id="<?= $person->getId() ?>" data-view="family">
                                                <span class="fa-stack" style="color:red">
                                                    <i class="fas fa-square fa-stack-2x"></i>
                                                    <i class="far fa-trash-alt fa-stack-1x fa-inverse"></i>
                                                </span>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
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
                        <div class="card">
                            <div class="card-header  border-0">
                                <!-- Nav tabs -->
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a href="#timeline" aria-controls="timeline" role="tab"
                                           data-toggle="tab" class="nav-link <?= ($mode == "TimeLine")?"active":"" ?>">
                                            <i class="fas fa-clock"></i> <?= _("Timeline") ?>
                                        </a></li>
                                    <li class="nav-item">
                                        <a href="#properties" aria-controls="properties" role="tab"
                                            data-toggle="tab" class="nav-link <?= ($mode == "Properties")?"active":"" ?>">
                                            <i class="fas fa-user-cog"></i> <?= _("Assigned Properties") ?>
                                        </a>
                                    </li>
                                    <?php
                                    if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
                                        ?>
                                        <li class="nav-item">
                                            <a href="#finance" aria-controls="finance" role="tab"
                                                data-toggle="tab" class="nav-link <?= ($mode == "Finance")?"active":"" ?>">
                                                <i class="far fa-credit-card"></i> <?= _("Automatic Payments") ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#pledges" aria-controls="pledges" role="tab"
                                                data-toggle="tab" class="nav-link <?= ($mode == "Pledges")?"active":"" ?>">
                                                <i class="fas fa-university"></i> <?= _("Pledges and Payments") ?>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                    <li role="presentation" class="nav-item">
                                        <a href="#notes" aria-controls="notes" role="tab"
                                                data-toggle="tab" class="nav-link <?= ($mode == "Documents")?"active":"" ?>">
                                            <i class="far fa-copy"></i> <?= _("Documents") ?>
                                        </a>
                                    </li>
                                </ul>

                            </div>
                            <div class="card-body">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tab-pane fade" class="tab-pane <?= ($mode == "TimeLine")?"active":"" ?>" id="timeline">
                                        <div class="timeline">
                                            <!-- timeline time label -->
                                            <div class="time-label">
                                                <span class="bg-red">
                                                    <?= $curYear ?>
                                                </span>
                                            </div>
                                            <!-- /.timeline-label -->

                                            <!-- timeline item -->
                                            <?php
                                            $countMainTimeLine = 0;  // number of items in the MainTimeLines

                                            foreach ($timelineServiceItems as $item) {
                                                $countMainTimeLine++;

                                                if ($countMainTimeLine > $maxMainTimeLineItems) break;// we break after 20 $items
                                                if ($curYear != $item['year']) {
                                                    $curYear = $item['year'];
                                                    ?>
                                                    <div class="time-label">
                                                        <span class="bg-gray">
                                                            <?= $curYear ?>
                                                        </span>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                                <div>
                                                    <!-- timeline icon -->
                                                    <i class="fa <?= $item['style'] ?>"></i>

                                                    <div class="timeline-item">
                                                        <span class="time"><i class="fas fa-clock"></i><?= $item['datetime'] ?>
                                                            <?php
                                                            if ((SessionUser::getUser()->isNotesEnabled()) && (isset($item["editLink"]) || isset($item["deleteLink"])) && $item['slim']) {
                                                                ?>
                                                                &nbsp;
                                                                <?php
                                                                if (isset($item["editLink"])) {
                                                                    ?>
                                                                    <?= $item["editLink"] ?>
                                                                    <span class="fa-stack">
                                                                        <i class="fas fa-square fa-stack-2x"></i>
                                                                        <i class="fas fa-edit fa-stack-1x fa-inverse"></i>
                                                                    </span>                                                                
                                                                <?php
                                                                }

                                                                if (isset($item["deleteLink"])) {
                                                                    ?>
                                                                    <?= $item["deleteLink"] ?>
                                                                    <span class="fa-stack">
                                                                        <i class="fas fa-square fa-stack-2x" style="color:red"></i>
                                                                        <i class="fas fa-trash-alt fa-stack-1x fa-inverse"></i>
                                                                    </span>
                                                                    <?php
                                                                }
                                                            } ?>
                                                        </span>                                                    

                                                        <h3 class="timeline-header">
                                                            <?php
                                                            if (in_array('headerlink', $item)) {
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
                                                                    <button type="button" class="btn btn-primary"><i
                                                                            class="fas fa-edit"></i></button>
                                                                    </a>
                                                                    <?php
                                                                }

                                                                if (isset($item["deleteLink"])) {
                                                                    ?>
                                                                    <?= $item["deleteLink"] ?>
                                                                    <button type="button" class="btn btn-danger"><i
                                                                            class="fas fa-trash-alt"></i></button>
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
                                    <div role="tab-pane fade" class="tab-pane <?= ($mode == "Properties")?"active":"" ?>" id="properties">
                                        <div class="main-box clearfix">
                                            <div class="main-box-body clearfix">
                                                <?php
                                                $sAssignedProperties = ",";
                                                ?>
                                                <table width="100%" cellpadding="4" id="assigned-properties-table"
                                                       class="table table-condensed dt-responsive dataTable no-footer dtr-inline"></table>
                                                <?php
                                                if ($bOkToEdit) {
                                                    ?>
                                                    <div class="alert alert-info">
                                                        <div>
                                                            <h4><strong><?= _("Assign a New Property") ?>:</strong></h4>

                                                            <div class="row">
                                                                <div class="form-group col-xs-12 col-md-7">
                                                                    <select name="PropertyId" id="input-family-properties"
                                                                            class="input-family-properties form-control select2"
                                                                            style="width:100%"
                                                                            data-placeholder="<?= _("Select") ?> ..."
                                                                            data-familyID="<?= $iFamilyID ?>">
                                                                        <option selected disabled>
                                                                            -- <?= _('select an option') ?>
                                                                            --
                                                                        </option>
                                                                        <?php
                                                                        foreach ($ormProperties as $ormProperty) {
                                                                            //If the property doesn't already exist for this Person, write the <OPTION> tag
                                                                            if (strlen(strstr($sAssignedProperties, "," . $ormProperty->getProId() . ",")) == 0) {
                                                                                ?>
                                                                                <option value="<?= $ormProperty->getProId() ?>"
                                                                                        data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>"
                                                                                        data-pro_Value=""><?= $ormProperty->getProName() ?></option>*/
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div id="prompt-box" class="col-xs-12 col-md-7"></div>
                                                                <div class="form-group col-xs-12 col-md-7">
                                                                    <input type="submit"
                                                                           class="btn btn-primary assign-property-btn"
                                                                           value="<?= _("Assign") ?>">
                                                                </div>
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
                                        <div role="tab-pane fade" class="tab-pane <?= ($mode == "Finance")?"active":"" ?>" id="finance">
                                            <div class="main-box clearfix">
                                                <div class="main-box-body clearfix">
                                                    <?php
                                                    if ($ormAutoPayments->count() > 0) {
                                                        ?>
                                                        <table class="table table-striped table-bordered"
                                                               id="automaticPaymentsTable" cellpadding="5" cellspacing="0"
                                                               width="100%"></table>
                                                        <?php
                                                    }
                                                    ?>
                                                    <p class="text-center">
                                                        <a class="btn btn-primary"
                                                           href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/-1/<?= $family->getId() ?>/v2-people-family-view-<?= $iFamilyID ?>"><i class="fa fa-plus"></i> <?= _("Add a new automatic payment") ?></a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tab-pane fade" class="tab-pane <?= ($mode == "Pledges")?"active":"" ?>" id="pledges">
                                            <div class="main-box clearfix">
                                                <div class="main-box-body clearfix">
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
                                                               class="table table-striped table-bordered"
                                                               cellspacing="0" width="100%"></table>
                                                        <?php
                                                    } // if bShowPledges
                                                    ?>

                                                    <p class="text-center">
                                                        <a class="btn btn-primary"
                                                           href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $family->getId() ?>/Pledge/v2-people-family-view-<?= $iFamilyID ?>"><i class="fa fa-plus"></i> <?= _("Add a new pledge") ?></a>
                                                        <a class="btn btn-default"
                                                           href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $family->getId() ?>/Payment/v2-people-family-view-<?= $iFamilyID ?>"><i class="fa fa-plus"></i> <?= _("Add a new payment") ?></a>
                                                    </p>

                                                    <?php
                                                    if (SessionUser::getUser()->isCanvasserEnabled()) {
                                                        ?>
                                                        <p class="text-center">
                                                            <a class="btn btn-default"
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
                                    <div role="tab-pane fade" class="tab-pane <?= ($mode == "Documents")?"active":"" ?>" id="notes">
                                        <div class="timeline">
                                            <!-- note time label -->
                                            <div class="time-label">
                                                <span class="bg-yellow">
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

                                                            <?php
                                                            if ($item['slim']) {
                                                                if ($item['editLink'] != '') {
                                                                    ?>
                                                                    <a href="#" data-id="<?= $item['id'] ?>" data-perid="<?= $item['perID'] ?>"
                                                                    data-famid="<?= $item['famID'] ?>" class="editDocument">
                                                                <span class="fa-stack">
                                                                <i class="fas fa-square fa-stack-2x"></i>
                                                                <i class="fas fa-edit fa-stack-1x fa-inverse"></i>
                                                                </span>
                                                            </a>
                                                                    <?php
                                                                }

                                                                if ($item['deleteLink'] != '') {
                                                                    ?>
                                                                    <a href="#" data-id="<?= $item['id'] ?>" data-perid="<?= $item['perID'] ?>"
                                                                        data-famid="<?= $item['famID'] ?>" class="deleteDocument">
                                                                        <span class="fa-stack">
                                                                            <i class="fas fa-square fa-stack-2x" style="color:red"></i>
                                                                            <i class="fas fa-trash-alt fa-stack-1x fa-inverse"></i>
                                                                        </span>
                                                                    </a>
                                                                <?php
                                                                }
                                                            }
                                                            ?>
                                                        </span>
                                                        <h3 class="timeline-header">
                                                            <?php
                                                            if (in_array('headerlink', $item)) {
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
                                                                    <?php
                                                                    if ($item['editLink'] != '') {
                                                                        ?>
                                                                        <a href="#" data-id="<?= $item['id'] ?>"
                                                                           data-perid="<?= $item['perID'] ?>"
                                                                           data-famid="<?= $item['famID'] ?>" class="editDocument">
                                                                            <button type="button" class="btn btn-primary"><i
                                                                                    class="fas fa-edit"></i></button>
                                                                        </a>
                                                                        <?php
                                                                    }

                                                                    if ($item['deleteLink'] != '') {
                                                                        ?>
                                                                        <a href="#" data-id="<?= $item['id'] ?>"
                                                                           data-perid="<?= $item['perID'] ?>"
                                                                           data-famid="<?= $item['famID'] ?>"
                                                                           class="deleteDocument">
                                                                            <button type="button" class="btn btn-danger"><i
                                                                                    class="fas fa-trash-alt"></i></button>
                                                                        </a>
                                                                        <?php
                                                                    }
                                                                    ?>

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
                        <?=  _("Private Data") ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

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
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("Cancel") ?></button>
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
} else if ($sMapProvider == 'BingMaps') {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
    <?php
}
?>

<script nonce="<?= $sCSPNonce ?>">
    window.CRM.currentPersonID = 0;
    window.CRM.currentFamily = <?= $iFamilyID ?>;
    window.CRM.docType = 'family';
    window.CRM.currentActive = <?= (empty($family->getDateDeactivated()) ? 'true' : 'false') ?>;
    window.CRM.fam_Name = "<?= $family->getName() ?>";
    window.CRM.iPhotoHeight = <?= SystemConfig::getValue("iPhotoHeight") ?>;
    window.CRM.iPhotoWidth = <?= SystemConfig::getValue("iPhotoWidth") ?>;
    window.CRM.familyMail = "<?= $family->getEmail() ?>";

    var dataT = 0;
    var dataPaymentTable = 0;
    var pledgePaymentTable = 0;

    <?php if ($location_available){ ?>
    // location and MAP
    window.CRM.churchloc = {
        lat: parseFloat(<?= $lat ?>),
        lng: parseFloat(<?= $lng ?>)
    };
    window.CRM.mapZoom = <?= $iLittleMapZoom ?>;

    initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, "<?= $family->getName() ?>", '', '');
    <?php } ?>
</script>
    
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
