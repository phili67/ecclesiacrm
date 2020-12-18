<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\PersonQuery;

use Sabre\CardDAV;
use Sabre\DAV;

use EcclesiaCRM\MyPDO\CardDavPDO;
use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\MiscUtils;

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
        $sSQL = 'DELETE FROM person2group2role_p2g2r WHERE p2g2r_per_ID = '.$personID.' AND p2g2r_grp_ID = '.$groupID;
        RunQuery($sSQL);

        // Check if this group has special properties
        $sSQL = 'SELECT grp_hasSpecialProps FROM group_grp WHERE grp_ID = '.$groupID;
        $rsTemp = RunQuery($sSQL);
        $rowTemp = mysqli_fetch_row($rsTemp);
        $bHasProp = $rowTemp[0];

        if ($bHasProp == 1) {
            $sSQL = 'DELETE FROM groupprop_'.$groupID." WHERE per_ID = '".$personID."'";
            RunQuery($sSQL);
        }

        // Reset any group specific property fields of type "Person from Group" with this person assigned
        $sSQL = 'SELECT grp_ID, prop_Field FROM groupprop_master WHERE type_ID = 9 AND prop_Special = '.$groupID;
        $result = RunQuery($sSQL);
        while ($aRow = mysqli_fetch_array($result)) {
            $sSQL = 'UPDATE groupprop_'.$aRow['grp_ID'].' SET '.$aRow['prop_Field'].' = NULL WHERE '.$aRow['prop_Field'].' = '.$personID;
            RunQuery($sSQL);
        }

        // Reset any custom person fields of type "Person from Group" with this person assigned
        $sSQL = 'SELECT custom_Field FROM person_custom_master WHERE type_ID = 9 AND custom_Special = '.$groupID;
        $result = RunQuery($sSQL);
        while ($aRow = mysqli_fetch_array($result)) {
            $sSQL = 'UPDATE person_custom SET '.$aRow['custom_Field'].' = NULL WHERE '.$aRow['custom_Field'].' = '.$personID;
            RunQuery($sSQL);
        }

      // we'll connect to sabre to create the group
      // we'll delete the card from the member
      $pdo = Propel::getConnection();

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
        global $cnInfoCentral;

        // Was a RoleID passed in?
        if ($iRoleID == 0) {
            // No, get the Default Role for this Group
            $sSQL = 'SELECT grp_DefaultRole FROM group_grp WHERE grp_ID = '.$iGroupID;
            $rsRoleID = RunQuery($sSQL);
            $Row = mysqli_fetch_row($rsRoleID);
            $iRoleID = $Row[0];
        }

        $sSQL = 'INSERT INTO person2group2role_p2g2r (p2g2r_per_ID, p2g2r_grp_ID, p2g2r_rle_ID) VALUES ('.$iPersonID.', '.$iGroupID.', '.$iRoleID.')';
        $result = RunQuery($sSQL, false);
        if ($result) {
            // Check if this group has special properties
            $sSQL = 'SELECT grp_hasSpecialProps FROM group_grp WHERE grp_ID = '.$iGroupID;
            $rsTemp = RunQuery($sSQL);
            $rowTemp = mysqli_fetch_row($rsTemp);
            $bHasProp = $rowTemp[0];

            if ($bHasProp == 'true') {
                $sSQL = 'INSERT INTO groupprop_'.$iGroupID." (per_ID) VALUES ('".$iPersonID."')";
                RunQuery($sSQL);
            }
        }

        // we get the person info
        $person = PersonQuery::create()->findPk($iPersonID);

        // We set the BackEnd for sabre Backends
        // we'll connect to sabre to create the group
        // we'll delete the card from the member
        $pdo = Propel::getConnection();

        $carddavBackend = new CardDavPDO();

        $addressbookId = $carddavBackend->getAddressBookForGroup ($iGroupID)['id'];

         // now we'll create all the cards
        $card = 'BEGIN:VCARD
