<?php
/*******************************************************************************
 *
 *  filename    : OptionsManager.php
 *  last change : 2003-04-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt
 *                Copyright 2019 Philippe Logel
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
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

$mode = trim($_GET['mode']);

$listID = 0;

$list_type = 'normal';

// Check security for the mode selected.
switch ($mode) {
    case 'famroles':
    case 'classes':
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            RedirectUtils::Redirect('Menu.php');
            exit;
        }
        break;
    case 'grptypes':
    case 'grptypesSundSchool':
        // optionId is used in ListID=3 for both the two lists in the same list !!!!
        // the difference between the grptypes and the grptypesSundSchool is the optionType : 'grptypes' and 'grptypesSundSchool'
        $listID = 3;
        $list_type = ($mode == 'grptypesSundSchool') ? 'sunday_school' : 'normal';
    case 'grproles':// dead code : http://ip/OptionManager.php?mode=grproles&ListID=22
        if (!$listID) {
            $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        }
    case 'groupcustom':
        if (!$listID) {
            $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        }

        $iGroupID = 0;
        $manager = null;

        $grpManager = GroupPropMasterQuery::Create()->findOneBySpecial($listID);

        if ($grpManager != null) {
            $iGroupID = $grpManager->getGroupId();
        }

        if ($iGroupID > 0) {
            $manager = GroupManagerPersonQuery::Create()->filterByPersonID(SessionUser::getUser()->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();
        }

        if (!(SessionUser::getUser()->isManageGroupsEnabled() || !empty($manager))) {
            RedirectUtils::Redirect('Menu.php');
            exit;
        }
        break;

    case 'custom':
    case 'famcustom':
    case 'securitygrp':
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            RedirectUtils::Redirect('Menu.php');
            exit;
        }
        break;

    default:
        RedirectUtils::Redirect('Menu.php');
        break;
}

// Select the proper settings for the editor mode
switch ($mode) {
    case 'famroles':
        //It don't work for postuguese because in it adjective come after noum
        $noun = _('Role');
        //In the same way, the plural isn't only add s
        $adjplusname = _('Family Role');
        $adjplusnameplural = _('Family Roles');
        $sPageTitle = _('Family Roles Editor');
        $listID = 2;
        $embedded = false;
        break;
    case 'classes':
        $noun = _('Classification');
        $adjplusname = _('Person Classification');
        $adjplusnameplural = _('Person Classifications');
        $sPageTitle = _('Person Classifications Editor');
        $listID = 1;
        $embedded = false;
        break;
    case 'grptypesSundSchool':
        $noun = _('Type');
        $adjplusname = _('Sunday School Group Type');
        $adjplusnameplural = _('Sunday School Group Types');
        $sPageTitle = _('Sunday School Group Types Editor');
        $listID = 3;
        $embedded = false;
        break;
    case 'grptypes':
        $noun = _('Type');
        $adjplusname = _('Group Type');
        $adjplusnameplural = _('Group Types');
        $sPageTitle = _('Group Types Editor');
        $listID = 3;
        $embedded = false;
        break;
    case 'securitygrp':
        $noun = _('Group');
        $adjplusname = _('Security Group');
        $adjplusnameplural = _('Security Groups');
        $sPageTitle = _('Security Groups Editor');
        $listID = 5;
        $embedded = false;
        break;
    case 'grproles':// unusefull : dead code : This can be defined in GroupEditor.php?GroupID=id
        $noun = _('Role');
        $adjplusname = _('Group Member Role');
        $adjplusnameplural = _('Group Member Roles');
        $sPageTitle = _('Group Member Roles Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;

        $ormGroupList = GroupQuery::Create()->findOneByRoleListId($listID);
        if (!is_null($ormGroupList)) {
            $iDefaultRole = $ormGroupList->getDefaultRole();
        } else {
            RedirectUtils::Redirect('Menu.php');
            exit;
        }

        break;
    case 'custom':
        $noun = _('Option');
        $adjplusname = _('Person Custom List Option');
        $adjplusnameplural = _('Person Custom List Options');
        $sPageTitle = _('Person Custom List Options Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;

        $per_cus = PersonCustomMasterQuery::Create()->filterByTypeId(12)->findByCustomSpecial($listID);

        if ($per_cus->count() == 0) {
            RedirectUtils::Redirect('Menu.php');
            break;
        }

        break;
    case 'groupcustom':
        $noun = _('Option');
        $adjplusname = _('Custom List Option');
        $adjplusnameplural = _('Custom List Options');
        $sPageTitle = _('Custom List Options Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;

        $group_cus = GroupPropMasterQuery::Create()->filterByTypeId(12)->findBySpecial($listID);

        if ($group_cus->count() == 0) {
            RedirectUtils::Redirect('Menu.php');
            break;
        }

        break;
    case 'famcustom':
        $noun = _('Option');
        $adjplusname = _('Family Custom List Option');
        $adjplusnameplural = _('Family Custom List Options');
        $sPageTitle = _('Family Custom List Options Editor');
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        $embedded = true;

        $fam_cus = FamilyCustomMasterQuery::Create()->filterByTypeId(12)->findByCustomSpecial($listID);

        if ($fam_cus->count() == 0) {
            RedirectUtils::Redirect('Menu.php');
            break;
        }

        break;
    default:
        RedirectUtils::Redirect('Menu.php');
        break;
}

$iNewNameError = 0;


// Check if we're adding a field
if (isset($_POST['AddField'])) {
    $newFieldName = InputUtils::FilterString($_POST['newFieldName']);

    if (strlen($newFieldName) == 0) {
        $iNewNameError = 1;
    } else {
        // Check for a duplicate option name
        $list = ListOptionQuery::Create()->filterByOptionType($list_type)->filterByOptionName($newFieldName)->findById($listID);

        if (!is_null($list) && $list->count() > 0) {
            $iNewNameError = 2;
        } else {
            // Get count of the options
            $list = ListOptionQuery::Create()->filterByOptionType($list_type)->findById($listID);

            $numRows = $list->count();
            $newOptionSequence = $numRows + 1;

            // Get the new OptionID
            $listMax = ListOptionQuery::Create()
                ->addAsColumn('MaxOptionID', 'MAX(' . ListOptionTableMap::COL_LST_OPTIONID . ')')
                ->findOneById($listID);

            // this ensure that the group list and sundaygroup list has ever an unique optionId.
            $max = $listMax->getMaxOptionID();

            $newOptionID = $max + 1;

            // Insert into the appropriate options table
            $lst = new ListOption();

            $lst->setId($listID);
            $lst->setOptionId($newOptionID);
            $lst->setOptionSequence($newOptionSequence);
            $lst->setOptionName($newFieldName);
            $lst->setOptionType($list_type);

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
    ->filterByOptionType($list_type)
    ->orderByOptionSequence()
    ->findById($listID);

$numRows = $ormLists->count();

$aNameErrors = [];

for ($row = 1; $row <= $numRows; $row++) {
    $aNameErrors[$row] = 0;
}

if (isset($_POST['SaveChanges'])) {
    $row = 1;

    foreach ($ormLists as $ormList) {
        $aOldNameFields[$row] = $ormList->getOptionName();
        $aIDs[$row] = $ormList->getOptionId();

        //addition save off sequence also
        $aSeqs[$row] = $ormList->getOptionSequence();

        $aNameFields[$row] = InputUtils::FilterString($_POST[$row . 'name']);

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
                $list = ListOptionQuery::Create()->filterByOptionId($aIDs[$row])->filterByOptionSequence($row)->filterByOptionType($list_type)->findOneById($listID);
                $list->setOptionName($aNameFields[$row]);
                $list->save();
            }
        }
    }
}

// Get data for the form as it now exists..
$ormLists = ListOptionQuery::Create()
    ->filterByOptionType($list_type)
    ->orderByOptionSequence()
    ->findById($listID);

$numRows = $ormLists->count();

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
    //$sPageTitle = $adj . ' ' . $noun . "s "._("Editor");
    include 'Include/Header.php';
}

if ($mode == 'classes') {
    ?>
    <div
        class="alert alert-danger"><?= _('Warning: Removing will reset all assignments for all persons with the assignment!') ?></div>
    <?php
} else if ($mode == 'famroles') {
    ?>
    <div
        class="alert alert-danger"><?= _('Warning: Removing will reset all assignments for all family roles with the assignment!') ?></div>
    <?php
} else if ($mode == 'grptypes' || $mode == 'grptypesSundSchool') {
    ?>
    <div
        class="alert alert-danger"><?= _('Warning: Removing will reset all assignments for all menus with the assignment!') ?></div>
    <?php
} else if ($mode == 'grproles') {//dead code
    ?>
    <div
        class="alert alert-danger"><?= _('Warning: Removing will reset all assignments for all group roles with the assignment!') ?></div>
    <?php
}
?>
<form method="post" action="OptionManager.php?<?= "mode=$mode&ListID=$listID" ?>" name="OptionManager">
<div class="card">
    <div class="card-body">
        <?php
        if ($bErrorFlag) {
        ?>
           <span class="MediumLargeText" style="color: red;">
        <?php
        if ($bDuplicateFound) {
            ?>
            <br><?= _('Error: Duplicate') . ' ' . $adjplusnameplural . ' ' . _('are not allowed.') ?>
            <?php
        }
        ?>
        <br><?= _('Invalid fields or selections. Changes not saved! Please correct and try again!') ?></span><br><br>
        <?php
        }
        ?>

        <br>
        <table cellpadding="3" width="50%" align="center">
            <?php
            for ($row = 1; $row <= $numRows; $row++) {
                $icon=null;
                if ($mode == 'classes') {
                    $icon = ListOptionIconQuery::Create()
                        ->filterByListId(1)
                        ->findOneByListOptionId($aIDs[$row]);
                }
                ?>
                <tr align="center">
                    <td class="LabelColumn">
                        <b>
                            <?php
                            if ($mode == 'grproles' && $aIDs[$row] == $iDefaultRole) {//dead code
                                ?>
                                <?= _('Default') . ' ' ?>
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
                            <img src="<?= SystemURLs::getRootPath() ?>/Images/uparrow.gif" border="0"
                                 class="row-action" data-mode="<?= $mode ?>" data-order="<?= $aSeqs[$row] ?>"
                                 data-listid="<?= $listID ?>" data-id="<?= $aIDs[$row] ?>" data-action="up">
                            <?php
                        }
                        if ($row < $numRows) {
                            ?>
                            <img src="<?= SystemURLs::getRootPath() ?>/Images/downarrow.gif" border="0"
                                 class="row-action" data-mode="<?= $mode ?>" data-order="<?= $aSeqs[$row] ?>"
                                 data-listid="<?= $listID ?>" data-id="<?= $aIDs[$row] ?>" data-action="down">
                            <?php
                        }
                        if ($numRows > 1) {
                            ?>
                            <?php
                            if ($embedded) {
                                ?>
                                <img src="Images/x.gif" border="0" class="row-action" data-mode="<?= $mode ?>"
                                     data-order="<?= $aSeqs[$row] ?>" data-listid="<?= $listID ?>"
                                     data-id="<?= $aIDs[$row] ?>" data-action="delete">
                                <?php
                            } else {
                                ?>
                                <img src="<?= SystemURLs::getRootPath() ?>/Images/x.gif"
                                     class="RemoveClassification" data-mode="<?= $mode ?>"
                                     data-order="<?= $aSeqs[$row] ?>" data-listid="<?= $listID ?>"
                                     data-id="<?= $aIDs[$row] ?>"
                                     data-name="<?= htmlentities(stripslashes($aNameFields[$row])) ?>" border="0">
                                <?php
                            }
                        }
                        ?>
                    </td>
                    <td class="TextColumn">
                        <span class="SmallText">
                            <input class="form-control input-md" type="text" name="<?= $row . 'name' ?>"
                                   value="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>" size="30"
                                   maxlength="40">
                        </span>
                        <?php

                        if ($aNameErrors[$row] == 1) {
                            ?>
                            <span style="color: red;"><BR><?= _('You must enter a name') ?> </span>
                            <?php
                        } elseif ($aNameErrors[$row] == 2) {
                            ?>
                            <span style="color: red;"><BR><?= _('Duplicate name found.') ?> </span>
                            <?php
                        } ?>
                    </td>
                    <?php
                    if ($mode == 'grproles') {//dead code
                        ?>
                        <td class="TextColumn"><input class="btn btn-success btn-xs row-action"
                                                      data-mode="<?= $mode ?>" data-order="<?= $aSeqs[$row] ?>"
                                                      data-listid="<?= $listID ?>" data-id="<?= $aIDs[$row] ?>"
                                                      data-action="makedefault" type="button"
                                                      class="btn btn-default" value="<?= _('Make Default') ?>"
                                                      Name="default">
                        </td>
                        <?php
                    } else if ($mode == 'classes') {
                        if (is_null($icon) || !is_null($icon) && $icon->getUrl() == '') {
                            ?>
                            <td><img src="Images/+.png" border="0" class="AddImage" data-ID="<?= $listID ?>"
                                     data-optionID="<?= $aIDs[$row] ?>"
                                     data-name="<?= htmlentities(stripslashes($aNameFields[$row]), ENT_NOQUOTES, 'UTF-8') ?>">
                            </td>
                            <td></td>
                            <td>&nbsp;</td>
                            <td align="left"><input type="checkbox" class="checkOnlyPersonView"
                                                    data-ID="<?= $listID ?>"
                                                    data-optionID="<?= $aIDs[$row] ?>" <?= ($icon != null && $icon->getOnlyVisiblePersonView()) ? "checked" : "" ?> />
                                <?= _("Visible only in PersonView") ?>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td><img src="Images/x.gif" border="0" class="RemoveImage" data-ID="<?= $listID ?>"
                                     data-optionID="<?= $aIDs[$row] ?>"></td>
                            <td><img src="/skin/icons/markers/<?= $icon->getUrl() ?>" border="0" height="25"></td>
                            <td>&nbsp;</td>
                            <td align="left"><input type="checkbox" class="checkOnlyPersonView"
                                                    data-ID="<?= $listID ?>"
                                                    data-optionID="<?= $aIDs[$row] ?>" <?= ($icon != null && $icon->getOnlyVisiblePersonView()) ? "checked" : "" ?> />
                                <?= _("Visible only in PersonView") ?>
                            </td>
                            <?php
                        }
                    }
                    ?>
                </tr>
                <?php
            } ?>
        </table>
        <br/>
        <input type="submit" class="btn btn-primary" value="<?= _('Save Changes') ?>" Name="SaveChanges">
        <?php if ($mode == 'groupcustom' || $mode == 'custom' || $mode == 'famcustom') {
            ?>
            <input type="button" class="btn btn-default" value="<?= _('Exit') ?>" Name="Exit"
                   onclick="javascript:window.close();">
            <?php
        } elseif ($mode != 'grproles') {// dead code
            ?>
            <input type="button" class="btn btn-default" value="<?= _('Exit') ?>" Name="Exit"
                   onclick="javascript:document.location='<?= 'Menu.php' ?>';">
            <?php
        } ?>
    </div>
</div>

<div class="card card-primary">
    <div class="card-body">
        <?= _('Name for New') . ' ' . $noun ?>:&nbsp;
        <span class="SmallText">
            <input class="form-control form-control input-md" type="text" name="newFieldName" size="30" maxlength="40">
        </span>
        <p></p>
        <input type="submit" class="btn btn-success" value="<?= _('Add New') . ' ' . $adjplusname ?>" Name="AddField">
        <?php
        if ($iNewNameError > 0) {
        ?>
            <div>
                <span style="color: red;">
                    <BR>
                <?php
                    if ($iNewNameError == 1) {
                ?>
                    <?= _('Error: You must enter a name') ?>
                <?php
                    } else {
                ?>
                   <?= _('Error: A ') . $noun . _(' by that name already exists.') ?>
                <?php
                    }
                ?>
                </span>
            </div>
        <?php
        }
        ?>
    </div>
</div>
</form>
<?php
if ($embedded) {
    ?>
    </body></html>
    <?php
} else {
    include 'Include/Footer.php';
}
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/sidebar/IconPicker.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/sidebar/OptionManager.js"></script>
