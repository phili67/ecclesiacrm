<?php
/*******************************************************************************
 *
 *  filename    : FamilyCustomFieldsRowOps.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt (http://www.openserve.org)
 *                Copyright 2004-2012 Michael Wilt
 *  Cloned from PersonCustomFieldsRowOps.php
 *
 *  function    : Row operations for the Family custom fields form
 *******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;

// Security: user must be administrator to use this page.
if (!$_SESSION['user']->isAdmin()) {
    Redirect('Menu.php');
    exit;
}

// Get the Group, Property, and Action from the querystring
$iOrderID = InputUtils::LegacyFilterInput($_GET['OrderID'], 'int');
$sField = InputUtils::LegacyFilterInput($_GET['Field']);
$sAction = $_GET['Action'];

switch ($sAction) {
    // Move a field up:  Swap the fam_custom_Order (ordering) of the selected row and the one above it
    case 'up':
        $firstFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomOrder($iOrderID - 1);
        $firstFamCus->setCustomOrder($iOrderID)->save();
        
        $secondFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomField($sField);
        $secondFamCus->setCustomOrder($iOrderID - 1)->save();
        break;

    // Move a field down:  Swap the fam_custom_Order (ordering) of the selected row and the one below it
    case 'down':
        $firstFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomOrder($iOrderID + 1);
        $firstFamCus->setCustomOrder($iOrderID)->save();
        
        $secondFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomField($sField);
        $secondFamCus->setCustomOrder($iOrderID + 1)->save();
        break;

    // Delete a field from the form
    case 'delete':
        // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
        $famCus = FamilyCustomMasterQuery::Create()->findOneByCustomField($sField);
        
        if ( $famCus->getTypeId() == 12 ) {
           $list = ListOptionQuery::Create()->findById($famCus->getCustomSpecial());
           if( !is_null($list) ) {
             $list->delete();
           }
        }
        
        // this can't be propeled
        $sSQL = 'ALTER TABLE `family_custom` DROP `'.$sField.'` ;';
        RunQuery($sSQL);

        // now we can delete the FamilyCustomMaster
        $famCus->delete();

        $allFamCus = FamilyCustomMasterQuery::Create()->find();
        $numRows = $allFamCus->count();

        // Shift the remaining rows up by one, unless we've just deleted the only row
        if ($numRows > 0) {
            for ($reorderRow = $iOrderID + 1; $reorderRow <= $numRows + 1; $reorderRow++) {
                $firstFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomOrder($reorderRow);
                $firstFamCus->setCustomOrder($reorderRow - 1)->save();
            }
        }
        break;

    // If no valid action was specified, abort and return to the GroupView
    default:
        Redirect('FamilyCustomFieldsEditor.php');
        break;
}

// Reload the Form Editor page
Redirect('FamilyCustomFieldsEditor.php');
exit;
