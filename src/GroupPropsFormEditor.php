<?php
/*******************************************************************************
 *
 *  filename    : GroupPropsFormEditor.php
 *  last change : 2003-02-09
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt (http://www.openserve.org)
 *                Copyright 2013 Michael Wilt
 *                Copyright 2019 Philippe Logel
 *
 *  function    : Editor for group-specific properties form
 *
******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\Map\GroupPropMasterTableMap;
use EcclesiaCRM\GroupPropMaster;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOption;

use EcclesiaCRM\Utils\MiscUtils;


// Get the Group from the querystring
$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');

$manager = GroupManagerPersonQuery::Create()->filterByPersonID(SessionUser::getUser()->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();

$is_group_manager = false;

if (!empty($manager)) {
  $is_group_manager = true;
}

// Security: user must be allowed to edit records to use this page.
if ( !(SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}


// Get the group information
$groupInfo = GroupQuery::Create()->findOneById ($iGroupID);

// Abort if user tries to load with group having no special properties.
if ($groupInfo->getHasSpecialProps() == false) {
    RedirectUtils::Redirect('v2/group/'.$iGroupID.'/view');
}

$sPageTitle = _('Group-Specific Properties Form Editor:').'  : '.$groupInfo->getName();

require 'Include/Header.php'; ?>

<p class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <?= _("Warning: Field changes will be lost if you do not 'Save Changes' before using an up, down, delete, or 'add new' button!") ?></p>

<div class="card">
<div class="card-header border-1">
    <h3 class="card-title"><?= _('Group-Specific Properties') ?></h3>
</div>

<?php
$bErrorFlag = false;
$aNameErrors = [];
$bNewNameError = false;
$bDuplicateNameError = false;

// Does the user want to save changes to text fields?
if (isset($_POST['SaveChanges'])) {

    // Fill in the other needed property data arrays not gathered from the form submit
    $propList = GroupPropMasterQuery::Create()->filterByGroupId ($iGroupID)->orderByPropId()->find();
    $numRows = $propList->count();

    $row = 1;
    foreach ($propList as $prop) {
        $aFieldFields[$row] = $prop->getField();
        $aTypeFields[$row]  = $prop->getTypeId();

        if (!is_null ($prop->getSpecial())) {
          if ($prop->getTypeId() == 9) {
            $aSpecialFields[$row] = $groupInfo->getId();
          } else {
            $aSpecialFields[$row] = $prop->getSpecial();
          }
        } else {
            $aSpecialFields[$row] = 'NULL';
        }

        $row++;
    }

    for ($iPropID = 1; $iPropID <= $numRows; $iPropID++) {
        $aNameFields[$iPropID] = InputUtils::LegacyFilterInput($_POST[$iPropID.'name']);

        if (strlen($aNameFields[$iPropID]) == 0) {
            $aNameErrors[$iPropID] = true;
            $bErrorFlag = true;
        } else {
            $aNameErrors[$iPropID] = false;
        }

        $aDescFields[$iPropID] = InputUtils::LegacyFilterInput($_POST[$iPropID.'desc']);

        if (isset($_POST[$iPropID.'special'])) {
            $aSpecialFields[$iPropID] = InputUtils::LegacyFilterInput($_POST[$iPropID.'special'], 'int');

            if ($aSpecialFields[$iPropID] == 0) {
                $aSpecialErrors[$iPropID] = true;
                $bErrorFlag = true;
            } else {
                $aSpecialErrors[$iPropID] = false;
            }
        }

        if (isset($_POST[$iPropID.'show'])) {
            $aPersonDisplayFields[$iPropID] = true;
        } else {
            $aPersonDisplayFields[$iPropID] = false;
        }
    }

    // If no errors, then update.
    if (!$bErrorFlag) {
        for ($iPropID = 1; $iPropID <= $numRows; $iPropID++) {
            if ($aPersonDisplayFields[$iPropID]) {
                $temp = 'true';
            } else {
                $temp = 'false';
            }

            if ($aTypeFields[$iPropID] == 2) {
               $aDescFields[$iPropID] = InputUtils::FilterDate($aDescFields[$iPropID]);
            }


            $groupMasterUpd = GroupPropMasterQuery::Create()->filterByGroupId ($iGroupID)->filterByPropId($iPropID)->findOne();

            $groupMasterUpd->setName ($aNameFields[$iPropID]);
            $groupMasterUpd->setDescription ($aDescFields[$iPropID]);
            $groupMasterUpd->setSpecial ($aSpecialFields[$iPropID]);
            $groupMasterUpd->setPersonDisplay ($temp);

            $groupMasterUpd->save();
        }
    }
} else {
    // Check if we're adding a field
    if (isset($_POST['AddField'])) {
        $newFieldType = InputUtils::LegacyFilterInput($_POST['newFieldType'], 'int');
        $newFieldName = InputUtils::LegacyFilterInput($_POST['newFieldName']);
        $newFieldDesc = InputUtils::LegacyFilterInput($_POST['newFieldDesc']);

        if (strlen($newFieldName) == 0) {
            $bNewNameError = true;
        } else {
            $groupMasters = GroupPropMasterQuery::Create()->filterByGroupId ($iGroupID)->find();

            foreach ($groupMasters as $groupMaster) {
              if ($groupMaster->getName() == $newFieldName) {
                $bDuplicateNameError = true;
              }
            }

            if (!$bDuplicateNameError) {
                // Get the new prop_ID (highest existing plus one)
                $propLists = GroupPropMasterQuery::Create()->findByGroupId ($iGroupID);
                $newRowNum = $propLists->count()+1;

                // Find the highest existing field number in the group's table to determine the next free one.
                // This is essentially an auto-incrementing system where deleted numbers are not re-used.

                // SELECT CAST(SUBSTR(groupprop_master.prop_Field, 2) as UNSIGNED) AS field, prop_Field FROM groupprop_master WHERE grp_ID=22 order by field
                $lastProps = GroupPropMasterQuery::Create()
                   ->withColumn('CAST(SUBSTR('.GroupPropMasterTableMap::COL_PROP_FIELD.', 2) as UNSIGNED)', 'field')
                   ->addDescendingOrderByColumn('field')
                   ->limit(1)
                   ->findOneByGroupId ($iGroupID);

                $newFieldNum = 1;
                if ( !is_null($lastProps) ) {
                    $newFieldNum = mb_substr($lastProps->getField(), 1) + 1;
                }

                // If we're inserting a new custom-list type field, create a new list and get its ID
                if ($newFieldType == 12) {
                    // Get the first available lst_ID for insertion.  lst_ID 0-9 are reserved for permanent lists.
                    $listMax = ListOptionQuery::Create()
                                ->addAsColumn('MaxOptionID', 'MAX('.ListOptionTableMap::COL_LST_ID.')')
                                ->findOne ();

                    // this ensure that the group list and sundaygroup list has ever an unique optionId.
                    $max = $listMax->getMaxOptionID();
                    if ($max > 9) {
                        $newListID = $max + 1;
                    } else {
                        $newListID = 10;
                    }

                    // Insert into the lists table with an example option.
                                // Insert into the appropriate options table
                    $list_type = 'normal';// this list is only for a group specific properties, so it's ever a normal list 'normal' and not 'sundayschool'

                    $lst = new ListOption();

                    $lst->setId($newListID);
                    $lst->setOptionId(1);
                    $lst->setOptionSequence(1);
                    $lst->setOptionType($list_type);
                    $lst->setOptionName(_("Default Option"));

                    $lst->save();

                    $newSpecial = $newListID;
                } else {
                    $newSpecial = NULL;
                }

                // Insert into the master table
                $groupPropMst = new GroupPropMaster();

                $groupPropMst->setGroupId ($iGroupID);
                $groupPropMst->setPropId ($newRowNum);
                $groupPropMst->setField ("c".$newFieldNum);
                $groupPropMst->setName ($newFieldName);
                $groupPropMst->setDescription ($newFieldDesc);
                $groupPropMst->setTypeId ($newFieldType);
                $groupPropMst->setSpecial ($newSpecial);

                $groupPropMst->save();

                // Insert into the group-specific properties table
                $sSQL = 'ALTER TABLE `groupprop_'.$iGroupID.'` ADD `c'.$newFieldNum.'` ';

                switch ($newFieldType) {
                case 1:
                    $sSQL .= "ENUM('false', 'true')";
                    break;
                case 2:
                    $sSQL .= 'DATE';
                    break;
                case 3:
                    $sSQL .= 'VARCHAR(50)';
                    break;
                case 4:
                    $sSQL .= 'VARCHAR(100)';
                    break;
                case 5:
                    $sSQL .= 'TEXT';
                    break;
                case 6:
                    $sSQL .= 'YEAR';
                    break;
                case 7:
                    $sSQL .= "ENUM('winter', 'spring', 'summer', 'fall')";
                    break;
                case 8:
                    $sSQL .= 'INT';
                    break;
                case 9:
                    $sSQL .= 'MEDIUMINT(9)';
                    break;
                case 10:
                    $sSQL .= 'DECIMAL(10,2)';
                    break;
                case 11:
                    $sSQL .= 'VARCHAR(30)';
                    break;
                case 12:
                    $sSQL .= 'TINYINT(4)';
                }

                $sSQL .= ' DEFAULT NULL ;';

                $connection = Propel::getConnection();

                $statement = $connection->prepare($sSQL);
                $statement->execute();
                $bNewNameError = false;
            }
        }
    }

    // Get data for the form as it now exists..
    $propList = GroupPropMasterQuery::Create()->filterByGroupId ($iGroupID)->orderByPropId()->find();
    $numRows = $propList->count();

    $row = 1;
    foreach ($propList as $prop) {
        $aTypeFields[$row]    = $prop->getTypeId();
        $aNameFields[$row]    = $prop->getName();
        $aDescFields[$row]    = $prop->getDescription();
        $aSpecialFields[$row] = $prop->getSpecial();
        $aFieldFields[$row]   = $prop->getField();

        if ($prop->getTypeId() == 9) {
          $aSpecialFields[$row] = $iGroupID;
        }

        $aPersonDisplayFields[$row] = ($prop->getPersonDisplay() == 'true');

        $row++;
    }
}

// Construct the form
?>

<form method="post" action="GroupPropsFormEditor.php?GroupID=<?= $iGroupID ?>" name="GroupPropFormEditor">

<center>
<div class="table-responsive">
<table class="table" >

<?php
if ($numRows == 0) {
    ?>
  <center><h2><?= _('No properties have been added yet') ?></h2>
            <a href="<?= SystemURLs::getRootPath() ?>/v2/group/<?= $iGroupID ?>/view" class="btn btn-success"><?= _("Return to Group") ?></a>
  </center>
<?php
} else {
        ?>

  <tr><td colspan="7" align="center">
  <?php
    if ($bErrorFlag) {
  ?>
     <p class="alert alert-danger"><span class="fas fa-exclamation-triangle"> <?= _("Invalid fields or selections. Changes not saved! Please correct and try again!") ?></span></p>
  <?php
    }
  ?>
  </td></tr>

    <tr>
      <th></th>
      <th></th>
      <th><?= _('Type') ?></th>
      <th><?= _('Name') ?></th>
      <th><?= _('Description') ?></th>
      <th><?= _('Special option') ?></th>
      <th><?= _('Show in') ?><br>"<?= _('Person Profile') ?>"</th>
    </tr>

  <?php

    for ($row = 1; $row <= $numRows; $row++) {
        ?>
    <tr>
      <td class="LabelColumn"><h2><b><?= $row ?></b></h2></td>
      <td class="TextColumn" width="5%" nowrap>
        <?php
          if ($row != 1) {
        ?>
            <img src="Images/uparrow.gif" border="0" class="up-action" data-GroupID="<?= $iGroupID ?>" data-PropID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>">
        <?php
          }
          if ($row < $numRows) {
        ?>
            <img src="Images/downarrow.gif" border="0" class="down-action" data-GroupID="<?= $iGroupID ?>" data-PropID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>">
        <?php
          }
        ?>
            <img src="Images/x.gif" border="0" class="delete-field" data-GroupID="<?= $iGroupID ?>" data-PropID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>">
      </td>
      <td class="TextColumn" style="font-size:70%;">
          <?= MiscUtils::PropTypes($aTypeFields[$row]) ?>
      </td>
      <td class="TextColumn">
         <input type="text" name="<?= $row ?>name" value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" size="25" maxlength="40" class= "form-control form-control-sm">
        <?php
          if (array_key_exists($row, $aNameErrors) && $aNameErrors[$row]) {
        ?>
            <span style="color: red;"><BR><?= _('You must enter a name') ?> </span>
        <?php
          }
        ?>
      </td>

      <td class="TextColumn">
         <?php
            OutputUtils::formCustomField($aTypeFields[$row], $row."desc", htmlentities(stripslashes($aDescFields[$row]), ENT_NOQUOTES, 'UTF-8') , $aSpecialFields[$row])
         ?>
      </td>

      <td class="TextColumn">
      <?php

            if ($aTypeFields[$row] == 9) {
      ?>
              <select name="<?= $row ?>special"  class="form-control form-control-sm">
                <option value="0" selected><?= _("Select a group") ?></option>
              <?php
                $groupList = GroupQuery::Create()->orderByName()->find();

                foreach ($groupList as $group) {
              ?>
                <option value="<?= $group->getId()?>" <?= ($aSpecialFields[$row] == $group->getId())?' selected':'' ?>>
                  <?= $group->getName() ?>
              <?php
                }
              ?>
              </select>

              <?php

                if ($aSpecialErrors[$row]) {
              ?>
                    <span style="color: red;"><BR><?= _('You must select a group.') ?></span>
              <?php
                }
            } elseif ($aTypeFields[$row] == 12) {
          ?>
                <a class="btn btn-success" href="javascript:void(0)" onClick="Newwin=window.open('OptionManager.php?mode=groupcustom&ListID=<?= $aSpecialFields[$row]?>','Newwin','toolbar=no,status=no,width=400,height=500')"><?= _("Edit List Options") ?></a>
          <?php
            } else {
          ?>
                &nbsp;
          <?php
            } ?>
      </td>
      <td class="TextColumn">
        <input type="checkbox" name="<?= $row ?>show" value="1"  <?= ($aPersonDisplayFields[$row])?' checked':'' ?>>
      </td>
    </tr>
  <?php
    } ?>

    <tr>
      <td colspan="7">
      <table width="100%">
        <tr>
          <td width="10%"></td>
          <td width="40%" align="center" valign="bottom">
            <a href="<?= SystemURLs::getRootPath() ?>/v2/group/<?= $iGroupID ?>/view" class="btn btn-default"><?= _("Return to Group") ?></a>
          </td>
          <td width="40%" align="center" valign="bottom">
            <input type="submit" class="btn btn-primary" value="<?= _('Save Changes') ?>" Name="SaveChanges">
          </td>
          <td width="10%"></td>
        </tr>
      </table>
      </td>
      <td>
    </tr>
<?php
    }
?>
   </table>
</div>
</center>
</div>
<div class="card">
<div class="card-header border-1">
  <h3 class="card-title"><?= _("Add Group-Specific Properties") ?></h3>
</div>

<table  width="100%" style="border:white">
    <tr><td colspan="7"></td></tr>
    <tr>
      <td colspan="7">
      <table width="100%" style="border-spacing : 10px;border-collapse : separate;">
        <tr>
          <td></td>
          <td><div><?= _('Type') ?>:</div></td>
          <td><div><?= _('Name') ?>:</div></td>
          <td><div><?= _('Description') ?>:</div></td>
          <td></td>
        </tr>
        <tr>
          <td width="15%"></td>
          <td valign="top">
             <select name="newFieldType" class="form-control form-control-sm">
          <?php
              for ($iOptionID = 1; $iOptionID <= MiscUtils::ProTypeCount(); $iOptionID++) {
          ?>
                  <option value="<?= $iOptionID ?>"> <?= MiscUtils::PropTypes($iOptionID) ?>
          <?php
              }
          ?>
            </select>
          <BR>
          <a href="<?= SystemURLs::getSupportURL() ?>"><?= _('Help on types..') ?></a>
          </td>
          <td valign="top">
            <input type="text" name="newFieldName" size="25" maxlength="40" class= "form-control form-control-sm">
            <?php
              if ($bNewNameError) {
            ?>
                  <div><span style="color: red;"><BR><?= _('You must enter a name')?></span></div>
            <?php
              }
              if ($bDuplicateNameError) {
            ?>
                  <div><span style="color: red;"><BR><?= _('That field name already exists.')?></span></div>
            <?php
              }
            ?>
            &nbsp;
          </td>
          <td valign="top">
            <input type="text" name="newFieldDesc" size="30" maxlength="60" class= "form-control form-control-sm">
            &nbsp;
          </td>
          <td valign="top">
            <input type="submit" class="btn btn-primary" value="<?= _('Add New Field') ?>" Name="AddField">
          </td>
          <td width="15%"></td>
        </tr>
      </table>
      </td>
    </tr>

  </table>
</div>
</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
  $(function() {
    $("[data-mask]").inputmask();
  });
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/group/GroupCustomFieldsEditor.js"></script>

<?php require 'Include/Footer.php' ?>
