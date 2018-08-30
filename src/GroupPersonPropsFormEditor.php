<?php
/*******************************************************************************
 *
 *  filename    : GroupPersonPropsFormEditor.php
 *  last change : 2018-06-09
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2018 Philippe Logel
 *
 *  function    : Editor for group-person-specific properties form
 *
******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;



// Get the Group from the querystring
$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');
$iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');

$person = PersonQuery::Create()->findOneById($iPersonID);

// Security: user must be allowed to edit records to use this page.
if ( !( $_SESSION['user']->isManageGroupsEnabled() || $_SESSION['user']->getPersonId() == $iPersonID ) ) {
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

$sPageTitle = gettext('Group-Specific Properties Form Editor:').'  : "'.$grp_Name.'" '.gettext("for")." : ".$person->getFullName();

require 'Include/Header.php'; ?>

<p class="alert alert-warning"><span class="fa fa-exclamation-triangle"> <?= gettext("Warning: Field changes will be lost if you do not 'Save Changes' before using an up, down, delete, or 'add new' button!") ?></span></p>

<div class="box">
<div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Group-Person-Specific Properties') ?></h3>
</div>

<?php
$bErrorFlag = false;
$aNameErrors = [];
$bNewNameError = false;
$bDuplicateNameError = false;

// Does the user want to save changes to text fields?
if (isset($_POST['SaveChanges'])) {

    // Fill in the other needed property data arrays not gathered from the form submit
    $sSQL = 'SELECT prop_ID, prop_Field, type_ID, prop_Special, prop_PersonDisplay, prop_Name FROM groupprop_master WHERE prop_PersonDisplay = "true" AND grp_ID = '.$iGroupID.' ORDER BY prop_ID';
    $rsPropList = RunQuery($sSQL);
    $numRows = mysqli_num_rows($rsPropList);

    for ($row = 1; $row <= $numRows; $row++) {
        $aRow = mysqli_fetch_array($rsPropList, MYSQLI_BOTH);
        extract($aRow);
        
        $sSQL = 'SELECT * FROM groupprop_'.$iGroupID.' WHERE per_ID = '.$iPersonID;
        $rsPersonProps = RunQuery($sSQL);
        $aPersonProps = mysqli_fetch_array($rsPersonProps, MYSQLI_BOTH);

        $aFieldFields[$row] = $prop_Field;
        $aTypeFields[$row] = $type_ID;
        $aDescFields[$row] = $aPersonProps[$prop_Field];
        $aSpecialFields[$row] = $prop_Special;
        $aPropFields[$row] = $prop_Field;
        $aNameFields[$row] = $prop_Name;
        
        
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
        // We can't update unless values already exist.
        $sSQL = "SELECT * FROM groupprop_".$iGroupID."
                 WHERE `per_ID` = '".$iPersonID."';";
                 
        $bRowExists = true;
        $iNumRows = mysqli_num_rows(RunQuery($sSQL));
        if ($iNumRows == 0) {
            $bRowExists = false;
        }

        if (!$bRowExists) { // If Row does not exist then insert default values.
            // Defaults will be replaced in the following Update                 
            $sSQL = "INSERT INTO groupprop_".$iGroupID." (per_ID) VALUES (".$iPersonID.")";
            $rsResult = RunQuery($sSQL);
        }
        
        for ($iPropID = 1; $iPropID <= $numRows; $iPropID++) {
            if ($aPersonDisplayFields[$iPropID]) {
                $temp = 'true';
            } else {
                $temp = 'false';
            }
            
            if ($aTypeFields[$iPropID] == 2) {            
               $aDescFields[$iPropID] = InputUtils::FilterDate($aDescFields[$iPropID]);
            }
            
            $sSQL = "UPDATE groupprop_".$iGroupID." 
              SET `".$aPropFields[$iPropID]."` = '".$aDescFields[$iPropID]."'
              WHERE `per_ID` = '".$iPersonID."';";
          
            RunQuery($sSQL);
        }
    }
} else {

    // Get data for the form as it now exists..
    $sSQL = 'SELECT * FROM groupprop_master WHERE prop_PersonDisplay = "true" AND grp_ID = '.$iGroupID.' ORDER BY prop_ID';

    $rsPropList = RunQuery($sSQL);
    $numRows = mysqli_num_rows($rsPropList);

    // Create arrays of the properties.
    for ($row = 1; $row <= $numRows; $row++) {
        $aRow = mysqli_fetch_array($rsPropList, MYSQLI_BOTH);
        extract($aRow);
        
        $sSQL = 'SELECT * FROM groupprop_'.$iGroupID.' WHERE per_ID = '.$iPersonID;
        $rsPersonProps = RunQuery($sSQL);
        $aPersonProps = mysqli_fetch_array($rsPersonProps, MYSQLI_BOTH);

        // This is probably more clear than using a multi-dimensional array
        $aTypeFields[$row] = $type_ID;
        $aNameFields[$row] = $prop_Name;
        $aDescFields[$row] = $aPersonProps[$prop_Field];
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

<form method="post" action="GroupPersonPropsFormEditor.php?GroupID=<?= $iGroupID ?>&PersonID=<?= $iPersonID ?>" name="GroupPersonPropsFormEditor">

<center>
<div class="table-responsive">
<table class="table" >

<?php
if ($numRows == 0) {
    ?>
  <center><h2><?= gettext('No properties have been added yet') ?></h2>
      <a href="PersonView.php?PersonID=<?= $iPersonID ?>" class="btn btn-default"><?= gettext("Return to Person") ?></a>
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
      <th><?= gettext('Name') ?></th>
      <th><?= gettext('Description') ?></th>
      <th><?= gettext('Special option') ?></th>
    </tr>

  <?php
  
    for ($row = 1; $row <= $numRows; $row++) {
        ?>
    <tr>
      <td class="LabelColumn"><h2><b><?= $row ?></b></h2></td>
      <td class="TextColumn" width="5%" nowrap></td>
      <td class="TextColumn" style="font-size:70%;">
          <?= $aPropTypes[$aTypeFields[$row]]; ?>
      </td>
      <td class="TextColumn">
         <?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>        
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

    </tr>
  <?php
    } ?>

    <tr>
      <td colspan="7">
      <table width="100%">
        <tr>
          <td width="10%"></td>
          <td width="40%" align="center" valign="bottom">
            <a href="PersonView.php?PersonID=<?= $iPersonID ?>" class="btn btn-default"><?= gettext("Return to Person") ?></a>
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
</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
  $(function() {
    $("[data-mask]").inputmask();
  });
</script>

<?php require 'Include/Footer.php' ?>
