<?php

/*******************************************************************************
 *
 *  filename    : groupview.php
 *  last change : 2025-05-13
 *  description : manage a group view
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorizaion
 *
 ******************************************************************************/

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\Propel;
use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i><?= _("Actions") ?></h3>
    </div>
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center" style="gap: 0.5rem;">
<?php if (SessionUser::getUser()->isShowMapEnabled() || SessionUser::getUser()->belongsToGroup($iGroupID)): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/map/<?= $thisGroup->getId() ?>">
                <i class="fas fa-map-marker-alt mr-1"></i><?= _('Map this group') ?>
            </a>
<?php endif; ?>
<?php if (Cart::GroupInCart($iGroupID) && SessionUser::getUser()->isShowCartEnabled()): ?>
            <a class="btn btn-sm btn-outline-info AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>">
                <i class="fas fa-times mr-1"></i><span class="cartActionDescription"><?= _("Remove from Cart") ?></span>
            </a>
<?php elseif (SessionUser::getUser()->isShowCartEnabled()): ?>
            <a class="btn btn-sm btn-outline-info AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>">
                <i class="fas fa-cart-plus mr-1"></i><span class="cartActionDescription"><?= _("Add to Cart") ?></span>
            </a>
<?php endif; ?>
<?php if (SessionUser::getUser()->isManageGroupsEnabled()): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?= $sRootPath ?>/v2/group/editor/<?= $thisGroup->getId() ?>"
                data-toggle="tooltip" data-placement="bottom"
                title="<?= _("To add special Group roles or to modify the role by default or to enable Group-specific properties") ?>">
                <i class="fas fa-pencil-alt mr-1"></i><?= _("Edit this Group") ?>
            </a>
            <button class="btn btn-sm btn-danger" id="deleteGroupButton">
                <i class="fas fa-trash-alt mr-1"></i><?= _("Delete this Group") ?>
            </button>
<?php endif; ?>
<?php if (SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled() || SessionUser::getUser()->isMenuOptionsEnabled()): ?>
            <a class="btn btn-sm btn-warning" id="add-event">
                <i class="far fa-calendar-plus mr-1"></i><?= _("Appointment") ?>
            </a>
