<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\GroupQuery as BaseGroupQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'group_grp' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */

use \Propel\Runtime\Connection\ConnectionInterface;
use \Propel\Runtime\ActiveQuery\Join;
use \Propel\Runtime\ActiveQuery\Criteria;

class GroupQuery extends BaseGroupQuery
{
    public function preSelect(ConnectionInterface $con)
    {
        $this->leftJoinGroupType();
        $this->withColumn('GroupType.ListOptionId','ListOptionId');
        $this->leftJoinPerson2group2roleP2g2r();

        $groupTypeJoin1 = new Join();
        $groupTypeJoin1->addCondition("person2group2role_p2g2r.PersonId", "person_per.per_ID", self::EQUAL );
        $groupTypeJoin1->setJoinType(Criteria::LEFT_JOIN);
        $this->addJoinObject($groupTypeJoin1);

        $this->where('person_per.per_datedeactivated is NULL');

        $this->withColumn('COUNT(person_per.per_ID)', 'memberCount');
        $this->groupBy('Group.Id');
        $groupTypeJoin = new Join();
        $groupTypeJoin->addCondition("GroupType.ListOptionId", "list_lst.lst_OptionId", self::EQUAL );
        $groupTypeJoin->addForeignValueCondition("list_lst", "lst_ID", '', 3, self::EQUAL);
        $groupTypeJoin->setJoinType(Criteria::LEFT_JOIN);
        $this->addJoinObject($groupTypeJoin);
        $this->withColumn('list_lst.lst_OptionName', 'groupType');
        $this->withColumn('list_lst.lst_Type', 'groupOptionType');
        parent::preSelect($con);
    }
}
