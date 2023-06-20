<?php
/*******************************************************************************
 *
 *  filename    : templates/personCustomEditor.php
 *  last change : 2023-06-20
 *  description : form to invoke directory report
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
 *  Copyright 2004-2012 Michael Wilt
 *  Copyright 2022-2023 Philippe Logel
 *
 ******************************************************************************/

 use Propel\Runtime\Propel;
 use EcclesiaCRM\Utils\InputUtils;
 use EcclesiaCRM\dto\SystemURLs;
 use EcclesiaCRM\PersonCustomMasterQuery;
 use EcclesiaCRM\PersonCustomMaster;
 use EcclesiaCRM\ListOptionQuery;
 use EcclesiaCRM\ListOption;
 use EcclesiaCRM\GroupQuery;
 use EcclesiaCRM\Map\ListOptionTableMap;
 use EcclesiaCRM\Map\PersonCustomMasterTableMap;
 use EcclesiaCRM\Utils\MiscUtils;
 

require $sRootDocument . '/Include/Header.php';
?>

<div class="alert alert-warning">
    <i class="fas fa-ban"></i>
    <?= _("Warning: Arrow and delete buttons take effect immediately.  Field name changes will be lost if you do not 'Save Changes' before using an up, down, delete or 'add new' button!") ?>
  </div>

