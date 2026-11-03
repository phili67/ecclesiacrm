<?php

namespace Plugins\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\Synchronize\FamilyDashboardItem;

class FamilyInfosDashboardItemPlugin implements DashboardItemInterface
{
    public static function getDashboardItemName()
    {
        return 'FamilyInfosDashboard';
    }

    public static function getDashboardItemValue()
    {
        return [
            'LatestFamilies' => FamilyDashboardItem::getLatestFamilies(),
            'UpdatedFamilies' => FamilyDashboardItem::getUpdatedFamilies(),
        ];
    }

    public static function shouldInclude($PageName)
    {
        return $PageName === '/v2/dashboard' || $PageName === '/v2/people/dashboard';
    }
}
