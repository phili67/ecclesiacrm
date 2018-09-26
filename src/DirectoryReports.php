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
$sPageTitle = gettext('Directory reports');
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


$aDefaultClasses = explode(',', SystemConfig::getValue('sDirClassifications'));
$aDirRoleHead = explode(',', SystemConfig::getValue('sDirRoleHead'));
$aDirRoleSpouse = explode(',', SystemConfig::getValue('sDirRoleSpouse'));
$aDirRoleChild = explode(',', SystemConfig::getValue('sDirRoleChild'));

?>
<?php 
   if (!array_key_exists('cartdir', $_GET)) {
?>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Exclude Inactive Families') ?></b>
   </div>
   <div class="col-sm-7">
      <input type="checkbox" Name="bExcludeInactive" value="1" checked>
   </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Select classifications to include') ?> </b>
   </div>
   <div class="col-sm-7">
            <div class="SmallText"><?= gettext('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirClassifications[]" size="5" multiple>
            <option value="0"><?= gettext("Unassigned") ?></option>
            <?php
               foreach ($ormClassifications as $rsClassification) {
            ?>
                  <option value="<?= $rsClassification->getOptionId()?>" <?= (in_array($rsClassification->getOptionId(), $aDefaultClasses))?' selected':''?>><?= gettext($rsClassification->getOptionName()) ?></option>
            <?php
               }
            ?>
            </select>
   </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Group Membership') ?>:</b>
   </div>
   <div class="col-sm-7">
            <div class="SmallText"><?= gettext('Use Ctrl Key to select multiple') ?></div>
            <select name="GroupID[]" size="5" multiple>
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
   <div class="col-sm-5">
      <b><?= gettext('Which role is the head of household?') ?></b>
   </div>
   <div class="col-sm-7">
            <div class="SmallText"><?= gettext('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirRoleHead[]" size="5" multiple>
            <?php
                foreach ($ormFamilyRoles as $ormFamilyRole) {
            ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleHead))?' selected':'' ?>> <?= gettext($ormFamilyRole->getOptionName()) ?></option>
            <?php
                }
            ?>
            </select>
   </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Which role is the spouse?') ?></b>
   </div>
   <div class="col-sm-7">
            <div class="SmallText"><?= gettext('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirRoleSpouse[]" size="5" multiple>
            <?php
                foreach ($ormFamilyRoles as $ormFamilyRole) {
            ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleSpouse))?' selected':'' ?>><?= gettext($ormFamilyRole->getOptionName()) ?></option>
            <?php
                }
            ?>
            </select>
   </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Which role is a child?') ?></b>
   </div>
   <div class="col-sm-7">
            <div class="SmallText"><?= gettext('Use Ctrl Key to select multiple') ?></div>
            <select name="sDirRoleChild[]" size="5" multiple>
            <?php
                foreach ($ormFamilyRoles as $ormFamilyRole) {
            ?>
                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleChild))?' selected':'' ?>><?= gettext($ormFamilyRole->getOptionName()) ?></option>
            <?php
                }
            ?>
            </select>
   </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Information to Include') ?>:</b>
   </div>
   <div class="col-sm-7">
            <input type="checkbox" Name="bDirAddress" value="1" checked> <?= gettext('Address') ?><br>
            <input type="checkbox" Name="bDirWedding" value="1" checked> <?= gettext('Wedding Date') ?><br>
            <input type="checkbox" Name="bDirBirthday" value="1" checked> <?= gettext('Birthday') ?><br>

            <input type="checkbox" Name="bDirFamilyPhone" value="1" checked> <?= gettext('Family Home Phone') ?><br>
            <input type="checkbox" Name="bDirFamilyWork" value="1" checked> <?= gettext('Family Work Phone') ?><br>
            <input type="checkbox" Name="bDirFamilyCell" value="1" checked> <?= gettext('Family Cell Phone') ?><br>
            <input type="checkbox" Name="bDirFamilyEmail" value="1" checked> <?= gettext('Family Email') ?><br>

            <input type="checkbox" Name="bDirPersonalPhone" value="1" checked> <?= gettext('Personal Home Phone') ?><br>
            <input type="checkbox" Name="bDirPersonalWork" value="1" checked> <?= gettext('Personal Work Phone') ?><br>
            <input type="checkbox" Name="bDirPersonalCell" value="1" checked> <?= gettext('Personal Cell Phone') ?><br>
            <input type="checkbox" Name="bDirPersonalEmail" value="1" checked> <?= gettext('Personal Email') ?><br>
            <input type="checkbox" Name="bDirPersonalWorkEmail" value="1" checked> <?= gettext('Personal Work/Other Email') ?><br>
            <input type="checkbox" Name="bDirPhoto" value="1" checked> <?= gettext('Photos') ?><br>            
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
          
   </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Number of Columns') ?>:</b>
   </div>
   <div class="col-sm-7">
        <input type="radio" Name="NumCols" value=1>1 <?= gettext('col') ?><br>
        <input type="radio" Name="NumCols" value=2 checked>2 <?= gettext('cols') ?><br>
        <input type="radio" Name="NumCols" value=3>3 <?= gettext('cols') ?><br>
    </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
      <b><?= gettext('Paper Size') ?>:</b>
   </div>
   <div class="col-sm-7">
        <input type="radio" name="PageSize" value="letter" checked>Letter (8.5x11)<br>
        <input type="radio" name="PageSize" value="legal">Legal (8.5x14)<br>
        <input type="radio" name="PageSize" value="a4">A4
    </div>
