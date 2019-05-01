<?php
/*******************************************************************************
 *
 *  filename    : GroupReports.php
 *  last change : 2003-09-03
 *  description : Detailed reports on group members
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Federico Nebiolo, Chris Gebhardt
 *  Copyright 2004-2012 Michael Wilt
  *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


// Get all the groups
$sSQL = 'SELECT * FROM group_grp ORDER BY grp_Name';
$rsGroups = RunQuery($sSQL);

$groupName = "";

if (isset($_POST['GroupID'])) {
    $iGroupID = InputUtils::LegacyFilterInput($_POST['GroupID'], 'int'); 
    $groupName = " : ".GroupQuery::Create()->findOneById($_POST['GroupID'])->getName();
}


// Set the page title and include HTML header
$sPageTitle = _('Group reports').$groupName;
require 'Include/Header.php';
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/group/GroupRoles.js"></script>

<?php 
  if (!isset($_POST['GroupID'])) {
    $currentUserBelongToGroup = SessionUser::getUser()->belongsToGroup($iGroupID);
    
    if ($currentUserBelongToGroup == 0) {
        RedirectUtils::Redirect('Menu.php');
    }
?>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= _('Select the group you would like to report') ?>:</h3>
            </div>
            <div class="box-body">
                <form method="POST" action="<?= SystemURLs::getRootPath() ?>/GroupReports.php">
                    <div class="row">
                        <div class="col-xs-6">
                            <label for="GroupID"><?= _('Select Group') ?>:</label>
                            <select id="GroupID" class="form-control input-sm" name="GroupID" onChange="UpdateRoles();">
                                <?php
                                // Create the group select drop-down
                                echo '<option value="0">'._('None').'</option>';
    while ($aRow = mysqli_fetch_array($rsGroups)) {
        extract($aRow);
        echo '<option value="'.$grp_ID.'">'.$grp_Name.'</option>';
    } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <label for=""><?= _('Select Role') ?>:</label>
                            <select name="GroupRole" class="form-control input-sm" id="GroupRole">
                                <option><?= _('No Role Selected') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <label for="OnlyCart"><?= _('Only cart persons?') ?>:</label>
                            <input type="checkbox" Name="OnlyCart" value="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <label for="ReportModel"><?= _('Report Model') ?>:</label>
                            <input type="radio" Name="ReportModel" value="1" checked><?= _('Report for group and role selected') ?>
                            <input type="radio" Name="ReportModel" value="2"><?= _('Report for any role in group selected') ?>
                            <?php
                            //<input type="radio" Name="ReportModel" value="3"><?= _("Report any group and role")
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Next') ?>">
                            <input type="button" class="btn btn-default" name="Cancel" value="<?= _('Cancel') ?>" onclick="javascript:document.location = 'ReportList.php';">

                        </div>
                    </div>
                </form>
            </div>
        </div>
    
        </div>
    </div>
    <?php
} else {
    $iGroupID = InputUtils::LegacyFilterInput($_POST['GroupID'], 'int'); 
    $groupName = GroupQuery::Create()->findOneById($_POST['GroupID'])->getName();
    
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= _('Select which information you want to include') ?></h3>
                </div>
                <div class="box-body">

                    <form method="POST" action="Reports/GroupReport.php">
                        <input type="hidden" Name="GroupID" <?= 'value="'.$iGroupID.'"' ?>>
                        <input type="hidden" Name="GroupRole" <?php
                        if (array_key_exists('GroupRole', $_POST)) {
                            echo 'value="'.$_POST['GroupRole'].'"';
                        } ?>>
                        <input type="hidden" Name="OnlyCart" <?php
                        if (array_key_exists('OnlyCart', $_POST)) {
                            echo 'value="'.$_POST['OnlyCart'].'"';
                        } ?>>
                        <input type="hidden" Name="ReportModel" <?= 'value="'.$_POST['ReportModel'].'"' ?>>

                        <?php
                        $sSQL = 'SELECT prop_Field, prop_Name FROM groupprop_master WHERE grp_ID = '.$iGroupID.' ORDER BY prop_ID';
                                $rsPropFields = RunQuery($sSQL); ?>

                        <table align="center">
                            <tr>
                                <td class="LabelColumn" valign="top"><?= _('Standard Info') ?></td>
                                <td valign="top">&nbsp;:&nbsp;</td>
                                <td class="TextColumn">
                                    <input type="checkbox" Name="AddressEnable" value="1"> <?= _('Address') ?> <br>
                                    <input type="checkbox" Name="HomePhoneEnable" value="1"> <?= _('Home Phone') ?> <br>
                                    <input type="checkbox" Name="WorkPhoneEnable" value="1"> <?= _('Work Phone') ?> <br>
                                    <input type="checkbox" Name="CellPhoneEnable" value="1"> <?= _('Cell Phone') ?> <br>
                                    <input type="checkbox" Name="EmailEnable" value="1"> <?= _('Email') ?> <br>
                                    <input type="checkbox" Name="OtherEmailEnable" value="1"> <?= _('Other Email') ?> <br>
                                    <input type="checkbox" Name="GroupRoleEnable" value="1"> <?= _('GroupRole') ?> <br>
                                </td>
                            </tr>
                            <tr>
                                <td class="LabelColumn" valign="top"><?= _('Group-Specific Property Fields') ?></td>
                                <td valign="top">&nbsp;:&nbsp;</td>
                                <td class="TextColumn">
                                    <?php
                                    if (mysqli_num_rows($rsPropFields) > 0) {
                                        while ($aRow = mysqli_fetch_array($rsPropFields)) {
                                            extract($aRow);
                                            echo '<input type="checkbox" Name="'.$prop_Field.'enable" value="1">'.$prop_Name.'<br>';
                                        }
                                    } else {
                                        echo _('None');
                                    } ?>
                                </td>
                            </tr>
                        </table>

                        <p align="center">
                            <BR>
                            <input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Create Report') ?>">
                            <input type="button" class="btn btn-default" name="Cancel" value="<?= _('Cancel') ?>" onclick="javascript:document.location = '<?= SystemURLs::getRootPath() ?>/GroupView.php?GroupID=<?= $iGroupID ?>';">
                        </p>
                    </form>

                </div>
            </div>
        </div>
    </div>

            <?php
                            } ?>

<?php require 'Include/Footer.php' ?>
