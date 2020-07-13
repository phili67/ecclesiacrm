<?php
/*******************************************************************************
 *
 *  filename    : DirectoryReports.php
 *  last change : 2003-09-03
 *  description : form to invoke directory report
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
 *  Copyright 2004-2012 Michael Wilt
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

// Check for Create Directory user permission.
if (!SessionUser::getUser()->isCreateDirectoryEnabled()) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

// Set the page title and include HTML header
$sPageTitle = _('Directory reports');
require 'Include/Header.php';

?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa fa-filter"></i> <?= _('Filters') ?></h3>
    </div>
    <div class="card-body">
        <form method="POST" action="Reports/DirectoryReport.php">

            <?php

            // Get classifications for the selects
            $ormClassifications = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById(1);

            //Get Family Roles for the drop-down
            $ormFamilyRoles = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById(2);

            // Get Field Security List Matrix
            $ormSecurityGrps = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById(5);

            foreach ($ormSecurityGrps as $ormSecurityGrp) {
                $aSecurityType[$ormSecurityGrp->getOptionId()] = $ormSecurityGrp->getOptionName();
            }

            // Get all the Groups
            $ormGroups = GroupQuery::Create()->orderByName()->find();

            // Get the list of custom person fields
            $ormCustomFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();
            $numCustomFields = $ormCustomFields->count();


            $aDefaultClasses = explode(',', SystemConfig::getValue('sDirClassifications'));
            $aDirRoleHead = explode(',', SystemConfig::getValue('sDirRoleHead'));
            $aDirRoleSpouse = explode(',', SystemConfig::getValue('sDirRoleSpouse'));
            $aDirRoleChild = explode(',', SystemConfig::getValue('sDirRoleChild'));

            ?>
            <?php
            if (!array_key_exists('cartdir', $_GET)) {
                ?>
                <div class="row">
                    <div class="col-sm-4">
                        <b><?= _('Exclude Inactive Families') ?></b>
                    </div>
                    <div class="col-sm-5">
                        <input type="checkbox" Name="bExcludeInactive" value="1" checked>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-4">
                        <b><?= _('Select classifications to include') ?> </b>
                    </div>
                    <div class="col-sm-5">
                        <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                        <select name="sDirClassifications[]" size="5" multiple class="form-control">
                            <option value="0"><?= _("Unassigned") ?></option>
                            <?php
                            foreach ($ormClassifications as $rsClassification) {
                                ?>
                                <option
                                    value="<?= $rsClassification->getOptionId() ?>" <?= (in_array($rsClassification->getOptionId(), $aDefaultClasses)) ? ' selected' : '' ?>><?= _($rsClassification->getOptionName()) ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-4">
                        <b><?= _('Group Membership') ?>:</b>
                    </div>
                    <div class="col-sm-5">
                        <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                        <select name="GroupID[]" size="5" multiple class="form-control">
                            <?php
                            foreach ($ormGroups as $group) {
                                ?>
                                <option value="<?= $group->getId() ?>"> <?= $group->getName() ?></option>
                                <?php
                            }
                            ?>
                        </select>

                    </div>
                </div>

                <?php
            }
            ?>

            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Which role is the head of household?') ?></b>
                </div>
                <div class="col-sm-5">
                    <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                    <select name="sDirRoleHead[]" size="5" multiple class="form-control">
                        <?php
                        foreach ($ormFamilyRoles as $ormFamilyRole) {
                            ?>
                            <option
                                value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleHead)) ? ' selected' : '' ?>> <?= _($ormFamilyRole->getOptionName()) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Which role is the spouse?') ?></b>
                </div>
                <div class="col-sm-5">
                    <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                    <select name="sDirRoleSpouse[]" size="5" multiple class="form-control">
                        <?php
                        foreach ($ormFamilyRoles as $ormFamilyRole) {
                            ?>
                            <option
                                value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleSpouse)) ? ' selected' : '' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Which role is a child?') ?></b>
                </div>
                <div class="col-sm-5">
                    <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                    <select name="sDirRoleChild[]" size="5" multiple class="form-control">
                        <?php
                        foreach ($ormFamilyRoles as $ormFamilyRole) {
                            ?>
                            <option
                                value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleChild)) ? ' selected' : '' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Information to Include') ?>:</b>
                </div>
                <div class="col-sm-5">
                    <input type="checkbox" Name="bDirAddress" value="1" checked> <?= _('Address') ?><br>
                    <input type="checkbox" Name="bDirWedding" value="1" checked> <?= _('Wedding Date') ?><br>
                    <input type="checkbox" Name="bDirBirthday" value="1" checked> <?= _('Birthday') ?><br>

                    <input type="checkbox" Name="bDirFamilyPhone" value="1" checked> <?= _('Family Home Phone') ?><br>
                    <input type="checkbox" Name="bDirFamilyWork" value="1" checked> <?= _('Family Work Phone') ?><br>
                    <input type="checkbox" Name="bDirFamilyCell" value="1" checked> <?= _('Family Cell Phone') ?><br>
                    <input type="checkbox" Name="bDirFamilyEmail" value="1" checked> <?= _('Family Email') ?><br>

                    <input type="checkbox" Name="bDirPersonalPhone" value="1" checked> <?= _('Personal Home Phone') ?>
                    <br>
                    <input type="checkbox" Name="bDirPersonalWork" value="1" checked> <?= _('Personal Work Phone') ?>
                    <br>
                    <input type="checkbox" Name="bDirPersonalCell" value="1" checked> <?= _('Personal Cell Phone') ?>
                    <br>
                    <input type="checkbox" Name="bDirPersonalEmail" value="1" checked> <?= _('Personal Email') ?><br>
                    <input type="checkbox" Name="bDirPersonalWorkEmail" value="1"
                           checked> <?= _('Personal Work/Other Email') ?><br>
                    <input type="checkbox" Name="bDirPhoto" value="1" checked> <?= _('Photos') ?><br>
                    <?php
                    if ($numCustomFields > 0) {
                        foreach ($ormCustomFields as $ormCustomField) {
                            if (($aSecurityType[$ormCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormCustomField->getCustomFieldSec()]])) {
                                ?>
                                <input type="checkbox" Name="bCustom<?= $ormCustomField->getCustomOrder() ?>" value="1"
                                       checked> <?= $ormCustomField->getCustomName() ?><br>
                                <?php
                            }
                        }
                    }
                    ?>

                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Number of Columns') ?>:</b>
                </div>
                <div class="col-sm-5">
                    <input type="radio" Name="NumCols" value=1>1 <?= _('col') ?><br>
                    <input type="radio" Name="NumCols" value=2 checked>2 <?= _('cols') ?><br>
                    <input type="radio" Name="NumCols" value=3>3 <?= _('cols') ?><br>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Paper Size') ?>:</b>
                </div>
                <div class="col-sm-5">
                    <input type="radio" name="PageSize" value="letter" checked>Letter (8.5x11)<br>
                    <input type="radio" name="PageSize" value="legal">Legal (8.5x14)<br>
                    <input type="radio" name="PageSize" value="a4">A4
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Font Size') ?>:</b>
                </div>
                <div class="col-sm-5">
                    <table>
                        <tr>
                            <td><input type="radio" Name="FSize" value=6>6<br>
                                <input type="radio" Name="FSize" value=8>8<br>
                                <input type="radio" Name="FSize" value=10 checked>10<br>

                            <td><input type="radio" Name="FSize" value=12>12<br>
                                <input type="radio" Name="FSize" value=14>14<br>
                                <input type="radio" Name="FSize" value=16>16<br>
                        </tr>
                    </table>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-4">
                    <b><?= _('Title page') ?>:</b>
                </div>
                <div class="col-sm-5">
                    <table>
                        <tr>
                            <td><?= _('Use Title Page') ?>
                            <td><input type="checkbox" Name="bDirUseTitlePage" value="1">
                        </tr>
                        <tr>
                            <td><?= _('Church Name') ?>
                            <td><input type="text" Name="sChurchName"
                                       value="<?= SystemConfig::getValue('sChurchName') ?>"
                                       class="form-control">
                        </tr>
                        <tr>
                            <td><?= _('Address') ?>
                            <td><input type="text" Name="sChurchAddress"
                                       value="<?= SystemConfig::getValue('sChurchAddress') ?>" class="form-control">
                        </tr>
                        <tr>
                            <td><?= _('City') ?>
                            <td><input type="text" Name="sChurchCity"
                                       value="<?= SystemConfig::getValue('sChurchCity') ?>"
                                       class="form-control">
                        </tr>
                        <tr>
                            <td><?= _('State') ?>
                            <td><input type="text" Name="sChurchState"
                                       value="<?= SystemConfig::getValue('sChurchState') ?>"
                                       class="form-control">
                        </tr>
                        <tr>
                            <td><?= _('Zip') ?>
                            <td><input type="text" Name="sChurchZip" value="<?= SystemConfig::getValue('sChurchZip') ?>"
                                       class="form-control">
                        </tr>
                        <tr>
                            <td><?= _('Phone') ?>
                            <td><input type="text" Name="sChurchPhone"
                                       value="<?= SystemConfig::getValue('sChurchPhone') ?>"
                                       class="form-control"><br>
                        </tr>
                        <tr>
                            <td><?= _('Disclaimer') ?>
                            <td><textarea Name="sDirectoryDisclaimer" cols="35" class="form-control"
                                          rows="4"><?= SystemConfig::getValue('sDirectoryDisclaimer1') . ' ' . SystemConfig::getValue('sDirectoryDisclaimer2') ?></textarea>
                        </tr>

                    </table>
                </div>
            </div>


            <?php
            if (array_key_exists('cartdir', $_GET)) {
                ?>
                <input type="hidden" name="cartdir" value="M">
                <?php
            }
            ?>
    </div>
    <div class="card-footer">
        <p align="center">
            <BR>
            <input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Create Directory') ?>">
            <input type="button" class="btn btn-default" name="Cancel" <?= 'value="' . _('Cancel') . '"' ?>
                   onclick="javascript:document.location='Menu.php';">
        </p>
    </div>
    </form>
</div>

<?php require 'Include/Footer.php' ?>
