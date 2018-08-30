<?php
/*******************************************************************************
 *
 *  filename    : GroupPropsFormEditor.php
 *  last change : 2003-02-09
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt (http://www.openserve.org)
 *                Copyright 2013 Michael Wilt
 *
 *  function    : Editor for group-specific properties form
 *
******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;



// Get the Group from the querystring
$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');

$manager = GroupManagerPersonQuery::Create()->filterByPersonID($_SESSION['user']->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();
  
$is_group_manager = false;

if (!empty($manager)) {
  $is_group_manager = true;
}

// Security: user must be allowed to edit records to use this page.
if ( !($_SESSION['user']->isManageGroupsEnabled() || $is_group_manager == true) ) {
    Redirect('Menu.php');
    exit;
}


// Get the group information
$sSQL = 'SELECT * FROM group_grp WHERE grp_ID = '.$iGroupID;
$rsGroupInfo = RunQuery($sSQL);
extract(mysqli_fetch_array($rsGroupInfo));

// Abort if user tries to load with group having no special properties.
if ($grp_hasSpecialProps == false) {
    Redirect('GroupView.php?GroupID='.$iGroupID);
}

$sPageTitle = gettext('Group-Specific Properties Form Editor:').'  : '.$grp_Name;

require 'Include/Header.php'; ?>

<p class="alert alert-warning"><span class="fa fa-exclamation-triangle"> <?= gettext("Warning: Field changes will be lost if you do not 'Save Changes' before using an up, down, delete, or 'add new' button!") ?></span></p>

<div class="box">
<div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Group-Specific Properties') ?></h3>
</div>

<?php
$bErrorFlag = false;
$aNameErrors = [];
$bNewNameError = false;
$bDuplicateNameError = false;

// Does the user want to save changes to text fields?
if (isset($_POST['SaveChanges'])) {

    // Fill in the other needed property data arrays not gathered from the form submit
    $sSQL = 'SELECT prop_ID, prop_Field, type_ID, prop_Special, prop_PersonDisplay FROM groupprop_master WHERE grp_ID = '.$iGroupID.' ORDER BY prop_ID';
    $rsPropList = RunQuery($sSQL);
    $numRows = mysqli_num_rows($rsPropList);

    for ($row = 1; $row <= $numRows; $row++) {
        $aRow = mysqli_fetch_array($rsPropList, MYSQLI_BOTH);
        extract($aRow);

        $aFieldFields[$row] = $prop_Field;
        $aTypeFields[$row] = $type_ID;
        $aSpecialFields[$row] = $prop_Special;

        if (isset($prop_Special)) {
          if ($type_ID == 9) {
            $aSpecialFields[$row] = $grp_ID;
          } else {
            $aSpecialFields[$row] = $prop_Special;
          }
        } else {
            $aSpecialFields[$row] = 'NULL';
        }
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

            $sSQL = "UPDATE groupprop_master
          SET `prop_Name` = '".$aNameFields[$iPropID]."',
            `prop_Description` = '".$aDescFields[$iPropID]."',
            `prop_Special` = ".$aSpecialFields[$iPropID].",
            `prop_PersonDisplay` = '".$temp."'
          WHERE `grp_ID` = '".$iGroupID."' AND `prop_ID` = '".$iPropID."';";

            RunQuery($sSQL);
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
            $sSQL = 'SELECT prop_Name FROM groupprop_master WHERE grp_ID = '.$iGroupID;
            $rsPropNames = RunQuery($sSQL);
            while ($aRow = mysqli_fetch_array($rsPropNames)) {
                if ($aRow[0] == $newFieldName) {
                    $bDuplicateNameError = true;
                    break;
                }
            }

            if (!$bDuplicateNameError) {
                // Get the new prop_ID (highest existing plus one)
                $sSQL = 'SELECT prop_ID  FROM groupprop_master WHERE grp_ID = '.$iGroupID;
                $rsPropList = RunQuery($sSQL);
                $newRowNum = mysqli_num_rows($rsPropList) + 1;

                // Find the highest existing field number in the group's table to determine the next free one.
                // This is essentially an auto-incrementing system where deleted numbers are not re-used.
                $tableName = 'groupprop_'.$iGroupID;

                $fields = mysqli_query($cnInfoCentral, 'SELECT * FROM '.$tableName);
                $newFieldNum = mysqli_num_fields($fields);

                // If we're inserting a new custom-list type field, create a new list and get its ID
                if ($newFieldType == 12) {
                    // Get the first available lst_ID for insertion.  lst_ID 0-9 are reserved for permanent lists.
                    $sSQL = 'SELECT MAX(lst_ID) FROM list_lst';
                    $aTemp = mysqli_fetch_array(RunQuery($sSQL));
                    if ($aTemp[0] > 9) {
                        $newListID = $aTemp[0] + 1;
                    } else {
                        $newListID = 10;
                    }

                    // Insert into the lists table with an example option.
                    $sSQL = "INSERT INTO list_lst VALUES ($newListID, 1, 1,".gettext("'Default Option'").')';
                    RunQuery($sSQL);

                    $newSpecial = "'$newListID'";
                } else {
                    $newSpecial = 'NULL';
                }

                // Insert into the master table
                $sSQL = "INSERT INTO `groupprop_master`
              ( `grp_ID` , `prop_ID` , `prop_Field` , `prop_Name` , `prop_Description` , `type_ID` , `prop_Special` )
              VALUES ('".$iGroupID."', '".$newRowNum."', 'c".$newFieldNum."', '".$newFieldName."', '".$newFieldDesc."', '".$newFieldType."', $newSpecial);";
                RunQuery($sSQL);

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
                RunQuery($sSQL);

                $bNewNameError = false;
            }
        }
    }

    // Get data for the form as it now exists..
    $sSQL = 'SELECT * FROM groupprop_master WHERE grp_ID = '.$iGroupID.' ORDER BY prop_ID';

    $rsPropList = RunQuery($sSQL);
    $numRows = mysqli_num_rows($rsPropList);

    // Create arrays of the properties.
    for ($row = 1; $row <= $numRows; $row++) {
        $aRow = mysqli_fetch_array($rsPropList, MYSQLI_BOTH);
        extract($aRow);

        // This is probably more clear than using a multi-dimensional array
        $aTypeFields[$row] = $type_ID;
        $aNameFields[$row] = $prop_Name;
        $aDescFields[$row] = $prop_Description;
        $aSpecialFields[$row] = $prop_Special;
        $aFieldFields[$row] = $prop_Field;
        
        if ($type_ID == 9) {
          $aSpecialFields[$row] = $iGroupID;
        }
        
        $aPersonDisplayFields[$row] = ($prop_PersonDisplay == 'true');
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
  <center><h2><?= gettext('No properties have been added yet') ?></h2>
            <a href="GroupView.php?GroupID=<?= $iGroupID ?>" class="btn btn-success"><?= gettext("Return to Group") ?></a>
  </center>
<?php
} else {
        ?>

  <tr><td colspan="7" align="center">
  <?php
    if ($bErrorFlag) {
  ?>
     <p class="alert alert-danger"><span class="fa fa-exclamation-triangle"> <?= gettext("Invalid fields or selections. Changes not saved! Please correct and try again!") ?></span></p>
  <?php
    } 
  ?>
  </td></tr>

    <tr>
      <th></th>
      <th></th>
      <th><?= gettext('Type') ?></th>
      <th><?= gettext('Name') ?></th>
      <th><?= gettext('Description') ?></th>
      <th><?= gettext('Special option') ?></th>
      <th><?= gettext('Show in') ?><br>"<?= gettext('Person Profile') ?>"</th>
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
            <a href="GroupPropsFormRowOps.php?GroupID=<?= $iGroupID ?>&PropID=<?= $row ?>&Field=<?= $aFieldFields[$row] ?>&Action=up"><img src="Images/uparrow.gif" border="0"></a>
        <?php
          }
          if ($row < $numRows) {
        ?>
            <a href="GroupPropsFormRowOps.php?GroupID=<?= $iGroupID ?>&PropID=<?= $row ?>&Field=<?= $aFieldFields[$row] ?>&Action=down"><img src="Images/downarrow.gif" border="0"></a>
        <?php
          } 
        ?>
            <a href="GroupPropsFormRowOps.php?GroupID=<?= $iGroupID ?>&PropID=<?= $row ?>&Field=<?= $aFieldFields[$row] ?>&Action=delete"><img src="Images/x.gif" border="0"></a>            
      </td>
      <td class="TextColumn" style="font-size:70%;">
          <?= $aPropTypes[$aTypeFields[$row]]; ?>
      </td>
      <td class="TextColumn">
         <input type="text" name="<?= $row ?>name" value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" size="25" maxlength="40" class="form-control">
        <?php
          if (array_key_exists($row, $aNameErrors) && $aNameErrors[$row]) {
        ?>
            <span style="color: red;"><BR><?= gettext('You must enter a name') ?> </span>
        <?php
          } 
        ?>
      </td>

      <td class="TextColumn">
         <?php 
            OutputUtils::formCustomField($aTypeFields[$row], $row."desc", htmlentities(stripslashes($aDescFields[$row]), ENT_NOQUOTES, 'UTF-8') , $aSpecialFields[$row], $bFirstPassFlag)
         ?>
      </td>

      <td class="TextColumn">
      <?php

            if ($aTypeFields[$row] == 9) {
      ?>
              <select name="<?= $row ?>special"  class="form-control input-sm">
                <option value="0" selected><?= gettext("Select a group") ?></option>
      <?php
                $sSQL = 'SELECT grp_ID,grp_Name FROM group_grp ORDER BY grp_Name';

                $rsGroupList = RunQuery($sSQL);

                while ($aRow = mysqli_fetch_array($rsGroupList)) {
                    extract($aRow);

                    echo '<option value="'.$grp_ID.'"';
                    if ($aSpecialFields[$row] == $grp_ID) {
                        echo ' selected';
                    }
                    echo '>'.$grp_Name;
                }

                echo '</select>';

                if ($aSpecialErrors[$row]) {
                    echo '<span style="color: red;"><BR>'.gettext('You must select a group.').'</span>';
                }
            } elseif ($aTypeFields[$row] == 12) {
          ?>
                <a class="btn btn-success" href="javascript:void(0)" onClick="Newwin=window.open('OptionManager.php?mode=groupcustom&ListID=<?= $aSpecialFields[$row]?>','Newwin','toolbar=no,status=no,width=400,height=500')"><?= gettext("Edit List Options") ?></a>
          <?php
            } else {
                echo '&nbsp;';
            } ?></td>

      <td class="TextColumn">
        <input type="checkbox" name="<?= $row ?>show" value="1"  <?php if ($aPersonDisplayFields[$row]) { echo ' checked';} ?>>
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
            <a href="GroupView.php?GroupID=<?= $iGroupID ?>" class="btn btn-default"><?= gettext("Return to Group") ?></a>
          </td>
          <td width="40%" align="center" valign="bottom">
            <input type="submit" class="btn btn-primary" value="<?= gettext('Save Changes') ?>" Name="SaveChanges">
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
<div class="box">
<div class="box-header with-border">
  <h3 class="box-title"><?= gettext("Add Group-Specific Properties") ?></h3>
</div>

<table  width="100%" style="border:white">
    <tr><td colspan="7"></td></tr>
    <tr>
      <td colspan="7">
      <table width="100%" style="border-spacing : 10px;border-collapse : separate;">
        <tr>
          <td></td>
          <td><div><?= gettext('Type') ?>:</div></td>
          <td><div><?= gettext('Name') ?>:</div></td>
          <td><div><?= gettext('Description') ?>:</div></td>
          <td></td>
        </tr>
        <tr>
          <td width="15%"></td>
          <td valign="top">
             <select name="newFieldType" class="form-control input-sm">
          <?php
              for ($iOptionID = 1; $iOptionID <= count($aPropTypes); $iOptionID++) {
          ?>
                  <option value="<?= $iOptionID ?>"> <?= $aPropTypes[$iOptionID] ?>
          <?php
              }
          ?>
            </select>
          <BR>
          <a href="<?= SystemURLs::getSupportURL() ?>"><?= gettext('Help on types..') ?></a>
          </td>
          <td valign="top">            
            <input type="text" name="newFieldName" size="25" maxlength="40" class="form-control">
            <?php
              if ($bNewNameError) {
            ?>
                  <div><span style="color: red;"><BR><?= gettext('You must enter a name')?></span></div>
            <?php
              }
              if ($bDuplicateNameError) {
            ?>
                  <div><span style="color: red;"><BR><?= gettext('That field name already exists.')?></span></div>
            <?php
              }
            ?>
            &nbsp;
          </td>
          <td valign="top">            
            <input type="text" name="newFieldDesc" size="30" maxlength="60" class="form-control">
            &nbsp;
          </td>
          <td valign="top">
            <input type="submit" class="btn btn-primary" value="<?= gettext('Add New Field') ?>" Name="AddField">
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

<?php require 'Include/Footer.php' ?>