</div>
<br>
<div class="row">
   <div class="col-sm-5">
       <b><?= gettext('Font Size') ?>:</b>
   </div>
   <div class="col-sm-7">
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
   <div class="col-sm-5">
      <b><?= gettext('Title page') ?>:</b>
   </div>
   <div class="col-sm-7">
            <table>
                <tr>
                    <td><?= gettext('Use Title Page') ?>  
                    <td><input type="checkbox" Name="bDirUseTitlePage" value="1">  
                </tr>
                <tr>
                    <td><?= gettext('Church Name') ?>  
                    <td><input type="text" Name="sChurchName" value="<?= SystemConfig::getValue('sChurchName') ?>" class="form-control">  
                </tr>
                <tr>
                    <td><?= gettext('Address') ?>  
                    <td><input type="text" Name="sChurchAddress" value="<?= SystemConfig::getValue('sChurchAddress') ?>" class="form-control">  
                </tr>
                <tr>
                    <td><?= gettext('City') ?>  
                    <td><input type="text" Name="sChurchCity" value="<?= SystemConfig::getValue('sChurchCity') ?>" class="form-control">  
                </tr>
                <tr>
                    <td><?= gettext('State') ?>  
                    <td><input type="text" Name="sChurchState" value="<?= SystemConfig::getValue('sChurchState') ?>" class="form-control">  
                </tr>
                <tr>
                    <td><?= gettext('Zip') ?>  
                    <td><input type="text" Name="sChurchZip" value="<?= SystemConfig::getValue('sChurchZip') ?>" class="form-control">  
                </tr>
                <tr>
                    <td><?= gettext('Phone') ?>  
                    <td><input type="text" Name="sChurchPhone" value="<?= SystemConfig::getValue('sChurchPhone') ?>" class="form-control"><br>  
                </tr>
                <tr>
                    <td><?= gettext('Disclaimer') ?>  
                    <td><textarea Name="sDirectoryDisclaimer" cols="35" rows="4"><?= SystemConfig::getValue('sDirectoryDisclaimer1').' '.SystemConfig::getValue('sDirectoryDisclaimer2') ?></textarea>  
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

<p align="center">
<BR>
<input type="submit" class="btn btn-primary" name="Submit" value="<?= gettext('Create Directory') ?>">
<input type="button" class="btn" name="Cancel" <?= 'value="'.gettext('Cancel').'"' ?> onclick="javascript:document.location='Menu.php';">
</p>
</form>
</div>

<?php require 'Include/Footer.php' ?>
