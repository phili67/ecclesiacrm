<?php
/*******************************************************************************
 *
 *  filename    : userseditor.php
 *  last change : 2023-05-09
 *  description : displays a list of all users
 *
 *  http://www.ecclesiacrm.com/
 *  Cpoyright 2023 Philippe Logel all tight reserved not MIT
 *
 ******************************************************************************/

use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\UserConfigChoicesQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;

use Propel\Runtime\ActiveQuery\Criteria;


require $sRootDocument . '/Include/Header.php';
?>

<div class="card special-card">
    <div class="card-header">
        <h3 class="card-title"><?= _("Role management") ?></h3>
    </div>
    <div class="card-body">
        <a href="#" id="addRole" class="btn btn-app"><i class="fa  fa-plus"></i><?= _("Add Role") ?></a>
        <a href="#" id="manageRole" class="btn btn-app"><i class="fas fa-cog"></i><?= _("Manage Roles") ?></a>
        <div class="btn-group">
            <a class="btn btn-app changeRole" id="mainbuttonRole" data-id="<?= $first_roleID ?>"><i
                    class="fas fa-arrow-circle-down"></i><?= _("Add Role to Current User") ?></a>
            <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu" role="menu" id="AllRoles">
                <?php
                foreach ($userRoles as $userRole) {
                    ?>
                    <a href="#" class="dropdown-item changeRole" data-id="<?= $userRole->getId() ?>"><i
                            class="fas fa-arrow-circle-down"></i><?= $userRole->getName() ?></a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div><!-- Default box -->

<form method="post" action="<?= $sRootPath ?>/v2/users/editor">

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
                        <select name="PersonID" size="30" id="personSelect" class="form-control form-control-sm">
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
                        <input class="form-control form-control-sm" type="text" name="UserName" value="<?= $sUserName ?>"
                               class= "form-control form-control-sm"
                               width="32" <?= (strtolower($sUserName) == "admin") ? "readonly" : "" ?>>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="card card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                    <?= _('Note: Changes will not take effect until next logon.') ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <table class="table table-hover data-person data-table1 no-footer dtr-title" style="width:100%"
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
                        <td><input class="form-control form-control-sm" type="text" name="UserName" value="<?= $sUserName ?>"
                                   class= "form-control form-control-sm"
                                   width="32" <?= (strtolower($sUserName) == "admin") ? "readonly" : "" ?>></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
                </div>
            </div>

            <div class="row">
                <div class="col-7 col-sm-9 preferences_pane">
                    <div class="tab-content" id="vert-tabs-right-tabContent">
                        <div class="tab-pane fade active show" id="vert-tabs-right-home" role="tabpanel" aria-labelledby="vert-tabs-right-home-tab">
                            <div class="card card-default">
                                <div class="card-header">
                                    <label class="card-title">
                                        <?= _("Global Permissions") ?>
                                    </label>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Management settings -->
                                            <div class="card card-default">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <?= _("Management") ?>
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Admin') ?>:<br/>
                                                            &nbsp;<span class="SmallText">(<?= _('Grants all privileges.') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="Admin"
                                                                   value="1"<?= ($usr_Admin) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Add Records') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="AddRecords"
                                                                   value="1"<?= ($usr_AddRecords) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Edit Records') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="EditRecords"
                                                                   value="1"<?= ($usr_EditRecords) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Delete Records') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="DeleteRecords"
                                                                   value="1"<?= ($usr_DeleteRecords) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Manage Properties and Classifications') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="MenuOptions"
                                                                   value="1"<?= ($usr_MenuOptions) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Manage Groups and Roles') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ManageGroups"
                                                                   value="1"<?= ($usr_ManageGroups) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Manage resource reservation schedules (room, computer, projectors)') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ManageCalendarResources"
                                                                   value="1"<?= ($usr_ManageCalendarResources) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('View, Add and Edit Notes') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="Notes"
                                                                   value="1"<?= ($usr_Notes) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                            <!-- end of Management settings -->

                                            <!-- user settings -->
                                            <div class="card card-default">
                                                <div class="card-header">
                                                    <h3 class="card-title"><?= _("User Account Permissions") ?></h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Main Dashboard') ?>:<br/>
                                                            <span
                                                                class="SmallText">(<?= _('Main Dashboard and the birthdates in the calendar are visible.') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="MainDashboard"
                                                                   value="1"<?= ($usr_MainDashboard) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Edit Self') ?>:<br/>
                                                            <span class="SmallText">(<?= _('Edit own family only.') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="EditSelf"
                                                                   value="1"<?= ($usr_EditSelf) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('EDrive') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="EDrive"
                                                                   value="1"<?= ($usr_EDrive) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Right to edit html code') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="HtmlSourceEditor"
                                                                   value="1"<?= ($usr_HtmlSourceEditor) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Can Send Email') ?>:<br/>
                                                            <span
                                                                class="SmallText">(<?= _('Allow to use the mail function and button in the CRM') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="CanSendEmail"
                                                                   value="1"<?= ($usr_CanSendEmail) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end of user settings -->

                                            <!-- View settings -->
                                            <div class="card card-default">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <?= _("Permissions To View") ?>
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Show Cart') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ShowCart"
                                                                   value="1"<?= ($usr_ShowCart) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Show Map') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ShowMap"
                                                                   value="1"<?= ($usr_ShowMap) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('See Privacy Data') ?>:<br/>
                                                            <span
                                                                class="SmallText">(<?= _('Allow user to see member privacy data, e.g. Birth Year, Age.') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="SeePrivacyData"
                                                                   value="1"<?= ($usr_SeePrivacyData) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <!-- View settings -->
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Export settings -->
                                            <div class="card card-default">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <?= _("Export Permissions") ?>
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('CSV Export') ?>:<br/>
                                                            &nbsp;<span
                                                                class="SmallText">(<?= _('User permission to export CSV files') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ExportCSV"
                                                                   value="1"<?= ($usr_ExportCSV) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Create Directory') ?>:<br/>
                                                            <span class="SmallText">(<?= _('User permission to create directories') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="CreateDirectory"
                                                                   value="1"<?= ($usr_CreateDirectory) ? ' checked' : '' ?>>

                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Sunday school PDF') ?>:<br/>
                                                            <span
                                                                class="SmallText">(<?= _('User permission to export PDF files for the sunday school') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ExportSundaySchoolPDF"
                                                                   value="1"<?= ($usr_ExportSundaySchoolPDF) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Sunday school CSV') ?>:<br/>
                                                            <span
                                                                class="SmallText">(<?= _('User permission to export CSV files for the sunday school') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="ExportSundaySchoolCSV"
                                                                   value="1"<?= ($usr_ExportSundaySchoolCSV) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                            <!-- Export settings -->

                                            <!-- Special settings -->
                                            <div class="card card-default">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <?= _("Special Permissions") ?>
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Manage Donations and Finance') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="Finance"
                                                                   value="1"<?= ($usr_Finance) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Canvasser') ?>:<br/>
                                                            <span class="SmallText">(<?= _('Canvass volunteer.') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="Canvasser"
                                                                   value="1"<?= ($usr_Canvasser) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Query Menu') ?>:<br/>
                                                            <span class="SmallText">(<?= _('Allow to manage the query menu') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="QueryMenu"
                                                                   value="1"<?= ($usr_showMenuQuery) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('MailChimp') ?>:<br/>
                                                            <span class="SmallText">(<?= _('Allow a user to use MailChimp tool') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="MailChimp"
                                                                   value="1"<?= ($usr_MailChimp) ? ' checked' : '' ?>>
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _("GRPD Data Protection Officer") ?>:<br/>
                                                            <span
                                                                class="SmallText">(<?= _('General Data Protection Regulation in UE') ?>)</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="GdrpDpo"
                                                                   value="1"<?= ($usr_GDRP_DPO) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-10">&bullet;
                                                            <?= _('Pastoral Care') ?>:
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="checkbox" class="global_settings" name="PastoralCare"
                                                                   value="1"<?= ($usr_PastoralCare) ? ' checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Special settings -->


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-right-profile" role="tabpanel" aria-labelledby="vert-tabs-right-profile-tab">
                            <!-- Default box -->
                            <div class="card card-default">
                                <div class="card-header">
                                    <label class="card-title">
                                        <?= _("Modifiable Permissions") ?>
                                    </label>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <?= _('Set Permission True to give this user the ability to change their current value.') ?>
                                    </div>
                                    <table class="table table-hover data-person data-table2 no-footer dtr-inline"
                                           style="width:100%">
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
                                                    <select class="form-control form-control-sm"
                                                            name="new_permission[<?= $userConfig->getId() ?>]">
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
                                                        <input class="form-control form-control-sm" type="text" size="30"
                                                               maxlength="255"
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
                                                        <input class="form-control form-control-sm" type="text" size="15"
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
                                                        <select class="form-control form-control-sm"
                                                                name="new_value[<?= $userConfig->getId() ?>]">
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
                                                        <select class="form-control form-control-sm"
                                                                name="new_value[<?= $userConfig->getId() ?>]">
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
                                </div>
                                <!-- /.box-body -->
                            </div>
                            <!-- /.box -->
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-right-messages" role="tabpanel" aria-labelledby="vert-tabs-right-messages-tab">
                            <!-- Global Plugin settings -->
                            <div class="card">
                                <div class="card-header">
                                    <label class="card-title">
                                        <?= _("Plugin permissions") ?>
                                    </label>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $plugins = PluginQuery::create()
                                        ->filterByCategory('Dashboard', Criteria::NOT_EQUAL)
                                        ->find();
                                    foreach ($plugins as $plugin) {
                                        $role = PluginUserRoleQuery::create()->filterByUserId($iPersonID)->findOneByPluginId($plugin->getId());

                                        $role_sel = 'none';
                                        if (!is_null($role)) {
                                            $role_sel = $role->getRole();
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-md-7">&bullet;
                                                <?= $plugin->getName() ?>:
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-control form-control-sm"
                                                        name="new_plugin[<?= $plugin->getId() ?>]">
                                                    <option value="none" <?= ($role_sel == 'none')?'SELECTED':'' ?>><?= _('No') ?>
                                                    <option value="user" <?= ($role_sel == 'user')?'SELECTED':'' ?>><?= _('User') ?>
                                                    <option value="admin" <?= ($role_sel == 'admin')?'SELECTED':'' ?>><?= _('Admin') ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <!-- Global Plugin settings -->

                            <!-- Dashboard Plugin settings -->
                            <div class="card">
                                <div class="card-header">
                                    <label class="card-title">
                                        <?= _("Visibilities of the dashboard plugins") ?>
                                    </label>
                                </div>
                                <div class="card-body">
                                <div class="row">
                                            <div class="col-md-4">
                                                <label><?= _("Name") ?></label>
                                            </div>
                                            <div class="col-md-2">
                                                <label><?= _("Security") ?></label>
                                            </div>
                                            <div class="col-md-2">
                                                <label><?= _("Status") ?></label>
                                            </div>
                                            <div class="col-md-2">
                                               <label><?= _("Position") ?></label>
                                            </div>
                                            <div class="col-md-2">
                                                <label><?= _("Role") ?></label>
                                            </div>
                                        </div>
                                    <?php
                                    $plugins = PluginQuery::create()
                                        ->filterByCategory('Dashboard', Criteria::EQUAL)
                                        ->orderByName()
                                        ->find();
                                    foreach ($plugins as $plugin) {
                                        $role = PluginUserRoleQuery::create()->filterByUserId($iPersonID)->findOneByPluginId($plugin->getId());

                                        $visible = 0;
                                        $place = 'top';
                                        if (!is_null($role)) {
                                            $visible = $role->getDashboardVisible();
                                            $place = $role->getDashboardOrientation();
                                        }

                                        // on this special case there only two possibilities : user or admin
                                        $role_sel = 'user';
                                        if ( !is_null($role) ) {
                                            $role_sel = $role->getRole();
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-md-4">&bullet;
                                                <?= $plugin->getName() ?>:
                                            </div>
                                            <div class="col-md-2">
                                                <?= $plugin->getPluginSecurityName() ?>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control form-control-sm"
                                                        name="new_plugin[<?= $plugin->getId() ?>]">
                                                    <option value="0" <?= ($visible == false)?'SELECTED':'' ?>><?= _('No') ?>
                                                    <option value="1" <?= ($visible == true)?'SELECTED':'' ?>><?= _('Yes') ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control form-control-sm"
                                                        name="new_plugin_place[<?= $plugin->getId() ?>]">
                                                    <option value="top" <?= ($place == 'top')?'SELECTED':'' ?>><?= _('Top') ?>
                                                    <option value="left" <?= ($place == 'left')?'SELECTED':'' ?>><?= _('Left') ?>
                                                    <option value="right" <?= ($place == 'right')?'SELECTED':'' ?>><?= _('Right') ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2" style="display: <?= $plugin->getUserRoleDashboardAvailability()?'block':'none'?>">
                                                <select class="form-control form-control-sm"
                                                        name="new_plugin_role[<?= $plugin->getId() ?>]">
                                                    <option value="user" <?= ($role_sel == 'user' or $role_sel == 'none')?'SELECTED':'' ?>><?= _('User') ?>
                                                    <option value="admin" <?= ($role_sel == 'admin')?'SELECTED':'' ?>><?= _('Admin') ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <!-- Dashboard Plugin settings -->
                        </div>
                    </div>
                </div>
                <div class="col-5 col-sm-3">
                    <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-right-home-tab" data-toggle="pill" href="#vert-tabs-right-home" role="tab" aria-controls="vert-tabs-right-home" aria-selected="true"><?= _("Global Permissions") ?></a>
                        <a class="nav-link" id="vert-tabs-right-profile-tab" data-toggle="pill" href="#vert-tabs-right-profile" role="tab" aria-controls="vert-tabs-right-profile" aria-selected="false"><?= _("Modifiable Permissions") ?></a>
                        <a class="nav-link" id="vert-tabs-right-messages-tab" data-toggle="pill" href="#vert-tabs-right-messages" role="tab" aria-controls="vert-tabs-right-messages" aria-selected="false"><?= _("Plugin permissions") ?></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">

                </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="card-footer">
            <div class="row float-left">
                <div class="col-md-2">
                </div>
                <div class="col-md-8">
                    <input type="submit" class="btn btn-primary" name="save"
                           value="<?= _('Save Settings') ?>">
                </div>
                <div class="col-md-2">
                    <input type="submit" class="btn btn-default" name="cancel" value="<?= _('Cancel') ?>">
                </div>
            </div>
        </div>
    </div>
    <!-- /.box -->
</form>

<script src="<?= $sRootPath ?>/skin/js/user/UserEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
