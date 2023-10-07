<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Person2group2roleP2g2r as BasePerson2group2roleP2g2r;
use EcclesiaCRM\Utils\MiscUtils;

/**
 * Skeleton subclass for representing a row from the 'person2group2role_p2g2r' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Person2group2roleP2g2r extends BasePerson2group2roleP2g2r
{
    public function preSave(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');        

        return parent::preSave($con);;
    }

    public function preUpdate(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');        

        return parent::preUpdate($con);;
    }

    public function preDelete(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');        

        return parent::preDelete($con);;
    }

    public function preInsert(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');        

        return parent::preInsert($con);;
    }
}
