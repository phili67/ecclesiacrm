<?php

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use Propel\Runtime\Propel;
use EcclesiaCRM\EventAttendQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class AttendeesPageItem implements DashboardItemInterface
{

    public static function getDashboardItemName()
    {
        return "EventAttendeesDisplay";
    }

    public static function getDashboardItemValue()
    {
        $countAttend = EventAttendQuery::create()
            ->filterByCheckoutId(null, Criteria::EQUAL)
            ->find()
            ->count();

        return ['EventCountAttend' => $countAttend];
    }

    public static function shouldInclude($PageName)
    {
        return $PageName == "/v2/dashboard" or $PageName == "/menu";
    }
}
