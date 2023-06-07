<?php

/*******************************************************************************
 *
 *  filename    : groupreport.php
 *  last change : 2023-05-29
 *  description : report a group
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupPropMasterQuery;

use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<script src="<?= $sRootPath ?>/skin/js/group/GroupRoles.js"></script>

<?php
  if ($iGroupID == -1) {
    $currentUserBelongToGroup = SessionUser::getUser()->belongsToGroup($iGroupID);

    if ($currentUserBelongToGroup == 0) {
        return $response->withStatus(302)->withHeader('Location', $sRootPath . '/v2/dashboard');
    }
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
            <div class="card-header border-1">
                <h3 class="card-title"><?= _('Select the group you would like to report') ?>:</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $sRootPath ?>/v2/group/reports">
                    <div class="row">
                        <div class="col-xs-6">
                            <label for="GroupID"><?= _('Select Group') ?>:</label>
                            <select id="GroupID" class="form-control form-control-sm" name="GroupID" onChange="UpdateRoles();">
                                // Create the group select drop-down
                                <option value="-1"><?= _('None') ?></option>
                              <?php
                                foreach ($groups as $group) {
                              ?>
                                <option value="<?= $group->getId()?>"><?= $group->getName() ?></option>
                              <?php
                                }
                              ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <label for=""><?= _('Select Role') ?>:</label>
                            <select name="GroupRole" class="form-control form-control-sm" id="GroupRole">
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
                            <input type="button" class="btn btn-default" name="Cancel" value="<?= _('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/ReportList.php';">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        </div>
    </div>
    <?php
} else {
    $groupName = GroupQuery::Create()->findOneById($iGroupID)->getName();

    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-1">
                    <h3 class="card-title"><?= _('Select which information you want to include') ?></h3>
                </div>
                <div class="card-body">

                    <form method="POST" action="<?= $sRootPath ?>/Reports/GroupReport.php">
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
                        $propFields = GroupPropMasterQuery::Create()->orderByPropId()->findByGroupId($iGroupID);
                      ?>

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
                                      if ($propFields->count() > 0) {
                                        foreach ($propFields as $propField) {
                                    ?>
                                            <input type="checkbox" Name="<?= $propField->getField() ?>enable" value="1"><?= $propField->getName() ?><br>
                                    <?php
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
                            <input type="button" class="btn btn-default" name="Cancel" value="<?= _('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/v2/group/<?= $iGroupID ?>/view';">
                        </p>
                    </form>

                </div>
            </div>
        </div>
    </div>

  <?php
    }
  ?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