<div class="card card-body">

  <?php

  $bErrorFlag = false;
  $bNewNameError = false;
  $bDuplicateNameError = false;
  $aNameErrors = [];

  // Does the user want to save changes to text fields?
  if (isset($_POST['SaveChanges'])) {
      // Fill in the other needed custom field data arrays not gathered from the form submit
      $ormCustomFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();

      $numRows = $ormCustomFields->count();

      $row = 1;

      foreach ($ormCustomFields as $ormCustomField) {
        $aFieldFields[$row] = $ormCustomField->getCustomField();
        $aTypeFields[$row] = $ormCustomField->getTypeId();

        if (!is_null($ormCustomField->getCustomSpecial())) {
            $aSpecialFields[$row] = $ormCustomField->getCustomSpecial();
        } else {
            $aSpecialFields[$row] = 'NULL';
        }
        $row++;
      }

      for ($iFieldID = 1; $iFieldID <= $numRows; $iFieldID++) {
          $aNameFields[$iFieldID] = InputUtils::LegacyFilterInput($_POST[$iFieldID.'name']);

          if (strlen($aNameFields[$iFieldID]) == 0) {
              $aNameErrors[$iFieldID] = true;
              $bErrorFlag = true;
          } else {
              $aNameErrors[$iFieldID] = false;
          }

          $aSideFields[$iFieldID] = $_POST[$iFieldID.'side'];
          $aFieldSecurity[$iFieldID] = $_POST[$iFieldID.'FieldSec'];

          if (isset($_POST[$iFieldID.'special'])) {
              $aSpecialFields[$iFieldID] = InputUtils::LegacyFilterInput($_POST[$iFieldID.'special'], 'int');

              if ($aSpecialFields[$iFieldID] == 0) {
                  $aSpecialErrors[$iFieldID] = true;
                  $bErrorFlag = true;
              } else {
                  $aSpecialErrors[$iFieldID] = false;
              }
          }
      }

      // If no errors, then update.
      if (!$bErrorFlag) {
          for ($iFieldID = 1; $iFieldID <= $numRows; $iFieldID++) {
            if ($aSideFields[$iFieldID] == 0) {
                $temp = 'left';
            } else {
                $temp = 'right';
            }

            $per_cus = PersonCustomMasterQuery::Create()->findOneByCustomField ($aFieldFields[$iFieldID]);

            $per_cus->setCustomName($aNameFields[$iFieldID]);
            $per_cus->setCustomSpecial($aSpecialFields[$iFieldID]);
            $per_cus->setCustomSide($temp);
            $per_cus->setCustomFieldSec($aFieldSecurity[$iFieldID]);
            $per_cus->setCustomComment(' ');

            $per_cus->save();
          }
      }
  } else {
      // Check if we're adding a field
      if (isset($_POST['AddField'])) {
          $newFieldType = InputUtils::LegacyFilterInput($_POST['newFieldType'], 'int');
          $newFieldName = InputUtils::LegacyFilterInput($_POST['newFieldName']);
          $newFieldSide = $_POST['newFieldSide'];
          $newFieldSec = $_POST['newFieldSec'];

          if (strlen($newFieldName) == 0) {
              $bNewNameError = true;
          } elseif (strlen($newFieldType) == 0 || $newFieldType < 1) {
              // This should never happen, but check anyhow.
              // $bNewTypeError = true;
          } else {
            $per_duplicate = PersonCustomMasterQuery::Create()->findOneByCustomName($newFieldName);

            if (!empty($per_duplicate)) {
              $bDuplicateNameError = true;
            }

              if (!$bDuplicateNameError) {
                  // Find the highest existing field number in the group's table to determine the next free one.
                  // This is essentially an auto-incrementing system where deleted numbers are not re-used.

                  // SELECT CAST(SUBSTR(person_custom_master.custom_Field, 2) as UNSIGNED) AS field, custom_Field FROM person_custom_master order by field
                  $lastPerCst = PersonCustomMasterQuery::Create()
                     ->withColumn('CAST(SUBSTR('.PersonCustomMasterTableMap::COL_CUSTOM_FIELD.', 2) as UNSIGNED)', 'field')
                     ->addDescendingOrderByColumn('field')
                     ->limit(1)
                     ->findOne ();

                  $newFieldNum = 1;
                  $last = 0;

                  if ( !is_null($lastPerCst) ) {
                      $newFieldNum = mb_substr($lastPerCst->getCustomField(), 1) + 1;
                      $last = PersonCustomMasterQuery::Create()->orderByCustomOrder('desc')->limit(1)->findOne()->getCustomOrder();
                  }

                  if ($newFieldSide == 0) {
                      $newFieldSide = 'left';
                  } else {
                      $newFieldSide = 'right';
                  }

                  // If we're inserting a new custom-list type field, create a new list and get its ID
                  if ($newFieldType == 12) {
                      // Get the first available lst_ID for insertion.  lst_ID 0-9 are reserved for permanent lists.
                      $listMax = ListOptionQuery::Create()
                            ->addAsColumn('MaxID', 'MAX('.ListOptionTableMap::COL_LST_ID.')')
                            ->findOne();

                    $max = $listMax->getMaxID();

                    if ($max > 9) {
                        $newListID = $max + 1;
                    } else {
                        $newListID = 10;
                    }

                    // Insert into the lists table with an example option.
                    $lst = new ListOption();

                    $lst->setId($newListID);
                    $lst->setOptionId(1);
                    $lst->setOptionSequence(1);
                    $lst->setOptionName(_("Default Option"));

                    $lst->save();

                    $newSpecial = $newListID;
                  } else {
                      $newSpecial = 'NULL';
                  }

                  // Insert into the master table
                  $newOrderID = $last + 1;

                  $per_cus = new PersonCustomMaster();

                  $per_cus->setCustomOrder($newOrderID);
                  $per_cus->setCustomField("c".$newFieldNum);
                  $per_cus->setCustomName($newFieldName);
                  $per_cus->setCustomSpecial($newSpecial);
                  $per_cus->setCustomSide($newFieldSide);
                  $per_cus->setCustomFieldSec($newFieldSec);
                  $per_cus->setCustomComment(' ');
                  $per_cus->setTypeId($newFieldType);

                  $per_cus->save();

                  // this can't be propeled
                  // Insert into the custom fields table
                  $sSQL = 'ALTER TABLE person_custom ADD c'.$newFieldNum.' ';

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
      $ormCustomFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();

      $numRows = $ormCustomFields->count();

      $row = 1;

      // Create arrays of the fields.
      foreach ($ormCustomFields as $ormCustomField) {
          $aNameFields[$row] = $ormCustomField->getCustomName();
          $aSpecialFields[$row] = $ormCustomField->getCustomSpecial();
          $aFieldFields[$row] = $ormCustomField->getCustomField();
          $aTypeFields[$row] = $ormCustomField->getTypeId();
          $aSideFields[$row] = ($ormCustomField->getCustomSide() == 'right');
          $aFieldSecurity[$row] = $ormCustomField->getCustomFieldSec();
          $aNameErrors[$row++] = false;
      }
  }
  // Prepare Security Group list
  $ormSecurityGrps = ListOptionQuery::Create()
            ->orderByOptionSequence()
            ->findById(5);

  $aSecurityGrp = [];
  foreach ($ormSecurityGrps as $ormSecurityGrp) {
    $aSecurityGrp[] = $ormSecurityGrp->toArray();
    $aSecurityType[$ormSecurityGrp->getOptionId()] = $ormSecurityGrp->getOptionName();
  }

  function GetSecurityList($aSecGrp, $fld_name, $currOpt = 'bAll')
  {
      $sOptList = '<select name="'.$fld_name.'" class="form-control form-control-sm">';
      $grp_Count = count($aSecGrp);

      for ($i = 0; $i < $grp_Count; $i++) {
          $aAryRow = $aSecGrp[$i];
          $sOptList .= '<option value="'.$aAryRow['OptionId'].'"';
          if ($aAryRow['OptionName'] == $currOpt) {
              $sOptList .= ' selected';
          }
          $sOptList .= '>'.$aAryRow['OptionName']."</option>\n";
      }
      $sOptList .= '</select>';

      return $sOptList;
  }

  // Construct the form
  ?>

  <form method="post" action="<?= $sRootPath ?>/v2/people/person/customfield/editor" name="PersonCustomFieldsEditor">
  <div class="table-responsive">
    <table class="table">

      <?php
      if ($numRows == 0) {
          ?>
       <h2><?= _('No custom person fields have been added yet') ?></h2>
        <?php
      } else {
          ?>
        <tr>
          <td colspan="6">
            <?php
              if ($bErrorFlag) {
            ?>
                <span class="LargeText" style="color: red;"><BR><?= _('Invalid fields or selections. Changes not saved! Please correct and try again!') ?></span>
            <?php
              }
            ?>
          </td>
        </tr>

        <tr>
          <th></th>
          <th></th>
          <th><?= _('Type') ?></th>
          <th><?= _('Name') ?></th>
          <th><?= _('Special option') ?></th>
          <th><?= _('Security Option') ?></th>
          <th><?= _('Person-View Side') ?></th>
        </tr>

        <?php

        for ($row = 1; $row <= $numRows; $row++) {
            ?>
          <tr>
            <td class="LabelColumn"><b><?= $row ?></b></td>
            <td class="TextColumnFam">
              <?php
              if ($row != 1) {
              ?>
                <img class="up-action" data-OrderID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" src="<?= $sRootPath ?>/Images/uparrow.gif" border="0">
              <?php
              }
            if ($row < $numRows) {
              ?>
                <img class="down-action" data-OrderID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" src="<?= $sRootPath ?>/Images/downarrow.gif" border="0">
            <?php
            } ?>
                <img class="delete-field" data-OrderID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" src="<?= $sRootPath ?>/Images/x.gif" border="0">
            </td>
            <td class="TextColumnFam">
              <?= MiscUtils::PropTypes($aTypeFields[$row]) ?>
            </td>
            <td class="TextColumnFam" >
              <input type="text" name="<?= $row ?>name"
                     value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" size="35"
                     maxlength="40" class= "form-control form-control-sm">
              <?php
                if (array_key_exists($row, $aNameErrors) && $aNameErrors[$row]) {
              ?>
                  <span style="color: red;"><BR><?= _('You must enter a name') ?></span>
              <?php
                }
              ?>
            </td>
            <td class="TextColumnFam" >
              <?php
                if ($aTypeFields[$row] == 9) {
              ?>
                <select name="<?= $row ?>special" class="form-control form-control-sm">
                  <option value="0" selected><?= _("Select a group") ?></option>
              <?php
                  $ormGroupList = GroupQuery::Create()->orderByName()->find();

                  foreach ($ormGroupList as $group) {
              ?>
                     <option value="<?= $group->getId()?>"<?= ($aSpecialFields[$row] == $group->getId())?' selected':''?>><?= $group->getName()?>
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
                  <a href="javascript:void(0)" class="btn btn-success" onClick="Newwin=window.open('v2/system/option/manager/custom/<?= $aSpecialFields[$row]?>','Newwin','toolbar=no,status=no,width=400,height=500')"><?= _('Edit List Options') ?></a>
              <?php
                } else {
              ?>
                  &nbsp;
              <?php
                }
              ?>

            </td>
            <td class="TextColumnFam"  nowrap>
              <?php
                if (isset($aSecurityType[$aFieldSecurity[$row]])) {
              ?>
                  <?= GetSecurityList($aSecurityGrp, $row.'FieldSec', $aSecurityType[$aFieldSecurity[$row]]) ?>
              <?php
              } else {
              ?>
                  <?= GetSecurityList($aSecurityGrp, $row.'FieldSec') ?>
              <?php
              } ?>
            </td>
            <td class="TextColumnFam"  nowrap>
                <input type="radio" Name="<?= $row ?>side" value="0" <?= !$aSideFields[$row] ? ' checked' : ''?>><?= _('Left') ?>
                <input type="radio" Name="<?= $row ?>side" value="1" <?= $aSideFields[$row] ? ' checked' : ''?>><?= _('Right') ?>
            </td>
          </tr>
        <?php
        } ?>

        <tr>
          <td colspan="7">
            <table width="100%">
              <tr>
                <td width="30%"></td>
                <td width="40%"  valign="bottom">
                  <input type="submit" class="btn btn-primary" value="<?= _('Save Changes') ?>"
                         Name="SaveChanges">
                </td>
                <td width="30%"></td>
              </tr>
            </table>
          </td>
          <td>
        </tr>
      <?php
      } ?>
      <tr>
        <td colspan="7">
          <hr>
        </td>
      </tr>
      <tr>
        <td colspan="7">
          <table width="100%">
            <tr>
                <td>
                </td>
                <td class="TextColumnFam">
                  <div><?= _('Type') ?>:</div>
                </td>
                <td class="TextColumnFam">
                  <div><?= _('Name') ?>:</div>
                </td>
                <td class="TextColumnFam">
                    <div><?= _('Side') ?>:</div>
                </td>
                <td nowrap>
                    <div><?= _('Security Option') ?></div>
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
            <tr>
              <td width="15%"></td>
              <td valign="top" class="TextColumnFam">
                 <select name="newFieldType" class="form-control form-control-sm">

              <?php
                for ($iOptionID = 1; $iOptionID <= MiscUtils::ProTypeCount(); $iOptionID++) {
              ?>
                    <option value="<?= $iOptionID ?>"><?= MiscUtils::PropTypes($iOptionID) ?></option>
              <?php
                }
              ?>
                </select>
                <BR>
                <a href="<?= SystemURLs::getSupportURL() ?>"><?= _('Help on types..') ?></a>
              </td>
              <td valign="top">
                <input type="text" name="newFieldName" size="30" maxlength="40" class= "form-control form-control-sm">
                <?php
                if ($bNewNameError) {
                ?>
                    <div><span style="color: red;"><BR><?= _('You must enter a name') ?></span></div>
                <?php
                }
                if ($bDuplicateNameError) {
                ?>
                    <div><span style="color: red;"><BR><?= _('That field name already exists.') ?></span></div>
                <?php
                }
                ?>
                &nbsp;
              </td>
              <td valign="top" nowrap class="TextColumnFam">
                <input type="radio" name="newFieldSide" value="0" checked><?= _('Left') ?>
                <input type="radio" name="newFieldSide" value="1"><?= _('Right') ?>
                &nbsp;
              </td>
              <td valign="top" nowrap class="TextColumnFam">
                <?= GetSecurityList($aSecurityGrp, 'newFieldSec') ?>
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
</div>

<script src="<?= $sRootPath ?>/skin/js/sidebar/PersonCustomFieldsEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


