<?php

namespace EcclesiaCRM;

use EcclesiaCRM\dto\KioskAssignmentTypes;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\Base\KioskAssignment as BaseKioskAssignment;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\ActiveQuery\Criteria;


/**
 * Skeleton subclass for representing a row from the 'kioskassginment_kasm' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class KioskAssignment extends BaseKioskAssignment
{

    private function getActiveEvent()
    {
        if ($this->getAssignmentType() == KioskAssignmentTypes::EVENTATTENDANCEKIOSK) {
            $Event = EventQuery::create()
                ->filterByStart('now', Criteria::LESS_EQUAL)
                ->filterByEnd('now', Criteria::GREATER_EQUAL)
                ->_and()->filterById($this->getEventId())
                ->findOne();

            return $Event;
        } else {
            throw new \Exception("This kiosk does not support group attendance");
        }
    }

    public function getActiveGroupMembers()
    {
        if ($this->getAssignmentType() == KioskAssignmentTypes::EVENTATTENDANCEKIOSK) {
            if (is_null($this->getActiveEvent())) {
                return NULL;
            }

            $groupTypeJoin = new Join();
            $groupTypeJoin->addCondition("Person2group2roleP2g2r.RoleId", "list_lst.lst_OptionId", Join::EQUAL);
            $groupTypeJoin->addForeignValueCondition("list_lst", "lst_ID", '', $this->getActiveEvent()->getGroup()->getRoleListId(), Join::EQUAL);
            $groupTypeJoin->setJoinType(Criteria::LEFT_JOIN);

            //Get Event Attendees details
            $ssClass = EventAttendQuery::create()
                ->joinWithPerson()
                ->usePersonQuery()
                    ->joinWithPerson2group2roleP2g2r()
                    ->usePerson2group2roleP2g2rQuery()
                        ->filterByGroupId($this->getEvent()->getGroupId())
                        ->joinGroup()
                        ->addJoinObject($groupTypeJoin)
                        ->withColumn(ListOptionTableMap::COL_LST_OPTIONNAME, "RoleName")
                    ->endUse()
                    ->orderByFirstName()
                ->endUse()
                ->filterByEventId($this->getActiveEvent()->getId())
                ->withColumn("(CASE WHEN event_attend.checkin_date IS NULL then 0 else 1 end)", "checkedIn")
                ->withColumn("(CASE WHEN event_attend.checkout_date IS NULL then 0 else 1 end)", "checkedOut")
                ->find();

            LoggerUtils::getAppLogger()->info(print_r($ssClass->toArray(),true));


            return $ssClass;
        } else {
            throw new \Exception("This kiosk does not support group attendance");
        }
    }

}
