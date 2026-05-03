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

$otherFamilyMembers = $PersonInfos['OtherFamilyMembers'];
$otherFamilyMembersCount = count($otherFamilyMembers);
$assignedGroupCount = $ormAssignedGroups->count();
$can_see_privatedata = ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isSeePrivacyDataEnabled() or SessionUser::getUser()->isEditRecordsEnabled()) ? true : false;

$personRoleLabel = empty($PersonInfos['FamilyRoleName']) ? _('Undefined') : _($PersonInfos['FamilyRoleName']);
$personClassLabel = _($PersonInfos['ClassName']);

// --- OPTIMISATION : préchargement des propriétés de groupe et des valeurs personnalisées ---
$groupIds = [];
foreach ($ormAssignedGroups as $group) {
    $groupIds[] = $group->getGroupId();
}

// Précharger toutes les propriétés spéciales pour tous les groupes
$allProps = GroupPropMasterQuery::create()
    ->filterByGroupId($groupIds)
    ->orderByPropId()
    ->find();

$propsByGroup = [];
foreach ($allProps as $prop) {
    $gid = $prop->getGroupId();
    $display = $prop->getPersonDisplay() ? 'true' : 'false';
    $propsByGroup[$gid][$display][] = $prop;
}

// Précharger toutes les valeurs personnalisées pour la personne sur tous les groupes
$aPersonPropsByGroup = [];
foreach ($groupIds as $gid) {
    // Vérifie si la table existe avant de requêter
    $sSQLCheck = "SHOW TABLES LIKE 'groupprop_" . $gid . "'";
    $statementCheck = $connection->prepare($sSQLCheck);
    $statementCheck->execute();
    if ($statementCheck->fetch()) {
        $sSQL = 'SELECT * FROM groupprop_' . $gid . ' WHERE per_ID = ' . $PersonInfos['iPersonID'];
        $statement = $connection->prepare($sSQL);
        $statement->execute();
        $aPersonPropsByGroup[$gid] = $statement->fetch(PDO::FETCH_BOTH);
    } else {
        $aPersonPropsByGroup[$gid] = null;
    }
}
// --- FIN OPTIMISATION ---
?>

