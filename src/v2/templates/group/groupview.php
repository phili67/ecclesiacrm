<?php

/*******************************************************************************
 *
 *  filename    : groupview.php
 *  last change : 2019-07-03
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

<?php
if (SessionUser::getUser()->isShowMapEnabled() || SessionUser::getUser()->belongsToGroup($iGroupID)) {
?>
    <a class="btn btn-app" href="<?= $sRootPath ?>/v2/map/<?= $thisGroup->getId() ?>"><i class="fas fa-map-marker-alt"></i><?= _('Map this group') ?></a>
<?php
}
?>

<?php
if (Cart::GroupInCart($iGroupID) && SessionUser::getUser()->isShowCartEnabled()) {
?>
    <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fas fa-times"></i> <span class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
<?php
} else if (SessionUser::getUser()->isShowCartEnabled()) {
?>
    <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fas fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
<?php
}
?>
<?php
if (SessionUser::getUser()->isManageGroupsEnabled()) {
?>
    <a class="btn btn-app" href="<?= $sRootPath ?>/v2/group/editor/<?= $thisGroup->getId() ?>"
        data-toggle="tooltip" data-placement="bottom" title="<?= _("To add special Group roles or to modify the role by default or to enable Group-specific properties") ?>"><i class="fas fa-pencil-alt"></i><?= _("Edit this Group") ?></a>
    <button class="btn btn-app bg-maroon" id="deleteGroupButton"><i class="fas fa-trash-alt"></i><?= _("Delete this Group") ?></button>
<?php
}
?>

<?php
if (
    SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
    || SessionUser::getUser()->isMenuOptionsEnabled()
) {
?>
    <a class="btn btn-app bg-orange" id="add-event"><i class="far fa-calendar-plus"></i><?= _("Appointment") ?></a>
<?php
}
?>

<?php
if (SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']) { // use session variable for an current group manager
?>
    <form method="POST" action="<?= $sRootPath ?>/v2/group/reports" style="display:inline">
        <input type="hidden" id="GroupID" name="GroupID" value="<?= $iGroupID ?>">
        <button type="submit" class="btn btn-app bg-green exportCheckOutCSV"><i class="fas fa-file-pdf"></i><?= _("Group reports") ?></button>
    </form>

    <a class="btn btn-app bg-purple" id="groupbadge" data-groupid="<?= $iGroupID ?>" data-toggle="tooltip"
        data-placement="bottom" title="<?= _("Create here your badges or QR-Code to call the register with them") ?>"> <i
            class="fas fa-id-badge"></i> <span
            class="cartActionDescription"><?= _("Group Badges") ?></span></a>

    <a class="btn btn-app bg-yellow-gradient <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> export-vcard-button" data-toggle="tooltip" data-placement="bottom" title="" href="<?= $sRootPath ?>/api/groups/addressbook/extract/<?= $iGroupID ?>" data-original-title="<?= _("Click to create an addressbook of the Group") ?>"><i class="far fa-id-card">
        </i> <?= _('Address Book') ?></a>
<?php
}
?>

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
            <a class="btn btn-app <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> email-button" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank"><i class="far fa-paper-plane"></i><?= _("Email Group") ?></a>
            <button type="button" class="btn btn-app dropdown-toggle email-button-dropdown" data-toggle="dropdown" <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?>>
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <?= MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
            </div>
        </div>

        <div class="btn-group">
            <a class="btn btn-app <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> email-cci-button" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank"><i class="fas fa-paper-plane"></i><?= _("Email (BCC)") ?></a>
            <button type="button" class="btn btn-app dropdown-toggle email-button-cci-dropdown" data-toggle="dropdown" <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?>>
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu" role="menu">
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
        <a class="btn btn-app <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> sms-button" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fas fa-mobile"></i><?= _('Text Group') ?></a>
        <script nonce="<?= $CSPNonce ?>">
            function allPhonesCommaD() {
                prompt("<?= _("Press CTRL + C to copy all group members\' phone numbers") ?>", "<?= mb_substr($sPhoneLink, 0, -2) ?>")
            };
        </script>
<?php
    }
}
?>

<br><br>

<div class="row">
    <?php
    if ($_SESSION['bManageGroups'] or SessionUser::getUser()->isManageGroupsEnabled()) {
    ?>
        <div class="col group_Side_bar">
            <div class="sticky-top">
                <div id="accordion">
                <?php
            }
            if (SessionUser::getUser()->isManageGroupsEnabled()) {
                ?>
                    <div class="card group_accordion">
                        <div class="card-header border-1 group_header_accordion" id="headingQuickSettings">
                            <h3 class="card-title">
                                <i class="fas fa-sliders fa-fw"></i> <button class="btn btn-link" data-toggle="collapse" data-target="#collapseQuickSettings" aria-expanded="true" aria-controls="collapseQuickSettings">
                                    <?= _('Quick Settings') ?>
                                </button>
                            </h3>
                            <div class="card-tools pull-right">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseQuickSettings" aria-expanded="true" aria-controls="collapseQuickSettings"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="collapseQuickSettings" class="collapse show" aria-labelledby="headingQuickSettings" data-parent="#accordion" style="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5"><label><?= _("Group is") ?></label> : </div>
                                    <div class="col-md-7">
                                        <input data-width="100" class="btn btn-primary btn-sm" id="isGroupActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Active') ?>" data-off="<?= _('Disabled') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5"><label><?= _("The emails are") ?></label> : </div>
                                    <div class="col-md-7">
                                        <input data-width="100" class="btn btn-primary btn-sm" id="isGroupEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= _('Include') ?>" data-off="<?= _('Exclude') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card group_accordion">
                        <div class="card-header border-1 group_header_accordion" id="headingGroupManager">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> <button class="btn btn-link" data-toggle="collapse" data-target="#collapseGroupManager" aria-expanded="true" aria-controls="collapseGroupManager">
                                    <?= _("Group Managers") ?>
                                </button>
                            </h3>
                            <div class="card-tools pull-right">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseGroupManager" aria-expanded="true" aria-controls="collapseGroupManager"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="collapseGroupManager" class="collapse" aria-labelledby="headingGroupManager" data-parent="#accordion" style="">
                            <div class="card-body">
                                <b><?= _("Assigned Managers") ?>:</b>
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
                                                <button class="delete-person-manager btn btn-danger btn-xs" data-personid="<?= $manager->getPerson()->getId() ?>" data-groupid="<?= $iGroupID ?>"><i class="icon far fa-trash-alt"></i></button> <?= $manager->getPerson()->getFullName() ?> <br />
                                        <?php
                                            }
                                        }
                                    } else {
                                        ?>
                                        <p><?= _("No assigned Manager") ?>.</p>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a class="btn btn-primary" id="add-manager"
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
                        <div class="card-header border-1 group_header_accordion" id="headingProperties">
                            <h3 class="card-title">
                                <i class="fas fa-gear"></i> <button class="btn btn-link" data-toggle="collapse" data-target="#collapseProperties" aria-expanded="true" aria-controls="collapseProperties">
                                    <?= _('Group Properties') ?>
                                </button>
                            </h3>
                            <div class="card-tools pull-right">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseProperties" aria-expanded="true" aria-controls="collapseProperties"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="collapseProperties" class="collapse" aria-labelledby="headingProperties" data-parent="#accordion" style="">
                            <div class="card-body">
                                <b><?= _('Assigned Properties') ?>:</b>
                                <?php
                                $sAssignedProperties = ',';
                                ?>
                                <table width="100%" cellpadding="2" class="table table-condensed dt-responsive dataTable no-footer dtr-inline" id="AssignedPropertiesTable"></table>

                                <?php
                                //}

                                if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) {
                                ?>
                                    <div class="alert alert-info">
                                        <div>
                                            <h4><strong><?= _('Assign a New Property') ?>:</strong></h4>

                                            <div class="row">
                                                <div class="form-group col-xs-12 col-md-7">
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
                                                <div id="prompt-box" class="col-xs-12 col-md-7"></div>
                                                <div class="form-group col-xs-12 col-md-7">
                                                    <input type="submit" class="btn btn-primary assign-property-btn" value="<?= _('Assign') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <br><br><br>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="card group_accordion">
                        <div class="card-header border-1 group_header_accordion" id="headingSpecificProperties">
                            <h3 class="card-title">
                                <i class="fas fa-gears"></i> <button class="btn btn-link" data-toggle="collapse" data-target="#collapseSpecificProperties" aria-expanded="true" aria-controls="collapseSpecificProperties">
                                    <?= _('Group-Specific Properties') ?>
                                </button>
                            </h3>
                            <div class="card-tools pull-right">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseSpecificProperties" aria-expanded="true" aria-controls="collapseSpecificProperties"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="collapseSpecificProperties" class="collapse" aria-labelledby="headingSpecificProperties" data-parent="#accordion" style="">
                            <div class="card-body">
                                <b><?= _('Assigned Properties') ?>:</b>
                                <?php
                                if ($thisGroup->getHasSpecialProps()) {
                                    // Create arrays of the properties.

                                    // Construct the table
                                    if ($ormPropList->count() == 0) {
                                ?>
                                        <p><?= _("No member properties have been created") ?></p>
                                    <?php
                                    } else {
                                    ?>

                                        <table width="100%" cellpadding="2" cellspacing="0" class="table table-condensed dt-responsive dataTable no-footer dtr-inline">
                                            <tr class="TableHeader">
                                                <td><b><?= _('Name') ?></b></td>
                                                <td><b><?= _('Description') ?></b></td>
                                            </tr>
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
                                    <a class="btn btn-primary" href="<?= $sRootPath ?>/v2/group/props/Form/editor/<?= $thisGroup->getId() ?>"><?= _('Edit Group-Specific Properties Form') ?></a>
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

    <div class="col" ?>
        <div class="card">
            <div class="card-header border-1">
                <h3 class="card-title"><i class="fas fa-users"></i> <?= _("Manage Group Members") ?>:</h3>
                <div class="card-tools pull-right">
                    <button class="btn btn-success" type="button">
                        <?= _('Type of Group') ?> <span class="badge bg-white"> <?= $sGroupType ?> </span>
                    </button>
                    <button class="btn btn-info" type="button">
                        <?php
                        if (!empty($defaultRole)) {
                        ?>
                            <?= _('Default Role') ?> <span class="badge  bg-white"><?= _($defaultRole->getOptionName()) ?></span>
                        <?php
                        }
                        ?>
                    </button>
                    <button class="btn btn-primary" type="button">
                        <?= _('Total Members') ?> <span class="badge  bg-white" id="iTotalMembers"></span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) {
                ?>

                    <div class="row">
                        <div class="col-md-1">
                            <?= _("Add") ?>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
                        </div>
                    </div>
                    <br>
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
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
<?php
}
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>