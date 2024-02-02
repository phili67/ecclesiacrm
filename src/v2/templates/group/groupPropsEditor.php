<?php

/*******************************************************************************
 *
 *  filename    : groupPropsEditor.php
 *  last change : 2023-06-08
 *  description : modify de group props
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\GroupPropMasterQuery;

use Propel\Runtime\Propel;

require $sRootDocument . '/Include/Header.php';
?>

<p class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <?= _("Warning: Field changes will be lost if you do not 'Save Changes' before using an up, down, delete, or 'add new' button!") ?></p>

<div class="card">
<div class="card-header border-1">
    <h3 class="card-title"><?= _('Group-Person-Specific Properties') ?></h3>
</div>

<?php
$bErrorFlag = false;
$aNameErrors = [];
$bNewNameError = false;
$bDuplicateNameError = false;

$connection = Propel::getConnection();

// Does the user want to save changes to text fields?
if (isset($_POST['SaveChanges'])) {

    // Fill in the other needed property data arrays not gathered from the form submit
    $propList = GroupPropMasterQuery::Create()
        ->filterByPersonDisplay("true")
        ->filterByGroupId ($iGroupID)
        ->orderByPropId()
        ->find();

    $numRows = $propList->count();

    $sSQL = 'SELECT * FROM groupprop_'.$iGroupID.' WHERE per_ID = '.$iPersonID;
    $statement = $connection->prepare($sSQL);
    $statement->execute();
    $aPersonProps = $statement->fetch(PDO::FETCH_BOTH);// permet de récupérer le tableau associatif

    $row = 1;
    foreach ($propList as $prop) {
      $aFieldFields[$row]   = $prop->getField();
      $aTypeFields[$row]    = $prop->getTypeId();
      $aDescFields[$row]    = $aPersonProps[$prop->getField()];
      $aSpecialFields[$row] = $prop->getSpecial();
      $aPropFields[$row]    = $prop->getField();
      $aNameFields[$row]    = $prop->getName();

      if (!is_null($prop->getSpecial())) {
        if ($prop->getTypeId() == 9) {
          $aSpecialFields[$row] = $group->getID();
        } else {
          $aSpecialFields[$row] = $prop->getSpecial();
        }
      } else {
          $aSpecialFields[$row] = 'NULL';
      }

      $row++;
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

    // If no errors, then update or insert
    if (!$bErrorFlag) {
        // We can't update unless values already exist.
        $sSQL = "SELECT * FROM groupprop_".$iGroupID."
                 WHERE `per_ID` = '".$iPersonID."';";

        $statement = $connection->prepare($sSQL);
        $statement->execute();
        $iNumRows = count($statement->fetchAll(PDO::FETCH_BOTH));

        $bRowExists = true;
        if ($iNumRows == 0) {
            $bRowExists = false;
        }

        if (!$bRowExists) { // If Row does not exist then insert default values.
            // Defaults will be replaced in the following Update
            $sSQL = "INSERT INTO groupprop_".$iGroupID." (per_ID) VALUES (".$iPersonID.")";
            $statement = $connection->prepare($sSQL);
            $statement->execute();
        }

        for ($iPropID = 1; $iPropID <= $numRows; $iPropID++) {
            if ($aPersonDisplayFields[$iPropID]) {
                $temp = 'true';
            } else {
                $temp = 'false';
            }

            if ($aTypeFields[$iPropID] == 1) {
               if (empty($aDescFields[$iPropID])) {
                  $aDescFields[$iPropID] = 'null';
                  $sSQL = "UPDATE groupprop_".$iGroupID."
                    SET `".$aPropFields[$iPropID]."` = null
                    WHERE `per_ID` = '".$iPersonID."';";
               }              
            } else {
              if ($aTypeFields[$iPropID] == 2) {
                 $aDescFields[$iPropID] = InputUtils::FilterDate($aDescFields[$iPropID]);
              }

              $sSQL = "UPDATE groupprop_".$iGroupID."
              SET `".$aPropFields[$iPropID]."` = '".$aDescFields[$iPropID]."'
              WHERE `per_ID` = '".$iPersonID."';";
            }

            

            $statement = $connection->prepare($sSQL);
            $statement->execute();
        }
    }
} else {

    // Get data for the form as it now exists..
    $propList = GroupPropMasterQuery::Create()
        ->filterByPersonDisplay("true")
        ->filterByGroupId ($iGroupID)
        ->orderByPropId()
        ->find();

    $numRows = $propList->count();

    $sSQL = 'SELECT * FROM groupprop_'.$iGroupID.' WHERE per_ID = '.$iPersonID;
    $statement = $connection->prepare($sSQL);
    $statement->execute();
    $aPersonProps = $statement->fetch(PDO::FETCH_BOTH);// permet de récupérer le tableau associatif

    $row = 1;
    foreach ($propList as $prop) {
      $aTypeFields[$row]    = $prop->getTypeId();
      $aNameFields[$row]    = $prop->getName();
      $aDescFields[$row]    = $aPersonProps[$prop->getField()];
      $aSpecialFields[$row] = $prop->getSpecial();
      $aFieldFields[$row]   = $prop->getField();

      if ($prop->getTypeId() == 9) {
        $aSpecialFields[$row] = $iGroupID;
      }

      $aPersonDisplayFields[$row++] = ($prop->getPersonDisplay() == 'true');
    }
}

// Construct the form
?>

<form method="post" action="<?= $sRootPath ?>/v2/group/props/editor/<?= $iGroupID ?>/<?= $iPersonID ?>" name="GroupPersonPropsFormEditor">

<center>
<div class="table-responsive">
<table class="table" >

<?php
if ($numRows == 0) {
    ?>
  <center><h2><?= _('No properties have been added yet') ?></h2>
      <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $iPersonID ?>" class="btn btn-default"><?= _("Return to Person") ?></a>
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
      <th><?= _('Name') ?></th>
      <th><?= _('Description') ?></th>
      <th><?= _('Special option') ?></th>
    </tr>

  <?php

    for ($row = 1; $row <= $numRows; $row++) {
        ?>
    <tr>
      <td class="LabelColumn"><b><?= $row ?></b></td>
      <td class="TextColumn" width="5%" nowrap></td>
      <td class="TextColumn">
          <?= MiscUtils::PropTypes($aTypeFields[$row]) ?>
      </td>
      <td class="TextColumn">
         <?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>
      </td>
      <td class="TextColumn">
         <?= OutputUtils::formCustomField($aTypeFields[$row], $row."desc", htmlentities(stripslashes($aDescFields[$row]), ENT_NOQUOTES, 'UTF-8') , $aSpecialFields[$row]) ?>
      </td>

      <td class="TextColumn">
      <?php

            if ($aTypeFields[$row] == 9) {
      ?>
              <select name="<?= $row ?>special"  class="form-control form-control-sm">
                <option value="0" selected><?= _("Select a group") ?></option>
      <?php
                foreach ($groups as $grp) {
                    echo '<option value="'.$grp->getId().'"';
                    if ($aSpecialFields[$row] == $grp->getId()) {
                        echo ' selected';
                    }
                    echo '>'.$grp->getName();
                }

                echo '</select>';

                if ($aSpecialErrors[$row]) {
                    echo '<span style="color: red;"><BR>'._('You must select a group.').'</span>';
                }
            } elseif ($aTypeFields[$row] == 12) {
          ?>
                <!--<a class="btn btn-success" href="javascript:void(0)" onClick="Newwin=window.open('<?= $sRootPath ?>/OptionManager.php?mode=groupcustom&ListID=<?= $aSpecialFields[$row]?>','Newwin','toolbar=no,status=no,width=400,height=500')"><?= _("Edit List Options") ?></a>-->
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
            <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $iPersonID ?>/Group" class="btn btn-default"><?= _("Return to Person") ?></a>
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
</form>

<script nonce="<?= $CSPNonce ?>" >
  $(function() {
    $("[data-mask]").inputmask();
  });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
