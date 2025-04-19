<?php

namespace EcclesiaCRM\Service;

use Propel\Runtime\Propel;
use PDO;

use EcclesiaCRM\CardDav\VcardUtils;

use EcclesiaCRM\MyPDO\CardDavPDO;

use EcclesiaCRM\Utils\MiscUtils;


use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOption;
use EcclesiaCRM\Person2group2roleP2g2r;

use EcclesiaCRM\Map\ListOptionTableMap;

class GroupService
{

    /**
     *  removeUserFromGroup.
     *
     * @param int $groupID  Group ID from which  to remove the user
     * @param int $personID UserID to remove from the group
     */
    public function removeUserFromGroup($groupID, $personID)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');

        $connection = Propel::getConnection();

        $one = Person2group2roleP2g2rQuery::create()->filterByPersonId($personID)->findOneByGroupId($groupID);
        if (!is_null($one)) {
            $one->delete();
        }

        // Check if this group has special properties
        $grp = GroupQuery::create()->findOneById($groupID);

        if ($grp->getHasSpecialProps() == 1) {
            $sSQL = 'DELETE FROM groupprop_'.$groupID." WHERE per_ID = '".$personID."'";
            $statement = $connection->prepare($sSQL);
            $statement->execute();
        }

        // Reset any group specific property fields of type "Person from Group" with this person assigned
        $grpms = GroupPropMasterQuery::create()
            ->filterByTypeId(9)
            ->_and()
            ->filterBySpecial($groupID)
            ->find();

        foreach ($grpms as $grpm) {
            $sSQL = 'UPDATE groupprop_'.$grpm->getGroupId().' SET '.$grpm->getField().' = NULL WHERE '.$grpm->getField().' = '.$personID;
            $statement = $connection->prepare($sSQL);
            $statement->execute();
        }

        // Reset any custom person fields of type "Person from Group" with this person assigned
        $perCMs = PersonCustomMasterQuery::create()
            ->filterByTypeId(9)
            ->_and()
            ->filterByCustomSpecial($groupID)
            ->find();

        foreach ($perCMs as $perCM) {
            $sSQL = 'UPDATE person_custom SET '.$perCM->getCustomField().' = NULL WHERE '.$perCM->getCustomField().' = '.$personID;
            $statement = $connection->prepare($sSQL);
            $statement->execute();
        }

        // we'll connect to sabre to create the group
        // We set the BackEnd for sabre Backends
        $carddavBackend = new CardDavPDO();

        $addressbookId = $carddavBackend->getAddressBookForGroup ($groupID)['id'];

