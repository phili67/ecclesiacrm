<?php
/*******************************************************************************
*
*  filename    : SelectList.php
*  website     : http://www.ecclesiacrm.com
*  copyright   : Copyright 2001-2003 Deane Barker and Chris Gebhardt
*
*  Additional Contributors:
*  2006 Ed Davis
*  2011 Michael Wilt
*  2019 : Copyright Philippe Logel All rights reserved

*  Design notes: this file would benefit from some thoughtful cleanup.  The filter
*  settings are using badly overloaded values, with positive, negative, and not-set
*  all significant.  Originally it relied on the old php behavior of not-set quietly
*  acting as nothing or zero, but the newer version of php does not permit this.
*  Fixing it for the new version of php involved adding a whole lot of calls to
*  isset().
*
******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\GroupQuery;

use Propel\Runtime\Propel;

$iTenThousand = 10000;  // Constant used to offset negative choices in drop down lists

// Create array with Classification Information (lst_ID = 1)
$ormClassifications = ListOptionQuery::create()->filterById(1)->orderByOptionSequence()->find();

unset($aClassificationName);
$aClassificationName[0] = _('Unassigned');
foreach ($ormClassifications as $classification) {
    $aClassificationName[intval($classification->getOptionId())] = $classification->getOptionName();
}

// Create array with Family Role Information (lst_ID = 2)
$ormFamilyRole =  ListOptionQuery::create()->filterById(2)->orderByOptionSequence()->find();

unset($aFamilyRoleName);
$aFamilyRoleName[0] = _('Unassigned');
foreach ($ormFamilyRole as $role) {
    $aFamilyRoleName[intval($role->getOptionId())] = $role->getOptionName();
}

// Create array with Person Property

// Get the total number of Person Properties (p) in table Property_pro
$ormPro = PropertyQuery::create()->orderByProName()->findByProClass('p');

unset($aPersonPropertyName);
$aPersonPropertyName[0] = _('Unassigned');
foreach ($ormPro as $pro) {
    $aPersonPropertyName[intval($pro->getProId())] = $pro->getProName();
}

// Create array with Group Type Information (lst_ID = 3)
$ormGroupTypes =  ListOptionQuery::create()
                        ->filterById(3)
                        ->filterByOptionType(['normal','sunday_school'])
                        ->orderByOptionName()
                        ->find();

unset($aGroupTypes);
foreach ($ormGroupTypes  as $type) {
    $aGroupTypes[intval($type->getOptionId())] = $type->getOptionName();
}

// Filter received user input as needed
if (isset($_GET['Sort'])) {
    $sSort = InputUtils::LegacyFilterInput($_GET['Sort']);
} else {
    $sSort = 'name';
}

$sBlankLine = '';
$sFilter = '';
$sLetter = '';
$sPrevLetter = '';
if (array_key_exists('Filter', $_GET)) {
    $sFilter = InputUtils::LegacyFilterInput($_GET['Filter']);
}
if (array_key_exists('Letter', $_GET)) {
    $sLetter = mb_strtoupper(InputUtils::LegacyFilterInput($_GET['Letter']));
}

if (array_key_exists('mode', $_GET)) {
    $sMode = InputUtils::LegacyFilterInput($_GET['mode']);
} elseif (array_key_exists('SelectListMode', $_SESSION)) {
    $sMode = $_SESSION['SelectListMode'];
}

switch ($sMode) {
    case 'groupassign':
        $_SESSION['SelectListMode'] = $sMode;
        break;
    case 'family':
        $_SESSION['SelectListMode'] = $sMode;
        break;
    default:
        $_SESSION['SelectListMode'] = 'person';
        break;
}

if (array_key_exists('Number', $_GET)) {
    $tmpUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
    $tmpUser->setSearchLimit(InputUtils::LegacyFilterInput($_GET['Number'], 'int'));
    $tmpUser->setSearchfamily($sMode != 'person');
    $tmpUser->save();
    
    $_SESSION['user'] = $tmpUser;
}

if (array_key_exists('PersonColumn3', $_GET)) {
    $_SESSION['sPersonColumn3'] = InputUtils::LegacyFilterInput($_GET['PersonColumn3']);
}

if (array_key_exists('PersonColumn5', $_GET)) {
    $_SESSION['sPersonColumn5'] = InputUtils::LegacyFilterInput($_GET['PersonColumn5']);
}

$iGroupTypeMissing = 0;

$iGroupID = -1;
$iRoleID = -1;
$iClassification = -1;
$iFamilyRole = -1;
$iGender = -1;
$iGroupType = -1;

if ($sMode == 'person') {
    // Set the page title
    $sPageTitle = _('Person Listing');
    $iMode = 1;

    if (array_key_exists('Classification', $_GET) && $_GET['Classification'] != '') {
        $iClassification = InputUtils::LegacyFilterInput($_GET['Classification'], 'int');
    }
    if (array_key_exists('FamilyRole', $_GET) && $_GET['FamilyRole'] != '') {
        $iFamilyRole = InputUtils::LegacyFilterInput($_GET['FamilyRole'], 'int');
    }
    if (array_key_exists('Gender', $_GET) && $_GET['Gender'] != '') {
        $iGender = InputUtils::LegacyFilterInput($_GET['Gender'], 'int');
    }
    if (array_key_exists('PersonProperties', $_GET) && $_GET['PersonProperties'] != '') {
        $iPersonProperty = InputUtils::LegacyFilterInput($_GET['PersonProperties'], 'int');
    }
    if (array_key_exists('grouptype', $_GET) && $_GET['grouptype'] != '') {
        $iGroupType = InputUtils::LegacyFilterInput($_GET['grouptype'], 'int');
        if (array_key_exists('groupid', $_GET)) {
            $iGroupID = InputUtils::LegacyFilterInput($_GET['groupid'], 'int');
            if ($iGroupID == 0) {
                $iGroupID = -1;
            }
        }
        if (array_key_exists('grouproleid', $_GET) && $_GET['grouproleid'] != '') {
            $iRoleID = InputUtils::LegacyFilterInput($_GET['grouproleid'], 'int');
            if ($iRoleID == 0) {
                $iRoleID = -1;
            }
        }
    }
} elseif ($sMode == 'groupassign') {
    $sPageTitle = _('Group Assignment Helper');
    $iMode = 2;

    if (array_key_exists('Classification', $_GET) && $_GET['Classification'] != '') {
        $iClassification = InputUtils::LegacyFilterInput($_GET['Classification'], 'int');
    }
    if (array_key_exists('FamilyRole', $_GET) && $_GET['FamilyRole'] != '') {
        $iFamilyRole = InputUtils::LegacyFilterInput($_GET['FamilyRole'], 'int');
    }
    if (array_key_exists('Gender', $_GET) && $_GET['Gender'] != '') {
        $iGender = InputUtils::LegacyFilterInput($_GET['Gender'], 'int');
    }
    if (array_key_exists('type', $_GET)) {
        $iGroupTypeMissing = InputUtils::LegacyFilterInput($_GET['type'], 'int');
    } else {
        $iGroupTypeMissing = 1;
    }
}

$iPerPage = SessionUser::getUser()->getSearchLimit();

$sLimit5 = '';
$sLimit10 = '';
$sLimit20 = '';
$sLimit25 = '';
$sLimit50 = '';
$sLimit100 = '';
$sLimit200 = '';
$sLimit500 = '';

if ($iPerPage != '5' && $iPerPage != '10' && $iPerPage != '20' && $iPerPage != '25'
    && $iPerPage != '50' && $iPerPage != '100' && $iPerPage != '200' && $iPerPage != '500') {
    $res = intval($iPerPage);
    if ($res < 5) {
        $iPerPage = '5';
    } else if ($res < 10) {
        $iPerPage = '10';
    } else if ($res < 20) {
        $iPerPage = '20';
    } else if ($res < 25) {
        $iPerPage = '25';
    } else if ($res < 50) {
        $iPerPage = '50';
    } else if ($res < 100) {
        $iPerPage = '100';
    } else if ($res < 200) {
        $iPerPage = '200';
    } else if ($res < 500) {
        $iPerPage = '500';
    }

    $tmpUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
    $tmpUser->setSearchLimit($iPerPage);
    $tmpUser->save();

    $_SESSION['user'] = $tmpUser;
}

$connection = Propel::getConnection();

// SQL for group-assignment helper
if ($iMode == 2) {
    $sBaseSQL = 'SELECT *, IF(LENGTH(per_Zip) > 0,per_Zip,fam_Zip) AS zip '.
                'FROM person_per LEFT JOIN family_fam '.
                'ON person_per.per_fam_ID = family_fam.fam_ID ';

    // Find people who are part of a group of the specified type.
    // MySQL doesn't have subqueries until version 4.1.. for now, do it the hard way
    $sSQLsub = 'SELECT per_ID FROM person_per LEFT 
             JOIN person2group2role_p2g2r  ON p2g2r_per_ID = per_ID 
             LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID 
             LEFT JOIN group_type ON grptp_grp_ID=grp_ID
            WHERE grptp_lst_OptionID = ' . $iGroupTypeMissing .' GROUP BY per_ID';
    $pdoSub= $connection->prepare($sSQLsub);
    $pdoSub->execute();

    if ($pdoSub->rowCount() > 0) {
        $sExcludedIDs = '';

        while ($aTemp = $pdoSub->fetch( \PDO::FETCH_BOTH )) {
            $sExcludedIDs .= $aTemp[0].',';
        }

        $sExcludedIDs = mb_substr($sExcludedIDs, 0, -1);
        $sGroupWhereExt = ' AND per_ID NOT IN ('.$sExcludedIDs.')';
    }
} else if ($iMode == 1) {
    // SQL for standard Person List
    // Set the base SQL
    $sBaseSQL = 'SELECT *, IF(LENGTH(per_Zip) > 0,per_Zip,fam_Zip) AS zip '.
                'FROM person_per LEFT JOIN family_fam '.
                'ON per_fam_ID = family_fam.fam_ID ';

    $sGroupWhereExt = ''; // Group Filtering Logic
    $sJoinExt = '';
    if (isset($iGroupType)) {
        if ($iGroupType >= 0) {
            $sJoinExt = ' LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID '.
                        ' LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID ';
            $sGroupWhereExt = ' AND grp_type = '.$iGroupType.' ';

            if ($iGroupID >= 0) {
                if ($iRoleID >= 0) {
                    $sJoinExt = ' LEFT JOIN person2group2role_p2g2r '.
                                ' ON per_ID = p2g2r_per_ID '.
                                ' LEFT JOIN list_lst '.
                                ' ON p2g2r_grp_ID = lst_ID ';
                    $sGroupWhereExt = ' AND p2g2r_grp_ID='.$iGroupID.' '.
                                        ' AND p2g2r_per_ID=per_ID '.
                                        ' AND p2g2r_rle_ID='.$iRoleID.' ';
                } else {
                    $sJoinExt = ' LEFT JOIN person2group2role_p2g2r '.
                                ' ON per_ID = p2g2r_per_ID ';
                    $sGroupWhereExt = ' AND p2g2r_grp_ID='.$iGroupID.' '.
                                        ' AND p2g2r_per_ID = per_ID ';
                }
            } else {
                $sJoinExt = ' LEFT JOIN person2group2role_p2g2r '.
                            ' ON per_ID = p2g2r_per_ID '.
                            ' LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID ';
                $sGroupWhereExt = ' AND grp_type='.$iGroupType.' '.
                                    ' AND per_ID NOT IN '.
                                    ' (SELECT p2g2r_per_ID FROM person2group2role_p2g2r '.
                                    '  WHERE p2g2r_grp_ID='.($iGroupID + $iTenThousand).') ';
            }
        } else {
            $sJoinExt = ' ';
            $sGroupWhereExt = ' AND per_ID NOT IN (SELECT p2g2r_per_ID '.
                                ' FROM person2group2role_p2g2r '.
                                ' LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID '.
                                ' WHERE grp_type = '.($iGroupType + $iTenThousand).')';
        }
    }
}

$sPersonPropertyWhereExt = ''; // Person Property Filtering Logic
$sJoinExt2 = '';
if (isset($iPersonProperty)) {
    if ($iPersonProperty >= 0) {
        $sJoinExt2 = ' LEFT JOIN record2property_r2p ON per_ID = r2p_record_ID '; // per_ID should match the r2p_record_ID
        $sPersonPropertyWhereExt = ' AND r2p_pro_ID = '.$iPersonProperty.' ';
    } else { // >>>> THE SQL CODE BELOW IS NOT TESTED PROPERLY <<<<<
        $sJoinExt2 = ' ';
        $sPersonPropertyWhereExt = ' AND per_ID NOT IN (SELECT r2p_record_ID '.
                            ' FROM record2property_r2p '.
                            ' WHERE r2p_pro_ID = '.($iPersonProperty + $iTenThousand).')';
    }
    $sJoinExt .= $sJoinExt2; // We add our new SQL statement to the JoinExt variable from the group type.
}

$sFilterWhereExt = '';
if (isset($sFilter)) {
    // Check if there's a space
    if (strstr($sFilter, ' ')) {
        // break on the space...
        $aFilter = explode(' ', $sFilter);

        // use the results to check the first and last names
        $sFilterWhereExt = " AND per_FirstName LIKE '%".$aFilter[0]."%' ".
                            " AND per_LastName LIKE '%".$aFilter[1]."%' ";
    } else {
        $sFilterWhereExt = " AND (per_FirstName LIKE '%".$sFilter."%' ".
                            " OR per_LastName LIKE '%".$sFilter."%') ";
    }
}

$sClassificationWhereExt = '';
if ($iClassification >= 0) {
    $sClassificationWhereExt = ' AND per_cls_ID='.$iClassification.' ';
} else {
    $sClassificationWhereExt = ' AND per_cls_ID!='.
                                    ($iClassification + $iTenThousand).' ';
}

$sFamilyRoleWhereExt = '';
if ($iFamilyRole >= 0) {
    $sFamilyRoleWhereExt = ' AND per_fmr_ID='.$iFamilyRole.' ';
} else {
    $sFamilyRoleWhereExt = ' AND per_fmr_ID!='.($iFamilyRole + $iTenThousand).' ';
}

if ($iGender >= 0) {
    $sGenderWhereExt = ' AND per_Gender = '.$iGender;
} else {
    $sGenderWhereExt = '';
}
if (isset($sLetter)) {
    $sLetterWhereExt = " AND per_LastName LIKE '".$sLetter."%'";
} else {
    $sLetterWhereExt = '';
}

$sGroupBySQL = ' GROUP BY per_ID';

if ( SystemConfig::getBooleanValue('bGDPR') ) {
  $activeFamiliesWhereExt = ' AND fam_DateDeactivated is null';
  $activePersonsWhereExt = ' AND per_DateDeactivated is null';
} else {
  $activeFamiliesWhereExt = ' ';
  $activePersonsWhereExt = ' ';
}



$sWhereExt = $sGroupWhereExt . $sFilterWhereExt . $sClassificationWhereExt .
    $sFamilyRoleWhereExt . $sGenderWhereExt . $sLetterWhereExt . $sPersonPropertyWhereExt . $activeFamiliesWhereExt . $activePersonsWhereExt;

$sSQL = $sBaseSQL.$sJoinExt.' WHERE 1'.$sWhereExt.$sGroupBySQL;

// URL to redirect back to this same page
$sRedirect = 'SelectList.php?';
if (array_key_exists('mode', $_GET)) {
    $sRedirect .= 'mode='.$_GET['mode'].'&amp;';
}
if (array_key_exists('type', $_GET)) {
    $sRedirect .= 'type='.$_GET['type'].'&amp;';
}
if (array_key_exists('Filter', $_GET)) {
    $sRedirect .= 'Filter='.$_GET['Filter'].'&amp;';
}
if (array_key_exists('Sort', $_GET)) {
    $sRedirect .= 'Sort='.$_GET['Sort'].'&amp;';
}
if (array_key_exists('Letter', $_GET)) {
    $sRedirect .= 'Letter='.$_GET['Letter'].'&amp;';
}
if (array_key_exists('Classification', $_GET)) {
    $sRedirect .= 'Classification='.$_GET['Classification'].'&amp;';
}
if (array_key_exists('FamilyRole', $_GET)) {
    $sRedirect .= 'FamilyRole='.$_GET['FamilyRole'].'&amp;';
}
if (array_key_exists('Gender', $_GET)) {
    $sRedirect .= 'Gender='.$_GET['Gender'].'&amp;';
}
if (array_key_exists('grouptype', $_GET)) {
    $sRedirect .= 'grouptype='.$_GET['grouptype'].'&amp;';
}
if (array_key_exists('groupid', $_GET)) {
    $sRedirect .= 'groupid='.$_GET['groupid'].'&amp;';
}
if (array_key_exists('grouproleid', $_GET)) {
    $sRedirect .= 'grouproleid='.$_GET['grouproleid'].'&amp;';
}
if (array_key_exists('Number', $_GET)) {
    $sRedirect .= 'Number='.$_GET['Number'].'&amp;';
}
if (array_key_exists('Result_Set', $_GET)) {
    $sRedirect .= 'Result_Set='.$_GET['Result_Set'].'&amp;';
}
if (array_key_exists('PersonProperties', $_GET)) {
    $sRedirect .= 'PersonProperties='.$_GET['PersonProperties'].'&amp;';
}

$sRedirect = mb_substr($sRedirect, 0, -5); // Chop off last &amp;

$statement = $connection->prepare($sSQL);
$statement->execute();

$peopleIDArray = array();
while ($aRow = $statement->fetch( \PDO::FETCH_BOTH )) {
    array_push($peopleIDArray, intval($aRow[0]));
}

// Get the total number of persons
$Total = count($peopleIDArray);

// Select the proper sort SQL
switch ($sSort) {
    case 'family':
            $sOrderSQL = ' ORDER BY fam_Name';
            break;
    case 'zip':
            $sOrderSQL = ' ORDER BY zip, per_LastName, per_FirstName';
            break;
    case 'entered':
            $sOrderSQL = ' ORDER BY per_DateEntered DESC';
            break;
    case 'edited':
            $sOrderSQL = ' ORDER BY per_DateLastEdited DESC';
            break;
    default:
            $sOrderSQL = ' ORDER BY per_LastName, per_FirstName';
            break;
}

if ($iClassification >= 0) {
    $iClassificationStr = $iClassification;
} else {
    $iClassificationStr = '';
}

if ($iFamilyRole >= 0) {
    $iFamilyRoleStr = $iFamilyRole;
} else {
    $iFamilyRoleStr = '';
}

if ($iGender >= 0) {
    $iGenderStr = $iGender;
} else {
    $iGenderStr = '';
}

if ($iGroupType >= 0) {
    $iGroupTypeStr = $iGroupType;
} else {
    $iGroupTypeStr = '';
}

if (isset($iGroupID) && $iGroupID != '') {
    $iGroupIDStr = $iGroupID;
} else {
    $iGroupIDStr = '';
}

if (isset($iRoleID) && $iRoleID != '') {
    $iRoleIDStr = $iRoleID;
} else {
    $iRoleIDStr = '';
}

if (isset($iPersonProperty) && $iPersonProperty != '') {
    $iPersonPropertyStr = $iPersonProperty;
} else {
    $iPersonPropertyStr = '';
}

// Regular PersonList display
$sLimitSQL = '';

// Append a LIMIT clause to the SQL statement
if (empty($_GET['Result_Set'])) {
    $Result_Set = 0;
} else {
    $Result_Set = InputUtils::LegacyFilterInput($_GET['Result_Set'], 'int');
}

$sLimitSQL .= " LIMIT $Result_Set, $iPerPage";

// Run the query with order and limit to get the final result for this list page
$finalSQL = $sSQL.$sOrderSQL.$sLimitSQL;
$pdoPersons = $connection->prepare($finalSQL);
$pdoPersons->execute();

// Run query to get first letters of last name.
$sSQL = 'SELECT DISTINCT LEFT(per_LastName,1) AS letter FROM person_per LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID '.
        $sJoinExt." WHERE 1 $sWhereExt ORDER BY letter";

$pdoLetters= $connection->prepare($sSQL);
$pdoLetters->execute();

require 'Include/Header.php';
?>
<script nonce="<?= SystemURLs::getCSPNonce()?>">
  var listPeople=<?= json_encode($peopleIDArray)?>;
</script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/SelectList.js"></script>


<?php 
  if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
?>
<div class="box box-primary">
    <div class="box-header  with-border">
       <h3 class="box-title">
        <?= _('Filter and Cart') ?>
       </h3>
    </div>
    <div class="box-body">
      <form method="get" action="SelectList.php" name="PersonList">
<?php
if ($iMode == 1) {
?>
      <p align="center">
<?php
} else {
?>
<p align="center" class="MediumText"><?= _('Show people <b>not</b> in this group type:') ?>
  <select name="type" onchange="this.form.submit()" class="form-control-min_width input-sm">
<?php
    $ormGroupTypes =  ListOptionQuery::create()->filterById(3)->find();

    foreach ($ormGroupTypes  as $type) {
?>
      <option value="<?= $type->getOptionId() ?>" <?= ($iGroupTypeMissing == $type->getOptionId())?' selected':'' ?>><?= $type->getOptionName().'&nbsp;' ?>
<?php
    }
?>
  </select>
</p>
<?php
}
?>

      <table align="center">
        <tr>
        <td>
        </td>
        <td align="center">
            <label><?= _('Sort order') ?>:</label>&nbsp;
            <select class="form-control-min_width input-sm" name="Sort" onchange="this.form.submit()">
                <option value="name" <?php if ($sSort == 'name' || empty($sSort)) {
                echo 'selected';
            } ?>><?= _('By Name') ?></option>
                <option value="family" <?php if ($sSort == 'family') {
                echo 'selected';
            } ?>><?= _('By Family') ?></option>
                <option value="zip" <?php if ($sSort == 'zip') {
                echo 'selected';
            } ?>><?= _('By Zip Code') ?></option>
                <option value="entered" <?php if ($sSort == 'entered') {
                echo 'selected';
            } ?>><?= _('By Newest Entries') ?></option>
                <option value="edited" <?php if ($sSort == 'edited') {
                echo 'selected';
            } ?>><?= _('By Recently Edited') ?></option>
            </select>&nbsp;&nbsp;
            <input type="text" name="Filter" class="form-control-min_width" value="<?= $sFilter ?>">
            <input type="hidden" name="mode" value="<?= $sMode ?>">
            <input type="hidden" name="Letter" value="<?= $sLetter ?>">&nbsp;&nbsp;
            <input type="submit" class="btn btn-info btn-sm" value="<?= _('Apply Filter') ?>">
         </td>
         <td>
         </td>
        </tr>
      </table>

      <br>


      <table align="center">
          <tr>
            <td align="center">
            <!-- Gender classification -->
            <select class="form-control-min_width input-sm" name="Gender" onchange="this.form.submit()">
              <option value="" <?= (!isset($iGender))?" selected ":"" ?>> <?= _("Male & Female") ?></option>
              <option value="1" <?= (isset($iGender) && $iGender == 1)?" selected ":"" ?>><?= _("Male") ?></option>
              <option value="2" <?= (isset($iGender) && $iGender == 2)?" selected ":"" ?>> <?= _("Female") ?></option>
            </select>&nbsp;
    
            <!--  ********** -->
            <!--  Classification drop down list -->
            <select class="form-control-min_width input-sm" name="Classification" onchange="this.form.submit()">
              <option value="" <?= ($iClassification >= 0)?' selected ':'' ?>><?= _('All Classifications') ?></option>

            <?php
            foreach ($aClassificationName as $key => $value) {
            ?>
              <option value="<?= $key ?>" <?=($iClassification >= 0 && $iClassification == $key)?' selected ':'' ?>><?= $value ?></option>
            <?php
            }

            foreach ($aClassificationName as $key => $value) {
            ?>
              <option value="<?= ($key - $iTenThousand) ?>" <?= ($iClassification < 0 && $iClassification == ($key - $iTenThousand))?' selected ':''?>>! <?= $value ?></option>
            <?php
            }
            ?>
             </select>&nbsp;

            <!--  ********** -->
            <!-- Family Role Drop Down Box -->

             <select class="form-control-min_width input-sm" name="FamilyRole" onchange="this.form.submit()">
               <option value="" <?= ($iFamilyRole < 0)?' selected ':''?>><?= _('All Family Roles') ?></option>
            <?php

            foreach ($aFamilyRoleName as $key => $value) {
            ?>
               <option value="<?= $key ?>" <?= ($iFamilyRole >= 0 && $iFamilyRole == $key)?' selected ':''?>><?= $value ?></option>
            <?php
            }

            foreach ($aFamilyRoleName as $key => $value) {
            ?>
               <option value="<?= ($key - $iTenThousand) ?>" <?= ($iFamilyRole < 0 && $iFamilyRole == ($key - $iTenThousand))?' selected ':'' ?>>! <?= $value ?></option>
            <?php
            }
            ?>
              </select>&nbsp;

              <!-- Person Property Drop Down Box -->
              <select class="form-control-min_width input-sm" name="PersonProperties" onchange="this.form.submit()">
                <option value="" <?= (!isset($iPersonProperty))?' selected ':'' ?>><?= _("All Contact Properties") ?></option>
       
            <?php
            foreach ($aPersonPropertyName as $key => $value) {
            ?>
                <option value="<?= $key ?>" <?= (isset($iPersonProperty) && ($iPersonProperty == $key))?' selected ':'' ?>><?= $value ?></option>
            <?php
            }

            foreach ($aPersonPropertyName as $key => $value) {
            ?>
                <option value="<?= ($key - $iTenThousand) ?>" <?=(isset($iPersonProperty) && ($iPersonProperty == ($key - $iTenThousand)))?' selected ':''?>>! <?= $value ?></option>
            <?php
            }
            ?>
              </select>&nbsp;


          <!-- grouptype drop down box -->

          <?php
          if ($iMode == 1) {
          ?>
            <select class="form-control-min_width input-sm" name="grouptype" onchange="this.form.submit()">

              <option value="" <?= (!isset($iGroupType))?' selected ':'' ?>><?= _('All Group Types') ?></option>
          <?php
            foreach ($aGroupTypes as $key => $value) {
          ?>
                <option value="<?= $key ?>" <?= (isset($iGroupType) && ($iGroupType == $key))?' selected ':''?>><?= $value ?></option>
          <?php
            }

            foreach ($aGroupTypes as $key => $value) {
            ?>
                <option value="<?= ($key - $iTenThousand) ?>" <?= (isset($iGroupType) && ($iGroupType == ($key - $iTenThousand)))?' selected ':'' ?>>! <?= $value ?></option>
           <?php    
            }
           ?>
            </select>&nbsp;
    
           <?php
            if (isset($iGroupType) && ($iGroupType > -1)) {
                // Create array with Group Information
                $groups=GroupQuery::Create()
                    ->useGroupTypeQuery()
                      ->filterByListOptionId($iGroupType)
                    ->endUse()
                    ->filterByType ([3,4])// normal groups + sunday groups
                    ->orderByName()
                    ->find();

                $aGroupNames = [];
                foreach ($groups as $group) {
                    $aGroupNames[intval($group->getId())] = $group->getName();
                }
           ?>
            <select class="form-control-min_width input-sm" name="groupid" onchange="this.form.submit()">
                <option value="" <?= (!isset($iGroupType))?' selected ':'' ?>><?= _('All Groups') ?></option>

          <?php
            foreach ($aGroupNames as $key => $value) {
          ?>
                <option value="<?= $key ?>" <?= (isset($iGroupType) && ($iGroupID == $key))?' selected ':'' ?>><?= $value ?></option>
          <?php      
            }

            foreach ($aGroupNames as $key => $value) {
              ?>    
                <option value="<?= ($key - $iTenThousand) ?>" <?= (isset($iGroupType) && ($iGroupID == ($key - $iTenThousand)))?' selected ':'' ?>>! <?= $value ?></option>
              <?php
                }
          ?>
              </select>&nbsp;
          <?php
            }
          ?>
            <!-- ********* -->
            <!-- Create Group Role drop down box -->
          <?php
            if (isset($iGroupID) && ($iGroupID > -1)) {

                // Get the group's role list ID
                $grp = GroupQuery::create()->findOneById($iGroupID);

                if (!is_null ($grp)) {
                    $iRoleListID  = $grp->getRoleListId();
                }

                // Get the roles
                $ormRoles = ListOptionQuery::create()->filterById($iRoleListID)->orderByOptionSequence()->find();

                unset($aGroupRoles);
                foreach ($ormRoles as $role) {
                    $aGroupRoles[intval($role->getOptionId())] = $role->getOptionName();
                }
             ?>
              <select class="form-control-min_width input-sm" name="grouproleid" onchange="this.form.submit()" >
                  <option value="" <?= ($iRoleID < 0)?' selected ':'' ?>><?= _("All Roles") ?></option>
              <?php
                foreach ($aGroupRoles as $key => $value) {
              ?>
                  <option value="<?= $key ?>" <?= ($iRoleID >= 0 && ($iRoleID == $key))?' selected ':'' ?>><?= _($value) ?></option>
              <?php
                }
              ?>
              </select>&nbsp;
          <?php      
            }
          } 
        ?>

        <input type="button" class="btn btn-info btn-sm" value="<?= _('Clear Filters') ?>" onclick="javascript:document.location='SelectList.php?mode=<?= $sMode ?>&amp;Sort=<?= $sSort ?>&amp;type=<?= $iGroupTypeMissing ?>'"><BR><BR>

        <?php
        if ( SessionUser::getUser()->isShowCartEnabled() ) {
        ?>
            <a id="AddAllPageToCart" class="btn btn-primary btn-sm" ><?= _('Add This Page to Cart') ?></a>
            <a id="RemoveAllPageFromCart" class="btn btn-danger btn-sm" ><?= _('Remove This Page from Cart') ?></a><br><br>
            <a id="AddAllToCart" class="btn btn-primary btn-sm" ><?= _('Add All to Cart') ?></a>
            <input name="IntersectCart" type="submit" class="btn btn-warning btn-sm" value="<?= _('Intersect with Cart') ?>">&nbsp;
            <a id="RemoveAllFromCart" class="btn btn-danger btn-sm" ><?= _('Remove All from Cart') ?></a>
        <?php
        }
        ?>
        </td>
      </tr>
    </table>
    </form>
  </div>
</div>

<?php
}
?>


<div class="box box-info">
  <div class="box-header with-border">
     <h3 class="box-title">
        <?= _('Listing')?>
     </h3>
    </div>
  <div class="box-body">
<?php
// Display record count
if ($Total == 1) {
    echo '<p align = "center"><b>' . $Total . " "._("record returned") . '</b></p>';
} else {
    echo '<p align = "center"><b>' . $Total . " "._("records returned") . '</b></p>';
}
// Create Sort Links
?>
<div align="center" style="margin-top: -25px;">
  <ul class="pagination pagination-sm">
    <li class="<?= empty($sLetter)?"active":"" ?>">
      <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr?>&amp;grouproleid=<?= $iRoleIDStr ?>&amp;PersonProperties=<?= $iPersonPropertyStr.(($sSort)?"&amp;Sort=$sSort":"") ?>"><?= _('View All') ?></a>
    </li>

    <?php
    while ($aLetter = $pdoLetters->fetch( \PDO::FETCH_BOTH )) {
        $aLetter[0] = mb_strtoupper($aLetter[0]);
        if ($aLetter[0] == $sLetter) {
        ?>
            <li class='active'>
               <a href='#'><?= $aLetter[0] ?></a>
            </li>
        <?php
        } else {
        ?>
            <li>
              <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr ?>&amp;grouproleid=<?= $iRoleIDStr.(($sSort)?"&amp;Sort=$sSort":"")."&amp;Letter=".$aLetter[0] ?>"><?= $aLetter[0] ?></a>
            </li>
        <?php
        }
    }
    ?>
  </ul>
</div>

<div align="center">
  <form method="get" action="SelectList.php" name="ListNumber">
    <table align="center">
      <tr>
        <td align="center">
          <ul class="pagination pagination-sm">
        <?php
        // Create Next / Prev Links and $Result_Set Value
        if ($Total > 0) {
        ?>

        <?php
            // Show previous-page link unless we're at the first page
            if ($Result_Set < $Total && $Result_Set > 0) {
                $thisLinkResult = $Result_Set - $iPerPage;
        ?>
           <li>
                <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassification ?>&amp;FamilyRole=<?= $iFamilyRole ?>&amp;Gender=<?= $iGender ?>&amp;grouptype=<?= $iGroupType ?>&amp;groupid=<?= $iGroupID ?>&amp;grouproleid=<?= $iRoleID ?>"><?= _('Previous Page') ?></a>
           </li> 
        <?php
            }

            // Calculate starting and ending Page-Number Links
            $Pages = ceil($Total / $iPerPage);
            $startpage = (ceil($Result_Set / $iPerPage)) - 6;
            if ($startpage <= 2) {
                $startpage = 1;
            }
            $endpage = (ceil($Result_Set / $iPerPage)) + 9;
            if ($endpage >= ($Pages - 1)) {
                $endpage = $Pages;
            }

            // Show Link "1 ..." if startpage does not start at 1
            if ($startpage != 1) {
            ?>
               <li>
                  <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?Result_Set=0&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassification ?>&amp;FamilyRole=<?= $iFamilyRole ?>&amp;Gender=<?= $iGender ?>&amp;grouptype=<?= $iGroupType ?>&amp;groupid=<?= $iGroupID ?>&amp;grouproleid=<?= $iRoleID ?>">1 ...</a>
               </li>
            <?php
            }

            // Display page links
            if ($Pages > 1) {
                for ($c = $startpage; $c <= $endpage; $c++) {
                    $b = $c - 1;
                    $thisLinkResult = $iPerPage * $b;
                    if ($thisLinkResult != $Result_Set) {
                    ?>
                      <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode?> &amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr?>&amp;grouproleid=<?= $iRoleIDStr?>"><?= $c ?></a>
                      </li>
                    <?php
                    } else {
                    ?>
                       <li class="active">
                        <a href="#"><?= $c ?></a>
                       </li>
                    <?php
                    }
                }
            }

            // Show Link "... xx" if endpage is not the maximum number of pages
            if ($endpage != $Pages) {
                $thisLinkResult = ($Pages - 1) * $iPerPage;
                ?>
                <li>
                  <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassification ?>&amp;FamilyRole=<?= $iFamilyRole ?>&amp;Gender=<?= $iGender ?>&amp;grouptype=<?= $iGroupType ?>&amp;groupid=<?= $iGroupID ?>&amp;grouproleid=<?= $iRoleID ?>">... <?= $Pages ?></a>
                </li>
            <?php      
            }
            // Show next-page link unless we're at the last page
            if ($Result_Set >= 0 && $Result_Set < $Total) {
                $thisLinkResult = $Result_Set + $iPerPage;
                if ($thisLinkResult < $Total) {
                ?>
                  <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr ?>&amp;grouproleid=<?= $iRoleIDStr ?>"><?= _('Next Page') ?></a>          
                  </li>
                <?php
                }
            }
          }          
        ?>
        </ul>
        </td>
        <td>
          &nbsp;&nbsp;&nbsp;
        </td>
        <td align="center">
        &nbsp;
        <input type="hidden" name="mode" value="<?= $sMode ?>">
            <?php
            if ($iGroupTypeMissing > 0) {
            ?>
                <input type="hidden" name="type" value="<?= $iGroupTypeMissing ?>">
            <?php
            }
            if (isset($sFilter)) {
            ?>
                <input type="hidden" name="Filter" value="<?= $sFilter ?>">
            <?php
            }
            if (isset($sSort)) {
            ?>
                <input type="hidden" name="Sort" value="<?= $sSort ?>">
            <?php
            }
            if (isset($sLetter)) {
            ?>
                <input type="hidden" name="Letter" value="<?= $sLetter ?>">
            <?php
            }
            if ($iClassification >= 0) {
            ?>
                <input type="hidden" name="Classification" value="<?= $iClassification ?>">
            <?php
            }
            if ($iFamilyRole >= 0) {
            ?>
                <input type="hidden" name="FamilyRole" value="<?= $iFamilyRole ?>">
            <?php
            }
            if ($iGender >= 0) {
            ?>
                <input type="hidden" name="Gender" value="<?= $iGender ?>">
            <?php
            }
            if ($iGroupType >= 0) {
            ?>
                <input type="hidden" name="grouptype" value="<?= $iGroupType ?>">
            <?php
            }
            if (isset($iPersonProperty)) {
            ?>
                <input type="hidden" name="PersonProperties" value="<?= $iPersonProperty ?>">
            <?php
            }
            if (isset($iGroupID)) {
            ?>
                <input type="hidden" name="groupid" value="<?= $iGroupID ?>">
            <?php
            }

            // Display record limit per page
            if ($iPerPage == '5') {
                $sLimit5 = 'selected';
            }
            if ($iPerPage == '10') {
                $sLimit10 = 'selected';
            }
            if ($iPerPage == '20') {
                $sLimit20 = 'selected';
            }
            if ($iPerPage == '25') {
                $sLimit25 = 'selected';
            }
            if ($iPerPage == '50') {
                $sLimit50 = 'selected';
            }
            if ($iPerPage == '100') {
                $sLimit100 = 'selected';
            }
            if ($iPerPage == '200') {
                $sLimit200 = 'selected';
            }
            if ($iPerPage == '500') {
                $sLimit500 = 'selected';
            }

        ?>

        &nbsp;
          <b><?= _('Display:') ?></b>
        &nbsp;
          <select class="form-control-min_width input-sm" name="Number" onchange="this.form.submit()">
              <option value="5" <?= $sLimit5 ?>>5</option>
              <option value="10" <?= $sLimit10 ?>>10</option>
              <option value="20" <?= $sLimit20 ?>>20</option>
              <option value="25" <?= $sLimit25 ?>>25</option>
              <option value="50" <?= $sLimit50 ?>>50</option>
              <option value="100" <?= $sLimit100 ?>>100</option>
              <option value="200" <?= $sLimit200 ?>>200</option>
              <option value="500" <?= $sLimit500 ?>>500</option>
          </select>&nbsp;
      </td>
    </tr>
  </table>
</form>

<?php

// At this point we have finished the forms at the top of SelectList.
// Now begin the table displaying results.

// Read if sort by person is selected columns 3 and 5 are user selectable.  If the
// user has not selected a value then read from session variable.
if (!isset($sPersonColumn3)) {
    if (array_key_exists('sPersonColumn3', $_SESSION)) {
        switch ($_SESSION['sPersonColumn3']) {
        case 'Family Role':
            $sPersonColumn3 = 'Family Role';
            break;
        case 'Gender':
            $sPersonColumn3 = 'Gender';
            break;
        default:
            $sPersonColumn3 = 'Classification';
        break;
        }
    } else {
        $sPersonColumn3 = 'Classification';
    }
}

if (!isset($sPersonColumn5)) {
    if (array_key_exists('sPersonColumn5', $_SESSION)) {
      switch ($_SESSION['sPersonColumn5']) {
        case 'Home Phone':
            $sPersonColumn5 = 'Home Phone';
            break;
        case 'Work Phone':
            $sPersonColumn5 = 'Work Phone';
            break;
        case 'Mobile Phone':
            $sPersonColumn5 = 'Mobile Phone';
        break;
        default:
            $sPersonColumn5 = 'Zip Code';
        break;
        }
    } else {
        $sPersonColumn5 = 'Zip Code';
    }
}

?>

  <form method="get" action="SelectList.php" name="ColumnOptions">
    <div class="table-responsive">
       <table class="table table-striped table-bordered dataTable no-footer dtr-inline" cellpadding="4" align="center" cellspacing="0" width="100%">

  <tr>
  <th><?= _('Photo') ?></th>
  <th>
        <a class="btn btn-<?= ($sSort == "name")?"info active":"default" ?> btn-sm" href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Sort=name&amp;Filter=<?= $sFilter ?>"><?= _('Name') ?></a>
  </th>

  <th><input type="hidden" name="mode" value="<?= $sMode ?>">

  <?php
  if ($iGroupTypeMissing > 0) {
  ?>
      <input type="hidden" name="type" value="<?= $iGroupTypeMissing ?>">
  <?php
  }
  if (isset($sFilter)) {
  ?>
      <input type="hidden" name="Filter" value="<?= $sFilter ?>">
  <?php
  }
  if (isset($sSort)) {
  ?>
      <input type="hidden" name="Sort" value="<?= $sSort ?>">
  <?php
  }
  if (isset($sLetter)) {
  ?>
      <input type="hidden" name="Letter" value="<?= $sLetter ?>">
  <?php
  }
  if ($iClassification >= 0) {
  ?>
      <input type="hidden" name="Classification" value="<?= $iClassification ?>">
  <?php
  }
  if ($iFamilyRole >= 0) {
  ?>
      <input type="hidden" name="FamilyRole" value="<?= $iFamilyRole ?>">
  <?php
  }
  if ($iGender >= 0) {
  ?>
      <input type="hidden" name="Gender" value="<?= $iGender ?>">
  <?php
  }
  if (isset($iPersonProperty)) {
  ?>
      <input type="hidden" name="PersonProperties" value="<?= $iPersonProperty ?>">
  <?php
  }
  if ($iGroupType >= 0) {
  ?>
      <input type="hidden" name="grouptype" value="<?= $iGroupType ?>">
  <?php
  }
  if (isset($iGroupID)) {
  ?>
      <input type="hidden" name="groupid" value="<?= $iGroupID ?>">
  <?php
  }

  ?>
  <select class="form-control-min_width input-sm" name="PersonColumn3" onchange="this.form.submit()">';
      <?php
      $aPersonCol3 = ['Classification', 'Family Role', 'Gender'];
      foreach ($aPersonCol3 as $s) {
          $sel = '';
          if ($sPersonColumn3 == $s) {
              $sel = ' selected';
          }
          echo '<option value="'.$s.'"'.$sel.'>'._($s).'</option>';
      }
      ?>
      </select>
  </th>
  <th>
     <a class="btn btn-<?= ($sSort == "family")?"info active":"default" ?> btn-sm " href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=<?= $sMode ?>&amp;type=<?=$iGroupTypeMissing ?>&amp;Sort=family&amp;Filter=<?= $sFilter ?>"><?= _('Family') ?></a>
  </th>
  <th>
   <select class="form-control-min_width input-sm" name="PersonColumn5" onchange="this.form.submit()">
    <?php
    $aPersonCol5 = ['Home Phone', 'Work Phone', 'Mobile Phone', 'Zip Code'];
    foreach ($aPersonCol5 as $s) {
        $sel = '';
        if ($sPersonColumn5 == $s) {
            $sel = ' selected';
        }
        echo '<option value="'.$s.'"'.$sel.'>'._($s).'</option>';
    }
    ?>
    </select>
  </th>
  <th>
  <?php
  if (SessionUser::getUser()->isEditRecordsEnabled()) {
      echo _('Edit');
  }
  ?>
  </th>
  <th><?= _('Cart') ?></th>
  <?php
  if ($iMode == 1) {
  ?>
      <th><?= _('Print') ?></th>
  <?php
  } else {
  ?>
      <th><?= _('Assign') ?></th>
  <?php
  }

  // Table for results begins here
  //echo '</tr><tr><td>&nbsp;</td></tr>';

  $sRowClass = 'RowColorA';

  $iPrevFamily = -1;
  
  if ($Total == 0) {
  ?>
    <tr>
      <td class="ControlBreak" colspan=8>
        <?= _("Not Available") ?>
      </td>
    </tr>
  <?php
  }

  //Loop through the person recordset
  while ($aRow = $pdoPersons->fetch( \PDO::FETCH_BOTH )) {
      $per_Title = '';
      $per_FirstName = '';
      $per_MiddleName = '';
      $per_LastName = '';
      $per_Suffix = '';
      $per_Gender = '';

      $fam_Name = '';
      $fam_Address1 = '';
      $fam_Address2 = '';
      $fam_City = '';
      $fam_State = '';

      extract($aRow);

      // Add alphabetical headers based on sort
      //$sBlankLine = '<tr><td>&nbsp;</td></tr>';
      switch ($sSort) {
      case 'family':
          if ($fam_ID != $iPrevFamily || $iPrevFamily == -1) {
              echo $sBlankLine;
              echo '<tr><td></td><td class="ControlBreak" colspan=7><b>';

              if (isset($fam_Name)) {
                  echo $fam_Name;
              } else {
                  echo _('Unassigned');
              }

              echo '</b></td></tr>';
              $sRowClass = 'RowColorA';
          }
          break;

      case 'name':
          if (mb_strtoupper(mb_substr($per_LastName, 0, 1, 'UTF-8')) != $sPrevLetter) {
              echo $sBlankLine;
              echo '<tr><td></td>';
              echo '<td class="ControlBreak" colspan=7><b>'.mb_strtoupper(mb_substr($per_LastName, 0, 1, 'UTF-8'));
              echo '</b></td></tr>';
              $sRowClass = 'RowColorA';
          }
          break;

      default:
          break;
      } // end switch

      //Alternate the row color
      $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

      //Display the row
      echo '<tr class="'.$sRowClass.'">'; ?>
    </td>
      <td><img src="<?= SystemURLs::getRootPath(); ?>/api/persons/<?= $per_ID ?>/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px" /> </td>
    <td>
        <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $per_ID ?>" >
          <?= OutputUtils::FormatFullName($per_Title, $per_FirstName, $per_MiddleName, $per_LastName, $per_Suffix, 3) ?>
        </a>
      </td>
      <td>
      <?php
      if ($sPersonColumn3 == 'Classification') {
          echo $aClassificationName[$per_cls_ID];
      } elseif ($sPersonColumn3 == 'Family Role') {
          echo $aFamilyRoleName[$per_fmr_ID];
      } else {    // Display Gender
          switch ($per_Gender) {
            case 1: echo _('Male'); 
              break;
            case 2: echo _('Female'); 
              break;
            default: echo '';
          }
      }
      echo '&nbsp;</td>';

      echo '<td>';
    
      if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
    
        if ($fam_Name != '') {
            echo '<a href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID='.$fam_ID.'">'.$fam_Name;
            echo MiscUtils::FormatAddressLine($fam_Address1, $fam_City, $fam_State).'</a>';
        }
        echo '&nbsp;</td>';
      } else {
        echo _('Private Data');
      }
    
      echo '<td>';
      if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
        // Phone number or zip code
        if ($sPersonColumn5 == 'Home Phone') {
            echo MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($fam_HomePhone, $fam_Country, $dummy),
                    MiscUtils::ExpandPhoneNumber($per_HomePhone, $fam_Country, $dummy), true);
        } elseif ($sPersonColumn5 == 'Work Phone') {
            echo MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($per_WorkPhone, $fam_Country, $dummy),
                    MiscUtils::ExpandPhoneNumber($fam_WorkPhone, $fam_Country, $dummy), true);
        } elseif ($sPersonColumn5 == 'Mobile Phone') {
            echo MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($per_CellPhone, $fam_Country, $dummy),
                    MiscUtils::ExpandPhoneNumber($fam_CellPhone, $fam_Country, $dummy), true);
        } else {
            if (isset($zip)) {
                echo $zip;
            } else {
                echo _('Unassigned');
            }
        } 
      } else {
        echo _('Private Data');
      }
      ?>
    </td>
      <td>
    <?php 
      if (SessionUser::getUser()->isEditRecordsEnabled()) {
    ?>
        <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?PersonID=<?= $per_ID ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?= _('Edit') ?>">
          <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
          </span>
        </a>
    <?php
      } 
    ?>
      </td>
    <td>
    <?php 
      if (!isset($_SESSION['aPeopleCart']) || !in_array($per_ID, $_SESSION['aPeopleCart'], false)) {
        if (SessionUser::getUser()->isShowCartEnabled()) {
    ?>
        <a class="AddToPeopleCart" data-cartpersonid="<?= $per_ID ?>" data-toggle="tooltip" title="" data-placement="top" data-original-title="<?= _('Add to Cart') ?>">
      <?php
        }
      ?>
      
      <span class="fa-stack">
          <i class="fa fa-square fa-stack-2x"></i>
          <i class="fa fa-stack-1x fa-inverse <?= (SessionUser::getUser()->isShowCartEnabled())?'fa-cart-plus':'fa-question' ?>"></i>
      </span>
      <?php
        if (SessionUser::getUser()->isShowCartEnabled()) {
      ?>
          </a>
      <?php
        }
      ?>      
      </td>
    <?php
      } else {
          ?>
      <?php
        if (SessionUser::getUser()->isShowCartEnabled()) {
      ?>
      <a class="RemoveFromPeopleCart" data-cartpersonid="<?= $per_ID ?>">
      <?php
        }
      ?>          
      <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-remove fa-stack-1x fa-inverse"></i>
              </span>
      <?php
        if (SessionUser::getUser()->isShowCartEnabled()) {
      ?>
          </a>
      <?php
        }
      ?>          
    
    <?php
      }
    ?>
    <td>
  <?php
   if(SessionUser::getUser()->isSeePrivacyDataEnabled()) {
      if ($iMode == 1) {
    ?>
            <a href="<?= SystemURLs::getRootPath() ?>/PrintView.php?PersonID=<?= $per_ID ?>"  data-toggle="tooltip" data-placement="top" data-original-title="<?= _('Print') ?>">
              <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-print fa-stack-1x fa-inverse"></i>
              </span>
            </a>
    <?php
      } else {
    ?>
         <a href="#" class="addGroup" data-personid="<?= $per_ID ?>" data-toggle="tooltip" title="" data-placement="left" data-original-title="<?= _('Add to Group') ?>">
              <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-tag fa-stack-1x fa-inverse"></i>
              </span>
         </a>
    <?php
      }
    } else {
     ?>
      <?= _('Private Data') ?> 
  
  <?php
    }
      echo '</td></tr>';
    

      //Store the family to enable the control break
      $iPrevFamily = $fam_ID;

      //If there was no family, set it to 0
      if (!isset($iPrevFamily)) {
          $iPrevFamily = 0;
      }

      //Store the first letter of this record to enable the control break
      $sPrevLetter = mb_strtoupper(mb_substr($per_LastName, 0, 1, 'UTF-8'));
  } // end of while loop
  ?>

      </table>
    </div>
  </form>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/MemberView.js"></script>

<?php require 'Include/Footer.php' ?>
