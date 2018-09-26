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
*  2018 : Copyright Philippe Logel All rights reserved

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
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\InputUtils;

$iTenThousand = 10000;  // Constant used to offset negative choices in drop down lists

// Create array with Classification Information (lst_ID = 1)
$sClassSQL = 'SELECT * FROM list_lst WHERE lst_ID=1 ORDER BY lst_OptionSequence';
$rsClassification = RunQuery($sClassSQL);
unset($aClassificationName);
$aClassificationName[0] = gettext('Unassigned');
while ($aRow = mysqli_fetch_array($rsClassification)) {
    extract($aRow);
    $aClassificationName[intval($lst_OptionID)] = $lst_OptionName;
}

// Create array with Family Role Information (lst_ID = 2)
$sFamRoleSQL = 'SELECT * FROM list_lst WHERE lst_ID=2 ORDER BY lst_OptionSequence';
$rsFamilyRole = RunQuery($sFamRoleSQL);
unset($aFamilyRoleName);
$aFamilyRoleName[0] = gettext('Unassigned');
while ($aRow = mysqli_fetch_array($rsFamilyRole)) {
    extract($aRow);
    $aFamilyRoleName[intval($lst_OptionID)] = $lst_OptionName;
}

// Create array with Person Property

 // Get the total number of Person Properties (p) in table Property_pro
$sSQL = 'SELECT * FROM property_pro WHERE pro_Class="p"';
$rsPro = RunQuery($sSQL);
$ProRows = mysqli_num_rows($rsPro);

$sPersonPropertySQL = 'SELECT * FROM property_pro WHERE pro_Class="p" ORDER BY pro_Name';
$rsPersonProperty = RunQuery($sPersonPropertySQL);
unset($aPersonPropertyName);
$aPersonPropertyName[0] = gettext('Unassigned');
$i = 1;
while ($i <= $ProRows) {
    $aRow = mysqli_fetch_array($rsPersonProperty);
    extract($aRow);
    $aPersonPropertyName[intval($pro_ID)] = $pro_Name;
    $i++;
}

