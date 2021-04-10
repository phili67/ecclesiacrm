<?php


//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

use EcclesiaCRM\FamilyCustomMasterQuery;

use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupTypeQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\GroupManagerPersonQuery;

use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOption;

use EcclesiaCRM\Map\ListOptionTableMap;

class SidebarGeneralRolesController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllGeneralRoles(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $mode = trim($args['mode']);

        $listID = 0;

        $list_type = 'normal';

// Check security for the mode selected.
        switch ($mode) {
            case 'famroles':
            case 'classes':
                if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
                    return $response->withJson(['success' => false]);
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
                    return $response->withJson(['success' => false]);
                }
                break;

            case 'custom':
            case 'famcustom':
            case 'securitygrp':
                if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
                    return $response->withJson(['success' => false]);
                }
                break;

            default:
                return $response->withJson(['success' => false]);
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
                    return $response->withJson(['success' => false]);
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
                    return $response->withJson(['success' => false]);
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
                    return $response->withJson(['success' => false]);
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
                    return $response->withJson(['success' => false]);
                }

                break;
            default:
                return $response->withJson(['success' => false]);
        }

        $iNewNameError = 0;

// Check if we're adding a field
        if (isset($_POST['AddField'])) {
            $newFieldName = InputUtils::LegacyFilterInput($_POST['newFieldName']);

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

                $aNameFields[$row] = InputUtils::LegacyFilterInput($_POST[$row . 'name']);

                $icon = ListOptionIconQuery::Create()->filterByListId(1)->findOneByListOptionId($aIDs[$row]);

                if (!is_null($icon) && $icon->getUrl() != '') {
                    $aIcon[$row] = ['isOnlyPersonViewVisible' => true, 'url' => $icon->getUrl()];
                } else {

                    $aIcon[$row] = null;
                }

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
            $aSeqs[$row] = $ormList->getOptionSequence();

            $icon = ListOptionIconQuery::Create()->filterByListId(1)->findOneByListOptionId($aIDs[$row]);

            if (!is_null($icon) && $icon->getUrl() != '') {
                $aIcon[$row] = ['isOnlyPersonViewVisible' => true, 'url' => $icon->getUrl()];
            } else {

                $aIcon[$row] = null;
            }
            $row++;
        }


        return $response->withJson(['sPageTitle' => $sPageTitle, 'bDuplicateFound' => $bDuplicateFound, 'noun' => $noun, 'adjplusname' => $adjplusname, 'adjplusnameplural' => $adjplusnameplural, 'iNewNameError' => $iNewNameError, 'embedded' => $embedded, 'listID' => $listID, 'iDefaultRole' => $iDefaultRole, 'numRows' => $numRows, 'aIDs' => $aIDs, 'aIcon' => $aIcon, 'aSeqs' => $aSeqs, 'aNameFields' => $aNameFields]);
    }

    public function generalRoleAssign(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $input = (object)$request->getParsedBody();

        if (isset ($input->mode) && isset ($input->Order) && isset ($input->ID)
            && isset ($input->Action) && isset ($input->ListID)) {
            // Get the Order, ID, Mode, and Action from the querystring
            $mode = trim($input->mode);
            $iOrder = InputUtils::LegacyFilterInput($input->Order, 'int');
            $iID = InputUtils::LegacyFilterInput($input->ID, 'int');  // the option ID
            $sAction = $input->Action;

// Check security for the mode selected.
            switch ($mode) {
                case 'famroles':
                case 'classes':
                    if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
                        return $response->withJson(['success' => false]);
                    }
                    break;

                case 'grptypes':
                case 'grptypesSundSchool':
                case 'grproles': // dead code for grproles
                    if (!SessionUser::getUser()->isManageGroupsEnabled()) {
                        return $response->withJson(['success' => false]);
                    }
                    break;

                case 'custom':
                case 'famcustom':
                case 'groupcustom':
                    if (!SessionUser::getUser()->isAdmin()) {
                        return $response->withJson(['success' => false]);
                    }
                    break;
                default:
                    return $response->withJson(['success' => false]);
            }

// Set appropriate table and field names for the editor mode
            $list_type = 'normal';

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
                case 'grptypesSundSchool':
                    $deleteCleanupTable = 'group_grp';
                    $deleteCleanupColumn = 'grp_Type';
                    $deleteCleanupResetTo = 0;
                    $listID = 3;
                    $list_type = ($mode == 'grptypesSundSchool') ? 'sunday_school' : 'normal';
                    break;
                case 'grproles': // dead code
                    $listID = InputUtils::LegacyFilterInput($input->ListID, 'int');

                    // Validate that this list ID is really for a group roles list. (for security)

                    $ormGroupList = GroupQuery::Create()->findByRoleListId($listID);
                    if ($ormGroupList->count() == 0) {
                        return $response->withJson(['success' => false]);
                    }

                    break;
                case 'custom':
                case 'famcustom':
                case 'groupcustom':
                    $listID = InputUtils::LegacyFilterInput($input->ListID, 'int');
                    break;
            }

            switch ($sAction) {
                // Move a field up:  Swap the OptionSequence (ordering) of the selected row and the one above it
                case 'up':
                    $list1 = ListOptionQuery::Create()->filterByOptionType($list_type)->filterById($listID)->findOneByOptionSequence($iOrder - 1);
                    $list1->setOptionSequence($iOrder)->save();

                    $list2 = ListOptionQuery::Create()->filterByOptionType($list_type)->filterById($listID)->findOneByOptionId($iID);
                    $list2->setOptionSequence($iOrder - 1)->save();
                    break;

                // Move a field down:  Swap the OptionSequence (ordering) of the selected row and the one below it
                case 'down':
                    $list1 = ListOptionQuery::Create()->filterByOptionType($list_type)->filterById($listID)->findOneByOptionSequence($iOrder + 1);
                    $list1->setOptionSequence($iOrder)->save();

                    $list2 = ListOptionQuery::Create()->filterByOptionType($list_type)->filterById($listID)->findOneByOptionId($iID);
                    $list2->setOptionSequence($iOrder + 1)->save();
                    break;

                // Delete a field from the form
                case 'delete':
                    $list = ListOptionQuery::Create()->findById($listID);
                    $numRows = $list->count();

                    // Make sure we never delete the only option
                    if ($list->count() > 1) {
                        $list = ListOptionQuery::Create()->filterByOptionType($list_type)->filterById($listID)->findOneByOptionSequence($iOrder);
                        $list->delete();


                        if ($listID == 1) { // we are in the case of custom icon for person classification, so we have to delete the icon in list_icon
                            $icon = ListOptionIconQuery::Create()->filterByListId($listID)->findOneByListOptionId($iID);
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

                            if (!is_null($grp)) {
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
                            RunKuery($sSQL);*/
                        } else if ($mode == 'grptypes' || $mode == 'grptypesSundSchool') {
                            // we've to delete the

                            $groupTypes = GroupTypeQuery::Create()->findByListOptionId($iID);

                            foreach ($groupTypes as $groupType) {
                                $groupType->setListOptionId(0);
                                $groupType->save();
                            }
                        } // Otherwise, for other types of assignees having a deleted option, reset them to default of 0 (undefined).
                        else {
                            if ($deleteCleanupTable != 0) {
                                $connection = Propel::getConnection();
                                $sSQL = "UPDATE $deleteCleanupTable SET $deleteCleanupColumn = $deleteCleanupResetTo WHERE $deleteCleanupColumn = " . $iID;
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
                    return $response->withJson(['success' => false]);
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => $input]);
    }
}