<?php if (!empty($PersonInfos['DateDeactivated'])) {
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
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img
                                src="<?= $sRootPath . '/api/persons/' . $PersonInfos['iPersonID'] . '/photo' ?>"
                                class="initials-image profile-user-img img-responsive img-rounded img-circle" alt="">
                            <?php
                            if ($bOkToEdit) {
                                ?>
                                <div class="after">
                                    <div class="buttons">
                                        <a href="#" class="hide" id="view-larger-image-btn" href="#"
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
                            <?= $PersonInfos['FullName'] ?>
                        </h3>
                        <div class="text-center mb-3">
                            <span class="badge badge-primary mr-1 mb-1"><i class="fas fa-user-tag mr-1"></i><?= $personRoleLabel ?></span>
                            <span class="badge badge-info mr-1 mb-1"><i class="fas fa-layer-group mr-1"></i><?= $personClassLabel ?></span>
                            <span class="badge badge-light mr-1 mb-1"><i class="fas fa-users mr-1"></i><?= $assignedGroupCount . ' ' . _('Groups') ?></span>
                            <?php if ($otherFamilyMembersCount > 0) { ?>
                                <span class="badge badge-light mb-1"><i class="fas fa-home mr-1"></i><?= ($otherFamilyMembersCount + 1) . ' ' . _('Family Members') ?></span>
                            <?php } ?>
                        </div>

                        <?php
                        if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() 
                            or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() 
                            or SessionUser::getUser()->isEditRecordsEnabled()) {
                            ?>
                            <p class="text-muted text-center mb-2">
                                <?= $personRoleLabel ?>
                                &nbsp;
                                <a id="edit-role-btn" data-person_id="<?= $PersonInfos['iPersonID'] ?>"
                                   data-family_role="<?= $PersonInfos['FamilyRoleName'] ?>"
                                   data-family_role_id="<?= $PersonInfos['FamilyRoleId'] ?>" class="btn btn-box-tool btn-sm <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </p>
                            <p class="text-muted text-center mb-3">
                                <b><img src="<?= $sRootPath . "/skin/icons/markers/" . $PersonInfos['ClassIcon'] ?>"
                                            width="18" alt="">
                                        <?= $personClassLabel ?>
                                    </b>

                                    
                                        <a id="edit-classification-btn" class="btn  btn btn-box-tool btn-sm <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>"
                                           data-person_id="<?= $PersonInfos['iPersonID'] ?>"
                                           data-classification_id="<?= $PersonInfos['ClassID'] ?>"
                                           data-classification_role="<?= $PersonInfos['ClassName'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    
                            </p>
                            <?php
                        }
                        if ($PersonInfos['MembershipDate'] 
                            and (
                                SessionUser::getUser()->isSeePrivacyDataEnabled()
                                or $PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() 
                                or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId()
                            )) {
                            ?>
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body py-2 px-3">
                            <ul class="list-group list-group-unbordered mb-0 bg-transparent">
                                <li class="list-group-item">
                                    <b><?= _('Membership Date') ?></b> <a class="float-right"><?= OutputUtils::FormatDateOrUnknown($PersonInfos['MembershipDate']) ?></a>
                                </li>
                                <?php if (!is_null($PersonInfos['FriendDate']) and $PersonInfos['FriendDate']->format('Y-m-d') != '1900-01-01'): ?>
                                <li class="list-group-item">
                                    <b><?= _('Friend Date') ?></b> <a class="float-right"><?= OutputUtils::FormatDateOrUnknown($PersonInfos['FriendDate']) ?></a>
                                </li>
                                <?php endif ?>                                
                            </ul>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                            <h5 class="mb-0"><?= _("Groups") ?></h5>
                            <span class="badge badge-light border"><?= $assignedGroupCount . ' ' . _('assigned') ?></span>
                        </div>
                        <?php if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isSeePrivacyDataEnabled() ) { ?>
                            <?php if ($assignedGroupCount > 0) { ?>
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
                            <?php } else { ?>
                                <div class="alert alert-light border mb-3">
                                    <i class="fas fa-users mr-2 text-muted"></i><?= _('No group assignments yet.') ?>
                                </div>
                            <?php } ?>
                        <?php
                        } else {
                        ?>
                        <?=  _("Private Data") ?>
                        <?php    
                        }
                        if ($bOkToEdit) {
                            ?>
                                     <a href="<?= $sRootPath ?>/v2/people/person/editor/<?= $PersonInfos['iPersonID'] ?>"
                                         class="btn btn-sm btn-outline-primary btn-block"><i class="fas fa-pen mr-1"></i><?php echo _('Edit'); ?></a>
                            <?php
                        }
                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- About card -->
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header border-0">
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
                            if (count($PersonInfos['OtherFamilyMembers']) > 0) {
                                ?>
                                <li style="left:-28px"><strong><i class="fa-solid fa-people-roof"></i> <?php echo _('Family:'); ?></strong>
                                    <span>
            <?php
            if ($PersonInfos['famId'] != '') {
                ?>
                <a href="<?= $sRootPath ?>/v2/people/family/view/<?= $PersonInfos['famId'] ?>"><?= $PersonInfos['person']->getFamily()->getName() ?> </a>
                <a href="<?= $sRootPath ?>/v2/people/family/editor/<?= $PersonInfos['famId'] ?>"
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

                            $personBirthDate = $PersonInfos['BirthDate'];
                            $birthDateUnknown = $personBirthDate instanceof DateTimeInterface
                                && $personBirthDate->format('Y-m-d') === '1901-01-01';

                            if ($dBirthDate || $birthDateUnknown) {
                                ?>
                                <li>
                                    <strong><i class="fa-li fas fa-calendar"></i><?= _('Birth Date') ?></strong>:
                                    <br>
                                    <p class="text-muted"><?= $birthDateUnknown ? OutputUtils::FormatDateOrUnknown($personBirthDate) : $dBirthDate ?>
                                        <?php
                                        if (!$birthDateUnknown && !$PersonInfos['person']->hideAge()) {
                                            ?>
                                            (<span
                                                data-birth-date="<?= $PersonInfos['BirthDate']->format('Y-m-d') ?>"></span> <?= OutputUtils::FormatAgeSuffix($PersonInfos['BirthDate'], $PersonInfos['person']->getFlags()) ?>)
                                            <?php
                                        }
                                        ?>
                                    </p>
                                </li>
                                <?php
                            }
                            if (!SystemConfig::getValue('bHideFriendDate') and $PersonInfos['FriendDate'] != '') { /* Friend Date can be hidden - General Settings */
                                ?>
                                <li><strong><i class="fa-li fas fa-tasks"></i><?= _('Friend Date') ?>:</strong>
                                    <span><?= OutputUtils::FormatDateOrUnknown($PersonInfos['FriendDate']) ?></span>
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
                                            href="sms:<?= str_replace(' ', '', $PersonInfos['sCellPhoneUnformatted']) ?>&body=<?= _("CRM text message") ?>"><?= $PersonInfos['sCellPhone'] ?></a></span>
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

                            if ($PersonInfos['WorkEmail'] != '') {
                                ?>
                                <li><strong><i class="fa-li far fa-envelope"></i><?= _('Work/Other Email') ?>:</strong>
                                    <span><a
                                            href="mailto:<?= $PersonInfos['WorkEmail'] ?>"  target="_blank"><?= $PersonInfos['WorkEmail'] ?></a></span>
                                </li>
                                <?php
                                if ($isMailChimpActive) {
                                    ?>
                                    <li><strong><i class="fa-li fas fa-paper-plane"></i>MailChimp <?= _("Work")?>:</strong> <span id="mailChimpUserWork"></span>
                                    </li>
                                    <?php
                                }
                            }

                            if ($PersonInfos['FacebookID'] > 0) {
                                ?>
                                <li><strong><i class="fa-li fab fa-facebook"></i><?= _('Facebook') ?>:</strong>
                                    <span><a
                                            href="https://www.facebook.com/<?= InputUtils::FilterInt($PersonInfos['FacebookID']) ?>"><?= _('Facebook') ?></a></span>
                                </li>
                                <?php
                            }

                            if (strlen($PersonInfos['Twitter']) > 0) {
                                ?>
                                <li><strong><i class="fa-li fas fa-twitter"></i><?= _('Twitter') ?>:</strong> <span><a
                                            href="https://www.twitter.com/<?= InputUtils::FilterString($PersonInfos['Twitter']) ?>"><?= _('Twitter') ?></a></span>
                                </li>
                                <?php
                            }

                            if (strlen($PersonInfos['LinkedIn']) > 0) {
                                ?>
                                <li><strong><i class="fa-li fab fa-linkedin"></i><?= _('LinkedIn') ?>:</strong> <span><a
                                            href="https://www.linkedin.com/in/<?= InputUtils::FilterString($PersonInfos['LinkedIn']) ?>"><?= _('LinkedIn') ?></a></span>
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
                <div class="alert alert-info small mt-2 mb-0">
                    <i class="fas fa-tree mr-1"></i><?php echo _('indicates items inherited from the associated family record.'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row align-items-lg-center">
                        <div class="col-lg-5 mb-3 mb-lg-0">
                            <div class="text-muted text-uppercase small"><?= _('Overview') ?></div>
                            <h2 class="h4 mb-2"><?= $PersonInfos['FullName'] ?></h2>
                            <div class="mb-2">
                                <span class="badge badge-primary mr-1 mb-1"><i class="fas fa-user-tag mr-1"></i><?= $personRoleLabel ?></span>
                                <span class="badge badge-info mr-1 mb-1"><i class="fas fa-layer-group mr-1"></i><?= $personClassLabel ?></span>
                                <span class="badge badge-light mr-1 mb-1"><i class="fas fa-users mr-1"></i><?= $assignedGroupCount . ' ' . _('Groups') ?></span>
                                <span class="badge badge-light mb-1"><i class="fas fa-shield-alt mr-1"></i><?= $can_see_privatedata ? _('Private access granted') : _('Private access restricted') ?></span>
                            </div>
                            <div class="text-muted small">
                                <?= $can_see_privatedata ? _('Use the quick actions to manage this profile, documents and assignments from one place.') : _('Some sections remain hidden because this profile contains private data.') ?>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="d-flex flex-wrap align-items-center justify-content-lg-end">
                    <?php
                    $buttons = 0;

                    if (Cart::PersonInCart($PersonInfos['iPersonID']) and SessionUser::getUser()->isShowCartEnabled()) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                            <a class="btn btn-sm btn-outline-info RemoveOneFromPeopleCart" id="AddPersonToCart"
                           data-onecartpersonid="<?= $PersonInfos['iPersonID'] ?>"> <i class="fas fa-times"></i> <span
                                class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
                        </div>
                        <?php
                    } else if (SessionUser::getUser()->isShowCartEnabled()) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                            <a class="btn btn-sm btn-outline-info AddOneToPeopleCart" id="AddPersonToCart"
                           data-onecartpersonid="<?= $PersonInfos['iPersonID'] ?>"><i
                                class="fas fa-cart-plus"></i><span
                                class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isEmailEnabled()) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                <a class="btn btn-sm btn-outline-success"
                           href="mailto:<?= urlencode(str_replace("<i class='fas  fa-tree'></i>", "", $PersonInfos['sEmail'])) ?>"  target="_blank"><i
                                class="far fa-paper-plane"></i><?= _('Email') ?></a>
                                <a class="btn btn-sm btn-outline-info"
                           href="mailto:?bcc=<?= urlencode(str_replace("<i class='fas  fa-tree'></i>", "", $PersonInfos['sEmail'])) ?>"  target="_blank"><i
                                class="fas fa-paper-plane"></i><?= _('Email (BCC)') ?></a>
                        </div>
                        <?php
                    }

                    if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                        if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId()) {

                            $buttons++;
                            ?>
                            <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/users/settings"><i
                                    class="fas fa-cog"></i> <?= _("Change Settings") ?></a>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/users/change/password/<?= $PersonInfos['iPersonID'] ?>"><i
                                    class="fas fa-key"></i> <?= _("Change Password") ?></a>
                            </div>
                            <?php
                        }
                        ?>
                            <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                <a class="btn btn-sm btn-outline-primary"
                           href="<?= $sRootPath ?>/v2/people/person/print/<?= $PersonInfos['iPersonID'] ?>"><i
                                class="fas fa-print"></i> <?= _("Printable Page") ?></a>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isAdmin()) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                            <a class="btn btn-sm btn-outline-primary" href="#" data-toggle="modal" data-target="#confirm-verify"><i
                                    class="fas fa-check-square"></i> <?= _("Verify Info") ?></a>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isPastoralCareEnabled()) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                <a class="btn btn-sm btn-outline-secondary"
                           href="<?= $sRootPath ?>/v2/pastoralcare/person/<?= $PersonInfos['iPersonID'] ?>"
                           data-toggle="tooltip" data-placement="bottom" title="<?= _("Add a pastoral care note") ?>"><i
                                class="far fa-question-circle"></i> <?= _("Pastoral Care") ?></a>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isNotesEnabled() or (SessionUser::getUser()->isEditSelfEnabled() and $PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId())) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                <a class="btn btn-sm btn-success" href="#" id="createDocument" data-toggle="tooltip"
                           data-placement="bottom"
                           title="<?= _("Create a document") ?>"><i
                                class="fas fa-file"></i><?= _("Create a document") ?></a>
                        </div>
                        <?php
                    }
                    if ( SessionUser::getUser()->isManageGroups() ) {
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                            <a class="btn btn-sm btn-outline-secondary addGroup" data-personid="<?= $PersonInfos['iPersonID'] ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Assign this user to a group") ?>"><i
                                    class="fas fa-users">
                                </i> <?= _("Assign New Group") ?></a>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                         $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                            <a class="btn btn-sm btn-outline-warning <?= (mb_strlen($PersonInfos['PersonInfos']['address1']) == 0 or mb_strlen($PersonInfos['PersonInfos']['address2']) == 0)?'disabled':'' ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Get the vCard of the person") ?>"
                               href="<?= $sRootPath ?>/api/persons/addressbook/extract/<?= $PersonInfos['iPersonID'] ?>"><i
                                    class="far fa-id-card">
                                </i> <?= _("vCard") ?></a>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isAdmin()) {
                        if (!$PersonInfos['person']->isUser()) {
                            $buttons++;
                            ?>
                            <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                     <a class="btn btn-sm btn-outline-dark"
                               href="<?= $sRootPath ?>/v2/users/editor/new/<?= $PersonInfos['iPersonID'] ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Create a CRM user") ?>"><i
                                    class="fas fa-user-secret"></i> <?= _('Make User') ?></a>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                     <a class="btn btn-sm btn-outline-dark"
                               href="<?= $sRootPath ?>/v2/users/editor/<?= $PersonInfos['iPersonID'] ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Add rights to this user") ?>"><i
                                    class="fas fa-user-secret"></i> <?= _('Edit User') ?></a>
                            </div>
                            <?php
                        }
                    }

                    if ($bOkToEdit and SessionUser::getUser()->isDeleteRecordsEnabled() and $PersonInfos['iPersonID'] != 1) {// the super user can't be deactivated
                        $buttons++;
                        ?>
                        <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                            <button class="btn btn-sm btn-warning" id="activateDeactivate">
                                <i class="fa <?= (empty($PersonInfos['DateDeactivated']) ? 'fa-times-circle' : 'fa-check-circle') ?> "></i><?php echo((empty($PersonInfos['DateDeactivated']) ? _('Deactivate') : _('Activate')) . " " . _(' this Person')); ?>
                            </button>
                        </div>
                        <?php
                    }

                    if (SessionUser::getUser()->isDeleteRecordsEnabled() and $PersonInfos['iPersonID'] != 1) {// the super user can't be deleted
                        $buttons++;

                        if (count($PersonInfos['OtherFamilyMembers']) > 0 or is_null($PersonInfos['Family'])) {
                            ?>
                            <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                     <a class="btn btn-sm btn-danger delete-person"
                               data-person_name="<?= $PersonInfos['FullName'] ?>"
                               data-person_id="<?= $PersonInfos['iPersonID'] ?>"><i
                                    class="far fa-trash-alt"></i> <?= _("Delete this Record") ?>
                            </a>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                                     <a class="btn btn-sm btn-danger"
                               href="<?= $sRootPath ?>/v2/people/family/delete/<?= $PersonInfos['famId'] ?>"><i
                                    class="far fa-trash-alt"></i><?= _("Delete this Record") ?></a>
                            </div>
                            <?php
                        }
                    }
                    ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (SessionUser::getUser()->isManageGroupsEnabled()                 
                or (SessionUser::getUser()->isEditSelfEnabled() 
                    and $PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() 
                    or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() 
                    or SessionUser::getUser()->isSeePrivacyDataEnabled())) {
            ?>
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header border-0 card-header-custom">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs flex-wrap">
                        <?php
                        $activeTab = "";
                        if (($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId()
                            or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId()
                            or SessionUser::getUser()->isSeePrivacyDataEnabled())) {
                            $activeTab = "timeline";
                            ?>
                            <li class="nav-item">
                                          <a class="nav-link <?= (!$bDocuments and !$bGroup) ? "active" : "" ?>"
                                              href="#timeline" aria-controls="timeline" role="tab"
                                   data-toggle="tab"><i class="fas fa-clock"></i> <?= _('Timeline') ?></a></li>
                            <?php
                        }
                        ?>
                        <?php
                        if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or count($PersonInfos['OtherFamilyMembers']) > 0 and SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (empty($activeTab)) ? 'active' : '' ?>"
                                   href="#family"
                                   aria-controls="family"
                                   role="tab"
                                   data-toggle="tab"><i class="fa-solid fa-people-roof"></i> <?= _('Family') ?></a>
                            </li>
                            <?php
                            if (empty($activeTab)) {
                                $activeTab = 'family';
                            }
                        }
                        ?>
                        <?php
                        if (SessionUser::getUser()->isManageGroupsEnabled() 
                            or $PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() 
                            or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId()) {
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
                        if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isSeePrivacyDataEnabled()) {
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
                        if (SystemConfig::getBooleanValue("bEnabledVolunteers") && 
                            ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() 
                            or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isCanvasserEnabled())) {
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
                        if (count($PersonInfos['OtherFamilyMembers']) == 0 and SessionUser::getUser()->isFinanceEnabled() and SystemConfig::getBooleanValue('bEnabledFinance')) {
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
                        if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isNotesEnabled()) {
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
                    </ul>
                </div>

                <div class="card-body">
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <?php
                        if ($PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId() or $PersonInfos['famId'] == SessionUser::getUser()->getPerson()->getFamId() or SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                            ?>
                            <div role="tabpanel" class="tab-pane fade <?= ($activeTab == 'timeline') ? "show active" : "" ?>" id="timeline">
                                <div class="timeline time-line-main">
                                    <?php
                                    $countMainTimeLine = 0;
                                    $maxMainTimeLineItems = 20;
                                    $curYear = date('Y');

                                    if (!empty($timelineServiceItems) && isset($timelineServiceItems[0]['year'])) {
                                        $curYear = $timelineServiceItems[0]['year'];
                                    }
                                    ?>

                                    <div class="time-label">
                                        <span class="bg-primary px-3 py-1 rounded-pill">
                                            <?= $curYear ?>
                                        </span>
                                    </div>

                                    <?php
                                    foreach ($timelineServiceItems as $item) {
                                        $countMainTimeLine++;

                                        if ($countMainTimeLine > $maxMainTimeLineItems) {
                                            break;
                                        }

                                        if (isset($item['year']) && $curYear != $item['year']) {
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
                                            <i class="fa <?= $item['style'] ?>"></i>

                                            <div class="timeline-item shadow-sm border-0">
                                                <span class="time text-muted small">
                                                    <i class="fas fa-clock mr-1"></i><?= $item['datetime'] ?>
                                                </span>
                                                <?php
                                                if (isset($item['style2'])) {
                                                    ?>
                                                    <i class="fa <?= $item['style2'] ?> share-type-2"></i>
                                                    <?php
                                                }
                                                ?>

                                                <h3 class="timeline-header border-0 font-weight-bold">
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
                                                        <span><?= ((!empty($item['info'])) ? $item['info'] . " : " : "") . $item['text'] ?></span>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <span><?= ((!empty($item['info'])) ? $item['info'] . " : " : "") . '<a href="' . $sRootPath . '/api/filemanager/getFile/' . $item['perID'] . "/" . $item['text'] . '"><i class="fa ' . $item['style2'] . ' share-type-2"></i> "' . _("click to download") . '"</a>' ?></span>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div role="tabpanel" class="tab-pane fade <?= ($activeTab == 'family') ? "show active" : "" ?>"
                             id="family">
                            <?php
                            if ($PersonInfos['famId'] != '') {
                                ?>
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                    <div>
                                        <h5 class="mb-1"><?= _('Family Circle') ?></h5>
                                        <div class="small text-muted"><?= _('See the rest of the household, their role and the fastest actions available.') ?></div>
                                    </div>
                                    <span class="badge badge-light border"><?= $otherFamilyMembersCount . ' ' . _('other members') ?></span>
                                </div>
                                <div class="table-responsive">
                                <table class="table table-sm table-hover table-borderless mb-0" width="100%">
                                    <thead>
                                    <tr class="border-bottom">
                                        <th class="text-muted small font-weight-bold"><i class="fas fa-users mr-2 text-info"></i><?= _('Family Members') ?></th>
                                        <th class="text-center text-muted small font-weight-bold"><i class="fas fa-tag mr-1"></i><?= _('Role') ?></th>
                                        <th class="text-muted small font-weight-bold"><i class="fas fa-birthday-cake mr-1"></i><?= _('Birthday') ?></th>
                                        <th class="text-muted small font-weight-bold"><i class="far fa-envelope mr-1"></i><?= _('Email') ?></th>
                                        <th class="text-center text-muted small font-weight-bold"><?= _('Actions') ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($PersonInfos['OtherFamilyMembers'] as $familyMember) {
                                        $tmpPersonId = $familyMember->getId();
                                        ?>
                                        <tr class="align-middle border-bottom">
                                            <td class="py-2">
                                                <div class="d-flex align-items-center">
                                                    <?= $familyMember->getJPGPhotoDatas() ?>
                                                    <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $tmpPersonId ?>"
                                                       class="user-link font-weight-500 ml-2"><?= $familyMember->getFullName() ?></a>
                                                </div>
                                            </td>
                                            <td class="text-center py-2">
                                                <?php
                                                $famRole = $familyMember->getFamilyRoleName();
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
                                                <?= OutputUtils::FormatBirthDate($familyMember->getBirthYear(), $familyMember->getBirthMonth(), $familyMember->getBirthDay(), '-', $familyMember->getFlags()); ?>
                                            </td>
                                            <td class="py-2">
                                                <?php
                                                $tmpEmail = $familyMember->getEmail();

                                                if ($tmpEmail != '') {
                                                    ?>
                                                    <a href="mailto:<?= $tmpEmail ?>" class="text-primary small" title="<?= $tmpEmail ?>" target="_blank"><?= $tmpEmail ?></a>
                                                    <?php
                                                } else {
                                                    echo '<span class="text-muted small">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="py-2 text-center" style="width: 20%;">
                                                <div class="btn-group btn-group-sm" role="group">
                                                <?php
                                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                                    ?>
                                                    <a class="AddToPeopleCart btn btn-outline-secondary" data-cartpersonid="<?= $tmpPersonId ?>" title="<?= _('Add to Cart') ?>" data-toggle="tooltip">
                                                        <i class="fas fa-cart-plus"></i>
                                                    </a>
                                                    <?php
                                                }

                                                if ($bOkToEdit) {
                                                    ?>
                                                    <a href="<?= $sRootPath ?>/v2/people/person/editor/<?= $tmpPersonId ?>" class="btn btn-outline-primary" title="<?= _('Edit') ?>" data-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a class="delete-person btn btn-outline-danger"
                                                       data-person_name="<?= $familyMember->getFullName() ?>"
                                                       data-person_id="<?= $tmpPersonId ?>" data-view="family" title="<?= _('Delete') ?>" data-toggle="tooltip">
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
                                        <div class="alert alert-light border mb-3">
                                            <i class="far fa-question-circle fa-lg mr-2 text-muted"></i>
                                            <span><?= _('No group assignments.') ?></span>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                            <div>
                                                <h5 class="mb-1"><?= _('Assigned Groups') ?></h5>
                                                <div class="small text-muted"><?= _('Manage memberships, group-specific information and role changes from this section.') ?></div>
                                            </div>
                                            <span class="badge badge-light border"><?= $assignedGroupCount . ' ' . _('groups') ?></span>
                                        </div>
                                        <?php
                                        ?>
                                        <?php
                                        // Loop through the rows
                                        $ids = SessionUser::getUser()->getGroupManagerIds();

                                        foreach ($ormAssignedGroups as $ormAssignedGroup) {
                                            if ( !SessionUser::getUser()->isManageGroups() and !in_array($ormAssignedGroup->getGroupID(),$ids) ) continue;

                                            if ($i % 3 == 0 or $i == 1) {
                                                $i=1;
                                                ?>
                                                <div class="row">
                                                <?php
                                            }
                                            ?>
                                            <div class="col-md-6">
                                                <!-- Info box -->
                                                <div class="card card-outline card-secondary shadow-sm h-100 mb-0">
                                                    <div class="card-header border-0">
                                                        <h3 class="card-title" style="font-size:tiny">
                                                            <a class="btn btn-outline-secondary btn-xs px-2"
                                                               href="<?= $sRootPath ?>/v2/group/<?= $ormAssignedGroup->getGroupID() ?>/view">
                                                                <i class="fas fa-users mr-1"></i><?= $ormAssignedGroup->getGroupName() ?>
                                                            </a>
                                                            <span class="badge badge-light border ml-1"><?= _($ormAssignedGroup->getRoleName()) ?></span>
                                                        </h3>

                                                        <div class="float-right">
                                                            <div class="btn-group btn-group-sm" role="group" aria-label="<?= _('Group actions') ?>">
                                                                <a href="<?= $sRootPath ?>/v2/group/<?= $ormAssignedGroup->getGroupID() ?>/view"
                                                                   class="btn btn-outline-secondary" role="button"
                                                                   data-toggle="tooltip" title="<?= _('View') ?>">
                                                                    <i class="fas fa-list"></i>
                                                                </a>
                                                                <button type="button"
                                                                        class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                                        title="<?= _('Action') ?>">
                                                                    <span class="sr-only">Toggle Dropdown</span>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right" role="menu">
                                                                    <a class="dropdown-item changeRole"
                                                                       data-groupid="<?= $ormAssignedGroup->getGroupID() ?>">
                                                                        <i class="fas fa-user-edit mr-2"></i><?= _('Change Role') ?>
                                                                    </a>
                                                                    <?php
                                                                    if ($ormAssignedGroup->getHasSpecialProps()) {
                                                                        ?>
                                                                        <a class="dropdown-item"
                                                                           href="<?= $sRootPath ?>/v2/group/props/editor/<?= $ormAssignedGroup->getGroupID() ?>/<?= $PersonInfos['iPersonID'] ?>">
                                                                            <i class="fas fa-sliders-h mr-2"></i><?= _('Update Properties') ?>
                                                                        </a>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <button
                                                                    data-groupid="<?= $ormAssignedGroup->getGroupID() ?>"
                                                                    data-groupname="<?= $ormAssignedGroup->getGroupName() ?>"
                                                                    type="button"
                                                                    class="btn btn-outline-danger groupRemove"
                                                                    data-toggle="tooltip" title="<?= _('Delete') ?>">
                                                                    <i class="far fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        if ( SessionUser::getUser()->isManageGroupsEnabled() ) {
                                                            ?>
                                                            <div class="text-center"></div>
                                                            <?php
                                                        }
                                                        // If this group has associated special properties, display those with values and prop_PersonDisplay flag set.
                                                        if ($ormAssignedGroup->getHasSpecialProps()) {
                                                            // Utilisation des propriétés préchargées
                                                            $ormPropLists = $propsByGroup[$ormAssignedGroup->getGroupId()]['false'] ?? [];
                                                            ?>

                                                            <div class="row small">
                                                                    <div class="col-md-6 mb-3 mb-md-0">
                                                                        <h6 class="text-uppercase text-muted mb-2"><?= _("Group Informations") ?></h6>
                                                                        <?php
                                                                            if (count($ormPropLists) > 0) {
                                                                            ?>
                                                                        <ul class="list-group list-group-flush mb-0">
                                                                            <?php
                                                                            foreach ($ormPropLists as $ormPropList) {
                                                                                $prop_Special = $ormPropList->getSpecial();
                                                                                if ($ormPropList->getTypeId() == 11) {
                                                                                    $prop_Special = $sPhoneCountry;
                                                                                }
                                                                                ?>
                                                                                <li class="list-group-item px-0 py-1 border-0 d-flex justify-content-between align-items-start">
                                                                                    <span class="text-muted mr-2"><?= $ormPropList->getName() ?></span>
                                                                                    <span class="text-right"><?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $ormPropList->getDescription(), $prop_Special) ?></span>
                                                                                </li>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </ul>
                                                                        <?php
                                                                            } else {
                                                                        ?>
                                                                            <span class="text-muted"><?= _("None") ?></span>
                                                                        <?php
                                                                            }
                                                                        ?>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <?php


                                                                    // Propriétés personnalisées préchargées
                                                                    $ormPropLists = $propsByGroup[$ormAssignedGroup->getGroupId()]['true'] ?? [];
                                                                    $aPersonProps = $aPersonPropsByGroup[$ormAssignedGroup->getGroupId()] ?? [];

                                                                    if (count($ormPropLists) > 0) {
                                                                        ?>
                                                                        <h6 class="text-uppercase text-muted mb-2"><?= _("Person Informations") ?></h6>
                                                                        <ul class="list-group list-group-flush mb-2">
                                                                            <?php
                                                                            foreach ($ormPropLists as $ormPropList) {
                                                                                $currentData = trim($aPersonProps[$ormPropList->getField()]);
                                                                                if (strlen($currentData) > 0) {
                                                                                    $prop_Special = $ormPropList->getSpecial();
                                                                                    if ($ormPropList->getTypeId() == 11) {
                                                                                        $prop_Special = $sPhoneCountry;
                                                                                    }
                                                                                    ?>
                                                                                    <li class="d-flex justify-content-between align-items-start py-1 border-bottom">
                                                                                        <span class="text-muted mr-2"><?= $ormPropList->getName() ?></span>
                                                                                        <span class="text-right"><?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $currentData, $prop_Special) ?></span>
                                                                                    </li>
                                                                                    <?php
                                                                                }
                                                                            }

                                                                            ?>
                                                                        </ul>
                                                                        <div class="text-center">
                                                                            <a href="<?= $sRootPath ?>/v2/group/props/editor/<?= $ormAssignedGroup->getGroupId() ?>/<?= $PersonInfos['iPersonID'] ?>"
                                                                            class="btn btn-sm btn-outline-primary px-3">
                                                                                <i class="fas fa-sliders-h mr-1"></i><?= _("Modify Specific Properties") ?>
                                                                            </a>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                    </div>
                                                                </div>
                                                            <?php
                                                        } else {
                                                            ?>
                                                                <div class="small text-muted"><?= _("No specific group properties defined !") ?></div>
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
                                                </div><br>
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
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                    <div>
                                        <h5 class="mb-1"><?= _('Assigned Properties') ?></h5>
                                        <div class="small text-muted"><?= _('Track the specific attributes already linked to this person and add new ones when needed.') ?></div>
                                    </div>
                                    <span class="badge badge-light border"><?= $ormAssignedProperties->count() . ' ' . _('assigned') ?></span>
                                </div>
                                <div class="alert alert-warning d-flex align-items-center mb-3"
                                     id="properties-warning" <?= ($ormAssignedProperties->count() > 0) ? 'style="display: none;"' : '' ?>>
                                    <i class="far fa-question-circle fa-lg mr-2"></i>
                                    <span><?= _('No property assignments.') ?></span>
                                </div>
                                <?php
                                $sAssignedProperties = ',';
                                ?>

                                <div
                                    id="properties-table" <?= ($ormAssignedProperties->count() == 0) ? 'style="display: none;"' : '' ?>>
                                    <table class="table table-sm table-hover dt-responsive"
                                           id="assigned-properties-table"
                                           width="100%"></table>
                                </div>

                                <?php if (SessionUser::getUser()->isEditRecordsEnabled() and $bOkToEdit and $ormProperties->count() != 0): ?>
                                    <div class="card border-0 bg-light mt-3">
                                        <div class="card-body py-3">
                                            <h6 class="text-uppercase text-muted mb-3"><?= _('Assign a New Property') ?></h6>
                                            <div class="row align-items-end">
                                                <div class="form-group col-12 col-md-6 mb-2 mb-md-0">
                                                    <select name="PropertyId" id="input-person-properties"
                                                            class="form-control form-control-sm input-person-properties select2"
                                                            style="width:100%"
                                                            data-placeholder="<?= _("Select") ?> ..."
                                                            data-personID="<?= $PersonInfos['iPersonID'] ?>">
                                                        <option value="" selected>-- <?= _('select an option') ?> --
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
                                                <div id="prompt-box" class="col-12 col-md-4 mb-2 mb-md-0">

                                                </div>
                                                <div class="form-group col-12 col-md-2 text-md-right mb-0">
                                                    <button id="assign-property-btn" type="button"
                                                            class="btn btn-sm btn-primary assign-property-btn px-3"
                                                            data-default-text="<?= _('Assign') ?>"
                                                            data-loading-text="<?= _('Assigning...') ?>">
                                                        <i class="fas fa-plus-circle mr-1"></i><?= _('Assign') ?>
                                                    </button>
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
                                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                    <div>
                                        <h5 class="mb-1"><?= _('Volunteer Opportunities') ?></h5>
                                        <div class="small text-muted"><?= _('Keep volunteer assignments visible and assign new opportunities from the same place.') ?></div>
                                    </div>
                                    <span class="badge badge-light border"><?= $ormAssignedVolunteerOpps->count() . ' ' . _('assigned') ?></span>
                                </div>
                                <?php

                                //Initialize row shading
                                $sRowClass = 'RowColorA';

                                $sAssignedVolunteerOpps = ',';

                                //Was anything returned?
                                ?>
                                <div class="alert alert-warning d-flex align-items-center mb-3"
                                     id="volunter-warning" <?= ($ormAssignedVolunteerOpps->count() > 0) ? 'style="display: none;"' : '' ?>>
                                    <i class="far fa-question-circle fa-lg mr-2"></i>
                                    <span><?= _('No volunteer opportunity assignments.') ?></span>
                                </div>

                                <div
                                    id="volunter-table" <?= ($ormAssignedVolunteerOpps->count() == 0) ? 'style="display: none;"' : '' ?>>
                                    <table class="table table-sm table-hover dt-responsive"
                                           id="assigned-volunteer-opps-table"
                                           width="100%"></table>
                                </div>

                                <?php
                                if (SessionUser::getUser()->isEditRecordsEnabled() and $ormVolunteerOpps->count()) {
                                    ?>
                                    <div class="card border-0 bg-light mt-3">
                                        <div class="card-body py-3">
                                            <h6 class="text-uppercase text-muted mb-3"><?= _('Assign a New Volunteer Opportunity') ?></h6>

                                            <div class="row align-items-end">
                                                <div class="form-group col-12 col-md-9 mb-2 mb-md-0">
                                                    <select id="input-volunteer-opportunities"
                                                            name="VolunteerOpportunityIDs[]" multiple
                                                            class="form-control form-control-sm select2" style="width:100%"
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
                                                <div class="form-group col-12 col-md-3 text-md-right mb-0">
                                                    <button type="button"
                                                            name="VolunteerOpportunityAssign"
                                                            class="btn btn-sm btn-primary VolunteerOpportunityAssign px-3"
                                                            data-default-text="<?= _('Assign') ?>"
                                                            data-loading-text="<?= _('Assigning...') ?>">
                                                        <i class="fas fa-plus-circle mr-1"></i><?= _('Assign') ?>
                                                    </button>
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
                                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                        <div>
                                            <h5 class="mb-1"><?= _('Automatic Payments') ?></h5>
                                            <div class="small text-muted"><?= _('Review recurring contributions and add a new payment setup when appropriate.') ?></div>
                                        </div>
                                        <span class="badge badge-light border"><?= $ormAutoPayments->count() . ' ' . _('records') ?></span>
                                    </div>
                                    <?php
                                    if (!is_null($PersonInfos['person']->getFamily())) {
                                        if ($ormAutoPayments->count() > 0) {
                                            ?>
                                            <div class="table-responsive mb-3">
                                                <table class="table table-striped table-bordered"
                                                       id="automaticPaymentsTable"
                                                       cellpadding="5" cellspacing="0" width="100%"></table>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <div class="alert alert-light border mb-3">
                                                <i class="far fa-credit-card mr-2 text-muted"></i><?= _('No automatic payments recorded for this person yet.') ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <div class="text-center text-md-right">
                                            <a class="btn btn-sm btn-outline-primary"
                                               href="<?= $sRootPath ?>/v2/deposit/autopayment/editor/-1/<?= $PersonInfos['famId'] ?>/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-plus mr-1"></i> <?= _("Add a new automatic payment") ?></a>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-map-marker-alt mr-2"></i><?= _("You must set an address for this person") ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div role="tab-pane fade" class="tab-pane" id="pledges">
                            <div class="main-box clearfix">
                                <div class="main-box-body clearfix">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                        <div>
                                            <h5 class="mb-1"><?= _('Pledges and Payments') ?></h5>
                                            <div class="small text-muted"><?= _('Filter contributions by period and add a new pledge or payment directly from this tab.') ?></div>
                                        </div>
                                    </div>
                                    <?php
                                    $tog = 0;

                                    if (($_SESSION['sshowPledges'] or $_SESSION['sshowPayments']) and !is_null($PersonInfos['person']->getFamily())) {
                                        ?>
                                        <div class="card border-0 bg-light mb-3">
                                            <div class="card-body py-3">
                                                <div class="row align-items-end">
                                                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2 mb-md-0">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="ShowPledges" id="ShowPledges"
                                                                   value="1" <?= ($_SESSION['sshowPledges']) ? " checked" : "" ?>>
                                                            <label class="custom-control-label" for="ShowPledges"><?= _("Show Pledges") ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2 mb-md-0">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="ShowPayments" id="ShowPayments"
                                                                   value="1" <?= ($_SESSION['sshowPayments']) ? " checked" : "" ?>>
                                                            <label class="custom-control-label" for="ShowPayments"><?= _("Show Payments") ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-1 col-md-2 col-sm-6 mb-2 mb-md-0">
                                                        <label class="mb-1" for="Min"><?= _("From") ?></label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2 mb-md-0">
                                                        <input class="form-control form-control-sm date-picker" type="text" id="Min"
                                                               Name="ShowSinceDate"
                                                               value="<?= SessionUser::getUser()->getShowSince()->format(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                                    </div>
                                                    <div class="col-lg-1 col-md-2 col-sm-6 mb-2 mb-md-0">
                                                        <label class="mb-1" for="Max"><?= _("To") ?></label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                                        <input class="form-control form-control-sm date-picker" type="text" id="Max"
                                                               Name="ShowToDate"
                                                               value="<?= SessionUser::getUser()->getShowTo()->format(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive mb-3">
                                            <table id="pledgePaymentTable" class="table table-striped table-bordered"
                                                   cellspacing="0" width="100%"></table>
                                        </div>
                                        <div class="text-center text-md-right">
                                            <a class="btn btn-sm btn-outline-primary mb-2 mb-md-0"
                                               href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $PersonInfos['famId'] ?>/Pledge/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-plus"></i> <?= _("Add a new pledge") ?></a>
                                            <a class="btn btn-sm btn-outline-secondary"
                                               href="<?= $sRootPath ?>/v2/deposit/pledge/editor/family/<?= $PersonInfos['famId'] ?>/Payment/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-plus"></i> <?= _("Add a new payment") ?></a>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-map-marker-alt mr-2"></i><?= _("You must set an address for this person") ?>
                                        </div>
                                        <?php
                                    }
                                    ?>


                                    <?php
                                    if (SessionUser::getUser()->isCanvasserEnabled() and !is_null($PersonInfos['person']->getFamily())) {
                                        ?>
                                        <div class="text-center text-md-right mt-3">
                                            <a class="btn btn-sm btn-outline-secondary"
                                               href="<?= $sRootPath ?>/v2/people/canvass/editor/<?= $PersonInfos['famId'] ?>/<?= $_SESSION['idefaultFY'] ?>/v2-people-person-view-<?= $PersonInfos['iPersonID'] ?>"><i class="fa fa-eye"></i> <?= MiscUtils::MakeFYString($_SESSION['idefaultFY']) . _(" Canvass Entry") ?></a>
                                        </div>
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
                        <?php $timelineNoteCount = count($timelineNotesServiceItems); ?>
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <div class="text-muted text-uppercase small"><?= _('Documents') ?></div>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <h5 class="mb-0 mr-2"><?= _('Timeline Documents') ?></h5>
                                            <span class="badge badge-warning"><?php echo date_create()->format(SystemConfig::getValue('sDateFormatLong')) ?></span>
                                            <span class="badge badge-light border ml-2"><?= $timelineNoteCount . ' ' . _('items') ?></span>
                                        </div>
                                        <div class="small text-muted mt-1"><?= _('Filter notes, audio, video and shared documents from a single stream.') ?></div>
                                    </div>
                                    <div class="col-md-4 ml-md-auto">
                                        <select name="PropertyId" class="filter-timeline form-control form-control-sm" size="1"
                                                data-placeholder="<?= _("Select") ?> ..."
                                                data-toggle="tooltip" data-placement="bottom" title="<?= _("Filter your documents by : ") ?>">
                                            <option value="all"><?= _("All type") ?></option>
                                            <option value="note"><?= MiscUtils::noteType("note") ?></option>
                                            <option value="video"><?= MiscUtils::noteType("video") ?></option>
                                            <option value="audio"><?= MiscUtils::noteType("audio") ?></option>
                                            <option disabled="disabled">_____________________________</option>
                                            <option value="shared"><?= _("Shared documents") ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($timelineNoteCount == 0) { ?>
                            <div class="alert alert-light border">
                                <i class="far fa-copy mr-2 text-muted"></i><?= _('No documents or notes available yet.') ?>
                            </div>
                        <?php } ?>
                        <div class="timeline time-line-note<?= $timelineNoteCount == 0 ? ' d-none' : '' ?>">
                            <!-- note time label -->
                            <div class="time-label"></div>
                            <!-- /.note-label -->

                            <!-- note item -->
                            <?php
                            $note_content = "";// this assume only the last note is visible

                            foreach ($timelineNotesServiceItems as $item) {
                                if ($note_content != $item['text'] and $item['type'] != 'file') {// this assume only the last note is visible

                                    $note_content = $item['text']; // this assume only the last note is visible
                                    $noteTypeLabel = in_array($item['type'], ['note', 'video', 'audio']) ? MiscUtils::noteType($item['type']) : ucfirst($item['type']);
                                    $noteTypeBadge = 'badge badge-secondary';

                                    if ($item['type'] == 'note') {
                                        $noteTypeBadge = 'badge badge-primary';
                                    } elseif ($item['type'] == 'video') {
                                        $noteTypeBadge = 'badge badge-danger';
                                    } elseif ($item['type'] == 'audio') {
                                        $noteTypeBadge = 'badge badge-info';
                                    }
                                    ?>
                                    <div class="type-<?= $item['type'] ?><?= (isset($item['style2']) ? " type-shared" : "") ?> mb-3">
                                        <!-- timeline icon -->
                                        <i class="fas <?= $item['style'] ?> icon-<?= $item['type'] ?><?= (isset($item['style2']) ? " icon-shared" : "") ?>"></i>

                                        <div class="timeline-item shadow-sm border-0">
                                            <span class="time text-muted small">
                                                <i class="fas fa-clock mr-1"></i><?= $item['datetime'] ?>
                                                &nbsp;
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php
                                                    if ($item['slim']) {
                                                        if ($item['editLink'] != '' or (isset($item['sharePersonID']) and $item['shareRights'] == 2)) {
                                                            ?>                            
                                                            <?= $item['editLink'] ?>                                                                                                                            
                                                            <?php
                                                        }
                                                        if ($item['deleteLink'] != '' and !isset($item['sharePersonID']) and (!isset($item['currentUserName']) or $item['userName'] == $PersonInfos['FullName'])) {
                                                            ?>
                                                            <?= $item['deleteLink'] ?>                                                                                                                        
                                                            <?php
                                                        }
                                                        if (!isset($item['sharePersonID']) and (!isset($item['currentUserName']) or $item['userName'] == $PersonInfos['FullName'])) {
                                                            ?>
                                                            <button data-id="<?= $item['id'] ?>"
                                                                data-shared="<?= $item['isShared'] ?>" 
                                                                data-toggle="tooltip" data-placement="bottom" title="<?= _("Share this document to another user") ?>"
                                                                class="shareNote btn btn-<?= $item['isShared'] ? "success" : "secondary" ?> btn-sm">                                
                                                                <i class="fas fa-share-square"></i>
                                                            </button>                            
                                                            <?php
                                                        }
                                                        if ($item['type'] == 'note' and $PersonInfos['iPersonID'] == SessionUser::getUser()->getPersonId()) {
                                                            ?>
                                                            <button data-id="<?= $item['id'] ?>"
                                                                data-toggle="tooltip" data-placement="bottom" title="<?= _("Export this document to word Format") ?>"
                                                                class="saveNoteAsWordFile btn btn-outline-<?= $item['isShared'] ? "primary" : "secondary" ?> btn-sm">                                
                                                                <i class="fas fa-file-word"></i>
                                                            </button>                           
                                                            <?php
                                                        }                    
                                                    } ?>
                                                </div>
                                            </span>                                        

                                            <?php
                                            if (isset($item['style2'])) {
                                                ?>
                                                <i class="fa <?= $item['style2'] ?> share-type-2"></i>
                                                <?php
                                            }
                                            ?>
                                            <div class="timeline-header border-0 pb-2">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                    <div class="mr-3 mb-2">
                                                        <div class="mb-1">
                                                            <span class="<?= $noteTypeBadge ?> mr-1"><?= $noteTypeLabel ?></span>
                                                            <?php if ($item['isShared']) { ?>
                                                                <span class="badge badge-light border"><?= _('Shared') ?></span>
                                                            <?php } ?>
                                                        </div>                                                        
                                                        <h3 class="h6 mb-0 font-weight-bold">

                                                <?php
                                                if (array_key_exists('headerlink', $item) and !isset($item['sharePersonID'])) {
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
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="timeline-body pt-0">
                                                <?php
                                                if (isset($item['currentUserName'])) {
                                                    ?>
                                                    <p class="text-danger mb-2">
                                                        <small><?= $item['currentUserName'] ?></small>
                                                    </p>
                                                    <?php
                                                } else if (isset($item['lastEditedBy'])) {
                                                    ?>
                                                    <p class="text-success mb-2">
                                                        <small><?= _("Last modification by") . " : " . $item['lastEditedBy'] ?></small>
                                                    </p>
                                                    <?php
                                                }
                                                ?>
                                                <?php if (!empty($item['info'])) { ?>
                                                    <div class="small text-muted mb-2"><?= $item['info'] ?></div>
                                                <?php } ?>
                                                <div><?= $item['text'] ?></div>
                                            </div>

                                            <?php
                                            $toto = 32;
                                            if ((SessionUser::getUser()->isNotesEnabled()) and ($item['editLink'] != '' or $item['deleteLink'] != '')) {
                                                ?>
                                                <div class="timeline-footer">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <?php
                                                        if (!$item['slim']) {?>
                                                            <?= $item['editLink'] ?>
                                                            <?= $item['deleteLink'] ?>
                                                            <?php
                                                            if (!isset($item['sharePersonID'])) {
                                                                ?>
                                                                <button type="button" data-id="<?= $item['id'] ?>"
                                                                        data-shared="<?= $item['isShared'] ?>"
                                                                        class="btn btn-sm  btn-<?= $item['isShared'] ? "success" : "default"
                                                                        ?> shareNote">
                                                                        <i class="fas fa-share-square"></i>
                                                                </button>
                                                                <?php
                                                            }
                                                            ?>
                                                            <button type="button" data-id="<?= $item['id'] ?>"
                                                                    data-shared="<?= $item['isShared'] ?>"
                                                                    class="btn btn-sm  btn-<?= $item['isShared'] ? "primary" : "default"?> saveNoteAsWordFile">
                                                                    <i class="fas fa-file-word"></i>
                                                            </button>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
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
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><?= _("Cancel") ?></button>
                <button class="btn btn-sm btn-danger danger" id="deletePhoto"><?= _("Delete") ?></button>
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
                if (count($familyInfos['sFamilyEmails']) > 0 and !empty(SystemConfig::getValue('sSMTPHost'))) {
                    ?>
                    <button type="button" id="onlineVerify" class="btn btn-sm btn-warning warning">
                        <i class="far fa-envelope"></i>
                        <?= _("Online Verification") ?>
                    </button>
                    <button type="button" id="onlineVerifyPDF" class="btn btn-sm btn-danger danger">
                        <i class="far fa-envelope"></i> <i class="fas fa-file-pdf"></i>
                        <?= _("Online Verification") ?>
                    </button>
                    <?php
                }
                ?>
                <button type="button" id="verifyDownloadPDF" class="btn btn-sm btn-info">
                    <i class="fas fa-download"></i>
                    <?= _("PDF Report") ?>
                </button>
                <button type="button" id="verifyNow" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i>
                    <?= _("Verified In Person") ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/external/jquery-photo-uploader/PhotoUploader.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/MemberView.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/AddRemoveCart.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/PersonView.js"></script>


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
    window.CRM.currentPersonID = <?= $PersonInfos['iPersonID'] ?>;
    window.CRM.currentFamily = <?= $familyInfos['iFamilyID'] ?>;
    window.CRM.docType = 'person';
    window.CRM.iPhotoHeight = <?= SystemConfig::getValue("iPhotoHeight") ?>;
    window.CRM.iPhotoWidth = <?= SystemConfig::getValue("iPhotoWidth") ?>;
    window.CRM.currentActive = <?= (empty($PersonInfos['DateDeactivated']) ? 'true' : 'false') ?>;
    window.CRM.personFullName = "<?= $PersonInfos['FullName'] ?>";
    window.CRM.normalMail = "<?= $PersonInfos['sEmail'] ?>";
    window.CRM.workMail = "<?= $PersonInfos['WorkEmail'] ?>";

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

        initMap(window.CRM.churchloc.lng, window.CRM.churchloc.lat, 'titre', "<?= $PersonInfos['FullName'] ?>", '');
    <?php } ?>
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