// Create array with Group Type Information (lst_ID = 3)
$sGroupTypeSQL = 'SELECT * FROM list_lst WHERE lst_ID=3 ORDER BY lst_OptionSequence';
$rsGroupTypes = RunQuery($sGroupTypeSQL);
unset($aGroupTypes);
while ($aRow = mysqli_fetch_array($rsGroupTypes)) {
    extract($aRow);
    $aGroupTypes[intval($lst_OptionID)] = $lst_OptionName;
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

// Save default search mode
$_SESSION['bSearchFamily'] = ($sMode != 'person');

if (array_key_exists('Number', $_GET)) {
    $_SESSION['SearchLimit'] = InputUtils::LegacyFilterInput($_GET['Number'], 'int');
    $tmpUser = UserQuery::create()->findPk($_SESSION['user']->getPersonId());
    $tmpUser->setSearchLimit($_SESSION['SearchLimit']);
    $tmpUser->save();
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
    $sPageTitle = gettext('Person Listing');
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
    $sPageTitle = gettext('Group Assignment Helper');
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

$iPerPage = $_SESSION['SearchLimit'];

$sLimit5 = '';
$sLimit10 = '';
$sLimit20 = '';
$sLimit25 = '';
$sLimit50 = '';
$sLimit100 = '';
$sLimit200 = '';
$sLimit500 = '';

// SQL for group-assignment helper
if ($iMode == 2) {
    $sBaseSQL = 'SELECT *, IF(LENGTH(per_Zip) > 0,per_Zip,fam_Zip) AS zip '.
                'FROM person_per LEFT JOIN family_fam '.
                'ON person_per.per_fam_ID = family_fam.fam_ID ';

    // Find people who are part of a group of the specified type.
    // MySQL doesn't have subqueries until version 4.1.. for now, do it the hard way
    $sSQLsub = 'SELECT per_ID FROM person_per LEFT JOIN person2group2role_p2g2r '.
                'ON p2g2r_per_ID = per_ID LEFT JOIN group_grp '.
                'ON grp_ID = p2g2r_grp_ID '.
                "WHERE grp_Type = $iGroupTypeMissing GROUP BY per_ID";
    $rsSub = RunQuery($sSQLsub);

    if (mysqli_num_rows($rsSub) > 0) {
        $sExcludedIDs = '';
        while ($aTemp = mysqli_fetch_row($rsSub)) {
            $sExcludedIDs .= $aTemp[0].',';
        }
        $sExcludedIDs = mb_substr($sExcludedIDs, 0, -1);
        $sGroupWhereExt = ' AND per_ID NOT IN ('.$sExcludedIDs.')';
    }
}

// SQL for standard Person List
if ($iMode == 1) {
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

$activeFamiliesWhereExt = ' AND fam_DateDeactivated is null';
$activePersonsWhereExt = ' AND per_DateDeactivated is null';

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


$peopleIDArray = array();
$rsPersons = RunQuery($sSQL);
while ($aRow = mysqli_fetch_row($rsPersons)) {
    array_push($peopleIDArray, intval($aRow[0]));
}


// Get the total number of persons
$rsPer = RunQuery($sSQL);
$Total = mysqli_num_rows($rsPer);

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
$rsPersons = RunQuery($finalSQL);

// Run query to get first letters of last name.
$sSQL = 'SELECT DISTINCT LEFT(per_LastName,1) AS letter FROM person_per LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID '.
        $sJoinExt." WHERE 1 $sWhereExt ORDER BY letter";
$rsLetters = RunQuery($sSQL);

require 'Include/Header.php';
?>
<script nonce="<?= SystemURLs::getCSPNonce()?>">
  var listPeople=<?= json_encode($peopleIDArray)?>;
</script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/SelectList.js"></script>


<?php 
  if ($_SESSION['user']->isSeePrivacyDataEnabled()) {
?>
<div class="box box-primary">
    <div class="box-header  with-border">
       <h3 class="box-title">
        <?= gettext('Filter and Cart') ?>
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
    $sSQLtemp = 'SELECT * FROM list_lst WHERE lst_ID = 3';
    $rsGroupTypes = RunQuery($sSQLtemp);
?>
       <p align="center" class="MediumText"><?= gettext('Show people <b>not</b> in this group type:') ?>
        <select name="type" onchange="this.form.submit()" class="form-control-min_width input-sm">
   <?php     
    while ($aRow = mysqli_fetch_array($rsGroupTypes)) {
        extract($aRow);
  ?>
        <option value="<?= $lst_OptionID ?>" <?= ($iGroupTypeMissing == $lst_OptionID)?' selected':'' ?>><?= $lst_OptionName.'&nbsp;' ?>
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
            <label><?= gettext('Sort order') ?>:</label>&nbsp;
            <select class="form-control-min_width input-sm" name="Sort" onchange="this.form.submit()">
                <option value="name" <?php if ($sSort == 'name' || empty($sSort)) {
                echo 'selected';
            } ?>><?= gettext('By Name') ?></option>
                <option value="family" <?php if ($sSort == 'family') {
                echo 'selected';
            } ?>><?= gettext('By Family') ?></option>
                <option value="zip" <?php if ($sSort == 'zip') {
                echo 'selected';
            } ?>><?= gettext('By Zip Code') ?></option>
                <option value="entered" <?php if ($sSort == 'entered') {
                echo 'selected';
            } ?>><?= gettext('By Newest Entries') ?></option>
                <option value="edited" <?php if ($sSort == 'edited') {
                echo 'selected';
            } ?>><?= gettext('By Recently Edited') ?></option>
            </select>&nbsp;&nbsp;
            <input type="text" name="Filter" class="form-control-min_width" value="<?= $sFilter ?>">
            <input type="hidden" name="mode" value="<?= $sMode ?>">
            <input type="hidden" name="Letter" value="<?= $sLetter ?>">&nbsp;&nbsp;
            <input type="submit" class="btn btn-info btn-sm" value="<?= gettext('Apply Filter') ?>">
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
              <option value="" <?= (!isset($iGender))?" selected ":"" ?>> <?= gettext("Male & Female") ?></option>
              <option value="1" <?= (isset($iGender) && $iGender == 1)?" selected ":"" ?>><?= gettext("Male") ?></option>
              <option value="2" <?= (isset($iGender) && $iGender == 2)?" selected ":"" ?>> <?= gettext("Female") ?></option>
            </select>&nbsp;
    
            <!--  ********** -->
            <!--  Classification drop down list -->
            <select class="form-control-min_width input-sm" name="Classification" onchange="this.form.submit()">
              <option value="" <?= ($iClassification >= 0)?' selected ':'' ?>><?= gettext('All Classifications') ?></option>

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
               <option value="" <?= ($iFamilyRole < 0)?' selected ':''?>><?= gettext('All Family Roles') ?></option>
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
                <option value="" <?= (!isset($iPersonProperty))?' selected ':'' ?>><?= gettext("All Contact Properties") ?></option>
       
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

              <option value="" <?= (!isset($iGroupType))?' selected ':'' ?>><?= gettext('All Group Types') ?></option>
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
                $sGroupsSQL = "SELECT * FROM group_grp WHERE grp_Type = $iGroupType ".
                                'ORDER BY grp_Name ';

                $rsGroups = RunQuery($sGroupsSQL);
                $aGroupNames = [];
                while ($aRow = mysqli_fetch_array($rsGroups)) {
                    extract($aRow);
                    $aGroupNames[intval($grp_ID)] = $grp_Name;
                }
           ?>
            <select class="form-control-min_width input-sm" name="groupid" onchange="this.form.submit()">
                <option value="" <?= (!isset($iGroupType))?' selected ':'' ?>><?= gettext('All Groups') ?></option>

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
                $sSQL = 'SELECT grp_RoleListID '.
                        'FROM group_grp WHERE grp_ID ='.$iGroupID;
                $aTemp = mysqli_fetch_array(RunQuery($sSQL));
                $iRoleListID = $aTemp[0];

                // Get the roles
                $sSQL = 'SELECT * FROM list_lst WHERE lst_ID = '.$iRoleListID.
                        ' ORDER BY lst_OptionSequence';
                $rsRoles = RunQuery($sSQL);
                unset($aGroupRoles);
                while ($aRow = mysqli_fetch_array($rsRoles)) {
                    extract($aRow);
                    $aGroupRoles[intval($lst_OptionID)] = $lst_OptionName;
                }
             ?>
              <select class="form-control-min_width input-sm" name="grouproleid" onchange="this.form.submit()" >
                  <option value="" <?= ($iRoleID < 0)?' selected ':'' ?>><?= gettext("All Roles") ?></option>
              <?php
                foreach ($aGroupRoles as $key => $value) {
              ?>
                  <option value="<?= $key ?>" <?= ($iRoleID >= 0 && ($iRoleID == $key))?' selected ':'' ?>><?= gettext($value) ?></option>
              <?php
                }
              ?>
              </select>&nbsp;
          <?php      
            }
          } 
        ?>

        <input type="button" class="btn btn-info btn-sm" value="<?= gettext('Clear Filters') ?>" onclick="javascript:document.location='SelectList.php?mode=<?= $sMode ?>&amp;Sort=<?= $sSort ?>&amp;type=<?= $iGroupTypeMissing ?>'"><BR><BR>

        <?php
        if ( $_SESSION['user']->isShowCartEnabled() ) {
        ?>
        <a id="AddAllToCart" class="btn btn-primary btn-sm" ><?= gettext('Add All to Cart') ?></a>
        <input name="IntersectCart" type="submit" class="btn btn-warning btn-sm" value="<?= gettext('Intersect with Cart') ?>">&nbsp;
        <a id="RemoveAllFromCart" class="btn btn-danger btn-sm" ><?= gettext('Remove All from Cart') ?></a>

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
        <?= gettext('Listing')?>
     </h3>
    </div>
  <div class="box-body">
<?php
// Display record count
if ($Total == 1) {
    echo '<p align = "center"><b>' . $Total . " ".gettext("record returned") . '</b></p>';
} else {
    echo '<p align = "center"><b>' . $Total . " ".gettext("records returned") . '</b></p>';
}
// Create Sort Links
?>
<div align="center" style="margin-top: -25px;">
  <ul class="pagination pagination-sm">
    <li class="<?= empty($sLetter)?"active":"" ?>">
    <a href="SelectList.php?mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr?>&amp;grouproleid=<?= $iRoleIDStr ?>&amp;PersonProperties=<?= $iPersonPropertyStr.(($sSort)?"&amp;Sort=$sSort":"") ?>"><?= gettext('View All') ?></a>
    </li>

    <?php
    while ($aLetter = mysqli_fetch_row($rsLetters)) {
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
              <a href="SelectList.php?mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr ?>&amp;grouproleid=<?= $iRoleIDStr.(($sSort)?"&amp;Sort=$sSort":"")."&amp;Letter=".$aLetter[0] ?>"><?= $aLetter[0] ?></a>
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
                <a href="SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassification ?>&amp;FamilyRole=<?= $iFamilyRole ?>&amp;Gender=<?= $iGender ?>&amp;grouptype=<?= $iGroupType ?>&amp;groupid=<?= $iGroupID ?>&amp;grouproleid=<?= $iRoleID ?>"><?= gettext('Previous Page') ?></a>
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
                  <a href="SelectList.php?Result_Set=0&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassification ?>&amp;FamilyRole=<?= $iFamilyRole ?>&amp;Gender=<?= $iGender ?>&amp;grouptype=<?= $iGroupType ?>&amp;groupid=<?= $iGroupID ?>&amp;grouproleid=<?= $iRoleID ?>">1 ...</a>
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
                        <a href="SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode?> &amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr?>&amp;grouproleid=<?= $iRoleIDStr?>"><?= $c ?></a>
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
                  <a href="SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassification ?>&amp;FamilyRole=<?= $iFamilyRole ?>&amp;Gender=<?= $iGender ?>&amp;grouptype=<?= $iGroupType ?>&amp;groupid=<?= $iGroupID ?>&amp;grouproleid=<?= $iRoleID ?>">... <?= $Pages ?></a>
                </li>
            <?php      
            }
            // Show next-page link unless we're at the last page
            if ($Result_Set >= 0 && $Result_Set < $Total) {
                $thisLinkResult = $Result_Set + $iPerPage;
                if ($thisLinkResult < $Total) {
                ?>
                  <li>
                    <a href="SelectList.php?Result_Set=<?= $thisLinkResult ?>&amp;mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Filter=<?= $sFilter ?>&amp;Sort=<?= $sSort ?>&amp;Letter=<?= $sLetter ?>&amp;Classification=<?= $iClassificationStr ?>&amp;FamilyRole=<?= $iFamilyRoleStr ?>&amp;Gender=<?= $iGenderStr ?>&amp;grouptype=<?= $iGroupTypeStr ?>&amp;groupid=<?= $iGroupIDStr ?>&amp;grouproleid=<?= $iRoleIDStr ?>"><?= gettext('Next Page') ?></a>          
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
            if ($_SESSION['SearchLimit'] == '5') {
                $sLimit5 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '10') {
                $sLimit10 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '20') {
                $sLimit20 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '25') {
                $sLimit25 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '50') {
                $sLimit50 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '100') {
                $sLimit100 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '200') {
                $sLimit200 = 'selected';
            }
            if ($_SESSION['SearchLimit'] == '500') {
                $sLimit500 = 'selected';
            }

        ?>

        &nbsp;
          <b><?= gettext('Display:') ?></b>
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
  <th><?= gettext('Photo') ?></th>
  <th>
        <a class="btn btn-<?= ($sSort == "name")?"info active":"default" ?> btn-sm" href="SelectList.php?mode=<?= $sMode ?>&amp;type=<?= $iGroupTypeMissing ?>&amp;Sort=name&amp;Filter=<?= $sFilter ?>"><?= gettext('Name') ?></a>
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
          echo '<option value="'.$s.'"'.$sel.'>'.gettext($s).'</option>';
      }
      ?>
      </select>
  </th>
  <th>
     <a class="btn btn-<?= ($sSort == "family")?"info active":"default" ?> btn-sm " href="SelectList.php?mode=<?= $sMode ?>&amp;type=<?=$iGroupTypeMissing ?>&amp;Sort=family&amp;Filter=<?= $sFilter ?>"><?= gettext('Family') ?></a>
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
        echo '<option value="'.$s.'"'.$sel.'>'.gettext($s).'</option>';
    }
    ?>
    </select>
  </th>
  <th>
  <?php
  if ($_SESSION['user']->isEditRecordsEnabled()) {
      echo gettext('Edit');
  }
  ?>
  </th>
  <th><?= gettext('Cart') ?></th>
  <?php
  if ($iMode == 1) {
  ?>
      <th><?= gettext('Print') ?></th>
  <?php
  } else {
  ?>
      <th><?= gettext('Assign') ?></th>
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
        <?= gettext("Not Available") ?>
      </td>
    </tr>
  <?php
  }

  //Loop through the person recordset
  while ($aRow = mysqli_fetch_array($rsPersons)) {
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
                  echo gettext('Unassigned');
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
      $sRowClass = AlternateRowStyle($sRowClass);

      //Display the row
      echo '<tr class="'.$sRowClass.'">'; ?>
    </td>
      <td><img src="<?= SystemURLs::getRootPath(); ?>/api/persons/<?= $per_ID ?>/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px" /> </td>
    <td>
        <a href="PersonView.php?PersonID=<?= $per_ID ?>" >
          <?= FormatFullName($per_Title, $per_FirstName, $per_MiddleName, $per_LastName, $per_Suffix, 3) ?>
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
            case 1: echo gettext('Male'); 
              break;
            case 2: echo gettext('Female'); 
              break;
            default: echo '';
          }
      }
      echo '&nbsp;</td>';

      echo '<td>';
    
      if ($_SESSION['user']->isSeePrivacyDataEnabled()) {
    
        if ($fam_Name != '') {
            echo '<a href="FamilyView.php?FamilyID='.$fam_ID.'">'.$fam_Name;
            echo FormatAddressLine($fam_Address1, $fam_City, $fam_State).'</a>';
        }
        echo '&nbsp;</td>';
      } else {
        echo gettext('Private Data');
      }
    
      echo '<td>';
      if ($_SESSION['user']->isSeePrivacyDataEnabled()) {
        // Phone number or zip code
        if ($sPersonColumn5 == 'Home Phone') {
            echo SelectWhichInfo(ExpandPhoneNumber($fam_HomePhone, $fam_Country, $dummy),
                    ExpandPhoneNumber($per_HomePhone, $fam_Country, $dummy), true);
        } elseif ($sPersonColumn5 == 'Work Phone') {
            echo SelectWhichInfo(ExpandPhoneNumber($per_WorkPhone, $fam_Country, $dummy),
                    ExpandPhoneNumber($fam_WorkPhone, $fam_Country, $dummy), true);
        } elseif ($sPersonColumn5 == 'Mobile Phone') {
            echo SelectWhichInfo(ExpandPhoneNumber($per_CellPhone, $fam_Country, $dummy),
                    ExpandPhoneNumber($fam_CellPhone, $fam_Country, $dummy), true);
        } else {
            if (isset($zip)) {
                echo $zip;
            } else {
                echo gettext('Unassigned');
            }
        } 
      } else {
        echo gettext('Private Data');
      }
      ?>
    </td>
      <td>
    <?php if ($_SESSION['user']->isEditRecordsEnabled()) {
          ?>
      <a href="PersonEditor.php?PersonID=<?= $per_ID ?>">
          <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
              </span>
          </a>
    <?php
      } ?>
      </td>
    <td>
    <?php 
      if (!isset($_SESSION['aPeopleCart']) || !in_array($per_ID, $_SESSION['aPeopleCart'], false)) {
        if ($_SESSION['user']->isShowCartEnabled()) {
    ?>
        <a class="AddToPeopleCart" data-cartpersonid="<?= $per_ID ?>">
      <?php
        }
      ?>
      
      <span class="fa-stack">
          <i class="fa fa-square fa-stack-2x"></i>
          <i class="fa fa-stack-1x fa-inverse <?= ($_SESSION['user']->isShowCartEnabled())?'fa-cart-plus':'fa-question' ?>"></i>
      </span>
      <?php
        if ($_SESSION['user']->isShowCartEnabled()) {
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
        if ($_SESSION['user']->isShowCartEnabled()) {
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
        if ($_SESSION['user']->isShowCartEnabled()) {
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
   if($_SESSION['user']->isSeePrivacyDataEnabled()) {
      if ($iMode == 1) {
    ?>
            <a href="PrintView.php?PersonID=<?= $per_ID ?>">
              <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-print fa-stack-1x fa-inverse"></i>
              </span>
            </a>
    <?php
      } else {
          echo '<a href="PersonToGroup.php?PersonID='.$per_ID;
          echo '&amp;prevquery='.rawurlencode($_SERVER['QUERY_STRING']).'">';
          echo gettext('Add to Group').'</a>';
      }
    
    } else {
     ?>
      <?= gettext('Private Data') ?> 
  
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

<?php require 'Include/Footer.php' ?>
