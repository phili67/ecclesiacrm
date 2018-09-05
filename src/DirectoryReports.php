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


// Check for Create Directory user permission.
if (!$bCreateDirectory) {
    Redirect('Menu.php');
    exit;
}

// Set the page title and include HTML header
$sPageTitle = _('Directory reports');
require 'Include/Header.php';

?>
<div class="box box-body">
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

$sSQL = 'SELECT person_custom_master.* FROM person_custom_master ORDER BY custom_Order';
$rsCustomFields = RunQuery($sSQL);
$numCustomFields = mysqli_num_rows($rsCustomFields);


$aDefaultClasses = explode(',', SystemConfig::getValue('sDirClassifications'));
$aDirRoleHead = explode(',', SystemConfig::getValue('sDirRoleHead'));
$aDirRoleSpouse = explode(',', SystemConfig::getValue('sDirRoleSpouse'));
$aDirRoleChild = explode(',', SystemConfig::getValue('sDirRoleChild'));

?>
<div class="table-responsive">
<table class="table" align="center" class="table">
<?php if (!array_key_exists('cartdir', $_GET)) {
    ?>
    <tr>
        <td class="LabelColumn"><?= _('Exclude Inactive Families') ?></td>
        <td><input type="checkbox" Name="bExcludeInactive" value="1" checked></td>
    </tr>
    <tr>
        <td class="LabelColumn"><?= _('Select classifications to include') ?></td>
        <td class="TextColumn">
            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirClassifications[]" size="5" multiple>
            <option value="0"><?= _("Unassigned") ?></option>
            <?php
               foreach ($ormClassifications as $rsClassification) {
            ?>
                  <option value="<?= $rsClassification->getOptionId()?>" <?= (in_array($rsClassification->getOptionId(), $aDefaultClasses))?' selected':''?>><?= _($rsClassification->getOptionName()) ?></option>
            <?php
               }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="LabelColumn"><?= _('Group Membership') ?>:</td>
        <td class="TextColumn">
            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
            <select name="GroupID[]" size="5" multiple>
              <?php
                foreach ($ormGroups as $group) {
              ?>
                    <option value="<?= $group->getId() ?>"> <?= $group->getName() ?></option>
              <?php
                }
              ?>
            </select>
        </td>
    </tr>

<?php
}
?>

    <tr>
        <td class="LabelColumn"><?= _('Which role is the head of household?') ?></td>
        <td class="TextColumn">
            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirRoleHead[]" size="5" multiple>
            <?php
                foreach ($ormFamilyRoles as $ormFamilyRole) {
            ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleHead))?' selected':'' ?>> <?= _($ormFamilyRole->getOptionName()) ?></option>
            <?php
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="LabelColumn"><?= _('Which role is the spouse?') ?></td>
        <td class="TextColumn">
            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirRoleSpouse[]" size="5" multiple>
            <?php
                foreach ($ormFamilyRoles as $ormFamilyRole) {
            ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleSpouse))?' selected':'' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
            <?php
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="LabelColumn"><?= _('Which role is a child?') ?></td>
        <td class="TextColumn">
            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirRoleChild[]" size="5" multiple>
            <?php
                foreach ($ormFamilyRoles as $ormFamilyRole) {
            ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleChild))?' selected':'' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
            <?php
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="LabelColumn"><?= _('Information to Include') ?>:</td>
        <td class="TextColumn">
            <input type="checkbox" Name="bDirAddress" value="1" checked> <?= _('Address') ?><br>
            <input type="checkbox" Name="bDirWedding" value="1" checked> <?= _('Wedding Date') ?><br>
            <input type="checkbox" Name="bDirBirthday" value="1" checked> <?= _('Birthday') ?><br>

            <input type="checkbox" Name="bDirFamilyPhone" value="1" checked> <?= _('Family Home Phone') ?><br>
            <input type="checkbox" Name="bDirFamilyWork" value="1" checked> <?= _('Family Work Phone') ?><br>
            <input type="checkbox" Name="bDirFamilyCell" value="1" checked> <?= _('Family Cell Phone') ?><br>
            <input type="checkbox" Name="bDirFamilyEmail" value="1" checked> <?= _('Family Email') ?><br>

            <input type="checkbox" Name="bDirPersonalPhone" value="1" checked> <?= _('Personal Home Phone') ?><br>
            <input type="checkbox" Name="bDirPersonalWork" value="1" checked> <?= _('Personal Work Phone') ?><br>
            <input type="checkbox" Name="bDirPersonalCell" value="1" checked> <?= _('Personal Cell Phone') ?><br>
            <input type="checkbox" Name="bDirPersonalEmail" value="1" checked> <?= _('Personal Email') ?><br>
            <input type="checkbox" Name="bDirPersonalWorkEmail" value="1" checked> <?= _('Personal Work/Other Email') ?><br>
            <input type="checkbox" Name="bDirPhoto" value="1" checked> <?= _('Photos') ?><br>            
        <?php
         if ($numCustomFields > 0) {
             foreach ($ormCustomFields as $ormCustomField) {
                 if (($aSecurityType[$ormCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormCustomField->getCustomFieldSec()]])) {
        ?>
                  <input type="checkbox" Name="bCustom<?= $ormCustomField->getCustomOrder() ?>" value="1" checked> <?= $ormCustomField->getCustomName() ?><br>
        <?php
                 }
             }
         }
        ?>
        </td>
    </tr>
  <tr>
   <td class="LabelColumn"><?= _('Number of Columns') ?>:</td>
    <td class="TextColumn">
        <input type="radio" Name="NumCols" value=1>1 <?= _('col') ?><br>
        <input type="radio" Name="NumCols" value=2 checked>2 <?= _('cols') ?><br>
        <input type="radio" Name="NumCols" value=3>3 <?= _('cols') ?><br>
  </td>
  </tr>
  <tr>
   <td class="LabelColumn"><?= _('Paper Size') ?>:</td>
    <td class="TextColumn">
        <input type="radio" name="PageSize" value="letter" checked>Letter (8.5x11)<br>
        <input type="radio" name="PageSize" value="legal">Legal (8.5x14)<br>
        <input type="radio" name="PageSize" value="a4">A4
  </td>
  </tr>
  <tr>
   <td class="LabelColumn"><?= _('Font Size') ?>:</td>
    <td class="TextColumn">
    <table>
    <tr>
        <td><input type="radio" Name="FSize" value=6>6<br>
        <input type="radio" Name="FSize" value=8>8<br>
        <input type="radio" Name="FSize" value=10 checked>10<br></td>

        <td><input type="radio" Name="FSize" value=12>12<br>
        <input type="radio" Name="FSize" value=14>14<br>
        <input type="radio" Name="FSize" value=16>16<br></td>
    </tr>
    </table>
  </td>
  </tr>
    <tr>
        <td class="LabelColumn"><?= _('Title page') ?>:</td>
        <td class="TextColumn">
            <table>
                <tr>
                    <td><?= _('Use Title Page') ?></td>
                    <td><input type="checkbox" Name="bDirUseTitlePage" value="1"></td>
                </tr>
                <tr>
                    <td><?= _('Church Name') ?></td>
                    <td><input type="text" Name="sChurchName" value="<?= SystemConfig::getValue('sChurchName') ?>"></td>
                </tr>
                <tr>
                    <td><?= _('Address') ?></td>
                    <td><input type="text" Name="sChurchAddress" value="<?= SystemConfig::getValue('sChurchAddress') ?>"></td>
                </tr>
                <tr>
                    <td><?= _('City') ?></td>
                    <td><input type="text" Name="sChurchCity" value="<?= SystemConfig::getValue('sChurchCity') ?>"></td>
                </tr>
                <tr>
                    <td><?= _('State') ?></td>
                    <td><input type="text" Name="sChurchState" value="<?= SystemConfig::getValue('sChurchState') ?>"></td>
                </tr>
                <tr>
                    <td><?= _('Zip') ?></td>
                    <td><input type="text" Name="sChurchZip" value="<?= SystemConfig::getValue('sChurchZip') ?>"></td>
                </tr>
                <tr>
                    <td><?= _('Phone') ?></td>
                    <td><input type="text" Name="sChurchPhone" value="<?= SystemConfig::getValue('sChurchPhone') ?>"></td>
                </tr>
                <tr>
                    <td><?= _('Disclaimer') ?></td>
                    <td><textarea Name="sDirectoryDisclaimer" cols="35" rows="4"><?= SystemConfig::getValue('sDirectoryDisclaimer1').' '.SystemConfig::getValue('sDirectoryDisclaimer2') ?></textarea></td>
                </tr>

            </table>
        </td>
    </tr>


</table>
</div>

<?php 
  if (array_key_exists('cartdir', $_GET)) {
?>
  <input type="hidden" name="cartdir" value="M">
<?php
  } 
?>

<p align="center">
<BR>
<input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Create Directory') ?>">
<input type="button" class="btn" name="Cancel" <?= 'value="'._('Cancel').'"' ?> onclick="javascript:document.location='Menu.php';">
</p>
</form>
</div>

<?php require 'Include/Footer.php' ?>
