<?php

/*******************************************************************************
 *
 *  filename    : templates/familyCustomFieldEditor.php
 *  last change : 2023-06-20
 *  copyright   : Copyright 2003 Chris Gebhardt (http://www.openserve.org)
 *  Clone from PersonCustomFieldsEditor.php
 *
 *  function    : Editor for family custom fields
 *
 *  Additional Contributors:
 *  2007 Ed Davis + copyright 2023-06-20 Philippe Logel
 *
 ******************************************************************************/

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMaster;
use EcclesiaCRM\Map\FamilyCustomMasterTableMap;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOption;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Map\ListOptionTableMap;

use EcclesiaCRM\Utils\MiscUtils;


require $sRootDocument . '/Include/Header.php';
?>


<?php

$bNewNameError = false;
$bDuplicateNameError = false;
$bErrorFlag = false;
$aNameErrors = [];

// Does the user want to save changes to text fields?
if (isset($_POST['SaveChanges'])) {
    // Fill in the other needed custom field data arrays not gathered from the form submit
    $ormCustomFields = FamilyCustomMasterQuery::Create()->orderByCustomOrder()->find();

    $numRows = $ormCustomFields->count();

    $row = 1;

    // Create arrays of the fields.
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
        $aNameFields[$iFieldID] = InputUtils::LegacyFilterInput($_POST[$iFieldID . 'name']);

        if (strlen($aNameFields[$iFieldID]) == 0) {
            $aNameErrors[$iFieldID] = true;
            $bErrorFlag = true;
        } else {
            $aNameErrors[$iFieldID] = false;
        }

        $aSideFields[$iFieldID] = $_POST[$iFieldID . 'side'];
        $aFieldSecurity[$iFieldID] = $_POST[$iFieldID . 'FieldSec'];

        if (isset($_POST[$iFieldID . 'special'])) {
            $aSpecialFields[$iFieldID] = InputUtils::LegacyFilterInput($_POST[$iFieldID . 'special'], 'int');

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

            $fam_cus = FamilyCustomMasterQuery::Create()->findOneByCustomField($aFieldFields[$iFieldID]);

            $fam_cus->setCustomName($aNameFields[$iFieldID]);
            $fam_cus->setCustomSpecial($aSpecialFields[$iFieldID]);
            $fam_cus->setCustomSide($temp);
            $fam_cus->setCustomFieldSec($aFieldSecurity[$iFieldID]);
            $fam_cus->setCustomComment(' ');

            $fam_cus->save();
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
            $fam_duplicate = FamilyCustomMasterQuery::Create()->findOneByCustomName($newFieldName);

            if (!empty($fam_duplicate)) {
                $bDuplicateNameError = true;
            }

            if (!$bDuplicateNameError) {
                // Find the highest existing field number in the group's table to determine the next free one.
                // This is essentially an auto-incrementing system where deleted numbers are not re-used.

                // SELECT CAST(SUBSTR(family_custom_master.fam_custom_Field, 2) as UNSIGNED) AS field, fam_custom_Field FROM family_custom_master order by field
                $lastFamCst = FamilyCustomMasterQuery::Create()
                    ->withColumn('CAST(SUBSTR(' . FamilyCustomMasterTableMap::COL_FAM_CUSTOM_FIELD . ', 2) as UNSIGNED)', 'field')
                    ->addDescendingOrderByColumn('field')
                    ->limit(1)
                    ->findOne();

                $newFieldNum = 1;
                $last = 0;

                if (!is_null($lastFamCst)) {
                    $newFieldNum = mb_substr($lastFamCst->getCustomField(), 1) + 1;
                    $last = FamilyCustomMasterQuery::Create()->orderByCustomOrder('desc')->limit(1)->findOne()->getCustomOrder();
                }

                if ($newFieldSide == 0) {
                    $newFieldSide = 'left';
                } else {
                    $newFieldSide = 'right';
                }

                // If we're inserting a new custom-list type field,
                // create a new list and get its ID
                if ($newFieldType == 12) {
                    // Get the first available lst_ID for insertion.
                    // lst_ID 0-9 are reserved for permanent lists.
                    $listMax = ListOptionQuery::Create()
                        ->addAsColumn('MaxID', 'MAX(' . ListOptionTableMap::COL_LST_ID . ')')
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

                $fam_cus = new FamilyCustomMaster();

                $fam_cus->setCustomOrder($newOrderID);
                $fam_cus->setCustomField("c" . $newFieldNum);
                $fam_cus->setCustomName($newFieldName);
                $fam_cus->setCustomSpecial($newSpecial);
                $fam_cus->setCustomSide($newFieldSide);
                $fam_cus->setCustomFieldSec($newFieldSec);
                $fam_cus->setCustomComment(' ');
                $fam_cus->setTypeId($newFieldType);

                $fam_cus->save();

                // this can't be propeled
                // Insert into the custom fields table
                $sSQL = 'ALTER TABLE `family_custom` ADD `c' . $newFieldNum . '` ';

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

    $ormCustomFields = FamilyCustomMasterQuery::Create()->orderByCustomOrder()->find();

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
    $sOptList = '<select name="' . $fld_name . '" class="form-control  form-control-sm">';
    $grp_Count = count($aSecGrp);

    for ($i = 0; $i < $grp_Count; $i++) {
        $aAryRow = $aSecGrp[$i];
        $sOptList .= '<option value="' . $aAryRow['OptionId'] . '"';
        if ($aAryRow['OptionName'] == $currOpt) {
            $sOptList .= ' selected';
        }
        $sOptList .= '>' . $aAryRow['OptionName'] . "</option>\n";
    }
    $sOptList .= '</select>';

    return $sOptList;
}

// Construct the form
?>


<form method="post" action="<?= $sRootPath ?>/v2/people/family/customfield/editor" name="FamilyCustomFieldsEditor">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card card-outline card-primary shadow-sm rounded-4">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-cogs text-primary me-2"></i> <?= _("Functions") ?></h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-md-2">
                            <label class="fw-bold" for="newFieldType"><i class="fas fa-list me-1"></i> <?= _('Type') ?></label>
                            <select name="newFieldType" id="newFieldType" class="form-control form-control-sm">
                                <?php for ($iOptionID = 1; $iOptionID <= MiscUtils::ProTypeCount(); $iOptionID++) { ?>
                                    <option value="<?= $iOptionID ?>"><?= MiscUtils::PropTypes($iOptionID) ?>
                                <?php } ?>
                            </select>
                            <a href="<?= SystemURLs::getSupportURL() ?>" class="small"><i class="fas fa-question-circle"></i> <?= _('Help on types..') ?></a>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold" for="newFieldName"><i class="fas fa-signature me-1"></i> <?= _('Name') ?></label>
                            <input type="text" name="newFieldName" id="newFieldName" maxlength="40" class="form-control form-control-sm">
                            <?php if ($bNewNameError) { ?>
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle"></i> <?= _('You must enter a name') ?></div>
                            <?php } ?>
                            <?php if ($bDuplicateNameError) { ?>
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle"></i> <?= _('That field name already exists.') ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold" for="newFieldSec"><i class="fas fa-shield-alt me-1"></i> <?= _('Security Option') ?></label>
                            <?= GetSecurityList($aSecurityGrp, 'newFieldSec') ?>
                        </div>
                        <div class="col-md-2">
                            <label class="fw-bold"><i class="fas fa-columns me-1"></i> <?= _('Side') ?></label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="newFieldSide" id="sideLeft" value="0" checked>
                                <label class="form-check-label" for="sideLeft"><?= _('Left') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="newFieldSide" id="sideRight" value="1">
                                <label class="form-check-label" for="sideRight"><?= _('Right') ?></label>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-success" name="AddField"><i class="fas fa-plus-circle me-1"></i> <?= _('Add New Field') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="alert alert-warning d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x text-black me-3"></i>
                <div>
                    <span class="fw-bold text-warning-emphasis"><?= _('Warning!') ?></span><br>
                    <span class="text-dark small"><?= _("Arrow and delete buttons take effect immediately. Field name changes will be lost if you do not 'Save Changes' before using an up, down, delete or 'add new' button!") ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-secondary shadow-sm rounded-4">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fa fa-list text-primary me-2"></i> <?= _("Custom fields") ?></h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if ($bErrorFlag) { ?>
                            <h5 class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> <?= _('Invalid fields or selections. Changes not saved! Please correct and try again!') ?></h5>
                        <?php } ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="custom-fields-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th><?= _("Place") ?></th>
                                    <th><?= _("Actions") ?></th>
                                    <th><?= _('Type') ?></th>
                                    <th><?= _('Name') ?></th>
                                    <th><?= _('Special option') ?></th>
                                    <th><?= _('Security Option') ?></th>
                                    <th><?= _('Family-View Side') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($row = 1; $row <= $numRows; $row++) { ?>
                                    <tr>
                                        <td class="LabelColumn">
                                            <span class="badge bg-secondary" style="min-width: 24px; padding: 4px 0px;"><?= $row ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($row > 1) { ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm up-action" data-OrderID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" title="<?= _('Move up') ?>"><i class="fa-solid fa-arrow-up"></i></button>
                                                <?php } ?>
                                                <?php if ($row < $numRows) { ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm down-action" data-OrderID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" title="<?= _('Move down') ?>"><i class="fa-solid fa-arrow-down"></i></button>
                                                <?php } ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete-field" data-OrderID="<?= $row ?>" data-Field="<?= $aFieldFields[$row] ?>" title="<?= _('Delete') ?>"><i class="fa fa-trash-can"></i></button>
                                            </div>
                                        </td>
                                        <td class="TextColumn">
                                            <?= MiscUtils::PropTypes($aTypeFields[$row]) ?>
                                        </td>
                                        <td class="TextColumn">
                                            <input type="text" class="form-control form-control-sm" name="<?= $row . 'name' ?>" value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" maxlength="40">
                                            <?php if ($aNameErrors[$row]) { ?>
                                                <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> <?= _('You must enter a name') ?></span>
                                            <?php } ?>
                                        </td>
                                        <td class="TextColumn">
                                            <?php if ($aTypeFields[$row] == 9) { ?>
                                                <select name="<?= $row ?>special" class="form-control form-control-sm">
                                                    <option value="0" selected><?= _("Select a group") ?></option>
                                                    <?php $ormGroupList = GroupQuery::Create()->orderByName()->find();
                                                    foreach ($ormGroupList as $group) { ?>
                                                        <option value="<?= $group->getId() ?>" <?= ($aSpecialFields[$row] == $group->getId()) ? ' selected' : '' ?>><?= $group->getName() ?>
                                                    <?php } ?>
                                                </select>
                                                <?php if ($aSpecialErrors[$row]) { ?>
                                                    <span class="text-danger small"><i class="fas fa-exclamation-circle"></i> <?= _('You must select a group.') ?></span>
                                                <?php } ?>
                                            <?php } elseif ($aTypeFields[$row] == 12) { ?>
                                                <a class="btn btn-success btn-sm" href="javascript:void(0)" onClick="Newwin=window.open('<?= $sRootPath ?>/v2/system/option/manager/famcustom/<?= $aSpecialFields[$row] ?>','Newwin','toolbar=no,status=no,width=400,height=500,scrollbars=1')"><i class="fas fa-list-ul me-1"></i> <?= _('Edit List Options') ?></a>
                                            <?php } else { ?>
                                                &nbsp;
                                            <?php } ?>
                                        </td>
                                        <td class="TextColumn" nowrap>
                                            <?php if (isset($aSecurityType[$aFieldSecurity[$row]])) { ?>
                                                <?= GetSecurityList($aSecurityGrp, $row . 'FieldSec', $aSecurityType[$aFieldSecurity[$row]]) ?>
                                            <?php } else { ?>
                                                <?= GetSecurityList($aSecurityGrp, $row . 'FieldSec') ?>
                                            <?php } ?>
                                        </td>
                                        <td class="TextColumn" nowrap>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="<?= $row ?>side" id="<?= $row ?>sideLeft" value="0" <?= !$aSideFields[$row] ? ' checked' : '' ?>>
                                                <label class="form-check-label" for="<?= $row ?>sideLeft"><?= _('Left') ?></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="<?= $row ?>side" id="<?= $row ?>sideRight" value="1" <?= $aSideFields[$row] ? ' checked' : '' ?>>
                                                <label class="form-check-label" for="<?= $row ?>sideRight"><?= _('Right') ?></label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary" name="SaveChanges"><i class="fas fa-check me-1"></i> <?= _('Save Changes') ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/sidebar/FamilyCustomFieldsEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>