<?php endif; ?>
<?php if (SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']): ?>
            <form method="POST" action="<?= $sRootPath ?>/v2/group/reports" style="display:inline">
                <input type="hidden" id="GroupID" name="GroupID" value="<?= $iGroupID ?>">
                <button type="submit" class="btn btn-sm btn-success exportCheckOutCSV">
                    <i class="fas fa-file-pdf mr-1"></i><?= _("Group reports") ?>
                </button>
            </form>
            <a class="btn btn-sm btn-outline-secondary" id="groupbadge" data-groupid="<?= $iGroupID ?>"
                data-toggle="tooltip" data-placement="bottom"
                title="<?= _("Create here your badges or QR-Code to call the register with them") ?>">
                <i class="fas fa-id-badge mr-1"></i><?= _("Group Badges") ?>
            </a>
            <a class="btn btn-sm btn-outline-warning <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> export-vcard-button"
                href="<?= $sRootPath ?>/api/groups/addressbook/extract/<?= $iGroupID ?>"
                data-toggle="tooltip" data-placement="bottom"
                title="<?= _("Click to create an addressbook of the Group") ?>">
                <i class="far fa-id-card mr-1"></i><?= _('Address Book') ?>
            </a>
<?php endif; ?>

<?php

// Email Group link
// Note: This will email entire group, even if a specific role is currently selected.
$sSQL = "SELECT per_Email, fam_Email, lst_OptionName as virt_RoleName
    FROM person_per
    LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
    LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
    LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
    INNER JOIN list_lst on  grp_RoleListID = lst_ID AND p2g2r_rle_ID = lst_OptionID
WHERE per_DateDeactivated IS NULL AND per_ID NOT IN
    (SELECT per_ID
        FROM person_per
        INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
        INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email')
    AND p2g2r_grp_ID = " . $iGroupID;

$connection = Propel::getConnection();

$statement = $connection->prepare($sSQL);
$statement->execute();

$sEmailLink = '';
$roleEmails = new stdClass();
while (list($per_Email, $fam_Email, $virt_RoleName) = $statement->fetch(\PDO::FETCH_BOTH)) {
    $sEmail = MiscUtils::SelectWhichInfo($per_Email, $fam_Email, false);
    if ($sEmail) {
        /* if ($sEmailLink) // Don't put delimiter before first email
        $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
        // Add email only if email address is not already in string
        if (!stristr($sEmailLink, $sEmail)) {
            $sEmailLink .= $sEmail . SessionUser::getUser()->MailtoDelimiter();

            $roleEmails->$virt_RoleName .= $sEmail . SessionUser::getUser()->MailtoDelimiter();
        }
    }
}
if ($sEmailLink) {
    // Add default email if default email has been set and is not already in string
    if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
        $sEmailLink .= SessionUser::getUser()->MailtoDelimiter() . SystemConfig::getValue('sToEmailAddress');
    }
    $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

    if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
        // Display link
?>
        <div class="btn-group">
            <a class="btn btn-sm btn-success <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> email-button"
                href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank">
                <i class="far fa-envelope mr-1"></i><?= _("Email Group") ?>
            </a>
            <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split email-button-dropdown"
                data-toggle="dropdown" <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?>>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu">
                <?= MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
            </div>
        </div>
        <div class="btn-group">
            <a class="btn btn-sm btn-info <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> email-cci-button"
                href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank">
                <i class="fas fa-envelope mr-1"></i><?= _("Email (BCC)") ?>
            </a>
            <button type="button" class="btn btn-sm btn-info dropdown-toggle dropdown-toggle-split email-cci-button-dropdown"
                data-toggle="dropdown" <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?>>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu">
                <?= MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=') ?>
            </div>
        </div>

    <?php
    }
}
// Group Text Message Comma Delimited - added by RSBC
// Note: This will provide cell phone numbers for the entire group, even if a specific role is currently selected.
$sSQL = "SELECT per_CellPhone, fam_CellPhone
    FROM person_per
    LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
    LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
    LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
WHERE per_DateDeactivated IS NULL AND per_ID NOT IN
    (SELECT per_ID
    FROM person_per
    INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
    INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS')
AND p2g2r_grp_ID = " . $iGroupID;

$statement = $connection->prepare($sSQL);
$statement->execute();

$sPhoneLink = '';
$sCommaDelimiter = ', ';
while (list($per_CellPhone, $fam_CellPhone) = $statement->fetch(\PDO::FETCH_BOTH)) {
    $sPhone = MiscUtils::SelectWhichInfo($per_CellPhone, $fam_CellPhone, false);
    if ($sPhone) {
        /* if ($sPhoneLink) // Don't put delimiter before first phone
        $sPhoneLink .= $sCommaDelimiter; */
        // Add phone only if phone is not already in string
        if (!stristr($sPhoneLink, $sPhone)) {
            $sPhoneLink .= $sPhone .= $sCommaDelimiter;
        }
    }
}

if ($sPhoneLink) {
    if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
        // Display link
    ?>
            <a class="btn btn-sm btn-outline-info <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> sms-button"
                href="javascript:void(0)" onclick="allPhonesCommaD()">
                <i class="fas fa-mobile-alt mr-1"></i><?= _('Text Group') ?>
            </a>
            <script nonce="<?= $CSPNonce ?>">
                function allPhonesCommaD() {
                    prompt("<?= _("Press CTRL + C to copy all group members' phone numbers") ?>", "<?= mb_substr($sPhoneLink, 0, -2) ?>")
                };
            </script>
<?php
    }
}
?>

            </div>
        </div>
    </div>
<div class="group_Side_bar_container">
    <?php
    if ($_SESSION['bManageGroups'] or SessionUser::getUser()->isManageGroupsEnabled()) {
    ?>
        <div class="group_Side_bar">
            <div class="sticky-top">
                <div id="accordion">
                <?php
            }
            if (SessionUser::getUser()->isManageGroupsEnabled()) {
                ?>
                    <div class="card group_accordion">
                        <div class="card-header" id="headingQuickSettings">
                                <h3 class="card-title">
                                    <i class="fas fa-sliders-h mr-2"></i>
                                    <a data-toggle="collapse" href="#collapseQuickSettings"><?= _('Quick Settings') ?></a>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseQuickSettings"><i class="fas fa-minus collapse-toggle-icon"></i></button>
                                </div>
                            </div>
                        <div id="collapseQuickSettings" class="collapse show" aria-labelledby="headingQuickSettings" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-5"><label class="mb-0"><?= _("Group is") ?></label></div>
                                    <div class="col-md-7 text-md-right">
                                        <input data-width="100" class="btn btn-primary btn-sm" id="isGroupActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Active') ?>" data-off="<?= _('Disabled') ?>">
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-5"><label class="mb-0"><?= _("The emails are") ?></label></div>
                                    <div class="col-md-7 text-md-right">
                                        <input data-width="100" class="btn btn-primary btn-sm" id="isGroupEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= _('Include') ?>" data-off="<?= _('Exclude') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card group_accordion">
                        <div class="card-header" id="headingGroupManager">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>
                                <a data-toggle="collapse" href="#collapseGroupManager"><?= _("Group Managers") ?></a>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseGroupManager"><i class="fas fa-minus collapse-toggle-icon"></i></button>
                            </div>
                        </div>
                        <div id="collapseGroupManager" class="collapse" aria-labelledby="headingGroupManager" data-parent="#accordion">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <b class="mb-0"><?= _("Assigned Managers") ?>:</b>
                                </div>
                                <div id="Manager-list">
                                    <?php
                                    $managers = GroupManagerPersonQuery::Create()->findByGroupId($iGroupID);

                                    $first_manager = null;

                                    if ($managers->count()) {
                                        foreach ($managers as $manager) {
                                            if (is_null($first_manager)) {
                                                $first_manager = $manager->getPerson();
                                            }
                                            if (!$manager->getPerson()->isDeactivated()) {
                                    ?>
                                                <div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1">
                                                    <span class="text-truncate pr-2"><?= $manager->getPerson()->getFullName() ?></span>
                                                    <button class="delete-person-manager btn btn-sm btn-outline-danger" data-personid="<?= $manager->getPerson()->getId() ?>" data-groupid="<?= $iGroupID ?>"><i class="fas fa-trash-alt"></i></button>
                                                </div>
                                        <?php
                                            }
                                        }
                                    } else {
                                        ?>
                                        <p class="text-muted mb-0"><?= _("No assigned Manager") ?>.</p>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a class="btn btn-primary btn-sm" id="add-manager"
                                    data-toggle="tooltip" data-placement="bottom" title="<?= _("Add a specific manager only for this group") ?>"><?= _("Add Manager") ?></a>
                            </div>
                        </div>
                    </div>

                <?php
            }
                ?>


                <?php
                if ($_SESSION['bManageGroups']) {
                ?>
                    <div class="card group_accordion">
                        <div class="card-header" id="headingProperties">
                            <h3 class="card-title">
                                <i class="fas fa-cog mr-2"></i>
                                <a data-toggle="collapse" href="#collapseProperties"><?= _('Group Properties') ?></a>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseProperties"><i class="fas fa-minus collapse-toggle-icon"></i></button>
                            </div>
                        </div>
                        <div id="collapseProperties" class="collapse" aria-labelledby="headingProperties" data-parent="#accordion">
                            <div class="card-body p-1">
                                <b class="d-block mb-2"><?= _('Assigned Properties') ?>:</b>
                                <?php
                                $sAssignedProperties = ',';
                                ?>
                                <table width="100%" cellpadding="2" class="table table-sm dt-responsive dataTable no-footer dtr-inline" id="AssignedPropertiesTable"></table>

                                <?php
                                //}

                                if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) {
                                ?>
                                    <div class="border rounded bg-light mt-3 p-2">
                                        <h4 class="h6 mb-2 d-flex align-items-center">
                                            <i class="fas fa-plus-circle mr-1 text-primary"></i><strong><?= _('Assign a New Property') ?></strong>
                                        </h4>
                                        <p class="small mb-2"><?= _('Choose a property, fill the value if requested, then assign.') ?></p>

                                        <div class="form-group mb-2">
                                            <label for="input-group-properties" class="small mb-1"><?= _('Property') ?></label>
                                            <select name="PropertyId" id="input-group-properties" class="input-group-properties form-control select2" style="width:100%" data-groupID="<?= $iGroupID ?>">
                                                <option disabled selected> -- <?= _('select an option') ?> -- </option>
                                                <?php
                                                foreach ($ormProperties as $ormProperty) {
                                                    //If the property doesn't already exist for this Person, write the <OPTION> tag
                                                    if (strlen(strstr($sAssignedProperties, ',' . $ormProperty->getProId() . ',')) == 0) {
                                                ?>
                                                        <option value="<?= $ormProperty->getProId() ?>" data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>" data-pro_Value=""><?= $ormProperty->getProName() ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div id="prompt-box" class="mb-2"></div>

                                        <div class="d-flex justify-content-end">
                                            <input type="submit" class="btn btn-primary btn-sm px-3 assign-property-btn" value="<?= _('Assign') ?>">
                                        </div>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <p class="text-muted small mt-3 mb-0"><?= _('No property assignment action available for your role.') ?></p>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="card group_accordion">
                        <div class="card-header" id="headingSpecificProperties">
                            <h3 class="card-title">
                                <i class="fas fa-cogs mr-2"></i>
                                <a data-toggle="collapse" href="#collapseSpecificProperties"><?= _('Group-Specific Properties') ?></a>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseSpecificProperties"><i class="fas fa-minus collapse-toggle-icon"></i></button>
                            </div>
                        </div>
                        <div id="collapseSpecificProperties" class="collapse" aria-labelledby="headingSpecificProperties" data-parent="#accordion">
                            <div class="card-body p-1">
                                <b class="d-block mb-2"><?= _('Assigned Properties') ?>:</b>
                                <?php
                                if ($thisGroup->getHasSpecialProps()) {
                                    // Create arrays of the properties.

                                    // Construct the table
                                    if ($ormPropList->count() == 0) {
                                ?>
                                        <p class="text-muted mb-0"><?= _("No member properties have been created") ?></p>
                                    <?php
                                    } else {
                                    ?>

                                        <table width="100%" class="table table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th><?= _('Name') ?></th>
                                                    <th><?= _('Description') ?></th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $sRowClass = 'RowColorA';

                                            foreach ($ormPropList as $prop) {
                                                $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);
                                                if (SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isManageGroupsEnabled()  || $is_group_manager == true || $prop->getPersonDisplay() == "true") {
                                            ?>
                                                    <tr class="<?= $sRowClass ?>">
                                                        <td><?= $prop->getName() ?></td>
                                                        <td><?= OutputUtils::displayCustomField($prop->getTypeId(), $prop->getDescription(), $prop->getSpecial()) ?></td>
                                                    </tr>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </table>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <p><?= _("Disabled for this group.") ?> <?= _("You should Edit the group and \"Enable Group Specific Properties\". To do this, press the button above : \"Edit this Group\"") ?></p>
                                <?php
                                }
                                //Print Assigned Properties
                                ?>

                                <?php
                                if ($thisGroup->getHasSpecialProps() && (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true)) {
                                ?>
                                    <a class="btn btn-outline-primary btn-sm" href="<?= $sRootPath ?>/v2/group/props/Form/editor/<?= $thisGroup->getId() ?>"><?= _('Edit Group-Specific Properties Form') ?></a>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                }

                if ($_SESSION['bManageGroups'] or SessionUser::getUser()->isManageGroupsEnabled()) {
                ?>
                </div>
            </div>
        </div>
    <?php
                }
    ?>

    <div class="group_Side_bar_right">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i><?= _("Manage Group Members") ?></h3>
                <div class="card-tools">
                    <span class="badge badge-success mr-1"><?= $sGroupType ?></span>
                    <?php if (!empty($defaultRole)): ?>
                        <span class="badge badge-info mr-1"><?= _($defaultRole->getOptionName()) ?></span>
                    <?php endif; ?>
                    <span class="badge badge-primary"><?= _('Members:') ?> <span id="iTotalMembers">0</span></span>
                </div>
            </div>
            <div class="card-body">
                <?php
                if (SessionUser::getUser()->isManageGroups() || $is_group_manager == true) {
                ?>

                    <div class="d-flex align-items-center mb-3">
                        <label class="mb-0 mr-2 text-nowrap font-weight-bold">
                            <i class="fas fa-user-plus mr-1 text-success"></i><?= _("Add member") ?>
                        </label>
                        <select class="form-control form-control-sm personSearch select2" name="addGroupMember" style="width:250px;max-width:100%"></select>
                    </div>
                <?php
                }
                ?>
                <!-- START GROUP MEMBERS LISTING  -->
                <table class="table" id="membersTable" style="width: 100%;font-size: 0.9em"></table>
                <!-- END GROUP MEMBERS LISTING -->
            </div>
        </div>


    </div>
</div>
<script nonce="<?= $CSPNonce ?>">
    $(function () {
        function syncAccordionIcons() {
            $('#accordion .collapse').each(function () {
                var targetId = '#' + this.id;
                var icon = $('#accordion button[data-target="' + targetId + '"] i.collapse-toggle-icon');
                if (!icon.length) {
                    return;
                }

                if ($(this).hasClass('show')) {
                    icon.removeClass('fa-plus').addClass('fa-minus');
                } else {
                    icon.removeClass('fa-minus').addClass('fa-plus');
                }
            });
        }

        $('#accordion .collapse').on('shown.bs.collapse hidden.bs.collapse', function () {
            syncAccordionIcons();
        });

        syncAccordionIcons();
    });

    window.CRM.currentGroup = <?= $iGroupID ?>;
    window.CRM.calendarID = <?= json_encode($calendarID) ?>;
    window.CRM.groupName = "<?= $thisGroup->getName() ?>";
    window.CRM.isActive = <?= $thisGroup->isActive() ? 'true' : 'false' ?>;
    window.CRM.isIncludeInEmailExport = <?= $thisGroup->isIncludeInEmailExport() ? 'true' : 'false' ?>;
    window.CRM.isManageGroupsEnabled = <?= (SessionUser::getUser()->isManageGroupsEnabled()) ? 'true' : 'false' ?>;

    var dataT = 0;

    var isShowable = <?php
                        // it should be better to write this part in the api/groups/members
                        if (
                            SessionUser::getUser()->isSeePrivacyDataEnabled()
                            || (!$thisGroup->isSundaySchool() && SessionUser::getUser()->belongsToGroup($iGroupID))
                            || ($thisGroup->isSundaySchool() && SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupID))
                        ) {
                            echo "true";
                        } else {
                            echo "false";
                        }
                        ?>;

    var sPageTitle = "<?= $sPageTitle ?>";

    <?php if (!is_null($first_manager)) { ?>
        window.CRM.churchloc = {
            lat: parseFloat(<?= $first_manager->getFamily()->getLatitude() ?>),
            lng: parseFloat(<?= $first_manager->getFamily()->getLongitude() ?>)
        };
        window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom") ?>;
        window.CRM.address = "<?= $first_manager->getFamily()->getAddress() ?>";
    <?php } else { ?>
        window.CRM.churchloc = {
            lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
            lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
        };
        window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom") ?>;
        window.CRM.address = '';
    <?php } ?>
</script>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
    type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>


<script src="<?= $sRootPath ?>/skin/js/group/GroupView.js"></script>
<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/groupcommon/group_sundaygroup.js"></script>

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

<?php require $sRootDocument . '/Include/Footer.php'; ?>
