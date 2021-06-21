<?php
/*******************************************************************************
 *
 *  filename    : UserEditor.php
 *  description : form for adding and editing users
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2002 Phillip Hullquist, Deane Barker
 *
 *  Updated 2005-03-19 by Everette L Mills: Updated to remove error that could be created
 *  by use of duplicate usernames
 *
 *  Additional Contributors:
 *  2006 Ed Davis
 *  2019 Philippe Logel All right reserved
 *
 ******************************************************************************/
// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Emails\NewAccountEmail;
use EcclesiaCRM\Emails\UpdateAccountEmail;
use EcclesiaCRM\User;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\UserRole;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\UserConfig;
use EcclesiaCRM\UserConfigChoicesQuery;


// Security: User must be an Admin to access this page.
// Otherwise re-direct to the main menu.
if (!SessionUser::getUser()->isAdmin()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$iPersonID = -1;
$vNewUser = false;
$bShowPersonSelect = false;
$usr_role_id = null;


// we search all the available roles
$userRoles = UserRoleQuery::Create()->find();

// Get the PersonID out of either querystring or the form, depending and what we're doing
if (isset($_GET['PersonID'])) {
    $iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');
    $bNewUser = false;
} elseif (isset($_POST['PersonID'])) {
    $iPersonID = InputUtils::LegacyFilterInput($_POST['PersonID'], 'int');
    $bNewUser = false;
} elseif (isset($_GET['NewPersonID'])) {
    $iPersonID = InputUtils::LegacyFilterInput($_GET['NewPersonID'], 'int');
    $bNewUser = true;
}

if (isset($_GET['ErrorText'])) {
    $sErrorText = InputUtils::LegacyFilterInput($_GET['ErrorText'], 'string');
} else {
    $sErrorText = '';
}

//Value to help determine correct return state on error
if (isset($_POST['NewUser'])) {
    $NewUser = InputUtils::LegacyFilterInput($_POST['NewUser'], 'string');
}

// Has the form been submitted?
if (isset($_POST['save']) && $iPersonID > 0) {

    // Assign all variables locally
    $sAction = $_POST['Action'];

    $defaultFY = MiscUtils::CurrentFY();
    $sUserName = strtolower(InputUtils::LegacyFilterInput($_POST['UserName']));

    if (strlen($sUserName) < 3) {
        if ($NewUser == false) {
            //Report error for current user creation
            RedirectUtils::Redirect('UserEditor.php?PersonID=' . $iPersonID . '&ErrorText=' . _("Login must be a least 3 characters!"));
        } else {
            //Report error for new user creation
            RedirectUtils::Redirect('UserEditor.php?NewPersonID=' . $iPersonID . '&ErrorText=' . _("Login must be a least 3 characters!"));
        }
    } else {

        if (isset($_POST['roleID'])) {
            $roleID = $_POST['roleID'];
        } else {
            $roleID = 0;
        }
        if (isset($_POST['AddRecords'])) {
            $AddRecords = 1;
        } else {
            $AddRecords = 0;
        }
        if (isset($_POST['EditRecords'])) {
            $EditRecords = 1;
        } else {
            $EditRecords = 0;
        }
        if (isset($_POST['ShowCart'])) {
            $ShowCart = 1;
        } else {
            $ShowCart = 0;
        }
        if (isset($_POST['ShowMap'])) {
            $ShowMap = 1;
        } else {
            $ShowMap = 0;
        }
        if (isset($_POST['EDrive'])) {
            $EDrive = 1;
        } else {
            $EDrive = 0;
        }
        if (isset($_POST['DeleteRecords'])) {
            $DeleteRecords = 1;
        } else {
            $DeleteRecords = 0;
        }
        if (isset($_POST['MenuOptions'])) {
            $MenuOptions = 1;
        } else {
            $MenuOptions = 0;
        }
        if (isset($_POST['ManageGroups'])) {
            $ManageGroups = 1;
        } else {
            $ManageGroups = 0;
        }
        if (isset($_POST['Finance'])) {
            $Finance = 1;
        } else {
            $Finance = 0;
        }
        if (isset($_POST['Notes'])) {
            $Notes = 1;
        } else {
            $Notes = 0;
        }
        if (isset($_POST['EditSelf'])) {
            $EditSelf = 1;
        } else {
            $EditSelf = 0;
        }
        if (isset($_POST['Canvasser'])) {
            $Canvasser = 1;
        } else {
            $Canvasser = 0;
        }

        if (isset($_POST['Admin'])) {
            $Admin = 1;
        } else {
            $Admin = 0;
        }

        if (isset($_POST['QueryMenu'])) {
            $QueryMenu = 1;
        } else {
            $QueryMenu = 0;
        }

        if (isset($_POST['CanSendEmail'])) {
            $CanSendEmail = 1;
        } else {
            $CanSendEmail = 0;
        }

        if (isset($_POST['ExportCSV'])) {
            $ExportCSV = 1;
        } else {
            $ExportCSV = 0;
        }

        if (isset($_POST['CreateDirectory'])) {
            $CreateDirectory = 1;
        } else {
            $CreateDirectory = 0;
        }

        if (isset($_POST['ExportSundaySchoolPDF'])) {
            $ExportSundaySchoolPDF = 1;
        } else {
            $ExportSundaySchoolPDF = 0;
        }

        if (isset($_POST['ExportSundaySchoolCSV'])) {
            $ExportSundaySchoolCSV = 1;
        } else {
            $ExportSundaySchoolCSV = 0;
        }

        if (isset($_POST['PastoralCare'])) {
            $PastoralCare = 1;
        } else {
            $PastoralCare = 0;
        }

        if (isset($_POST['MailChimp'])) {
            $MailChimp = 1;
        } else {
            $MailChimp = 0;
        }

        if (isset($_POST['MainDashboard'])) {
            $MainDashboard = 1;
        } else {
            $MainDashboard = 0;
        }

        if (isset($_POST['SeePrivacyData'])) {
            $SeePrivacyData = 1;
        } else {
            $SeePrivacyData = 0;
        }


        if (isset($_POST['GdrpDpo'])) {
            $GdrpDpo = 1;
        } else {
            $GdrpDpo = 0;
        }

        // Initialize error flag
        $bErrorFlag = false;

        // Were there any errors?
        if (!$bErrorFlag) {
            $undupCount = UserQuery::create()->filterByUserName($sUserName)->_and()->filterByPersonId($iPersonID, Criteria::NOT_EQUAL)->count();

            // Write the ORM depending on whether we're adding or editing
            if ($sAction == 'add') {
                if ($undupCount == 0) {
                    $rawPassword = User::randomPassword();
                    $sPasswordHashSha256 = hash('sha256', $rawPassword . $iPersonID);

                    $user = new User();

                    $user->setPersonId($iPersonID);
                    $user->setPassword($sPasswordHashSha256);
                    $user->setLastLogin(date('Y-m-d H:i:s'));

                    $user->setPastoralCare($PastoralCare);
                    $user->setMailChimp($MailChimp);
                    $user->setMainDashboard($MainDashboard);
                    $user->setSeePrivacyData($SeePrivacyData);
                    $user->setGdrpDpo($GdrpDpo);
                    $user->setAddRecords($AddRecords);
                    $user->setEditRecords($EditRecords);
                    $user->setDeleteRecords($DeleteRecords);

                    $user->setRoleId($roleID);

                    $user->setShowCart($ShowCart);
                    $user->setShowMap($ShowMap);
                    $user->setEDrive($EDrive);
                    $user->setMenuOptions($MenuOptions);

                    $user->setManageGroups($ManageGroups);
                    $user->setFinance($Finance);
                    $user->setNotes($Notes);

                    $user->setAdmin($Admin);
                    $user->setShowMenuQuery($QueryMenu);
                    $user->setCanSendEmail($CanSendEmail);
                    $user->setExportCSV($ExportCSV);
                    $user->setCreatedirectory($CreateDirectory);
                    $user->setExportSundaySchoolPDF($ExportSundaySchoolPDF);
                    $user->setExportSundaySchoolCSV($ExportSundaySchoolCSV);
                    //$user->setDefaultFY($usr_defaultFY);
                    $user->setUserName($sUserName);

                    $user->setEditSelf($EditSelf);
                    $user->setCanvasser($Canvasser);

                    $user->save();

                    $user->createTimeLineNote("created");
                    $user->createHomeDir();

                    if ($ManageGroups) {// in the case the user is a group manager, we add all the group calendars
                        $user->createGroupAdminCalendars();
                    }

                    $email = new NewAccountEmail($user, $rawPassword);
                    $email->send();
                } else {
                    // Set the error text for duplicate when new user
                    RedirectUtils::Redirect('UserEditor.php?NewPersonID=' . $iPersonID . '&ErrorText=' . _("Login already in use, please select a different login!"));
                }
            } else {
                if ($undupCount == 0) {
                    //$user->createHomeDir();
                    $user = UserQuery::create()->findPk($iPersonID);

                    $old_ManageGroups = $user->isManageGroupsEnabled();
                    $oldUserName = $user->getUserName();

                    $user->setAddRecords($AddRecords);
                    $user->setPastoralCare($PastoralCare);
                    $user->setMailChimp($MailChimp);
                    if ($roleID > 0) {
                        $user->setRoleId($roleID);
                    }
                    $user->setMainDashboard($MainDashboard);
                    $user->setSeePrivacyData($SeePrivacyData);
                    $user->setGdrpDpo($GdrpDpo);
                    $user->setEditRecords($EditRecords);
                    $user->setDeleteRecords($DeleteRecords);
                    $user->setShowCart($ShowCart);
                    $user->setShowMap($ShowMap);
                    $user->setEDrive($EDrive);
                    $user->setMenuOptions($MenuOptions);
                    $user->setManageGroups($ManageGroups);
                    $user->setFinance($Finance);
                    $user->setNotes($Notes);
                    $user->setAdmin($Admin);
                    $user->setShowMenuQuery($QueryMenu);
                    $user->setCanSendEmail($CanSendEmail);
                    $user->setExportCSV($ExportCSV);
                    $user->setCreatedirectory($CreateDirectory);
                    $user->setExportSundaySchoolPDF($ExportSundaySchoolPDF);
                    $user->setExportSundaySchoolCSV($ExportSundaySchoolCSV);

                    if (strtolower($oldUserName) != "admin") {
                        $user->setUserName($sUserName);
                    }

                    $user->setEditSelf($EditSelf);
                    $user->setCanvasser($Canvasser);
                    $user->save();

                    if (strtolower($oldUserName) != "admin") {
                        $user->renameHomeDir($oldUserName, $sUserName);
                    }

                    $user->createTimeLineNote("updated");// the calendars are moved from one username to another in the function : renameHomeDir

                    if ($ManageGroups || $Admin) {
                        if (!$old_ManageGroups) {// only when the user has now the role group manager
                            $user->deleteGroupAdminCalendars();
                            $user->createGroupAdminCalendars();
                        }
                    } else if ($old_ManageGroups) {// only delete group calendars in the case He was a group manager
                        $user->deleteGroupAdminCalendars();
                    }

                    $email = new UpdateAccountEmail($user, _("The same as before"));
                    $email->send();
                } else {
                    // Set the error text for duplicate when currently existing
                    RedirectUtils::Redirect('UserEditor.php?PersonID=' . $iPersonID . '&ErrorText=' . _("Login already in use, please select a different login!"));
                }
            }
        }
    }
} else {

    // Do we know which person yet?
    if ($iPersonID > 0) {
        $usr_per_ID = $iPersonID;

        if (!$bNewUser) {
            // Get the data on this user
            $user = UserQuery::create()
                ->innerJoinWithPerson()
                ->withColumn('Person.FirstName', 'FirstName')
                ->withColumn('Person.LastName', 'LastName')
                ->findOneByPersonId($iPersonID);

            $sUser = $user->getLastName() . ', ' . $user->getFirstName();
            $sUserName = $user->getUserName();

            $usr_AddRecords = $user->getAddRecords();
            $usr_PastoralCare = $user->getPastoralCare();
            $usr_GDRP_DPO = $user->getGdrpDpo();
            $usr_MailChimp = $user->getMailChimp();
            $usr_MainDashboard = $user->getMainDashboard();
            $usr_SeePrivacyData = $user->getSeePrivacyData();
            $usr_EditRecords = $user->getEditRecords();
            $usr_DeleteRecords = $user->getDeleteRecords();
            $usr_ShowCart = $user->getShowCart();
            $usr_ShowMap = $user->getShowMap();
            $usr_EDrive = $user->getEdrive();
            $usr_MenuOptions = $user->getMenuOptions();
            $usr_ManageGroups = $user->getManageGroups();
            $usr_Finance = $user->getFinance();
            $usr_Notes = $user->getNotes();
            $usr_Admin = $user->getAdmin();
            $usr_showMenuQuery = $user->getShowMenuQuery();
            $usr_CanSendEmail = $user->getCanSendEmail();
            $usr_ExportCSV = $user->getExportCSV();
            $usr_CreateDirectory = $user->getCreatedirectory();
            $usr_ExportSundaySchoolPDF = $user->getExportSundaySchoolPDF();
            $usr_ExportSundaySchoolCSV = $user->getExportSundaySchoolCSV();
            $usr_EditSelf = $user->getEditSelf();
            $usr_Canvasser = $user->getCanvasser();

            $sAction = 'edit';
        } else {
            $dbPerson = PersonQuery::create()->findPk($iPersonID);
            $sUser = $dbPerson->getFullName();
            if ($dbPerson->getEmail() != '') {
                $sUserName = $dbPerson->getEmail();
            } else {
                $sUserName = $dbPerson->getFirstName() . $dbPerson->getLastName();
            }
            $sAction = 'add';
            $vNewUser = 'true';

            $usr_AddRecords = 0;
            $usr_PastoralCare = 0;
            $usr_GDRP_DPO = 0;
            $usr_MailChimp = 0;
            $usr_MainDashboard = 0;
            $usr_SeePrivacyData = 0;
            $usr_EditRecords = 0;
            $usr_DeleteRecords = 0;
            $usr_ShowCart = 0;
            $usr_ShowMap = 0;
            $usr_EDrive = 0;
            $usr_MenuOptions = 0;
            $usr_ManageGroups = 0;
            $usr_Finance = 0;
            $usr_Notes = 0;
            $usr_Admin = 0;
            $usr_showMenuQuery = 0;
            $usr_CanSendEmail = 0;
            $usr_ExportCSV = 0;
            $usr_CreateDirectory = 0;
            $usr_ExportSundaySchoolPDF = 0;
            $usr_ExportSundaySchoolCSV = 0;
            $usr_EditSelf = 1;
            $usr_Canvasser = 0;
        }

        // New user without person selected yet
    } else {
        $sAction = 'add';
        $bShowPersonSelect = true;

        $usr_AddRecords = 0;
        $usr_PastoralCare = 0;
        $usr_GDRP_DPO = 0;
        $usr_MailChimp = 0;
        $usr_MainDashboard = 0;
        $usr_SeePrivacyData = 0;
        $usr_EditRecords = 0;
        $usr_DeleteRecords = 0;
        $usr_ShowCart = 0;
        $usr_ShowMap = 0;
        $usr_EDrive = 0;
        $usr_MenuOptions = 0;
        $usr_ManageGroups = 0;
        $usr_Finance = 0;
        $usr_Notes = 0;
        $usr_Admin = 0;
        $usr_showMenuQuery = 0;
        $usr_CanSendEmail = 0;
        $usr_ExportCSV = 0;
        $usr_CreateDirectory = 0;
        $usr_ExportSundaySchoolPDF = 0;
        $usr_ExportSundaySchoolCSV = 0;
        $usr_EditSelf = 1;
        $usr_Canvasser = 0;

        $sUserName = '';
        $vNewUser = 'true';


        $people = PersonQuery::create()
            ->leftJoinUser()
            ->withColumn('User.PersonId', 'UserPersonId')
            ->orderByLastName()
            ->find();
    }
}

// Save Settings
if (isset($_POST['save']) && ($iPersonID > 0)) {
    $new_value = $_POST['new_value'];
    $new_permission = $_POST['new_permission'];
    $type = $_POST['type'];


    ksort($type);
    reset($type);
    foreach ($type as  $key => $value) {
        $id = $key;
        $current_type = $value;
        // Filter Input
        if ($current_type == 'text' || $current_type == 'textarea') {
            $value = InputUtils::LegacyFilterInput($new_value[$id]);
        } elseif ($current_type == 'number') {
            $value = InputUtils::LegacyFilterInput($new_value[$id], 'float');
        } elseif ($current_type == 'date') {
            // todo dates !!!! PL
            $value = InputUtils::LegacyFilterInput($new_value[$id], 'date');
        } elseif ($current_type == 'boolean') {
            if ($new_value[$id] != '1') {
                $value = '';
            } else {
                $value = '1';
            }
        } elseif ($current_type == 'choice') {
            $value = $new_value[$id];
        }

        if ($new_permission[$id] != 'TRUE') {
            $permission = 'FALSE';
        } else {
            $permission = 'TRUE';
        }

        // We can't update unless values already exist.
        $userConf = UserConfigQuery::create()->filterById($id)->findOneByPersonId($iPersonID);

        if (is_null($userConf)) { // If Row does not exist then insert default values.
            // Defaults will be replaced in the following Update
            $userDefault = UserConfigQuery::create()->filterById($id)->findOneByPersonId(0);

            if (!is_null($userDefault)) {
                $userConf = new UserConfig();

                $userConf->setPersonId($iPersonID);
                $userConf->setId($id);
                $userConf->setName($userDefault->getName());
                $userConf->setValue($value);
                $userConf->setType($current_type);
                $userConf->setChoicesId($userDefault->getChoicesId());
                $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                $userConf->setPermission($permission);
                $userConf->setCat($userDefault->getCat());

                $userConf->save();
            } else {
                echo '<br> Error on line ' . __LINE__ . ' of file ' . __FILE__;
                exit;
            }
        } else {

            $userConf->setValue($value);
            $userConf->setPermission($permission);
            $userConf->setType($current_type);

            $userConf->save();

        }
    }

    RedirectUtils::Redirect('v2/users');
    exit;
}

// Set the page title and include HTML header
$sPageTitle = _('User Editor');
require 'Include/Header.php';

$first_roleID = 0;
foreach ($userRoles as $userRole) {
    $first_roleID = $userRole->getId();
    break;
}

if ($usr_role_id == null) {
    $usr_role_id = $first_roleID;
}

?>

<div class="card">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _("Role management") ?></h3>
    </div>
    <div class="card-body">
        <a href="#" id="addRole" class="btn btn-app"><i class="fa  fa-plus"></i><?= _("Add Role") ?></a>
        <a href="#" id="manageRole" class="btn btn-app"><i class="fa fa-gear"></i><?= _("Manage Roles") ?></a>
        <div class="btn-group">
            <a class="btn btn-app changeRole" id="mainbuttonRole" data-id="<?= $first_roleID ?>"><i
                    class="fa fa-arrow-circle-o-down"></i><?= _("Add Role to Current User") ?></a>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu" role="menu" id="AllRoles">
                <?php
                foreach ($userRoles as $userRole) {
                    ?>
                    <a href="#" class="dropdown-item changeRole" data-id="<?= $userRole->getId() ?>"><i
                            class="fa fa-arrow-circle-o-down"></i><?= $userRole->getName() ?></a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div><!-- Default box -->

<form method="post" action="UserEditor.php">

    <input id="roleID" name="roleID" type="hidden" value="<?= $usr_role_id ?>">
    <input type="hidden" name="Action" value="<?= $sAction ?>">
    <input type="hidden" name="NewUser" value="<?= $vNewUser ?>">
    <input type="hidden" name="PersonID" value="<?= $iPersonID ?>">

    <?php
    // Are we adding?
    if ($bShowPersonSelect) {
        //Yes, so display the people drop-down
        ?>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <?= _('Person to Make User') ?>:
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <select name="PersonID" size="30" id="personSelect" class="form-control input-sm">
                            <?php
                            // Loop through all the people
                            foreach ($people as $member) {
                                if (is_null($member->getUserPersonId())) {
                                    ?>
                                    <option
                                        value="<?= $member->getId() ?>"<?= ($member->getId() == $iPersonID) ? ' selected' : '' ?>
                                        data-email="<?= $member->getEmail() ?>"><?= $member->getLastName() . ', ' . $member->getFirstName() ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-2">
                        <?= _('Login Name') ?>:
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <input class="form-control input-md" type="text" name="UserName" value="<?= $sUserName ?>"
                               class="form-control"
                               width="32" <?= (strtolower($sUserName) == "admin") ? "readonly" : "" ?>>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <?= _('Note: Changes will not take effect until next logon.') ?>
            </div>
            <table class="table table-hover data-person data-table1 no-footer dtr-inline" style="width:100%"
                   id="table1">
                <thead>
                <?php

                // Are we adding?
                if ($bShowPersonSelect) {
                    //Yes, so display the people drop-down
                    ?>
                    <th></th>
                    <th></th>
                    <?php
                } else { // No, just display the user name?>
                    <th><?= _('User') ?>:</th>
                    <th><?= $sUser ?></th>
                    <?php
                } ?>
                </thead>
                <tbody>
                <?php if ($sErrorText != '') {
                    ?>
                    <tr>
                        <td>
                            <span style="color:red;" id="PasswordError"><?= $sErrorText ?>)</span>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <?php
                } ?>

                <?php
                // Are we adding?
                if (!$bShowPersonSelect) {
                    //Yes, so display the people drop-down
                    ?>
                    <tr>
                        <td><?= _('Login Name') ?>:</td>
                        <td><input class="form-control input-md" type="text" name="UserName" value="<?= $sUserName ?>"
                                   class="form-control"
                                   width="32" <?= (strtolower($sUserName) == "admin") ? "readonly" : "" ?>></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td><?= _('Add Records') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="AddRecords"
                               value="1"<?= ($usr_AddRecords) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Edit Records') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="EditRecords"
                               value="1"<?= ($usr_EditRecords) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Delete Records') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="DeleteRecords"
                               value="1"<?= ($usr_DeleteRecords) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Show Cart') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="ShowCart"
                               value="1"<?= ($usr_ShowCart) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Show Map') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="ShowMap"
                               value="1"<?= ($usr_ShowMap) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('EDrive') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="EDrive"
                               value="1"<?= ($usr_EDrive) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Manage Properties and Classifications') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="MenuOptions"
                               value="1"<?= ($usr_MenuOptions) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Manage Groups and Roles') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="ManageGroups"
                               value="1"<?= ($usr_ManageGroups) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Manage Donations and Finance') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="Finance"
                               value="1"<?= ($usr_Finance) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('View, Add and Edit Notes') ?>:</td>
                    <td><input type="checkbox" class="global_settings" name="Notes"
                               value="1"<?= ($usr_Notes) ? ' checked' : '' ?>></td>
                </tr>

                <tr>
                    <td><?= _('Edit Self') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="EditSelf"
                               value="1"<?= ($usr_EditSelf) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('Edit own family only.') ?>)</span>
                    </td>
                </tr>
                <tr>
                    <td><?= _('Canvasser') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="Canvasser"
                               value="1"<?= ($usr_Canvasser) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('Canvass volunteer.') ?>)</span>
                    </td>
                </tr>
                <tr>
                    <td><?= _('Admin') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="Admin"
                               value="1"<?= ($usr_Admin) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('Grants all privileges.') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Query Menu') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="QueryMenu"
                               value="1"<?= ($usr_showMenuQuery) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('Allow to manage the query menu') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Can Send Email') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="CanSendEmail"
                               value="1"<?= ($usr_CanSendEmail) ? ' checked' : '' ?>>
                        &nbsp;<span
                            class="SmallText">(<?= _('Allow to use the mail function and button in the CRM') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('CSV Export') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="ExportCSV"
                               value="1"<?= ($usr_ExportCSV) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('User permission to export CSV files') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Create Directory') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="CreateDirectory"
                               value="1"<?= ($usr_CreateDirectory) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('User permission to create directories') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Sunday school PDF') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="ExportSundaySchoolPDF"
                               value="1"<?= ($usr_ExportSundaySchoolPDF) ? ' checked' : '' ?>>
                        &nbsp;<span
                            class="SmallText">(<?= _('User permission to export PDF files for the sunday school') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Sunday school CSV') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="ExportSundaySchoolCSV"
                               value="1"<?= ($usr_ExportSundaySchoolCSV) ? ' checked' : '' ?>>
                        &nbsp;<span
                            class="SmallText">(<?= _('User permission to export CSV files for the sunday school') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Main Dashboard') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="MainDashboard"
                               value="1"<?= ($usr_MainDashboard) ? ' checked' : '' ?>>
                        &nbsp;<span
                            class="SmallText">(<?= _('Main Dashboard and the birthdates in the calendar are visible.') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('See Privacy Data') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="SeePrivacyData"
                               value="1"<?= ($usr_SeePrivacyData) ? ' checked' : '' ?>>
                        &nbsp;<span
                            class="SmallText">(<?= _('Allow user to see member privacy data, e.g. Birth Year, Age.') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('MailChimp') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="MailChimp"
                               value="1"<?= ($usr_MailChimp) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('Allow a user to use MailChimp tool') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _("GRPD Data Protection Officer") ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="GdrpDpo"
                               value="1"<?= ($usr_GDRP_DPO) ? ' checked' : '' ?>>
                        &nbsp;<span class="SmallText">(<?= _('General Data Protection Regulation in UE') ?>)</span>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Pastoral Care') ?>:</td>
                    <td>
                        <input type="checkbox" class="global_settings" name="PastoralCare"
                               value="1"<?= ($usr_PastoralCare) ? ' checked' : '' ?>>
                    </td>
                </tr>

                </tbody>
            </table>
            <br>
            <div class="row">
                <div class="col-md-2">
                </div>
                <div class="col-md-6">
                    <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="save">&nbsp;
                    <input type="button" class="btn btn-default" name="Cancel" value="<?= _('Cancel') ?>"
                           onclick="javascript:document.location='v2/users';">
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    <!-- Default box -->
    <div class="card">
        <div class="card-body card-danger">
            <div
                class="alert alert-info"><?= _('Set Permission True to give this user the ability to change their current value.') ?>
            </div>
            <table class="table table-hover data-person data-table2 no-footer dtr-inline" style="width:100%">
                <thead>
                <tr>
                    <th><?= _('Permission') ?></h3></th>
                    <th><?= _('Variable name') ?></th>
                    <th><?= _('Current Value') ?></h3></th>
                    <th><?= _('Notes') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                //First get default settings, then overwrite with settings from this user

                // Get default settings
                $defaultConfigs = UserConfigQuery::create()->orderById()->findByPersonId(0);

                // List Default Settings
                foreach ($defaultConfigs as $defaultConfig) {
                    $userConfig = UserConfigQuery::create()->filterById($defaultConfig->getId())->findOneByPersonId($usr_per_ID);

                    if (is_null($userConfig)) {// when the user is created there isn't any settings: so we load the default one
                        $userConfig = $defaultConfig;
                    }

                    // Default Permissions
                    $sel1 = '';
                    $sel2 = '';

                    if ($userConfig->getPermission() == 'TRUE') {
                        $sel2 = 'SELECTED';
                        $sel1 = '';
                    } else {
                        $sel1 = 'SELECTED';
                        $sel2 = '';
                    }
                    ?>
                    <tr class="user_settings" data-name="<?= $userConfig->getName() ?>">
                        <td>
                            <select class="form-control input-sm" name="new_permission[<?= $userConfig->getId() ?>]">
                                <option value="FALSE" <?= $sel1 ?>><?= _('False') ?>
                                <option value="TRUE" <?= $sel2 ?>><?= _('True') ?>
                            </select>
                        </td>

                        <?php
                        // Variable Name & Type
                        ?>

                        <td>
                            <?= $userConfig->getName() ?>
                        </td>

                        <?php
                        // Current Value
                        if ($userConfig->getType() == 'text') {
                            ?>
                            <td>
                                <input class="form-control input-md" type="text" size="30" maxlength="255"
                                       name="new_value[<?= $userConfig->getId() ?>]"
                                       value="<?= htmlspecialchars($userConfig->getValue(), ENT_QUOTES) ?>">
                            </td>
                            <?php
                        } elseif ($userConfig->getType() == 'textarea') {
                            ?>
                            <td>
                      <textarea rows="4" cols="30" name="new_value[<?= $userConfig->getId() ?>]\">
                            <?= htmlspecialchars($userConfig->getValue(), ENT_QUOTES) ?>
                      </textarea>
                            </td>
                            <?php
                        } elseif ($userConfig->getType() == 'number' || $userConfig->getType() == 'date') {
                            // todo dates !!!! PL
                            ?>
                            <td>
                                <input class="form-control input-md" type="text" size="15"
                                       maxlength="15" name="new_value[<?= $userConfig->getId() ?>]\"
                                       value="<?= $userConfig->getValue() ?>">
                            </td>
                            <?php
                        } elseif ($userConfig->getType() == 'boolean') {
                            if ($userConfig->getValue()) {
                                $sel2 = 'SELECTED';
                                $sel1 = '';
                            } else {
                                $sel1 = 'SELECTED';
                                $sel2 = '';
                            }
                            ?>
                            <td>
                                <select class="form-control input-sm" name="new_value[<?= $userConfig->getId() ?>]">
                                    <option value="" <?= $sel1 ?>><?= _('False') ?>
                                    <option value="1" <?= $sel2 ?>><?= _('True') ?>
                                </select>
                            </td>
                            <?php
                        } elseif ($userConfig->getType() == 'choice') {
                            // we seach ever the default settings
                            $userChoices = UserConfigChoicesQuery::create()->findOneById(($defaultConfig->getChoicesId() == null) ? 0 : $defaultConfig->getChoicesId());

                            $choices = explode(",", $userChoices->getChoices());
                            ?>
                            <td>
                                <select class="form-control input-sm" name="new_value[<?= $userConfig->getId() ?>]">
                                    <?php
                                    foreach ($choices

                                    as $choice) {
                                    ?>
                                    <option
                                        value="<?= $choice ?>" <?= (($userConfig->getValue() == $choice) ? ' selected' : '') ?>> <?= $choice ?>
                                        <?php
                                        }
                                        ?>
                                </select>
                            </td>
                            <?php
                        }

                        // Notes
                        ?>
                        <td>
                            <input type="hidden" name="type[<?= $userConfig->getId() ?>]\"
                                   value="<?= $userConfig->getType() ?>">
                            <?= _($userConfig->getTooltip()) ?>
                        </td>
                    </tr>
                    <?php
                }

                // Cancel, Save Buttons
                ?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-2">
                </div>
                <div class="col-md-6">
                    <input type="submit" class="btn btn-primary" name="save"
                           value="<?= _('Save Settings') ?>">
                    <input type="submit" class="btn btn-default" name="cancel" value="<?= _('Cancel') ?>">
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->

</form>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/user/UserEditor.js"></script>

<?php require 'Include/Footer.php' ?>
