<?php
/*******************************************************************************
 *
 *  filename    : personview.php
 *  last change : 2023-05-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\GroupPropMasterQuery;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Theme;
use EcclesiaCRM\dto\Cart;

require $sRootDocument . '/Include/Header.php';
?>

<?php if (!empty($PersonInfos['person']->getDateDeactivated())) {
    ?>
    <div class="alert alert-warning">
        <strong><?= _("This Person is Deactivated") ?> </strong>
    </div>
    <?php
} ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="sticky-top">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img
                                src="<?= $sRootPath . '/api/persons/' . $PersonInfos['person']->getId() . '/photo' ?>"
                                class="initials-image profile-user-img img-responsive img-rounded img-circle" alt="">
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
                        <h3 class="profile-username text-center">
                            <?php
                            if ($PersonInfos['person']->isMale()) {
                                ?>
                                <i class="fas fa-male"></i>
                                <?php
                            } elseif ($PersonInfos['person']->isFemale()) {
                                ?>
                                <i class="fas fa-female"></i>
                                <?php
                            }
                            ?>
                            <?= $PersonInfos['person']->getFullName() ?>
                        </h3>

                        <?php
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isEditRecordsEnabled()) {
                            ?>
                            <p class="text-muted text-center">
                                <?= empty($PersonInfos['person']->getFamilyRoleName()) ? _('Undefined') : _($PersonInfos['person']->getFamilyRoleName()); ?>
                                &nbsp;
                                <a id="edit-role-btn" data-person_id="<?= $PersonInfos['person']->getId() ?>"
                                   data-family_role="<?= $PersonInfos['person']->getFamilyRoleName() ?>"
                                   data-family_role_id="<?= $PersonInfos['person']->getFmrId() ?>" class="btn btn-box-tool btn-sm <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </p>
                            <?php
                        }
                        if ($PersonInfos['person']->getMembershipDate()) {
                            ?>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><?= _('Member Since') ?></b> <a class="float-right"><?= OutputUtils::FormatDate($PersonInfos['person']->getMembershipDate()->format('Y-m-d'), false) ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b><img
                                            src="<?= $sRootPath . "/skin/icons/markers/" . $PersonInfos['person']->getClassIcon() ?>"
                                            width="18" alt="">
                                        <?= _($PersonInfos['person']->getClassName()) ?>
                                    </b>

                                    <div class="float-right">
                                        <a id="edit-classification-btn" class="btn  btn btn-box-tool btn-sm <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>"
                                           data-person_id="<?= $PersonInfos['person']->getId() ?>"
                                           data-classification_id="<?= $PersonInfos['person']->getClassID() ?>"
                                           data-classification_role="<?= $PersonInfos['person']->getClassName() ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </li>
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
                                    <i class="fas fa-users"></i> <a href="<?= $sRootPath ?>/v2/group/<?= $groupAssigment->getGroupId()?>/view"><?= $groupAssigment->getGroupName() ?>
                                </b>

                                <div class="float-right">
                                    <?= _($groupAssigment->getRoleName()) ?>

                                    <a class="changeRole btn btn-box-tool btn-sm <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>"
                                           data-groupid="<?= $groupAssigment->getGroupId() ?>">
                                            <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </li>
                            <?php
                            }
                            ?>
                        </ul>
                        <?php
                        if ($bOkToEdit) {
                            ?>
                            <a href="<?= $sRootPath ?>/v2/people/person/editor/<?= $PersonInfos['person']->getId() ?>"
                               class="btn btn-primary btn-block"><b><?php echo _('Edit'); ?></b></a>
                            <?php
                        }
                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- About card -->
                <?php
                $can_see_privatedata = ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isEditRecordsEnabled()) ? true : false;
                ?>
                <div class="card">
                    <div class="card-header border-1">
                        <h3 class="card-title text-center"><i
                                class="fas fa-info-circle"></i> <?php echo _('Informations'); ?></h3>
                        <div class="card-tools pull-right">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="card-body">
                        <ul class="fa-ul">
                            <?php
                            if ($can_see_privatedata) {
                            if (count($PersonInfos['person']->getOtherFamilyMembers()) > 0) {
                                ?>
                                <li style="left:-28px"><strong><i class="fas fa-male"></i><i class="fas fa-female"></i><i
                                            class="fas fa-child"></i> <?php echo _('Family:'); ?></strong>
                                    <span>
            <?php
            if (!is_null($PersonInfos['person']->getFamily()) && $PersonInfos['person']->getFamily()->getId() != '') {
                ?>
                <a href="<?= $sRootPath ?>/v2/people/family/view/<?= $PersonInfos['person']->getFamily()->getId() ?>"><?= $PersonInfos['person']->getFamily()->getName() ?> </a>
                <a href="<?= $sRootPath ?>/v2/people/family/editor/<?= $PersonInfos['person']->getFamily()->getId() ?>"
                   class="table-link">
                  <span class="fa-stack">
                    <i class="fas fa-square fa-stack-2x"></i>
                    <i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>
                  </span>
                </a>
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

                            if (!empty($PersonInfos['formattedMailingAddress'])) {
                                $adresses = explode('<br>',$PersonInfos['plaintextMailingAddress']);
                                $count = count($adresses);
                                ?>
                                <li>
                                <strong>
                                    <i class="fa-li fas fa-home"></i><?php echo _('Address'); ?>:<?= $count>1?'<br>':'' ?>
                                </strong>
                                <span>
                                    <?php foreach ($adresses as $adress) { ?>
                                        <?= $count>1?'•':'' ?> <?= OutputUtils::GetLinkMapFromAddress($adress) ?><br>
                                    <?php } ?>
                                </span>
                                <?php if ($PersonInfos['location_available']) { ?>
                                    <div id="MyMap" style="width:100%"></div>
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
                                        if (!$PersonInfos['person']->hideAge()) {
                                            ?>
                                            (<span
                                                data-birth-date="<?= $PersonInfos['person']->getBirthDate()->format('Y-m-d') ?>"></span> <?= OutputUtils::FormatAgeSuffix($PersonInfos['person']->getBirthDate(), $PersonInfos['person']->getFlags()) ?>)
                                            <?php
                                        }
                                        ?>
                                    </p>
                                </li>
                                <?php
                            }
                            if (!SystemConfig::getValue('bHideFriendDate') && $PersonInfos['person']->getFriendDate() != '') { /* Friend Date can be hidden - General Settings */
                                ?>
                                <li><strong><i class="fa-li fas fa-tasks"></i><?= _('Friend Date') ?>:</strong>
                                    <span><?= OutputUtils::FormatDate($PersonInfos['person']->getFriendDate()->format('Y-m-d'), false) ?></span>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                        <hr/>
                        <ul class="fa-ul">
                            <?php

                            if ($PersonInfos['sCellPhone']) {
                                ?>
                                <li><strong><i class="fa-li fas fa-mobile"></i><?= _('Mobile Phone') ?>:</strong>
                                    <span><a
                                            href="tel:<?= $PersonInfos['sCellPhoneUnformatted'] ?>"><?= $PersonInfos['sCellPhone'] ?></a></span></li>
                                <li><strong><i class="fa-li fas fa-mobile"></i><?= _('Text Message') ?>:</strong>
                                    <span><a
                                            href="sms:<?= str_replace(' ', '', $PersonInfos['sCellPhoneUnformatted']) ?>&body=<?= _("EcclesiaCRM text message") ?>"><?= $PersonInfos['sCellPhone'] ?></a></span>
                                </li>
                                <?php
                            }

                            if ($PersonInfos['sHomePhone']) {
                                ?>
                                <li><strong><i class="fa-li fas fa-phone"></i><?= _('Home Phone') ?>:</strong> <span><a
                                            href="tel:<?= $PersonInfos['sHomePhoneUnformatted'] ?>"><?= $PersonInfos['sHomePhone'] ?></a></span></li>
                                <?php
                            }

                            if (!SystemConfig::getBooleanValue("bHideFamilyNewsletter")) { /* Newsletter can be hidden - General Settings */
                                ?>
                                <li><strong><i class="fa-li fab fa-hacker-news"></i><?= _("Send Newsletter") ?>:</strong>
                                    <span id="NewsLetterSend"></span>
                                </li>
                                <?php
                            }
                            if ($PersonInfos['sEmail'] != '') {
                                ?>
                                <li><strong><i class="fa-li far fa-envelope"></i><?= _('Email') ?>:</strong> <span><a
                                            href="mailto:<?= $PersonInfos['sUnformattedEmail'] ?>" target="_blank"><?= $PersonInfos['sEmail'] ?></a></span></li>
                                <?php
                                if ($isMailChimpActive) {
                                    ?>
                                    <li><strong><i class="fa-li fas fa-paper-plane"></i>MailChimp:</strong> <span
                                            id="mailChimpUserNormal"></span>
                                    </li>
                                    <?php
                                }
                            }

                            if ($PersonInfos['sWorkPhone']) {
                                ?>
                                <li><strong><i class="fa-li fas fa-phone"></i><?= _('Work Phone') ?>:</strong> <span><a
                                            href="tel:<?= $PersonInfos['sWorkPhoneUnformatted'] ?>"><?= $PersonInfos['sWorkPhone'] ?></a></span></li>
                                <?php
                            }

                            if ($PersonInfos['person']->getWorkEmail() != '') {
                                ?>
                                <li><strong><i class="fa-li far fa-envelope"></i><?= _('Work/Other Email') ?>:</strong>
                                    <span><a
                                            href="mailto:<?= $PersonInfos['person']->getWorkEmail() ?>"  target="_blank"><?= $PersonInfos['person']->getWorkEmail() ?></a></span>
                                </li>
                                <?php
                                if ($isMailChimpActive) {
                                    ?>
                                    <li><strong><i class="fa-li fas fa-paper-plane"></i>MailChimp <?= _("Work")?>:</strong> <span id="mailChimpUserWork"></span>
                                    </li>
                                    <?php
                                }
                            }

                            if ($PersonInfos['person']->getFacebookID() > 0) {
                                ?>
                                <li><strong><i class="fa-li fab fa-facebook"></i><?= _('Facebook') ?>:</strong>
                                    <span><a
                                            href="https://www.facebook.com/<?= InputUtils::FilterInt($PersonInfos['person']->getFacebookID()) ?>"><?= _('Facebook') ?></a></span>
                                </li>
                                <?php
                            }

                            if (strlen($PersonInfos['person']->getTwitter()) > 0) {
                                ?>
                                <li><strong><i class="fa-li fas fa-twitter"></i><?= _('Twitter') ?>:</strong> <span><a
                                            href="https://www.twitter.com/<?= InputUtils::FilterString($PersonInfos['person']->getTwitter()) ?>"><?= _('Twitter') ?></a></span>
                                </li>
                                <?php
                            }

                            if (strlen($PersonInfos['person']->getLinkedIn()) > 0) {
                                ?>
                                <li><strong><i class="fa-li fab fa-linkedin"></i><?= _('LinkedIn') ?>:</strong> <span><a
                                            href="https://www.linkedin.com/in/<?= InputUtils::FiltersTring($PersonInfos['person']->getLinkedIn()) ?>"><?= _('LinkedIn') ?></a></span>
                                </li>
                                <?php
                            }

                            } else {
                                ?>
                                <?=  _("Private Data") ?>
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
                </div>
                <div class="alert alert-info alert-dismissable">
                    <i class="fas  fa-tree"></i> <?php echo _('indicates items inherited from the associated family record.'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card special-card">
                <div class="card-body special-card-body">
                    <?php
                    $buttons = 0;

                    if (Cart::PersonInCart($PersonInfos['iPersonID']) && SessionUser::getUser()->isShowCartEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app RemoveOneFromPeopleCart" id="AddPersonToCart"
                           data-onecartpersonid="<?= $PersonInfos['iPersonID'] ?>"> <i class="fas fa-times"></i> <span
                                class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
                        <?php
                    } else if (SessionUser::getUser()->isShowCartEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app AddOneToPeopleCart" id="AddPersonToCart"
                           data-onecartpersonid="<?= $PersonInfos['iPersonID'] ?>"><i
                                class="fas fa-cart-plus"></i><span
                                class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isEmailEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app"
                           href="mailto:<?= urlencode(str_replace("<i class='fas  fa-tree'></i>", "", $PersonInfos['sEmail'])) ?>"  target="_blank"><i
                                class="far fa-paper-plane"></i><?= _('Email') ?></a>
                        <a class="btn btn-app"
                           href="mailto:?bcc=<?= urlencode(str_replace("<i class='fas  fa-tree'></i>", "", $PersonInfos['sEmail'])) ?>"  target="_blank"><i
                                class="fas fa-paper-plane"></i><?= _('Email (BCC)') ?></a>
                        <?php
                    }

                    if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId()) {

                            $buttons++;
                            ?>
                            <a class="btn btn-app" href="<?= $sRootPath ?>/v2/users/settings"><i
                                    class="fas fa-cog"></i> <?= _("Change Settings") ?></a>
                            <a class="btn btn-app" href="<?= $sRootPath ?>/v2/users/change/password/<?= $PersonInfos['iPersonID'] ?>"><i
                                    class="fas fa-key"></i> <?= _("Change Password") ?></a>
                            <?php
                        }
                        ?>
                        <a class="btn btn-app"
                           href="<?= $sRootPath ?>/v2/people/person/print/<?= $PersonInfos['iPersonID'] ?>"><i
                                class="fas fa-print"></i> <?= _("Printable Page") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isAdmin()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-info" href="#" data-toggle="modal" data-target="#confirm-verify"><i
                                class="fas fa-check-square"></i> <?= _("Verify Info") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isPastoralCareEnabled()) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-purple"
                           href="<?= $sRootPath ?>/v2/pastoralcare/person/<?= $PersonInfos['iPersonID'] ?>"
                           data-toggle="tooltip" data-placement="bottom" title="<?= _("Add a pastoral care note") ?>"><i
                                class="far fa-question-circle"></i> <?= _("Pastoral Care") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isNotesEnabled() || (SessionUser::getUser()->isEditSelfEnabled() && $PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId())) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app bg-gradient-green" href="#" id="createDocument" data-toggle="tooltip"
                           data-placement="bottom"
                           title="<?= _("Create a document") ?>"><i
                                class="fas fa-file"></i><?= _("Create a document") ?></a>
                        <?php
                    }
                    if (SessionUser::getUser()->isManageGroupsEnabled() or SessionUser::getUser()->isGroupManagerEnabled() ) {
                        $buttons++;
                        ?>
                        <a class="btn btn-app addGroup" data-personid="<?= $PersonInfos['iPersonID'] ?>"
                           data-toggle="tooltip" data-placement="bottom" title="<?= _("Assign this user to a group") ?>"><i
                                class="fas fa-users">
                            </i> <?= _("Assign New Group") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                         $buttons++;
                        ?>
                        <a class="btn btn-app bg-yellow-gradient <?= (mb_strlen($PersonInfos['person']->getAddress1()) == 0 || !is_null($PersonInfos['person']->getFamily()) && mb_strlen($PersonInfos['person']->getFamily()->getAddress1()) == 0)?'disabled':'' ?>"
                           data-toggle="tooltip" data-placement="bottom" title="<?= _("Get the vCard of the person") ?>"
                           href="<?= $sRootPath ?>/api/persons/addressbook/extract/<?= $PersonInfos['iPersonID'] ?>"><i
                                class="far fa-id-card">
                            </i> <?= _("vCard") ?></a>
                        <?php
                    }

                    if (SessionUser::getUser()->isAdmin()) {
                        if (!$PersonInfos['person']->isUser()) {
                            $buttons++;
                            ?>
                            <a class="btn btn-app"
                               href="<?= $sRootPath ?>/v2/users/editor/new/<?= $PersonInfos['iPersonID'] ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Create a CRM user") ?>"><i
                                    class="fas fa-user-secret"></i> <?= _('Make User') ?></a>
                            <?php
                        } else {
                            ?>
                            <a class="btn btn-app"
                               href="<?= $sRootPath ?>/v2/users/editor/<?= $PersonInfos['iPersonID'] ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Add rights to this user") ?>"><i
                                    class="fas fa-user-secret"></i> <?= _('Edit User') ?></a>
                            <?php
                        }
                    }

                    if ($bOkToEdit && SessionUser::getUser()->isDeleteRecordsEnabled() && $PersonInfos['iPersonID'] != 1) {// the super user can't be deactivated
                        $buttons++;
                        ?>
                        <button class="btn btn-app bg-gradient-orange" id="activateDeactivate">
                            <i class="fa <?= (empty($PersonInfos['person']->getDateDeactivated()) ? 'fa-times-circle' : 'fa-check-circle') ?> "></i><?php echo((empty($PersonInfos['person']->getDateDeactivated()) ? _('Deactivate') : _('Activate')) . " " . _(' this Person')); ?>
                        </button>
                        <?php
                    }

                    if (SessionUser::getUser()->isDeleteRecordsEnabled() && $PersonInfos['iPersonID'] != 1) {// the super user can't be deleted
                        $buttons++;

                        if (count($PersonInfos['person']->getOtherFamilyMembers()) > 0 || is_null($PersonInfos['person']->getFamily())) {
                            ?>
                            <a class="btn btn-app bg-gradient-maroon delete-person"
                               data-person_name="<?= $PersonInfos['person']->getFullName() ?>"
                               data-person_id="<?= $PersonInfos['iPersonID'] ?>"><i
                                    class="far fa-trash-alt"></i> <?= _("Delete this Record") ?>
                            </a>
                            <?php
                        } else {
                            ?>
                            <a class="btn btn-app bg-maroon"
                               href="<?= $sRootPath ?>/v2/people/family/delete/<?= $PersonInfos['person']->getFamily()->getId() ?>"><i
                                    class="far fa-trash-alt"></i><?= _("Delete this Record") ?></a>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <?php
            if (SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isGroupManagerEnabled() || (SessionUser::getUser()->isEditSelfEnabled() && $PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isSeePrivacyDataEnabled())) {
            ?>
            <div class="card">
                <div class="card-header  border-1">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills">
                        <?php
                        $activeTab = "";
                        if (($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId()
                            || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId()
                            || SessionUser::getUser()->isSeePrivacyDataEnabled())) {
                            $activeTab = "timeline";
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (!$bDocuments && !$bEDrive && !$bGroup) ? "active" : "" ?>"
                                   href=" #timeline" aria-controls="timeline" role="tab"
                                   data-toggle="tab"><i class="fas fa-clock"></i> <?= _('Timeline') ?></a></li>
                            <?php
                        }
                        ?>
                        <?php
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || count($PersonInfos['person']->getOtherFamilyMembers()) > 0 && SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (empty($activeTab)) ? 'active' : '' ?>"
                                   href="#family"
                                   aria-controls="family"
                                   role="tab"
                                   data-toggle="tab"><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i> <?= _('Family') ?></a>
                            </li>
                            <?php
                            if (empty($activeTab)) {
                                $activeTab = 'family';
                            }
                        }
                        ?>
                        <?php
                        if (SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isGroupManagerEnabled() || $PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId()) {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($bGroup) ? 'active' : '' ?>"
                                   href="#groups"
                                   aria-controls="groups"
                                   role="tab"
                                   data-toggle="tab"><i
                                        class="fas fa-users"></i> <?= _('Assigned Groups') ?></a></li>
                            <?php
                            if (empty($activeTab)) {
                                $activeTab = 'group';
                            }
                        }
                        ?>

                        <?php
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (empty($activeTab)) ? 'active' : '' ?>"
                               href="#properties"
                               aria-controls="properties"
                               role="tab"
                               data-toggle="tab"><i class="fas fa-user-cog"></i> <?= _('Assigned Properties') ?></a>
                        </li>
                        <?php
                        }
                        ?>

                        <?php
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isCanvasserEnabled()) {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link"
                                   href="#volunteer" aria-controls="volunteer" role="tab"
                                   data-toggle="tab"><i class="fas fa-hands-helping"></i> <?= _('Volunteer Opportunities') ?></a></li>
                            <?php
                            if (empty($activeTab)) {
                                $activeTab = 'properties';
                            }

                            if ($bGroup) $activeTab = 'group';
                        }
                        ?>
                        <?php
                        if (count($PersonInfos['person']->getOtherFamilyMembers()) == 0 && SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (empty($activeTab)) ? 'active' : '' ?>"
                                   href="#finance"
                                   aria-controls="finance"
                                   role="tab"
                                   data-toggle="tab"><i
                                        class="far fa-credit-card"></i> <?= _("Automatic Payments") ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                   href="#pledges" aria-controls="pledges" role="tab"
                                   data-toggle="tab">
                                    <i class="fas fa-bank"></i> <?= _("Pledges and Payments") ?>
                                </a>
                            </li>

                            <?php
                            if (empty($activeTab)) {
                                $activeTab = 'finance';
                            }
                        }
                        ?>
                        <?php
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isNotesEnabled()) {
                            if ($bDocuments) $activeTab = 'notes';
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($bDocuments) ? "active" : "" ?>"
                                   href="#notes"
                                   aria-controls="notes"
                                   role="tab"
                                   data-toggle="tab" <?= ($bDocuments) ? "aria-expanded=\"true\"" : "" ?>><i
                                        class="far fa-copy"></i> <?= _("Documents") ?></a></li>
                            <?php
                        }
                        ?>
                        <?php
                        if (SessionUser::getUser()->isEDriveEnabled($PersonInfos['iPersonID'])) {
                            if ($bEDrive) $activeTab = 'edrive';
                            ?>
                            <li class="nav-item">
                                <a class="nav-link  <?= ($bEDrive) ? "active" : "" ?>"
                                   href="#edrive"
                                   aria-controls="edrive"
                                   role="tab"
                                   data-toggle="tab" <?= ($bDocuments) ? "aria-expanded=\"true\"" : "" ?>><i
                                        class="fas fa-cloud"></i> <?= _("EDrive") ?></a></li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>

                <div class="card-body">
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <?php
                        if ($PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                            ?>
                            <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'timeline') ? "active" : "" ?>"
                                 id="timeline">
                                <div class="row filter-note-type card">
                                    <div class="col-md-1" style="line-height:27px">
                                        <table width=400px>
                                            <tr>
                                                <td>
                                                  <span class="time-line-head-red">
                                                    <?php
                                                    $now = new DateTime('');
                                                    echo $now->format(SystemConfig::getValue('sDateFormatLong'))
                                                    ?>
                                                  </span>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                </td>
                                                <td>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="timeline time-line-main">
                                    <!-- timeline time label -->
                                    <!--<li class="time-label">
                    <span class="bg-red">
                      <?php $now = new DateTime('');
                                    echo $now->format(SystemConfig::getValue('sDateFormatLong')) ?>
                    </span>
            </li>-->
                                    <div class="time-label">
                                    </div>
                                    <!-- /.timeline-label -->

                                    <!-- timeline item -->
                                    <?php
                                    $countMainTimeLine = 0;  // number of items in the MainTimeLines

                                    foreach ($timelineServiceItems as $item) {
                                        $countMainTimeLine++;

                                        if ($countMainTimeLine > $maxMainTimeLineItems) break;// we break after 20 $items
                                        ?>
                                        <div>
                                            <!-- timeline icon -->
                                            <i class="fa <?= $item['style'] ?>"></i>

                                            <div class="timeline-item">
                  <span class="time">
                    <i class="fas fa-clock"></i> <?= $item['datetime'] ?>
                  </span>
                                                <?php
                                                if (isset($item['style2'])) {
                                                    ?>
                                                    <i class="fa <?= $item['style2'] ?> share-type-2"></i>
                                                    <?php
                                                }
                                                ?>

                                                <h3 class="timeline-header">
                                                    <?php
                                                    if (array_key_exists('headerlink', $item) && $item['type'] != 'file') {
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
                                                    <?php
                                                    if ($item['type'] != 'file') {
                                                        ?>
                                                        <span
                                                            style="line-height: 1.2;"><?= ((!empty($item['info'])) ? $item['info'] . " : " : "") . $item['text'] ?></span>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <span
                                                            style="line-height: 1.2;"><?= ((!empty($item['info'])) ? $item['info'] . " : " : "") . '<a href="' . $sRootPath . '/api/filemanager/getFile/' . $item['perID'] . "/" . $item['text'] . '"><i class="fa ' . $item['style2'] . 'share-type-2"></i> "' . _("click to download") . '"</a>' ?></span>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <!-- END timeline item -->
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div role="tab-pane fade <?= ($activeTab == 'family') ? "active" : "" ?>" class="tab-pane"
                             id="family">
                            <?php
                            if ($PersonInfos['person']->getFamId() != '') {
                                ?>
                                <table class="table user-list table-hover">
                                    <thead>
                                    <tr>
                                        <th><span><?= _('Family Members') ?></span></th>
                                        <th class="text-center"><span><?= _('Role') ?></span></th>
                                        <th><span><?= _('Birthday') ?></span></th>
                                        <th><span><?= _('Email') ?></span></th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($PersonInfos['person']->getOtherFamilyMembers() as $familyMember) {
                                        $tmpPersonId = $familyMember->getId();
                                        ?>
                                        <tr>
                                            <td>
                                                <?= $familyMember->getJPGPhotoDatas() ?>
                                                <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $tmpPersonId ?>"
                                                   class="user-link"><?= $familyMember->getFullName() ?> </a>
                                            </td>
                                            <td class="text-center">
                                                <?= $familyMember->getFamilyRoleName() ?>
                                            </td>
                                            <td>
                                                <?= OutputUtils::FormatBirthDate($familyMember->getBirthYear(), $familyMember->getBirthMonth(), $familyMember->getBirthDay(), '-', $familyMember->getFlags()); ?>
                                            </td>
                                            <td>
                                                <?php
                                                $tmpEmail = $familyMember->getEmail();

                                                if ($tmpEmail != '') {
                                                    ?>
                                                    <a href="mailto:<?= $tmpEmail ?>"  target="_blank"><?= $tmpEmail ?></a>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                            <td style="width: 20%;">
                                                <?php
                                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                                    ?>
                                                    <a class="AddToPeopleCart" data-cartpersonid="<?= $tmpPersonId ?>">
                    <span class="fa-stack">
                      <i class="fas fa-square fa-stack-2x"></i>
                      <i class="fas fa-cart-plus fa-stack-1x fa-inverse"></i>
                    </span>
                                                    </a>
                                                    <?php
                                                }

                                                if ($bOkToEdit) {
                                                    ?>
                                                    <a href="<?= $sRootPath ?>/v2/people/person/editor/<?= $tmpPersonId ?>">
                      <span class="fa-stack" style="color:green">
                        <i class="fas fa-square fa-stack-2x"></i>
                        <i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>
                      </span>
                                                    </a>
                                                    <a class="delete-person"
                                                       data-person_name="<?= $familyMember->getFullName() ?>"
                                                       data-person_id="<?= $tmpPersonId ?>" data-view="family">
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
                                <?php
                            }
                            ?>
                        </div>
                        <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'group') ? "active" : "" ?>"
                             id="groups">
                            <div class="main-box clearfix">
                                <div class="main-box-body clearfix">
                                    <?php
                                    //Was anything returned?
                                    $i = 1;
                                    if ($ormAssignedGroups->count() == 0) {
                                        ?>
                                        <br>
                                        <div class="alert alert-warning">
                                            <i class="far fa-question-circle  fa-lg"></i>
                                            <span><?= _('No group assignments.') ?></span>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <?php
                                        // Loop through the rows
                                        $ids = SessionUser::getUser()->getGroupManagerIds();

                                        foreach ($ormAssignedGroups as $ormAssignedGroup) {
                                            if ( !SessionUser::getUser()->isManageGroups() && !in_array($ormAssignedGroup->getGroupID(),$ids) ) continue;

                                            if ($i % 3 == 0 || $i == 1) {
                                                $i=1;
                                                ?>
                                                <div class="row">
                                                <?php
                                            }
                                            ?>
                                            <div class="col-md-6">
                                                <!-- Info box -->
                                                <div class="card">
                                                    <div class="card-header bg-gradient-secondary">
                                                        <h3 class="card-title" style="font-size:tiny">
                                                        <a class="btn btn-default btn-sm" href="<?= $sRootPath ?>/v2/group/<?= $ormAssignedGroup->getGroupID() ?>/view"><?= $ormAssignedGroup->getGroupName() ?></a> (<?= _($ormAssignedGroup->getRoleName()) ?>)
                                                        </h3>

                                                        <div class="pull-right">
                                                            <div class="label bg-aqua">
                                                                <code>
                                                                    <a href="<?= $sRootPath ?>/v2/group/<?= $ormAssignedGroup->getGroupID() ?>/view"
                                                                       class="btn btn-success btn-xs" role="button"><i class="fas fa-list"></i>
                                                                    </a>
                                                                    <div class="btn-group">
                                                                        <button type="button"
                                                                                class="btn btn-default btn-xs"><?= _('Action') ?></button>
                                                                        <button type="button"
                                                                                class="btn btn-default dropdown-toggle btn-xs"
                                                                                data-toggle="dropdown">
                                                                            <span class="caret"></span>
                                                                            <span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <div class="dropdown-menu" role="menu">
                                                                            <a class="dropdown-item changeRole"
                                                                               data-groupid="<?= $ormAssignedGroup->getGroupID() ?>">
                                                                                <?= _('Change Role') ?>
                                                                            </a>
                                                                            <?php
                                                                            if ($ormAssignedGroup->getHasSpecialProps()) {
                                                                                ?>
                                                                                <a class="dropdown-item"
                                                                                   href="<?= $sRootPath ?>/v2/group/props/editor/<?= $ormAssignedGroup->getGroupID() ?>/<?= $PersonInfos['iPersonID'] ?>">
                                                                                    <?= _('Update Properties') ?>
                                                                                </a>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="btn-group">
                                                                        <button
                                                                            data-groupid="<?= $ormAssignedGroup->getGroupID() ?>"
                                                                            data-groupname="<?= $ormAssignedGroup->getGroupName() ?>"
                                                                            type="button"
                                                                            class="btn btn-danger groupRemove btn-xs"
                                                                            data-toggle="dropdown"><i
                                                                                class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>
                                                                </code>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        if ( SessionUser::getUser()->isManageGroupsEnabled() or SessionUser::getUser()->isGroupManagerEnabled() ) {
                                                            ?>
                                                            <div class="text-center"></div>
                                                            <?php
                                                        }
                                                        // If this group has associated special properties, display those with values and prop_PersonDisplay flag set.
                                                        if ($ormAssignedGroup->getHasSpecialProps()) {
                                                            // Get the special properties for this group only for the group
                                                            $ormPropLists = GroupPropMasterQuery::Create()->filterByPersonDisplay('false')->orderByPropId()->findByGroupId($ormAssignedGroup->getGroupId());
                                                            ?>

                                                            <small>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h5><?= _("Group Informations") ?></h5>
                                                                        <?php
                                                                            if ($ormPropLists->count() > 0) {
                                                                            ?>
                                                                        <ul>
                                                                            <?php
                                                                            foreach ($ormPropLists as $ormPropList) {
                                                                                $prop_Special = $ormPropList->getSpecial();
                                                                                if ($ormPropList->getTypeId() == 11) {
                                                                                    $prop_Special = $sPhoneCountry;
                                                                                }
                                                                                ?>
                                                                                <li>
                                                                                    <strong><?= $ormPropList->getName() ?></strong>: <?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $ormPropList->getDescription(), $prop_Special) ?>
                                                                                </li>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </ul>
                                                                        <?php
                                                                            } else {
                                                                        ?>
                                                                            <?= _("None") ?>
                                                                        <?php
                                                                            }
                                                                        ?>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <?php

                                                                    // now we add only the personnal group prop
                                                                    $ormPropLists = GroupPropMasterQuery::Create()->filterByPersonDisplay('true')->orderByPropId()->findByGroupId($ormAssignedGroup->getGroupId());

                                                                    $sSQL = 'SELECT * FROM groupprop_' . $ormAssignedGroup->getGroupId() . ' WHERE per_ID = ' . $PersonInfos['iPersonID'];

                                                                    $statement = $connection->prepare($sSQL);
                                                                    $statement->execute();
                                                                    $aPersonProps = $statement->fetch(PDO::FETCH_BOTH);

                                                                    if ($ormPropLists->count() > 0) {
                                                                        ?>
                                                                        <h5><?= _("Person Informations") ?></h5>
                                                                        <ul>
                                                                            <?php
                                                                            foreach ($ormPropLists as $ormPropList) {
                                                                                $currentData = trim($aPersonProps[$ormPropList->getField()]);
                                                                                if (strlen($currentData) > 0) {
                                                                                    $prop_Special = $ormPropList->getSpecial();
                                                                                    if ($ormPropList->getTypeId() == 11) {
                                                                                        $prop_Special = $sPhoneCountry;
                                                                                    }
                                                                                    ?>
                                                                                    <li>
                                                                                        <strong><?= $ormPropList->getName() ?></strong>: <?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $currentData, $prop_Special) ?>
                                                                                    </li>
                                                                                    <?php
                                                                                }
                                                                            }

                                                                            ?>
                                                                        </ul>
                                                                        <div class="text-center">
                                                                            <a href="<?= $sRootPath ?>/v2/group/props/editor/<?= $ormAssignedGroup->getGroupId() ?>/<?= $PersonInfos['iPersonID'] ?>"
                                                                            class="btn btn-primary"><?= _("Modify Specific Properties") ?></a>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                    </div>
                                                                </div>
                                                            </small>
                                                            <?php
                                                        } else {
                                                            ?>
                                                                <?= _("No specific group properties defined !") ?>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <!-- /.box -->
                                            </div>
                                            <?php
                                            // NOTE: this method is crude.  Need to replace this with use of an array.
                                            $sAssignedGroups .= $ormAssignedGroup->getGroupID() . ',';
                                            $i++;
                                            if ($i % 3 == 0) {
                                                $i=1;
                                                ?>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }

                                    if ($i > 1) {
                                    ?>
                                </div>
                                <?php
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                    <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'properties') ? "active" : "" ?>"
                         id="properties">
                        <div class="main-box clearfix">
                            <div class="main-box-body clearfix">
                                <div class="alert alert-warning"
                                     id="properties-warning" <?= ($ormAssignedProperties->count() > 0) ? 'style="display: none;"' : '' ?>>
                                    <i class="far fa-question-circle  fa-lg"></i>
                                    <span><?= _('No property assignments.') ?></span>
                                </div>
                                <?php
                                $sAssignedProperties = ',';
                                ?>

                                <div
                                    id="properties-table" <?= ($ormAssignedProperties->count() == 0) ? 'style="display: none;"' : '' ?>>
                                    <table class="table table-condensed dt-responsive"
                                           id="assigned-properties-table"
                                           width="100%"></table>
                                </div>

                                <?php if (SessionUser::getUser()->isEditRecordsEnabled() && $bOkToEdit && $ormProperties->count() != 0): ?>
                                    <div class="alert alert-secondary">
                                        <div>
                                            <h4><strong><?= _('Assign a New Property') ?>:</strong></h4>
                                            <div class="row">
                                                <div class="form-group col-xs-12 col-md-7">
                                                    <select name="PropertyId" id="input-person-properties"
                                                            class="form-control input-person-properties select2"
                                                            style="width:100%"
                                                            data-placeholder="<?= _("Select") ?> ..."
                                                            data-personID="<?= $PersonInfos['iPersonID'] ?>">
                                                        <option disabled selected> -- <?= _('select an option') ?>--
                                                        </option>
                                                        <?php
                                                        foreach ($ormProperties as $ormProperty) {
                                                            $attributes = "value=\"{$ormProperty->getProId()}\" ";
                                                            if (strlen(strstr($sAssignedProperties, ',' . $ormProperty->getProId() . ',')) == 0) {
                                                                ?>
                                                                <option value="<?= $ormProperty->getProId() ?>"
                                                                        data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>"
                                                                        data-pro_Value=""><?= $ormProperty->getProName() ?></option>
                                                            <?php }

                                                        } ?>
                                                    </select>
                                                </div>
                                                <div id="prompt-box" class="col-xs-12 col-md-7">

                                                </div>
                                                <div class="form-group col-xs-12 col-md-7">
                                                    <input id="assign-property-btn" type="submit"
                                                           class="btn btn-primary  assign-property-btn"
                                                           value="<?= _('Assign') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'finance') ? "active" : "" ?>"
                         id="volunteer">
                        <div class="main-box clearfix">
                            <div class="main-box-body clearfix">
                                <?php

                                //Initialize row shading
                                $sRowClass = 'RowColorA';

                                $sAssignedVolunteerOpps = ',';

                                //Was anything returned?
                                ?>
                                <div class="alert alert-warning"
                                     id="volunter-warning" <?= ($ormAssignedVolunteerOpps->count() > 0) ? 'style="display: none;"' : '' ?>>
                                    <i class="far fa-question-circle  fa-lg"></i>
                                    <span><?= _('No volunteer opportunity assignments.') ?></span>
                                </div>

                                <div
                                    id="volunter-table" <?= ($ormAssignedVolunteerOpps->count() == 0) ? 'style="display: none;"' : '' ?>>
                                    <table class="table table-condensed dt-responsive"
                                           id="assigned-volunteer-opps-table"
                                           width="100%"></table>
                                </div>

                                <?php
                                if (SessionUser::getUser()->isEditRecordsEnabled() && $ormVolunteerOpps->count()) {
                                    ?>
                                    <div class="alert alert-secondary">
                                        <div>
                                            <h4><strong><?= _('Assign a New Volunteer Opportunity') ?>:</strong>
                                            </h4>

                                            <div class="row">
                                                <div class="form-group col-xs-12 col-md-7">
                                                    <select id="input-volunteer-opportunities"
                                                            name="VolunteerOpportunityIDs[]" multiple
                                                            class="form-control select2" style="width:100%"
                                                            data-placeholder="<?= _("Select") ?>...">
                                                        <?php
                                                        foreach ($ormVolunteerOpps as $ormVolunteerOpp) {
                                                            //If the property doesn't already exist for this Person, write the <OPTION> tag
                                                            if (strlen(strstr($sAssignedVolunteerOpps, ',' . $ormVolunteerOpp->getId() . ',')) == 0) {
                                                                ?>
                                                                <option
                                                                    value="<?= $ormVolunteerOpp->getId() ?>"><?= $ormVolunteerOpp->getName() ?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-xs-12 col-md-7">
                                                    <input type="submit" value="<?= _('Assign') ?>"
                                                           name="VolunteerOpportunityAssign"
                                                           class="btn btn-primary VolunteerOpportunityAssign">
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
                        <div role="tab-pane fade" class="tab-pane" id="finance">
                            <div class="main-box clearfix">
                                <div class="main-box-body clearfix">
                                    <?php
                                    if (!is_null($PersonInfos['person']->getFamily())) {
                                        if ($ormAutoPayments->count() > 0) {
                                            ?>
                                            <table class="table table-striped table-bordered"
                                                   id="automaticPaymentsTable"
                                                   cellpadding="5" cellspacing="0" width="100%"></table>
                                            <?php
                                        }
                                        ?>
                                        <p class="text-center">
                                            <a class="btn btn-primary"
                                               href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/-1/<?= $PersonInfos['person']->getFamily()->getId() ?>/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-plus"></i> <?= _("Add a new automatic payment") ?></a>
                                        </p>
                                        <?php
                                    } else {
                                        ?>
                                        <?= _("You must set an address for this person") ?>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div role="tab-pane fade" class="tab-pane" id="pledges">
                            <div class="main-box clearfix">
                                <div class="main-box-body clearfix">
                                    <?php
                                    $tog = 0;

                                    if (($_SESSION['sshowPledges'] || $_SESSION['sshowPayments']) && !is_null($PersonInfos['person']->getFamily())) {
                                        ?>
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
                                        <table id="pledgePaymentTable" class="table table-striped table-bordered"
                                               cellspacing="0" width="100%"></table>
                                        <p class="text-center">
                                            <a class="btn btn-primary"
                                               href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $PersonInfos['person']->getFamily()->getId() ?>/Pledge/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-plus"></i> <?= _("Add a new pledge") ?></a>
                                            <a class="btn btn-default"
                                               href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $PersonInfos['person']->getFamily()->getId() ?>/Payment/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-plus"></i> <?= _("Add a new payment") ?></a>
                                        </p>
                                        <?php
                                    } else {
                                        ?>
                                        <?= _("You must set an address for this person") ?>
                                        <?php
                                    }
                                    ?>


                                    <?php
                                    if (SessionUser::getUser()->isCanvasserEnabled() && !is_null($PersonInfos['person']->getFamily())) {
                                        ?>
                                        <p class="text-center">
                                            <a class="btn btn-default"
                                               href="<?= $sRootPath ?>/v2/people/canvass/editor/<?= $PersonInfos['person']->getFamily()->getId() ?>/<?= $_SESSION['idefaultFY'] ?>/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-eye"></i> <?= MiscUtils::MakeFYString($_SESSION['idefaultFY']) . _(" Canvass Entry") ?></a>
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
                    <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'notes') ? "active" : "" ?>"
                         id="notes">
                        <div class="row filter-note-type card">
                            <div class="col-md-1" style="line-height:27px">
                                <table width=370px>
                                    <tr>
                                        <td>
                                            <span class="time-line-head-yellow">
                                              <?php echo date_create()->format(SystemConfig::getValue('sDateFormatLong')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select name="PropertyId" class="filter-timeline form-control form-control-sm" size="1"
                                                    style="width:170px" data-placeholder="<?= _("Select") ?> ..."
                                                    data-toggle="tooltip" data-placement="bottom" title="<?= _("Filter your documents by : ") ?>">
                                                <option value="all"><?= _("All type") ?></option>
                                                <option value="note"><?= MiscUtils::noteType("note") ?></option>
                                                <option value="video"><?= MiscUtils::noteType("video") ?></option>
                                                <option value="audio"><?= MiscUtils::noteType("audio") ?></option>
                                                <option disabled="disabled">_____________________________</option>
                                                <option value="shared"><?= _("Shared documents") ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="timeline time-line-note">
                            <!-- note time label -->
                            <div class="time-label"></div>
                            <!-- /.note-label -->

                            <!-- note item -->
                            <?php
                            $note_content = "";// this assume only the last note is visible

                            foreach ($timelineNotesServiceItems as $item) {
                                if ($note_content != $item['text'] && $item['type'] != 'file') {// this assume only the last note is visible

                                    $note_content = $item['text']; // this assume only the last note is visible
                                    ?>
                                    <div
                                        class="type-<?= $item['type'] ?><?= (isset($item['style2']) ? " type-shared" : "") ?>">
                                        <!-- timeline icon -->
                                        <i class="fa <?= $item['style'] ?> icon-<?= $item['type'] ?><?= (isset($item['style2']) ? " icon-shared" : "") ?>"></i>

                                        <div class="timeline-item">
                                                <span class="time">
                     <i class="fas fa-clock"></i> <?= $item['datetime'] ?>
                      &nbsp;
                     <?php
                     if ($item['slim'] && (!isset($item['currentUserName']) || $item['userName'] == $PersonInfos['person']->getFullName())) {
                         if ($item['editLink'] != '' || (isset($item['sharePersonID']) && $item['shareRights'] == 2)) {
                             ?>
                             <!--<a href="<?= $item['editLink'] ?>">-->
                             <?= $item['editLink'] ?>
                             <span class="fa-stack" data-toggle="tooltip" data-placement="bottom" title="<?= _("Edit this document") ?>">
                          <i class="fas fa-square fa-stack-2x"></i>
                          <i class="fas fa-edit fa-stack-1x fa-inverse"></i>
                        </span>
                             </a>
                             <?php
                         }
                         if ($item['deleteLink'] != '' && !isset($item['sharePersonID']) && (!isset($item['currentUserName']) || $item['userName'] == $PersonInfos['person']->getFullName())) {
                             ?>
                             <?= $item['deleteLink'] ?>
                             <span class="fa-stack" data-toggle="tooltip" data-placement="bottom" title="<?= _("Delete this document") ?>">
                          <i class="fas fa-square fa-stack-2x" style="color:red"></i>
                          <i class="fas fa-trash-alt fa-stack-1x fa-inverse"></i>
                        </span>
                             </a>
                             <?php
                         }
                         if (!isset($item['sharePersonID']) && (!isset($item['currentUserName']) || $item['userName'] == $PersonInfos['person']->getFullName())) {
                             ?>
                             <span class="fa-stack shareNote" data-id="<?= $item['id'] ?>"
                                   data-shared="<?= $item['isShared'] ?>"
                                   data-toggle="tooltip" data-placement="bottom" title="<?= _("Share this document to another user") ?>">
                          <i class="fas fa-square fa-stack-2x"
                             style="color:<?= $item['isShared'] ? "green" : "#777" ?>"></i>
                          <i class="fas fa-share-square fa-stack-1x fa-inverse"></i>
                        </span>
                             <?php
                         }
                     } ?>
                                                    <?php
                                                    if ($item['type'] == 'note' && $PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId()) {
                                                        ?>
                                                        <span class="fa-stack saveNoteAsWordFile"
                                                              data-id="<?= $item['id'] ?>"
                                                              data-toggle="tooltip" data-placement="bottom" title="<?= _("Export this document to word Format") ?>">
                          <i class="fas fa-square fa-stack-2x" style="color:#001FFF"></i>
                          <i class="fas fa-file-word fa-stack-1x fa-inverse"></i>
                        </span>
                                                        <?php
                                                    }
                                                    ?>
                    </span>

                                            <?php
                                            if (isset($item['style2'])) {
                                                ?>
                                                <i class="fa <?= $item['style2'] ?> share-type-2"></i>
                                                <?php
                                            }
                                            ?>
                                            <h3 class="timeline-header">

                                                <?php
                                                if (array_key_exists('headerlink', $item) && !isset($item['sharePersonID'])) {
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
                                                <?php
                                                if (isset($item['currentUserName'])) {
                                                    ?>
                                                    <p class="text-danger">
                                                        <small><?= $item['currentUserName'] ?></small>
                                                    </p><br>
                                                    <?php
                                                } else if (isset($item['lastEditedBy'])) {
                                                    ?>
                                                    <p class="text-success">
                                                        <small><?= _("Last modification by") . " : " . $item['lastEditedBy'] ?></small>
                                                    </p><br>
                                                    <?php
                                                }
                                                ?>
                                                <?= ((!empty($item['info'])) ? $item['info'] . " : " : "") . $item['text'] ?>
                                            </div>

                                            <?php
                                            if ((SessionUser::getUser()->isNotesEnabled()) && ($item['editLink'] != '' || $item['deleteLink'] != '')) {
                                                ?>
                                                <div class="timeline-footer">
                                                    <?php
                                                    if (!$item['slim']) {
                                                        if ($item['editLink'] != '') {
                                                            ?>
                                                            <?= $item['editLink'] ?>
                                                            <button type="button" class="btn btn-primary editDocument"
                                                                    data-id="<?= $item['id'] ?>"
                                                                    data-perid="<?= $item['perID'] ?>" data-famid="0"><i
                                                                    class="fas fa-edit"></i></button>
                                                            </button>
                                                            <?php
                                                        }

                                                        if ($item['deleteLink'] != '') {
                                                            ?>
                                                            <?= $item['deleteLink'] ?>
                                                            <button type="button" class="btn btn-danger"><i
                                                                    class="fas fa-trash-alt"></i></button>
                                                            </button>
                                                            <?php
                                                        }

                                                        if (!isset($item['sharePersonID'])) {
                                                            ?>
                                                            <button type="button" data-id="<?= $item['id'] ?>"
                                                                    data-shared="<?= $item['isShared'] ?>"
                                                                    class="btn btn-<?= $item['isShared'] ? "success" : "default"
                                                                    ?> shareNote"><i class="fas fa-share-square"></i>
                                                            </button>
                                                            <?php
                                                        }
                                                        ?>
                                                        <button type="button" data-id="<?= $item['id'] ?>"
                                                                data-shared="<?= $item['isShared'] ?>"
                                                                class="btn btn-<?= $item['isShared'] ? "success" : "default"
                                                                ?> shareNote"><i class="fas fa-share-square"></i>
                                                        </button>

                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <!-- END timeline item -->
                        </div>
                    </div>
                    <?php
                    if (SessionUser::getUser()->isEDriveEnabled($PersonInfos['iPersonID'])) {
                        ?>
                        <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'edrive') ? "active" : "" ?>"
                             id="edrive">
                            <div class="row filter-note-type card" style="line-height:54px">
                                <div class="col-md-12" style="line-height:25px">
                                    <table width=400px>
                                        <tr>
                                            <td>
                                                  <span class="time-line-head-red">
                                                    <?= _("All Files") ?>
                                                  </span>

                                                &nbsp;&nbsp;&nbsp;

                                                <div class="btn-group">
                                                  <?php
                                                    if (SessionUser::getUser()->isNotesEnabled() || (SessionUser::getUser()->isEditSelfEnabled() && $PersonInfos['person']->getId() == SessionUser::getUser()->getPersonId() || $PersonInfos['person']->getFamId() == SessionUser::getUser()->getPerson()->getFamId())) {
                                                  ?>
                                                        <button type="button" id="uploadFile" class="btn btn-success btn-sm drag-elements" data-personid="<?= $PersonInfos['iPersonID'] ?>" data-toggle="tooltip" data-placement="top" title="<?= _("Upload a file in EDrive") ?>">
                                                            &nbsp;&nbsp;<i class="fas fa-cloud-upload-alt"></i>&nbsp;&nbsp;
                                                        </button>
                                                  <?php
                                                    }
                                                    ?>

                                                    <button type="button" class="btn btn-primary btn-sm drag-elements new-folder" data-personid="<?= $PersonInfos['iPersonID'] ?>"
                                                            data-toggle="tooltip" data-placement="top" title="<?= _("Create a Folder") ?>">
                                                        &nbsp;&nbsp;<i class="far fa-folder"></i>&nbsp;&nbsp;
                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-sm drag-elements trash-drop" data-personid="<?= $PersonInfos['iPersonID'] ?>"
                                                            data-toggle="tooltip" data-placement="top" title="<?= _("Delete") ?>">
                                                        &nbsp;&nbsp;<i class="fas fa-trash-alt"></i>&nbsp;&nbsp;
                                                    </button>

                                                    <button type="button" class="btn btn-info btn-sm drag-elements folder-back-drop" data-personid="<?= $PersonInfos['iPersonID'] ?>"
                                                            data-toggle="tooltip" data-placement="top" title="<?= _("Up One Level") ?>"
                                                        <?= (!is_null($PersonInfos['user']) && $PersonInfos['user']->getCurrentpath() != "/") ? "" : 'style="display: none;"' ?>>
                                                        &nbsp;&nbsp;<i class="fas fa-level-up-alt"></i>&nbsp;&nbsp;
                                                    </button>


                                                    <button type="button" class="btn btn-default btn-sm drag-elements filemanager-refresh"
                                                            data-toggle="tooltip" data-placement="top" title="<?= _("Actualize files") ?>">
                                                        &nbsp;&nbsp;<i class="fas fa-sync-alt"></i>&nbsp;&nbsp;
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <br/>
                            <br/>
                            <br/>
                            <div class="row">
                                <div class="col-md-12 filmanager-left">
                                    <table class="table table-striped table-bordered" id="edrive-table"
                                           width="100%"></table>
                                </div>
                                <div class="col-md-3 filmanager-right" style="display: none;">
                                    <h3><?= _("Preview") ?>
                                        <button type="button" class="close close-file-preview" data-dismiss="alert"
                                                aria-hidden="true">×
                                        </button>
                                    </h3>
                                    <span class="preview"></span>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="col-md-12">
                <span class="float-left" id="currentPath">
                  <?= !is_null($PersonInfos['user']) ? MiscUtils::pathToPathWithIcons($PersonInfos['user']->getCurrentpath()) : "" ?>
                </span>
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
        } else {
            ?>
            <div class="card  card-primary">
                <div class="card-header  border-1">
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

<!-- Modal -->
<div id="photoUploader"></div>

<div class="modal fade" id="confirm-delete-image" tabindex="-1" role="dialog" aria-labelledby="delete-Image-label"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="delete-Image-label"><?= _('Confirm Delete') ?></h4>
                <button type="button" class="bootbox-close-button close" aria-hidden="true" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <p><?= _('You are about to delete the profile photo, this procedure is irreversible.') ?></p>

                <p><?= _('Do you want to proceed?') ?></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("Cancel") ?></button>
                <button class="btn btn-danger danger" id="deletePhoto"><?= _("Delete") ?></button>
            </div>
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
                    <b><?= _("Select how do you want to request the person information to be verified") ?></b>
                </p>
            </div>
            <div class="modal-footer text-center">
                <?php
                if (count($familyInfos['sFamilyEmails']) > 0 && !empty(SystemConfig::getValue('sSMTPHost'))) {
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

<?php if (SessionUser::getUser()->isEDriveEnabled($PersonInfos['iPersonID'])) : ?>
<script src="<?= $sRootPath ?>/skin/js/filemanager.js"></script>
<?php endif ?>

<script src="<?= $sRootPath ?>/skin/external/jquery-photo-uploader/PhotoUploader.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/MemberView.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/AddRemoveCart.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/PersonView.js"></script>


<!-- Document editor -->
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= $sRootPath ?>/skin/js/document.js"></script>
<!-- !Document editor -->

<!-- Drag and drop -->
<script src="<?= $sRootPath ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script
    src="<?= $sRootPath ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
<!-- !Drag and Drop -->

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
    window.CRM.currentPersonID = <?= $PersonInfos['iPersonID'] ?>;
    window.CRM.currentFamily = <?= $familyInfos['iFamilyID'] ?>;
    window.CRM.docType = 'person';
    window.CRM.iPhotoHeight = <?= SystemConfig::getValue("iPhotoHeight") ?>;
    window.CRM.iPhotoWidth = <?= SystemConfig::getValue("iPhotoWidth") ?>;
    window.CRM.currentActive = <?= (empty($PersonInfos['person']->getDateDeactivated()) ? 'true' : 'false') ?>;
    window.CRM.personFullName = "<?= $PersonInfos['person']->getFullName() ?>";
    window.CRM.normalMail = "<?= $PersonInfos['sEmail'] ?>";
    window.CRM.workMail = "<?= $PersonInfos['person']->getWorkEmail() ?>";
    window.CRM.browserImage = false;

    window.CRM.contentsExternalCssFont = '<?= $contentsExternalCssFont ?>';
    window.CRM.extraFont = '<?= $extraFont ?>';

    if ((/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
        (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)))) {
        $(".fa-special-icon").addClass("fa-2x");
    }

    <?php if ($PersonInfos['location_available']){ ?>
        // location and MAP
        window.CRM.churchloc = {
            lat: parseFloat(<?= $PersonInfos['lat'] ?>),
            lng: parseFloat(<?= $PersonInfos['lng'] ?>)
        };
        window.CRM.mapZoom   = <?= $iLittleMapZoom ?>;

        initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, 'titre', "<?= $PersonInfos['person']->getFullName() ?>", '');
    <?php } ?>
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
