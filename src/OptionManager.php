<?php
/*******************************************************************************
 *
 *  filename    : OptionsManager.php
 *  last change : 2003-04-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt
 *
 *  OptionName : Interface for editing simple selection options such as those
 *              : used for Family Roles, Classifications, and Group Types
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOption;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMaster;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonCustomMaster;
use EcclesiaCRM\GroupQuery;

$mode = trim($_GET['mode']);

$listID = 0;

// Check security for the mode selected.
switch ($mode) {
    case 'famroles':
    case 'classes':
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            Redirect('Menu.php');
            exit;
        }
        break;

    case 'grptypes':
        $listID = 3;
    case 'grproles':
        if (!$listID) {
          $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        }
    case 'groupcustom':
        if (!$listID) {
          $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        }
        
        $iGroupID = 0;
        $manager  = null;
        
        $grpManager = GroupPropMasterQuery::Create()->findOneBySpecial($listID);
        
        if ($grpManager != null) {
          $iGroupID = $grpManager->getGroupId();
        }
         
        if ($iGroupID > 0) {
          $manager = GroupManagerPersonQuery::Create()->filterByPersonID($_SESSION['user']->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();
        }

        if (!($_SESSION['user']->isManageGroupsEnabled() || !empty($manager) ) ) {
            Redirect('Menu.php');
            exit;
        }
        break;

    case 'custom':
    case 'famcustom':
    case 'securitygrp':
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            Redirect('Menu.php');
            exit;
        }
        break;

    default:
        Redirect('Menu.php');
        break;
}

// Select the proper settings for the editor mode
switch ($mode) {
    case 'famroles':
        //It don't work for postuguese because in it adjective come after noum
        $noun = gettext('Role');
        //In the same way, the plural isn't only add s
        $adjplusname = gettext('Family Role');
        $adjplusnameplural = gettext('Family Roles');
        $sPageTitle = gettext('Family Roles Editor');
        $listID = 2;
        $embedded = false;
        break;
    case 'classes':
        $noun = gettext('Classification');
        $adjplusname = gettext('Person Classification');
        $adjplusnameplural = gettext('Person Classifications');
        $sPageTitle = gettext('Person Classifications Editor');
        $listID = 1;
        $embedded = false;
        break;
    case 'grptypes':
        $noun = gettext('Type');
        $adjplusname = gettext('Group Type');
        $adjplusnameplural = gettext('Group Types');
        $sPageTitle = gettext('Group Types Editor');
        $listID = 3;
        $embedded = false;
        break;
    case 'securitygrp':
        $noun = gettext('Group');
        $adjplusname = gettext('Security Group');
        $adjplusnameplural = gettext('Security Groups');
        $sPageTitle = gettext('Security Groups Editor');
        $listID = 5;
        $embedded = false;
        break;
    case 'grproles':// unusefull : dead code : This can be defined in GroupEditor.php?GroupID=id
        $noun = gettext('Role');
        $adjplusname = gettext('Group Member Role');
        $adjplusnameplural = gettext('Group Member Roles');
        $sPageTitle = gettext('Group Member Roles Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;

        $ormGroupList = GroupQuery::Create()->findOneByRoleListId($listID);
        if (!is_null($ormGroupList) ) {
           $iDefaultRole = $ormGroupList->getDefaultRole();
        } else {
          Redirect('Menu.php');
          exit;
        }
        
        break;
    case 'custom':
        $noun = gettext('Option');
        $adjplusname = gettext('Person Custom List Option');
        $adjplusnameplural = gettext('Person Custom List Options');
        $sPageTitle = gettext('Person Custom List Options Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;
        
        $per_cus = PersonCustomMasterQuery::Create()->filterByTypeId(12)->findByCustomSpecial($listID);
        
        if ($per_cus->count() == 0) {
            Redirect('Menu.php');
            break;
        }

        break;
    case 'groupcustom':
        $noun = gettext('Option');
        $adjplusname = gettext('Custom List Option');
        $adjplusnameplural = gettext('Custom List Options');
        $sPageTitle = gettext('Custom List Options Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;
        
        $group_cus = GroupPropMasterQuery::Create()->filterByTypeId(12)->findBySpecial($listID);

        if ($group_cus->count() == 0) {
            Redirect('Menu.php');
            break;
        }
        
        break;
    case 'famcustom':
        $noun = gettext('Option');
        $adjplusname = gettext('Family Custom List Option');
        $adjplusnameplural = gettext('Family Custom List Options');
        $sPageTitle = gettext('Family Custom List Options Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;

        $fam_cus = FamilyCustomMasterQuery::Create()->filterByTypeId(12)->findByCustomSpecial($listID);
                
        if ($fam_cus->count() == 0) {
            Redirect('Menu.php');
            break;
        }
        
        break;
    default:
        Redirect('Menu.php');
        break;
}

$iNewNameError = 0;

// Check if we're adding a field
if (isset($_POST['AddField'])) {
    $newFieldName = InputUtils::LegacyFilterInput($_POST['newFieldName']);

    if (strlen($newFieldName) == 0) {
        $iNewNameError = 1;
    } else {
        // Check for a duplicate option name
        $list = ListOptionQuery::Create()->filterByOptionName($newFieldName)->findById ($listID);
        
        if (!is_null ($list) && $list->count() > 0) {
            $iNewNameError = 2;
        } else {
            // Get count of the options
            $list = ListOptionQuery::Create()->findById ($listID);
            $numRows = $list->count();
            $newOptionSequence = $numRows + 1;

            // Get the new OptionID
            $listMax = ListOptionQuery::Create()
                        ->addAsColumn('MaxOptionID', 'MAX('.ListOptionTableMap::COL_LST_OPTIONID.')')
                        ->findOneById ($listID);
            
            $max = $listMax->getMaxOptionID();
            
            $newOptionID = $max+1;

            // Insert into the appropriate options table
            $lst = new ListOption();
            
            $lst->setId($listID);
            $lst->setOptionId($newOptionID);
            $lst->setOptionSequence($newOptionSequence);
            $lst->setOptionName($newFieldName);
            
            $lst->save();
                    
            $iNewNameError = 0;
        }
    }
}

$bErrorFlag = false;
$bDuplicateFound = false;

// Get the original list of options..
//ADDITION - get Sequence Also
$ormLists = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById ($listID);

$numRows =  $ormLists->count();
              
$aNameErrors = [];

for ($row = 1; $row <= $numRows; $row++) {
    $aNameErrors[$row] = 0;    
}

if (isset($_POST['SaveChanges'])) {
    $row = 1;

    foreach ($ormLists as $ormList) {
      $aOldNameFields[$row] = $ormList->getOptionName();
      $aIDs[$row]           = $ormList->getOptionId();

      //addition save off sequence also
      $aSeqs[$row]          = $ormList->getOptionSequence();

      $aNameFields[$row]    = InputUtils::LegacyFilterInput($_POST[$row.'name']);
    
      $row++;
    }
    
    for ($row = 1; $row <= $numRows; $row++) {
        if (strlen($aNameFields[$row]) == 0) {
            $aNameErrors[$row] = 1;
            $bErrorFlag = true;
        } elseif ($row < $numRows) {
            $aNameErrors[$row] = 0;
            for ($rowcmp = $row + 1; $rowcmp <= $numRows; $rowcmp++) {
                if ($aNameFields[$row] == $aNameFields[$rowcmp]) {
                    $bErrorFlag = true;
                    $bDuplicateFound = true;
                    $aNameErrors[$row] = 2;
                    break;
                }
            }
        } else {
            $aNameErrors[$row] = 0;
        }
    }

    // If no errors, then update.
    if (!$bErrorFlag) {
        for ($row = 1; $row <= $numRows; $row++) {
            // Update the type's name if it has changed from what was previously stored
            if ($aOldNameFields[$row] != $aNameFields[$row]) {
                $list = ListOptionQuery::Create()->filterByOptionSequence($row)->findOneById($listID);
                
                $list->setOptionName($aNameFields[$row]);
                $list->save();
            }
        }
    }
}

// Get data for the form as it now exists..
$ormLists = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById ($listID);

$numRows =  $ormLists->count();

// Create arrays of the option names and IDs
$row = 1;

foreach ($ormLists as $ormList) {
    if (!$bErrorFlag) {
        $aNameFields[$row] = $ormList->getOptionName();
    }

    $aIDs[$row] = $ormList->getOptionId();
    //addition save off sequence also
    $aSeqs[$row++] = $ormList->getOptionSequence();
}

//Set the starting row color
$sRowClass = 'RowColorA';

// Use a minimal page header if this form is going to be used within a frame
if ($embedded) {
    include 'Include/Header-Minimal.php';
} else {    //It don't work for postuguese because in it adjective come after noum
    //$sPageTitle = $adj . ' ' . $noun . "s ".gettext("Editor");
    include 'Include/Header.php';
}

?>

<div class="callout callout-danger"><?= gettext('Warning: Removing will reset all assignments for all persons with the assignment!') ?></div>

<div class="box">
    <div class="box-body">
<form method="post" action="OptionManager.php?<?= "mode=$mode&ListID=$listID" ?>" name="OptionManager">

<?php

if ($bErrorFlag) {
?>
    <span class="MediumLargeText" style="color: red;">
<?php
    if ($bDuplicateFound) {
?>
        <br><?= gettext('Error: Duplicate').' '.$adjplusnameplural.' '.gettext('are not allowed.') ?>
<?php
    }
?>
        <br><?= gettext('Invalid fields or selections. Changes not saved! Please correct and try again!')?></span><br><br>
<?php
}
?>

<br>
<table cellpadding="3" width="50%" align="center">

<?php
for ($row = 1; $row <= $numRows; $row++) {
    ?>
    <tr align="center">
        <td class="LabelColumn">
            <b>
            <?php
            if ($mode == 'grproles' && $aIDs[$row] == $iDefaultRole) {
            ?>
                <?= gettext('Default').' '?>
            <?php
            } 
            ?>
            
             <?= $row ?>
            </b>
        </td>

        <td class="TextColumn" nowrap>

            <?php
            if ($row != 1) {
            ?>
                <a href="<?= SystemURLs::getRootPath() ?>/OptionManagerRowOps.php?mode=<?= $mode ?>&Order=<?= $aSeqs[$row] ?>&ListID=<?= $listID ?>&ID=<?= $aIDs[$row] ?>&Action=up"><img src="<?= SystemURLs::getRootPath() ?>/Images/uparrow.gif" border="0"></a>
            <?php
            }
            if ($row < $numRows) {
            ?>
                <a href="<?= SystemURLs::getRootPath() ?>/OptionManagerRowOps.php?mode=<?= $mode ?>&Order=<?= $aSeqs[$row] ?>&ListID=<?= $listID ?>&ID=<?= $aIDs[$row] ?>&Action=down"><img src="<?= SystemURLs::getRootPath() ?>/Images/downarrow.gif" border="0"></a>
            <?php
            }
            if ($numRows > 0) {
            ?>
              <?php
              if ($embedded) {
              ?>                
                <a href="<?= SystemURLs::getRootPath() ?>/OptionManagerRowOps.php?mode=<?= $mode ?>&Order=<?= $aSeqs[$row] ?>&ListID=<?= $listID ?>&ID=<?= $aIDs[$row] ?>&Action=delete"><img src="Images/x.gif" border="0"></a>
              <?php
                } else {
              ?>
                 <img src="<?= SystemURLs::getRootPath() ?>/Images/x.gif" class="RemoveClassification" data-mode="<?= $mode ?>" data-order="<?= $aSeqs[$row] ?>" data-listid="<?= $listID ?>" data-id="<?= $aIDs[$row] ?>" data-name="<?= htmlentities(stripslashes($aNameFields[$row])) ?>" border="0">
            <?php
                }
            } 
            ?>
        </td>
        <td class="TextColumn">
            <span class="SmallText">
                <input class="form-control input-md" type="text" name="<?= $row.'name' ?>" value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" size="30" maxlength="40">
            </span>
            <?php

            if ($aNameErrors[$row] == 1) {
            ?>
                <span style="color: red;"><BR><?= gettext('You must enter a name') ?> </span>
            <?php
            } elseif ($aNameErrors[$row] == 2) {
            ?>
                <span style="color: red;"><BR><?= gettext('Duplicate name found.') ?> </span>
            <?php
            } ?>
        </td>
        <?php
        if ($mode == 'grproles') {
        ?>
            <td class="TextColumn"><input class="btn btn-success btn-xs" type="button" class="btn btn-default" value="<?= gettext('Make Default')?>" Name="default" onclick="javascript:document.location='OptionManagerRowOps.php?mode=<?= $mode ?>&ListID=<?= $listID ?>&ID=<?= $aIDs[$row]?>&Action=makedefault';" ></td>
        <?php
        } else if ($mode == 'classes') {
          $icon = ListOptionIconQuery::Create()->filterByListId(1)->findOneByListOptionId($aIDs[$row]);
          
          if ($icon == null || $icon != null && $icon->getUrl() == '') {
          ?>
            <td><img src="Images/+.png" border="0" class="AddImage" data-ID="<?= $listID ?>" data-optionID="<?= $aIDs[$row] ?>" data-name="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>"></td>
            <td></td>
            <td>&nbsp;</td>
            <td align="left"><input type="checkbox" class="checkOnlyPersonView" data-ID="<?= $listID ?>" data-optionID="<?= $aIDs[$row] ?>" <?= ($icon != null && $icon->getOnlyVisiblePersonView())?"checked":"" ?> />
            <?= gettext("Visible only in PersonView") ?></td>
          <?php
          } else {
          ?>
            <td><img src="Images/x.gif" border="0" class="RemoveImage"  data-ID="<?= $listID ?>" data-optionID="<?= $aIDs[$row] ?>"  ></td>
            <td><img src="/skin/icons/markers/<?= $icon->getUrl() ?>" border="0" height="25"></td>
            <td>&nbsp;</td>
            <td align="left"><input type="checkbox" class="checkOnlyPersonView" data-ID="<?= $listID ?>" data-optionID="<?= $aIDs[$row] ?>"  <?= ($icon != null && $icon->getOnlyVisiblePersonView())?"checked":"" ?> />
            <?= gettext("Visible only in PersonView") ?></td>
          <?php
          }
        }
        
        
        ?>

    </tr>
<?php
} ?>

</table>
  <br/>
    <input type="submit" class="btn btn-primary" value="<?= gettext('Save Changes') ?>" Name="SaveChanges">


    <?php if ($mode == 'groupcustom' || $mode == 'custom' || $mode == 'famcustom') {
            ?>
        <input type="button" class="btn btn-default" value="<?= gettext('Exit') ?>" Name="Exit" onclick="javascript:window.close();">
    <?php
        } elseif ($mode != 'grproles') {
            ?>
        <input type="button" class="btn btn-default" value="<?= gettext('Exit') ?>" Name="Exit" onclick="javascript:document.location='<?= 'Menu.php' ?>';">
    <?php
        } ?>
    </div>
</div>

<div class="box box-primary">
    <div class="box-body">
<?=  gettext('Name for New').' '.$noun ?>:&nbsp;
<span class="SmallText">
    <input class="form-control form-control input-md" type="text" name="newFieldName" size="30" maxlength="40">
</span>
<p>  </p>
<input type="submit" class="btn btn-success" value="<?= gettext('Add New').' '.$adjplusname ?>" Name="AddField">
<?php
    if ($iNewNameError > 0) {
?>
        <div><span style="color: red;"><BR>
      <?php
        if ($iNewNameError == 1) {
      ?>
            <?= gettext('Error: You must enter a name') ?>
      <?php
        } else {
      ?>        
            <?= gettext('Error: A ').$noun.gettext(' by that name already exists.') ?>
      <?php
        }
      ?>
        <?= '</span></div>' ?>
<?php
    }
?>
</center>
</form>
    </div>
</div>
<?php
if ($embedded) {
?>
    </body></html>
<?php
} else {
    include 'Include/Footer.php';
}
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/IconPicker.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/OptionManager.js"></script>