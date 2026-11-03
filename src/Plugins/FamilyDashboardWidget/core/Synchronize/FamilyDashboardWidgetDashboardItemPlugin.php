<?php

namespace Plugins\Synchronize;

use EcclesiaCRM\Service\PastoralCareService;
use EcclesiaCRM\Synchronize\DashboardItemInterface;

class FamilyDashboardWidgetDashboardItemPlugin implements DashboardItemInterface
{
    public static function getDashboardItemName()
    {
        return 'FamilyDashboardWidget';
    }

    public static function getDashboardItemValue()
    {
        $service = new PastoralCareService();

        return ['familyCount' => $service->getAllRealFamilies()->count()];
    }

    public static function shouldInclude($PageName)
    {
        return $PageName === '/v2/dashboard' || $PageName === '/v2/people/dashboard';
    }
}
