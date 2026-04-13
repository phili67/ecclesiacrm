<?php

/*******************************************************************************
 *
 *  filename    : groupPropsFormEditor.php.php
 *  last change : 2023-06-10
 *  description : manage the group list
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\GroupPropMaster;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\Map\GroupPropMasterTableMap;
use EcclesiaCRM\ListOption;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\GroupQuery;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemURLs;

use Propel\Runtime\Propel;

require $sRootDocument . '/Include/Header.php';

$bErrorFlag = false;
$aNameErrors = [];
$bNewNameError = false;
$bDuplicateNameError = false;

// Does the user want to save changes to text fields?
if (isset($_POST['SaveChanges'])) {

  // Fill in the other needed property data arrays not gathered from the form submit
  $propList = GroupPropMasterQuery::Create()->filterByGroupId($iGroupID)->orderByPropId()->find();
  $numRows = $propList->count();

  $row = 1;
  foreach ($propList as $prop) {
    $aFieldFields[$row] = $prop->getField();
    $aTypeFields[$row]  = $prop->getTypeId();

    if (!is_null($prop->getSpecial())) {
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
    $aNameFields[$iPropID] = InputUtils::LegacyFilterInput($_POST[$iPropID . 'name']);

    if (strlen($aNameFields[$iPropID]) == 0) {
      $aNameErrors[$iPropID] = true;
      $bErrorFlag = true;
    } else {
      $aNameErrors[$iPropID] = false;
    }

    $aDescFields[$iPropID] = InputUtils::LegacyFilterInput($_POST[$iPropID . 'desc']);

    if (isset($_POST[$iPropID . 'special'])) {
      $aSpecialFields[$iPropID] = InputUtils::LegacyFilterInput($_POST[$iPropID . 'special'], 'int');

      if ($aSpecialFields[$iPropID] == 0) {
        $aSpecialErrors[$iPropID] = true;
        $bErrorFlag = true;
      } else {
        $aSpecialErrors[$iPropID] = false;
      }
    }

    if (isset($_POST[$iPropID . 'show'])) {
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


      $groupMasterUpd = GroupPropMasterQuery::Create()->filterByGroupId($iGroupID)->filterByPropId($iPropID)->findOne();

      $groupMasterUpd->setName($aNameFields[$iPropID]);
      $groupMasterUpd->setDescription($aDescFields[$iPropID]);
      $groupMasterUpd->setSpecial($aSpecialFields[$iPropID]);
      $groupMasterUpd->setPersonDisplay($temp);

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
      $groupMasters = GroupPropMasterQuery::Create()->filterByGroupId($iGroupID)->find();

      foreach ($groupMasters as $groupMaster) {
        if ($groupMaster->getName() == $newFieldName) {
          $bDuplicateNameError = true;
        }
      }

      if (!$bDuplicateNameError) {
        // Get the new prop_ID (highest existing plus one)
        $propLists = GroupPropMasterQuery::Create()->findByGroupId($iGroupID);
        $newRowNum = $propLists->count() + 1;

        // Find the highest existing field number in the group's table to determine the next free one.
        // This is essentially an auto-incrementing system where deleted numbers are not re-used.

        // SELECT CAST(SUBSTR(groupprop_master.prop_Field, 2) as UNSIGNED) AS field, prop_Field FROM groupprop_master WHERE grp_ID=22 order by field
        $lastProps = GroupPropMasterQuery::Create()
          ->withColumn('CAST(SUBSTR(' . GroupPropMasterTableMap::COL_PROP_FIELD . ', 2) as UNSIGNED)', 'field')
          ->addDescendingOrderByColumn('field')
          ->limit(1)
          ->findOneByGroupId($iGroupID);

        $newFieldNum = 1;
        if (!is_null($lastProps)) {
          $newFieldNum = mb_substr($lastProps->getField(), 1) + 1;
        }

        // If we're inserting a new custom-list type field, create a new list and get its ID
        if ($newFieldType == 12) {
          // Get the first available lst_ID for insertion.  lst_ID 0-9 are reserved for permanent lists.
          $listMax = ListOptionQuery::Create()
            ->addAsColumn('MaxOptionID', 'MAX(' . ListOptionTableMap::COL_LST_ID . ')')
            ->findOne();

          // this ensure that the group list and sundaygroup list has ever an unique optionId.
          $max = $listMax->getMaxOptionID();
          if ($max > 9) {
            $newListID = $max + 1;
          } else {
            $newListID = 10;
          }

          // Insert into the lists table with an example option.
          // Insert into the appropriate options table
          $list_type = 'normal'; // this list is only for a group specific properties, so it's ever a normal list 'normal' and not 'sundayschool'

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

        $groupPropMst->setGroupId($iGroupID);
        $groupPropMst->setPropId($newRowNum);
        $groupPropMst->setField("c" . $newFieldNum);
        $groupPropMst->setName($newFieldName);
        $groupPropMst->setDescription($newFieldDesc);
        $groupPropMst->setTypeId($newFieldType);
        $groupPropMst->setSpecial($newSpecial);

        $groupPropMst->save();

        // Insert into the group-specific properties table
        $sSQL = 'ALTER TABLE `groupprop_' . $iGroupID . '` ADD `c' . $newFieldNum . '` ';

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
  $propList = GroupPropMasterQuery::Create()->filterByGroupId($iGroupID)->orderByPropId()->find();
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


<form method="post" action="<?= $sRootPath ?>/v2/group/props/Form/editor/<?= $iGroupID ?>" name="GroupPropFormEditor">
  <div class="row">
    <div class="col-md-12 mb-4">
      <div class="card card-outline card-primary shadow-sm rounded-4">
        <div class="card-header bg-white">
          <h3 class="card-title mb-0"><i class="fas fa-cogs text-primary me-2"></i> <?= _("Fonctions") ?></h3>
        </div>
        <div class="card-body">
          <div class="row align-items-end g-2">
            <div class="col-md-2">
              <label class="fw-bold mb-1" for="newFieldType"><i class="fas fa-list me-1"></i> <?= _('Type') ?></label>
              <select name="newFieldType" id="newFieldType" class="form-control form-control-sm">
                <?php for ($iOptionID = 1; $iOptionID <= MiscUtils::ProTypeCount(); $iOptionID++) { ?>
                  <option value="<?= $iOptionID ?>"> <?= MiscUtils::PropTypes($iOptionID) ?>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="fw-bold mb-1" for="newFieldName"><i class="fas fa-signature me-1"></i> <?= _('Nom') ?></label>
              <input class="form-control form-control-sm" type="text" name="newFieldName" id="newFieldName" maxlength="40">
              <?php if ($bNewNameError): ?>
                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle"></i> <?= _('Vous devez entrer un nom') ?></div>
              <?php endif; ?>
              <?php if ($bDuplicateNameError): ?>
                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle"></i> <?= _('Ce nom de champ existe déjà.') ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="fw-bold mb-1" for="newFieldDesc"><i class="fas fa-align-left me-1"></i> <?= _('Description') ?></label>
              <input class="form-control form-control-sm" type="text" name="newFieldDesc" id="newFieldDesc" maxlength="60">
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-success btn-sm w-100" name="AddField">
                <i class="fas fa-plus-circle me-1"></i> <?= _('Ajouter') ?>
              </button>
            </div>
          </div>
          <div class="row"><div class="col-md-2"><small class="d-block mt-2"><a href="<?= SystemURLs::getSupportURL() ?>"><?= _('Aide sur les types..') ?></a></small></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="alert alert-warning d-flex align-items-center mb-3">
    <span><i class="fa-solid fa-exclamation-triangle fa-2x text-dark"></i></span>
    <div>
      <?= _("Attention : Les modifications de champs seront perdues si vous n'enregistrez pas avant d'utiliser les boutons de déplacement, suppression ou ajout !") ?>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-secondary shadow-sm rounded-4">
        <div class="card-header bg-white">
          <h3 class="card-title mb-0"><i class="fa fa-list text-primary me-2"></i> <?= _("Propriétés spécifiques au groupe") ?></h3>
        </div>
        <div class="card-body">
          <?php if ($bErrorFlag) { ?>
            <div class="alert alert-danger d-flex align-items-center mb-3">
              <i class="fas fa-exclamation-triangle fa-2x text-danger me-3"></i>
              <div>
                <span class="fw-bold text-danger-emphasis"><?= _("Champs ou sélections invalides. Modifications non enregistrées ! Veuillez corriger et réessayer !") ?></span>
              </div>
            </div>
          <?php } ?>
          <div class="table-responsive">
            <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="custom-fields-table" style="width:100%">
              <thead class="table-light">
                <tr>
                  <th><?= _("Place") ?></th>
                  <th><?= _("Actions") ?></th>
                  <th><?= _('Type') ?></th>
                  <th><?= _('Nom') ?></th>
                  <th><?= _('Description') ?></th>
                  <th><?= _('Option spéciale') ?></th>
                  <th><?= _('Afficher dans') ?><br>"<?= _('Profil de la personne') ?>"</th>
                </tr>
              </thead>
              <tbody>
                <?php for ($row = 1; $row <= $numRows; $row++) { ?>
                  <tr>
                    <td>
                      <span class="badge bg-secondary" style="min-width: 24px; padding: 4px 0px;"><?= $row ?></span>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <?php if ($row != 1) { ?>
                          <button type="button" class="up-action btn btn-outline-secondary btn-xs" data-GroupID="<?= $iGroupID ?>" data-PropID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" title="<?= _('Monter') ?>"><i class="fa-solid fa-arrow-up"></i></button>
                        <?php } ?>
                        <?php if ($row < $numRows) { ?>
                          <button type="button" class="down-action btn btn-outline-secondary btn-xs" data-GroupID="<?= $iGroupID ?>" data-PropID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" title="<?= _('Descendre') ?>"><i class="fa-solid fa-arrow-down"></i></button>
                        <?php } ?>
                        <button type="button" class="delete-field btn btn-outline-danger btn-xs" data-GroupID="<?= $iGroupID ?>" data-PropID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" title="<?= _('Supprimer') ?>"><i class="fa fa-trash-can"></i></button>
                      </div>
                    </td>
                    <td>
                      <?= MiscUtils::PropTypes($aTypeFields[$row]) ?>
                    </td>
                    <td>
                      <input class="form-control form-control-sm" type="text" name="<?= $row ?>name" value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" maxlength="40">
                      <?php if (array_key_exists($row, $aNameErrors) && $aNameErrors[$row]) { ?>
                        <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> <?= _('Vous devez entrer un nom') ?></span>
                      <?php } ?>
                    </td>
                    <td>
                      <?= OutputUtils::formCustomField($aTypeFields[$row], $row . "desc", htmlentities(stripslashes($aDescFields[$row]), ENT_NOQUOTES, 'UTF-8'), $aSpecialFields[$row]) ?>
                    </td>
                    <td>
                      <?php if ($aTypeFields[$row] == 9) { ?>
                        <select name="<?= $row ?>special" class="form-control form-control-sm">
                          <option value="0" selected><?= _("Sélectionner un groupe") ?></option>
                          <?php $groupList = GroupQuery::Create()->orderByName()->find();
                          foreach ($groupList as $group) { ?>
                            <option value="<?= $group->getId() ?>" <?= ($aSpecialFields[$row] == $group->getId()) ? ' selected' : '' ?>>
                              <?= $group->getName() ?>
                            <?php } ?>
                        </select>
                        <?php if (!empty($aSpecialErrors[$row])) { ?>
                          <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> <?= _('Vous devez sélectionner un groupe.') ?></span>
                        <?php } ?>
                      <?php } elseif ($aTypeFields[$row] == 12) { ?>
                        <a class="btn btn-outline-success btn-xs" href="javascript:void(0)" onClick="Newwin=window.open('<?= $sRootPath ?>/v2/system/option/manager/groupcustom/<?= $aSpecialFields[$row] ?>','Newwin','toolbar=no,status=no,width=400,height=500')"><i class="fas fa-edit me-1"></i><?= _("Modifier les options de la liste") ?></a>
                      <?php } else { ?>
                        &nbsp;
                      <?php } ?>
                    </td>
                    <td>
                      <input type="checkbox" name="<?= $row ?>show" value="1" <?= ($aPersonDisplayFields[$row]) ? ' checked' : '' ?>>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer text-end">
          <a href="<?= $sRootPath ?>/v2/group/<?= $iGroupID ?>/view" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-arrow-left me-1"></i> <?= _("Retour au groupe") ?></a>
          <button type="submit" class="btn btn-primary btn-sm" name="SaveChanges"><i class="fas fa-check me-1"></i><?= _('Enregistrer les modifications') ?></button>
        </div>
      </div>
    </div>
  </div>
</form>

<script src="<?= $sRootPath ?>/skin/js/group/GroupCustomFieldsEditor.js"></script>


<?php require $sRootDocument . '/Include/Footer.php'; ?>