VERSION:3.0
PRODID:-//Apple Inc.//Mac OS X 10.12.6//EN
N:'.$person->getLastName().';'.$person->getFirstName().';'.$person->getMiddleName().';;
FN:'.$person->getFirstName().' '.$person->getLastName();

        if ( !empty($person->getWorkEmail()) ) {
          $card .="\nEMAIL;type=INTERNET;type=WORK;type=pref:".$person->getWorkEmail();
        }
        if ( !empty($person->getEmail()) ) {
          $card .="\nEMAIL;type=INTERNET;type=HOME;type=pref:".$person->getEmail();
        }

        if ( !empty($person->getHomePhone()) ) {
          $card .="\nTEL;type=HOME;type=VOICE;type=pref:".$person->getHomePhone();
        }

        if ( !empty($person->getCellPhone()) ) {
          $card .="\nTEL;type=CELL;type=VOICE:".$person->getCellPhone();
        }

        if ( !empty($person->getWorkPhone()) ) {
          $card .="\nTEL;type=WORK;type=VOICE:".$person->getWorkPhone();
        }

        if ( !empty($person->getAddress1()) || !empty($person->getCity()) || !empty($person->getZip()) ) {
          $card .="\nitem1.ADR;type=HOME;type=pref:;;".$person->getAddress1().';'.$person->getCity().';;'.$person->getZip();
        } else if (!is_null ($person->getFamily())) {
          $card .="\nitem1.ADR;type=HOME;type=pref:;;".$person->getFamily()->getAddress1().';'.$person->getFamily()->getCity().';;'.$person->getFamily()->getZip();
        }

        $card .= "\nitem1.X-ABADR:fr
