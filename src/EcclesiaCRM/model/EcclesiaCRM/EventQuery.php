<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\EventQuery as BaseEventQuery;
use EcclesiaCRM\Map\CalendarinstancesTableMap;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\PrincipalsTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for performing query and update operations on the 'events_event' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class EventQuery extends BaseEventQuery
{
    public function preSelect(ConnectionInterface $con)
    {
        $this->addJoin(EventTableMap::COL_EVENT_TYPE, EventTypesTableMap::COL_TYPE_ID, Criteria::LEFT_JOIN)
            ->addJoin(EventTableMap::COL_EVENT_GRPID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
            ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID, Criteria::LEFT_JOIN)
            ->addJoin(CalendarinstancesTableMap::COL_PRINCIPALURI, PrincipalsTableMap::COL_URI, Criteria::LEFT_JOIN)
            ->addAsColumn('EventTypeName', EventTypesTableMap::COL_TYPE_NAME)
            ->addAsColumn('GroupName', GroupTableMap::COL_GRP_NAME)
            ->addAsColumn('CalendarName', CalendarinstancesTableMap::COL_DISPLAYNAME)
            ->addAsColumn('rights', CalendarinstancesTableMap::COL_ACCESS)
            ->addAsColumn('CalendarType', CalendarinstancesTableMap::COL_CAL_TYPE)
            ->addAsColumn('login', PrincipalsTableMap::COL_URI)
            ->groupById();

        parent::preSelect($con);
    }
}