        $carddavBackend->deleteCardForPerson($addressbookId,$personID);
    }

    /**
     *  addUserToGroup.
     *
     * @param int $groupID  Group ID from which  to remove the user
     * @param int $personID UserID to remove from the group
     * @param int $roleID   Role ID to set to the person
     */
    public function addUserToGroup($iGroupID, $iPersonID, $iRoleID)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        //
        // Adds a person to a group with specified role.
        // Returns false if the operation fails. (such as person already in group)
        //
        // Was a RoleID passed in?
        if ($iRoleID == 0) {
            // No, get the Default Role for this Group
            $grp = GroupQuery::create()->findOneById($iGroupID);
            $iRoleID = $grp->getDefaultRole();
        }

        $per = new Person2group2roleP2g2r();

        if ( !is_null ( $per ) ) {
            $per->setPersonId($iPersonID);
            $per->setGroupId($iGroupID);
            $per->setRoleId($iRoleID);
            $per->save();

            // Check if this group has special properties
            $grp = GroupQuery::create()->findOneById($iGroupID);

            if ($grp->getHasSpecialProps() == 'true') {
                $connection = Propel::getConnection();

                $sSQL = 'INSERT INTO groupprop_'.$iGroupID." (per_ID) VALUES ('".$iPersonID."')";

                $statement = $connection->prepare($sSQL);
                $statement->execute();
            }
        }

        // we get the person info
        $person = PersonQuery::create()->findPk($iPersonID);

        // We set the BackEnd for sabre Backends
        // we'll connect to sabre to create the group
        $carddavBackend = new CardDavPDO();
        $vcard = VcardUtils::Person2Vcard($person);
        $card = $vcard->serialize(); 

        $addressbookId = $carddavBackend->getAddressBookForGroup ($iGroupID)['id'];               

        // now we can create the vcard
        $carddavBackend->createCard($addressbookId, 'UUID-'.\Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());

        return $this->getGroupMembers($iGroupID, $iPersonID);
    }

    /**
     *  getGroupRoles.
     *
     * @param int $groupID ID of the group
     *
     * @return array represnting the roles of the group
     */
    public function getGroupRoles($groupID)
    {
        $groupRoles = [];

        $connection = Propel::getConnection();

        $sSQL = 'SELECT grp_ID, lst_OptionName, lst_OptionID, lst_OptionSequence
              FROM group_grp
              LEFT JOIN list_lst ON
              list_lst.lst_ID = group_grp.grp_RoleListID
              WHERE group_grp.grp_ID = '.$groupID;

        $pdoList = $connection->prepare($sSQL);
        $pdoList->execute();

        // Validate that this list ID is really for a group roles list. (for security)
        if ($pdoList->rowCount() == 0) {
            throw new \Exception('invalid request');
        }

        while ($row = $pdoList->fetch( \PDO::FETCH_BOTH )) {
            array_push($groupRoles, $row);
        }

        return $groupRoles;
    }



    public function setGroupRoleOrder($groupID, $groupRoleID, $groupRoleOrder)
    {
        $connection = Propel::getConnection();

        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQL = 'UPDATE list_lst
                 INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 SET list_lst.lst_OptionSequence = "'.$groupRoleOrder.'"
                 WHERE group_grp.grp_ID = "'.$groupID.'"
                    AND list_lst.lst_OptionID = '.$groupRoleID;

        $statement = $connection->prepare($sSQL);
        $statement->execute();
    }

    public function getGroupRoleOrder($groupID, $groupRoleID)
    {
        $connection = Propel::getConnection();

        $sSQL = 'SELECT list_lst.lst_OptionSequence FROM list_lst
                INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 WHERE group_grp.grp_ID = "'.$groupID.'"
                   AND list_lst.lst_OptionID = '.$groupRoleID;

        $pdoPropList = $connection->prepare($sSQL);
        $pdoPropList->execute();

        $rowOrder = $pdoPropList->fetchAll(PDO::FETCH_ASSOC);// permet de récupérer le tableau associatif

        return $rowOrder[0]['lst_OptionSequence'];
    }

    public function deleteGroupRole($groupID, $groupRoleID)
    {
        $connection = Propel::getConnection();

        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQL = 'SELECT * FROM list_lst
                INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 WHERE group_grp.grp_ID = "'.$groupID.'"';

        $pdoPropList = $connection->prepare($sSQL);
        $pdoPropList->execute();

        $numRows = $pdoPropList->rowCount();
        // Make sure we never delete the only option
        if ($numRows > 1) {
            $thisSequence = $this->getGroupRoleOrder($groupID, $groupRoleID);
            $sSQL = 'DELETE list_lst.* FROM list_lst
                    INNER JOIN group_grp
                        ON group_grp.grp_RoleListID = list_lst.lst_ID
                    WHERE group_grp.grp_ID = "'.$groupID.'"
                    AND lst_OptionID = '.$groupRoleID;

            $statement = $connection->prepare($sSQL);
            $statement->execute();

            //check if we've deleted the old group default role.  If so, reset default to role ID 1
            // Next, if any group members were using the deleted role, reset their role to the group default.
            // Reset if default role was just removed.
            $sSQL = "UPDATE group_grp SET grp_DefaultRole = 1 WHERE grp_ID = $groupID AND grp_DefaultRole = $groupRoleID";
            $statement = $connection->prepare($sSQL);
            $statement->execute();


            // Get the current default role and Group ID (so we can update the p2g2r table)
            // This seems backwards, but grp_RoleListID is unique, having a 1-1 relationship with grp_ID.
            $sSQL = "SELECT grp_ID,grp_DefaultRole FROM group_grp WHERE grp_ID = $groupID";
            $statement = $connection->prepare($sSQL);
            $statement->execute();


            $sSQL = "UPDATE person2group2role_p2g2r SET p2g2r_rle_ID = 1 WHERE p2g2r_grp_ID = $groupID AND p2g2r_rle_ID = $groupRoleID";
            $statement = $connection->prepare($sSQL);
            $statement->execute();


            //Shift the remaining rows IDs up by one

            $sSQL = 'UPDATE list_lst
                    INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                    SET list_lst.lst_OptionID = list_lst.lst_OptionID -1
                    WHERE group_grp.grp_ID = '.$groupID.'
                    AND list_lst.lst_OptionID >= '.$groupRoleID;

            $statement = $connection->prepare($sSQL);
            $statement->execute();


            //Shift up the remaining row Sequences by one

            $sSQL = 'UPDATE list_lst
                    INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                    SET list_lst.lst_OptionSequence = list_lst.lst_OptionSequence -1
                    WHERE group_grp.grp_ID ='.$groupID.'
                    AND list_lst.lst_OptionSequence >= '.$thisSequence;

            $statement = $connection->prepare($sSQL);
            $statement->execute();


            return $this->getGroupRoles($groupID);
        } else {
            throw new \Exception('You cannot delete the only group');
        }
    }

    public function addGroupRole($groupID, $groupRoleName)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');

        $connection = Propel::getConnection();

        if (strlen($groupRoleName) == 0) {
            throw new \Exception('New field name cannot be blank');
        } else {
            // Check for a duplicate option name
            $sSQL = 'SELECT \'\' FROM list_lst
                INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 WHERE group_grp.grp_ID = "'.$groupID.'" AND
                 lst_OptionName = "'.$groupRoleName.'"';

            $statement = $connection->prepare($sSQL);
            $statement->execute();

            $rsCount = $statement->rowCount();
            if ($rsCount > 0) {
                throw new \Exception('Field '.$groupRoleName.' already exists');
            } else {
                $grp = GroupQuery::create()->findOneById($groupID);

                $listID = $grp->getRoleListId();

                // Get count of the options
                $lists = ListOptionQuery::create()->findById($listID);
                if ( !is_null($lists) ) {
                    $numRows = $lists->count();
                }
                $newOptionSequence = $numRows + 1;

                // Get the new OptionID
                $list = ListOptionQuery::create()
                    ->addAsColumn('Max', 'MAX('.ListOptionTableMap::COL_LST_OPTIONID.')')
                    ->findOneById($listID);

                $newOptionID = $list->getMax() + 1;

                // Insert into the appropriate options table
                $new_option_list = new ListOption();
                $new_option_list->setId($listID);
                $new_option_list->setOptionId($newOptionID);
                $new_option_list->setOptionName($groupRoleName);
                $new_option_list->setOptionSequence($newOptionSequence);
                $new_option_list->save();

                $iNewNameError = 0;
            }
        }

        return '{"newRole":{"roleID":"'.$newOptionID.'", "roleName":"'.$groupRoleName.'", "sequence":"'.$newOptionSequence.'"}}';
    }

    public function enableGroupSpecificProperties($groupID)
    {
        $connection = Propel::getConnection();

        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQL = 'UPDATE group_grp SET grp_hasSpecialProps = true
            WHERE grp_ID = '.$groupID;
        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $sSQLp = 'CREATE TABLE groupprop_'.$groupID." (
                        per_ID mediumint(8) unsigned NOT NULL default '0',
                        PRIMARY KEY  (per_ID),
                          UNIQUE KEY per_ID (per_ID)
                        ) ENGINE=InnoDB;";
        $statement = $connection->prepare($sSQLp);
        $statement->execute();

        $groupMembers = $this->getGroupMembers($groupID);

        foreach ($groupMembers as $member) {
            $sSQLr = 'INSERT INTO groupprop_'.$groupID." ( per_ID ) VALUES ( '".$member['per_ID']."' );";
            $statement = $connection->prepare($sSQLr);
            $statement->execute();
        }
    }

    public function disableGroupSpecificProperties($groupID)
    {
        $connection = Propel::getConnection();

        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQLp = 'DROP TABLE groupprop_'.$groupID;
        $statement = $connection->prepare($sSQLp);
        $statement->execute();

        // need to delete the master index stuff
        $sSQLp = 'DELETE FROM groupprop_master WHERE grp_ID = '.$groupID;
        $statement = $connection->prepare($sSQLp);
        $statement->execute();

        $sSQL = 'UPDATE group_grp SET grp_hasSpecialProps = false
            WHERE grp_ID = '.$groupID;

        $statement = $connection->prepare($sSQL);
        $statement->execute();
    }

    public function getGroupMembers($groupID, $personID = null)
    {
        $connection = Propel::getConnection();

        $whereClause = '';
        if (is_numeric($personID)) {
            $whereClause = ' AND p2g2r_per_ID = '.$personID;
        }

        $members = [];
        // Main select query
        $sSQL = 'SELECT p2g2r_per_ID, p2g2r_grp_ID, p2g2r_rle_ID, lst_OptionName FROM person2group2role_p2g2r

        INNER JOIN group_grp ON
        person2group2role_p2g2r.p2g2r_grp_ID = group_grp.grp_ID

        INNER JOIN list_lst ON
        group_grp.grp_RoleListID = list_lst.lst_ID AND
        person2group2role_p2g2r.p2g2r_rle_ID =  list_lst.lst_OptionID

        WHERE p2g2r_grp_ID ='.$groupID.' '.$whereClause;

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        while ($row = $statement->fetch( \PDO::FETCH_BOTH ) ) {
            //on teste si les propriétés sont bonnes
            if (array_key_exists('p2g2r_per_ID',$row) && array_key_exists('lst_OptionName',$row))
            {
                $dbPerson = PersonQuery::create()->findPk($row['p2g2r_per_ID']);

                if (!array_key_exists('displayName',$dbPerson->toArray()))
                {
                    $person['per_ID'] = $dbPerson->getId();
                    $person['displayName'] = $dbPerson->getFullName();
                    $person['groupRole'] = $row['lst_OptionName'];
                    array_push($members, $person);
                }
            }
        }

        return $members;
    }

}
