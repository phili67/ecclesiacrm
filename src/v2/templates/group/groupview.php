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

<div class="row">
    <div class="col-md-12">
        <center>
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
            <?php
            if (SessionUser::getUser()->isAdmin()) {
                ?>
                <a class="btn btn-danger" href="<?= $sRootPath ?>/api/groups/addressbook/extract/<?= $iGroupID ?>">
                    <?= _('Address Book') ?>
                    <span class="badge  bg-white">
              <i class="fa fa fa-address-card-o" aria-hidden="true"></i>
            </span>
                </a>
                <?php
            }
            ?>
        </center>
    </div>
</div>

<br/>

<div class="card">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Group Functions') ?></h3>
    </div>
    <div class="card-body">
        <?php
        if ( SessionUser::getUser()->isShowMapEnabled() || SessionUser::getUser()->belongsToGroup($iGroupID) ) {
            ?>
            <a class="btn btn-app" href="<?= $sRootPath ?>/v2/map/<?= $thisGroup->getId() ?>"><i class="fa fa-map-marker"></i><?= _('Map this group') ?></a>
            <?php
        }
        ?>

        <?php
        if (Cart::GroupInCart($iGroupID) && SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
            <?php
        } else if (SessionUser::getUser()->isShowCartEnabled()){
            ?>
            <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
            <?php
        }
        ?>
        <?php
        if ( SessionUser::getUser()->isManageGroupsEnabled() ) {
            ?>
            <a class="btn btn-app" href="<?= $sRootPath ?>/GroupEditor.php?GroupID=<?= $thisGroup->getId()?>"><i class="fa fa-pencil"></i><?= _("Edit this Group") ?></a>
            <button class="btn btn-app bg-maroon"  id="deleteGroupButton"><i class="fa fa-trash"></i><?= _("Delete this Group") ?></button>
            <?php
        }
        ?>

        <?php
        if ($_SESSION['bManageGroups']) {
            ?>
            <form method="POST" action="<?= $sRootPath ?>/GroupReports.php" style="display:inline">
                <input type="hidden" id="GroupID" name="GroupID" value="<?= $iGroupID?>">
                <button type="submit" class="btn btn-app bg-green exportCheckOutCSV"><i class="fa fa-file-pdf-o"></i><?= _("Group reports") ?></button>
            </form>
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
        WHERE per_ID NOT IN
            (SELECT per_ID
                FROM person_per
                INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
                INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email')
            AND p2g2r_grp_ID = ".$iGroupID;

        $connection = Propel::getConnection();

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $sEmailLink = '';
        while (list($per_Email, $fam_Email, $virt_RoleName) = $statement->fetch( \PDO::FETCH_BOTH )) {
            $sEmail = MiscUtils::SelectWhichInfo($per_Email, $fam_Email, false);
            if ($sEmail) {
                /* if ($sEmailLink) // Don't put delimiter before first email
              $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
                // Add email only if email address is not already in string
                if (!stristr($sEmailLink, $sEmail)) {
                    $sEmailLink .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                    $roleEmails->$virt_RoleName .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                }
            }
        }
        if ($sEmailLink) {
            // Add default email if default email has been set and is not already in string
            if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
                $sEmailLink .= SessionUser::getUser()->MailtoDelimiter().SystemConfig::getValue('sToEmailAddress');
            }
            $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

            if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
                // Display link
                ?>
                <div class="btn-group">
                    <a  class="btn btn-app" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>"><i class="fa fa-send-o"></i><?= _("Email Group") ?></a>
                    <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown" >
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                        <?= MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
                    </div>
                </div>

                <div class="btn-group">
                    <a class="btn btn-app" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>"><i class="fa fa-send"></i><?= _("Email (BCC)") ?></a>
                    <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown" >
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
        WHERE per_ID NOT IN
            (SELECT per_ID
            FROM person_per
            INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
            INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS')
        AND p2g2r_grp_ID = ".$iGroupID;

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $sPhoneLink = '';
        $sCommaDelimiter = ', ';
        while (list($per_CellPhone, $fam_CellPhone) = $statement->fetch( \PDO::FETCH_BOTH )) {
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
                <a class="btn btn-app" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fa fa-mobile-phone"></i><?= _('Text Group') ?></a>
                <script nonce="<?= $CSPNonce ?>">function allPhonesCommaD() {prompt("<?= _("Press CTRL + C to copy all group members\' phone numbers") ?>", "<?= mb_substr($sPhoneLink, 0, -2) ?>")};</script>
                <?php
            }
        }
        ?>

        <a class="btn btn-app bg-purple" id="groupbadge" data-groupid="<?= $iGroupID ?>"> <i
                class="fa fa-file-picture-o"></i> <span
                class="cartActionDescription"><?= _("Group Badges") ?></span></a>

        <?php
        if (SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
            || SessionUser::getUser()->isMenuOptionsEnabled()) {
            ?>
            <a class="btn btn-app bg-orange" id="add-event"><i class="fa fa-calendar-plus-o"></i><?= _("Appointment") ?></a>
            <?php
        }
        ?>
    </div>
</div>

<?php
if ( SessionUser::getUser()->isManageGroupsEnabled() ) {
    ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card collapsed-card">
                <div class="card-header with-border">
                    <h3 class="card-title"><?= _('Quick Settings') ?></h3>
                    <div class="card-tools pull-right">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"> <b><?= _('Status') ?>:</b>
                            <input data-width="150" id="isGroupActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Active') ?>" data-off="<?= _('Disabled') ?>">
                        </div>
                        <div class="col-md-6"> <b><?= _('Email export') ?>:</b>
                            <input data-width="150" id="isGroupEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= _('Include') ?>" data-off="<?= _('Exclude') ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card collapsed-card">
                <div class="card-header with-border">
                    <h3 class="card-title" data-toggle="tooltip"  title="" data-placement="bottom" data-original-title="<?= _("Assign a group manager only for This Group. He can add or remove member from This Group, but not create Members.") ?>"><?= _("Group Managers") ?></h3>
                    <div class="card-tools pull-right">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <b><?= _("Assigned Managers") ?>:</b>
                    <div id="Manager-list">
                        <?php
                        $managers = GroupManagerPersonQuery::Create()->findByGroupId($iGroupID);

                        $first_manager = null;

                        if ($managers->count()) {
                            foreach ($managers as $manager) {
                                if ( is_null ($first_manager) ) {
                                    $first_manager = $manager->getPerson();
                                }
                                if (!$manager->getPerson()->isDeactivated()) {
                                    ?>
                                    <?= $manager->getPerson()->getFullName()?><a class="delete-person-manager" data-personid="<?= $manager->getPerson()->getId() ?>" data-groupid="<?= $iGroupID ?>"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>,
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
                    <a class="btn btn-primary" id="add-manager"><?= _("Add Manager") ?></a>
                </div>
            </div>
        </div>
    </div>

    <?php
}
?>

<?php
if ( $_SESSION['bManageGroups'] ) {
    ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card collapsed-card">
                <div class="card-header with-border">
                    <h3 class="card-title" data-toggle="tooltip"  title="" data-placement="bottom" data-original-title="<?= _("Assign properties for This Group. This properties are global properties and this can be changed in the admin right side bar &rarr; Group Properties") ?>"><?= _('Group Properties') ?></h3>
                    <div class="card-tools pull-right">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <b><?= _('Assigned Properties') ?>:</b>
                    <?php
                    $sAssignedProperties = ',';
                    ?>
                    <table width="100%" cellpadding="2" class="table table-condensed dt-responsive dataTable no-footer dtr-inline" id="AssignedPropertiesTable"></table>

                    <?php
                    //}

                    if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true ) {
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
                                                if (strlen(strstr($sAssignedProperties, ','.$ormProperty->getProId().',')) == 0) {
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
        <div class="col-lg-6">
            <div class="card collapsed-card">
                <div class="card-header with-border">
                    <h3 class="card-title" data-toggle="tooltip" title="" data-placement="bottom" data-original-title="<?= _("Assign properties for all members of the group. This properties are visible in each Person Profile &rarr; Assigned Group") ?>"><?= _('Group-Specific Properties') ?></h3>
                    <div class="card-tools pull-right">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <b><?= _('Assigned Properties') ?>:</b>
                    <?php
                    if ($thisGroup->getHasSpecialProps()) {
                        // Create arrays of the properties.

                        // Construct the table
                        if ($ormPropList->count() == 0) {
                            ?>
                            <p><?= _("No member properties have been created")?></p>
                            <?php
                        } else {
                            ?>

                            <table width="100%" cellpadding="2" cellspacing="0"  class="table table-condensed dt-responsive dataTable no-footer dtr-inline">
                                <tr class="TableHeader">
                                    <!--<td><b><?= _('Type') ?></b></td>-->
                                    <td><b><?= _('Name') ?></b></td>
                                    <td><b><?= _('Description') ?></b></td>
                                </tr>
                                <?php
                                $sRowClass = 'RowColorA';

                                foreach ($ormPropList as $prop) {
                                    $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);
                                    if ( SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isManageGroupsEnabled()  || $is_group_manager == true || $prop->getPersonDisplay() == "true") {
                                        ?>
                                        <tr class="<?= $sRowClass ?>">
                                            <!--<td><?= $aPropTypes[$prop->getTypeId()] ?></td>-->
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
                    if ($thisGroup->getHasSpecialProps() && (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) ) {
                        ?>
                        <a class="btn btn-primary" href="<?= $sRootPath ?>/GroupPropsFormEditor.php?GroupID=<?= $thisGroup->getId() ?>"><?= _('Edit Group-Specific Properties Form') ?></a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
}
?>

<div class="card">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Group Members:') ?></h3>
    </div>
    <div class="card-body">
        <!-- START GROUP MEMBERS LISTING  -->
        <table class="table" id="membersTable"></table>
        <!-- END GROUP MEMBERS LISTING -->
    </div>
</div>

<?php
if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) {
    ?>
    <div class="card">
        <div class="card-header with-border">
            <h3 class="card-title"><i class="fa fa-users"></i> <?= _("Manage Group Members") ?>:</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-1">
                    <?= _("Add") ?>
                </div>
                <div class="col-md-3">
                    <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
                </div>
                <div class="col-md-4">
                    <button type="button" id="deleteSelectedRows" class="btn btn-danger" disabled> <?= _('Remove Selected Members from group') ?> </button>
                </div>
                <?php
                if (SessionUser::getUser()->isManageGroupsEnabled()) {
                    ?>
                    <div class="col-md-4">
                        <div class="btn-group">
                            <button type="button" id="addSelectedToCart" class="btn btn-success"  disabled> <?= _('Add Selected Members to Cart') ?></button>
                            <button type="button" id="buttonDropdown" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item" id="addSelectedToGroup"   disabled> <?= _('Add Selected Members to Group') ?></a>
                                <a class="dropdown-item" id="moveSelectedToGroup"  disabled> <?= _('Move Selected Members to Group') ?></a>
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
}
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.currentGroup            = <?= $iGroupID ?>;
    window.CRM.calendarID              = <?= json_encode($calendarID) ?>;
    window.CRM.groupName               = "<?= $thisGroup->getName() ?>";
    window.CRM.isActive                = <?= $thisGroup->isActive()? 'true': 'false' ?>;
    window.CRM.isIncludeInEmailExport  = <?= $thisGroup->isIncludeInEmailExport()? 'true': 'false' ?>;

    var dataT = 0;

    var isShowable  = <?php
        // it should be better to write this part in the api/groups/members
        if (SessionUser::getUser()->isSeePrivacyDataEnabled()
            || (!$thisGroup->isSundaySchool() && SessionUser::getUser()->belongsToGroup($iGroupID))
            || ($thisGroup->isSundaySchool() && SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupID))) {
            echo "true";
        } else {
            echo "false";
        }
        ?>;

    var sPageTitle = '<?= $sPageTitle ?>';

    <?php if ( !is_null ($first_manager) ) { ?>
        window.CRM.churchloc = {
            lat: <?= $first_manager->getFamily()->getLatitude() ?>,
            lng: <?= $first_manager->getFamily()->getLongitude() ?>};
        window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
        window.CRM.address = "<?= $first_manager->getFamily()->getAddress() ?>";
    <?php } else { ?>
        window.CRM.churchloc = {
            lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
            lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};
        window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom")?>;
        window.CRM.address = '';
    <?php } ?>
</script>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
        type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>


<script src="<?= $sRootPath ?>/skin/js/group/GroupView.js" ></script>
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