UID:".\Sabre\DAV\UUIDUtil::getUUID().'
END:VCARD';

        $carddavBackend->createCard(addressbookId, 'UUID-'.\Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());

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
        $sSQL = 'SELECT grp_ID, lst_OptionName, lst_OptionID, lst_OptionSequence
              FROM group_grp
              LEFT JOIN list_lst ON
              list_lst.lst_ID = group_grp.grp_RoleListID
              WHERE group_grp.grp_ID = '.$groupID;
        $rsList = RunQuery($sSQL);

        // Validate that this list ID is really for a group roles list. (for security)
        if (mysqli_num_rows($rsList) == 0) {
            throw new \Exception('invalid request');
        }

        while ($row = mysqli_fetch_assoc($rsList)) {
            array_push($groupRoles, $row);
        }

        return $groupRoles;
    }



    public function setGroupRoleOrder($groupID, $groupRoleID, $groupRoleOrder)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQL = 'UPDATE list_lst
                 INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 SET list_lst.lst_OptionSequence = "'.$groupRoleOrder.'"
                 WHERE group_grp.grp_ID = "'.$groupID.'"
                    AND list_lst.lst_OptionID = '.$groupRoleID;
        RunQuery($sSQL);
    }

    public function getGroupRoleOrder($groupID, $groupRoleID)
    {
        $sSQL = 'SELECT list_lst.lst_OptionSequence FROM list_lst
                INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 WHERE group_grp.grp_ID = "'.$groupID.'"
                   AND list_lst.lst_OptionID = '.$groupRoleID;

        $rsPropList = RunQuery($sSQL);
        $rowOrder = mysqli_fetch_array($rsPropList);

        return $rowOrder[0];
    }

    public function deleteGroupRole($groupID, $groupRoleID)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQL = 'SELECT * FROM list_lst
                INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 WHERE group_grp.grp_ID = "'.$groupID.'"';
        $rsPropList = RunQuery($sSQL);
        $numRows = mysqli_num_rows($rsPropList);
        // Make sure we never delete the only option
        if ($numRows > 1) {
            $thisSequence = $this->getGroupRoleOrder($groupID, $groupRoleID);
            $sSQL = 'DELETE list_lst.* FROM list_lst
                    INNER JOIN group_grp
                        ON group_grp.grp_RoleListID = list_lst.lst_ID
                    WHERE group_grp.grp_ID = "'.$groupID.'"
                    AND lst_OptionID = '.$groupRoleID;

            RunQuery($sSQL);

            //check if we've deleted the old group default role.  If so, reset default to role ID 1
            // Next, if any group members were using the deleted role, reset their role to the group default.
            // Reset if default role was just removed.
            $sSQL = "UPDATE group_grp SET grp_DefaultRole = 1 WHERE grp_ID = $groupID AND grp_DefaultRole = $groupRoleID";
            RunQuery($sSQL);

            // Get the current default role and Group ID (so we can update the p2g2r table)
            // This seems backwards, but grp_RoleListID is unique, having a 1-1 relationship with grp_ID.
            $sSQL = "SELECT grp_ID,grp_DefaultRole FROM group_grp WHERE grp_ID = $groupID";
            $rsTemp = RunQuery($sSQL);
            $aTemp = mysqli_fetch_array($rsTemp);

            $sSQL = "UPDATE person2group2role_p2g2r SET p2g2r_rle_ID = 1 WHERE p2g2r_grp_ID = $groupID AND p2g2r_rle_ID = $groupRoleID";
            RunQuery($sSQL);

            //Shift the remaining rows IDs up by one

            $sSQL = 'UPDATE list_lst
                    INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                    SET list_lst.lst_OptionID = list_lst.lst_OptionID -1
                    WHERE group_grp.grp_ID = '.$groupID.'
                    AND list_lst.lst_OptionID >= '.$groupRoleID;

            RunQuery($sSQL);

            //Shift up the remaining row Sequences by one

            $sSQL = 'UPDATE list_lst
                    INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                    SET list_lst.lst_OptionSequence = list_lst.lst_OptionSequence -1
                    WHERE group_grp.grp_ID ='.$groupID.'
                    AND list_lst.lst_OptionSequence >= '.$thisSequence;

            //echo $sSQL;

            RunQuery($sSQL);

            return $this->getGroupRoles($groupID);
        } else {
            throw new \Exception('You cannot delete the only group');
        }
    }

    public function addGroupRole($groupID, $groupRoleName)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        if (strlen($groupRoleName) == 0) {
            throw new \Exception('New field name cannot be blank');
        } else {
            // Check for a duplicate option name
            $sSQL = 'SELECT \'\' FROM list_lst
                INNER JOIN group_grp
                    ON group_grp.grp_RoleListID = list_lst.lst_ID
                 WHERE group_grp.grp_ID = "'.$groupID.'" AND
                 lst_OptionName = "'.$groupRoleName.'"';
            $rsCount = RunQuery($sSQL);
            if (mysqli_num_rows($rsCount) > 0) {
                throw new \Exception('Field '.$groupRoleName.' already exists');
            } else {
                $sSQL = "SELECT grp_RoleListID FROM group_grp WHERE grp_ID = $groupID";
                $rsTemp = RunQuery($sSQL);
                $listIDTemp = mysqli_fetch_array($rsTemp);
                $listID = $listIDTemp[0];
                // Get count of the options
                $sSQL = "SELECT '' FROM list_lst WHERE lst_ID = $listID";
                $rsTemp = RunQuery($sSQL);
                $numRows = mysqli_num_rows($rsTemp);
                $newOptionSequence = $numRows + 1;

                // Get the new OptionID
                $sSQL = "SELECT MAX(lst_OptionID) FROM list_lst WHERE lst_ID = $listID";
                $rsTemp = RunQuery($sSQL);
                $aTemp = mysqli_fetch_array($rsTemp);
                $newOptionID = $aTemp[0] + 1;

                // Insert into the appropriate options table
                $sSQL = 'INSERT INTO list_lst (lst_ID, lst_OptionID, lst_OptionName, lst_OptionSequence)
                        VALUES ('.$listID.','.$newOptionID.",'".$groupRoleName."',".$newOptionSequence.')';

                RunQuery($sSQL);
                $iNewNameError = 0;
            }
        }

        return '{"newRole":{"roleID":"'.$newOptionID.'", "roleName":"'.$groupRoleName.'", "sequence":"'.$newOptionSequence.'"}}';
    }

    public function enableGroupSpecificProperties($groupID)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQL = 'UPDATE group_grp SET grp_hasSpecialProps = true
            WHERE grp_ID = '.$groupID;
        RunQuery($sSQL);
        $sSQLp = 'CREATE TABLE groupprop_'.$groupID." (
                        per_ID mediumint(8) unsigned NOT NULL default '0',
                        PRIMARY KEY  (per_ID),
                          UNIQUE KEY per_ID (per_ID)
                        ) ENGINE=InnoDB;";
        RunQuery($sSQLp);

        $groupMembers = $this->getGroupMembers($groupID);

        foreach ($groupMembers as $member) {
            $sSQLr = 'INSERT INTO groupprop_'.$groupID." ( per_ID ) VALUES ( '".$member['per_ID']."' );";
            RunQuery($sSQLr);
        }
    }

    public function disableGroupSpecificProperties($groupID)
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        $sSQLp = 'DROP TABLE groupprop_'.$groupID;
        RunQuery($sSQLp);

        // need to delete the master index stuff
        $sSQLp = 'DELETE FROM groupprop_master WHERE grp_ID = '.$groupID;
        RunQuery($sSQLp);

        $sSQL = 'UPDATE group_grp SET grp_hasSpecialProps = false
            WHERE grp_ID = '.$groupID;

        RunQuery($sSQL);
    }

    public function getGroupMembers($groupID, $personID = null)
    {
        global $cnInfoCentral;
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
        $result = mysqli_query($cnInfoCentral, $sSQL);
        while ($row = mysqli_fetch_assoc($result)) {
        	//on teste si les propriétés sont bonnes
        	if (array_key_exists('p2g2r_per_ID',$row) && array_key_exists('lst_OptionName',$row))
        	{
            $dbPerson = PersonQuery::create()->findPk($row['p2g2r_per_ID']);

       			if (array_key_exists('displayName',$dbPerson))
       			{
	            $person['displayName'] = $dbPerson->getFullName();
  	          $person['groupRole'] = $row['lst_OptionName'];
    	        array_push($members, $person);
    	      }
    	    }
        }

        return $members;
    }

}
