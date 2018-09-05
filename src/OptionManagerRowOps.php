<?php
/*******************************************************************************
 *
 *  filename    : OptionManagerRowOps.php
 *  last change : 2003-04-09
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt (http://www.openserve.org)
 *
 *  function    : Row operations for the option manager
 *******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;

// Get the Order, ID, Mode, and Action from the querystring
if (array_key_exists('Order', $_GET)) {
    $iOrder = InputUtils::LegacyFilterInput($_GET['Order'], 'int');
}  // the option Sequence
$sAction = $_GET['Action'];
$iID = InputUtils::LegacyFilterInput($_GET['ID'], 'int');  // the option ID
$mode = trim($_GET['mode']);

// Check security for the mode selected.
switch ($mode) {
    case 'famroles':
    case 'classes':
        if (!$_SESSION['user']->isMenuOptionsEnabled()) {
            Redirect('Menu.php');
            exit;
        }
        break;

    case 'grptypes':
    case 'grproles':
        if (!$_SESSION['user']->isManageGroupsEnabled()) {
            Redirect('Menu.php');
            exit;
        }
        break;

    case 'custom':
    case 'famcustom':
    if (!$_SESSION['user']->isAdmin()) {
        Redirect('Menu.php');
        exit;
    }
        break;
    default:
        Redirect('Menu.php');
        break;
}

// Set appropriate table and field names for the editor mode
switch ($mode) {
    case 'famroles':
        $deleteCleanupTable = 'person_per';
        $deleteCleanupColumn = 'per_fmr_ID';
        $deleteCleanupResetTo = 0;
        $listID = 2;
        break;
    case 'classes':
        $deleteCleanupTable = 'person_per';
        $deleteCleanupColumn = 'per_cls_ID';
        $deleteCleanupResetTo = 0;
        $listID = 1;
        break;
    case 'grptypes':
        $deleteCleanupTable = 'group_grp';
        $deleteCleanupColumn = 'grp_Type';
        $deleteCleanupResetTo = 0;
        $listID = 3;
        break;
    case 'grproles':
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');

        // Validate that this list ID is really for a group roles list. (for security)
        
        $ormGroupList = GroupQuery::Create()->findByRoleListId($listID);
        if ($ormGroupList->count() == 0) {
            Redirect('Menu.php');
            break;
        }

        break;
    case 'custom':
    case 'famcustom':
        $listID = InputUtils::LegacyFilterInput($_GET['ListID'], 'int');
        break;
}

switch ($sAction) {
    // Move a field up:  Swap the OptionSequence (ordering) of the selected row and the one above it
    case 'up':
        $list1 = ListOptionQuery::Create()->filterById($listID)->findOneByOptionSequence($iOrder - 1);
        $list1->setOptionSequence($iOrder)->save();
        
        $list2 = ListOptionQuery::Create()->filterById($listID)->findOneByOptionId($iID);
        $list2->setOptionSequence($iOrder - 1)->save();
        break;

    // Move a field down:  Swap the OptionSequence (ordering) of the selected row and the one below it
    case 'down':
        $list1 = ListOptionQuery::Create()->filterById($listID)->findOneByOptionSequence($iOrder + 1);
        $list1->setOptionSequence($iOrder)->save();
        
        $list2 = ListOptionQuery::Create()->filterById($listID)->findOneByOptionId($iID);
        $list2->setOptionSequence($iOrder + 1)->save();
        break;

    // Delete a field from the form
    case 'delete':
        $list = ListOptionQuery::Create()->findById($listID);
        $numRows = $list->count();

        // Make sure we never delete the only option
        if ($list->count() > 1) {
            $list = ListOptionQuery::Create()->filterById($listID)->findOneByOptionSequence($iOrder);
            $list->delete();

            
            if ($listID == 1) { // we are in the case of custom icon for person classification, so we have to delete the icon in list_icon
              $icon = ListOptionIconQuery::Create()->filterByListId($listID)->findOneByListOptionId ($iID);
              
              if (!is_null($icon)) {
                $icon->delete();
              }
            }

            // Shift the remaining rows up by one
            for ($reorderRow = $iOrder + 1; $reorderRow <= $numRows + 1; $reorderRow++) {
                $list_upd = ListOptionQuery::Create()->filterById($listID)->findOneByOptionSequence($reorderRow);
                if (!is_null($list_upd)) {
                  $list_upd->setOptionSequence($reorderRow - 1)->save();
                }
            }

            // If group roles mode, check if we've deleted the old group default role.  If so, reset default to role ID 1
            // Next, if any group members were using the deleted role, reset their role to the group default.
            if ($mode == 'grproles') {// unusefull : dead code : This can be defined in GroupEditor.php?GroupID=id
                // Reset if default role was just removed.
                $grp = GroupQuery::Create()->filterByRoleListId($listID)->findOneByDefaultRole($iID);
                
                if (!is_null($grp)){
                  $grp->setDefaultRole(1)->save();
                }

                // Get the current default role and Group ID (so we can update the p2g2r table)
                // This seems backwards, but grp_RoleListID is unique, having a 1-1 relationship with grp_ID.
                $grp = GroupQuery::Create()->findOneByRoleListId($listID);
                
                
                $persons = Person2group2roleP2g2rQuery::Create()->filterByGroupId($grp->getId())->findByRoleId($iID);
                
                foreach ($persons as $person) {
                  $person->setRoleId($grp->getDefaultRole());
                  $person->save();
                }
                
                /*$sSQL = "UPDATE person2group2role_p2g2r SET p2g2r_rle_ID = ".$grp->getDefaultRole()." WHERE p2g2r_grp_ID = ".$grp->getId()." AND p2g2r_rle_ID = $iID";
                RunQuery($sSQL);*/
            }

            // Otherwise, for other types of assignees having a deleted option, reset them to default of 0 (undefined).
            else {
                if ($deleteCleanupTable != 0) {
                    $connection = Propel::getConnection();
                    $sSQL = "UPDATE $deleteCleanupTable SET $deleteCleanupColumn = $deleteCleanupResetTo WHERE $deleteCleanupColumn = ".$iID;
                    $connection->exec($sSQL);
                }
            }
        }
        break;

    // Currently this is used solely for group roles
    case 'makedefault':// unusefull : dead code : This can be defined in GroupEditor.php?GroupID=id
        $grp = GroupQuery::Create()->findOneByRoleListId($listID);
        $grp->setDefaultRole($iID)->save();
        break;

    // If no valid action was specified, abort
    default:
        Redirect('Menu.php');
        break;
}

// Reload the option manager page
Redirect("OptionManager.php?mode=$mode&ListID=$listID");
exit;
