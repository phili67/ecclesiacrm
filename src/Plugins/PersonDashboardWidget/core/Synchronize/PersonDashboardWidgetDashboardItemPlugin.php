<?php

namespace Plugins\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\Synchronize\PersonDashboardItem;

class PersonDashboardWidgetDashboardItemPlugin implements DashboardItemInterface
{
    public static function getDashboardItemName()
    {
        return 'PersonDashboardWidget';
    }

    public static function getDashboardItemValue()
    {
        return ['personCount' => PersonDashboardItem::getMembersCount()];
    }

    public static function shouldInclude($PageName)
    {
        return $PageName === '/v2/dashboard' || $PageName === '/v2/people/dashboard';
    }
}
