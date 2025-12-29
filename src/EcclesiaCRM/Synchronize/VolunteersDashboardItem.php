<?php

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\VolunteerOpportunityQuery;

class VolunteersDashboardItem implements DashboardItemInterface
{

    public static function getDashboardItemName()
    {
        return "VolunteersDisplay";
    }

    public static function getDashboardItemValue()
    {        
        $volunteerOpportunities = VolunteerOpportunityQuery::create()->filterByActive('true')->find();

        $data = ['volunteers' => $volunteerOpportunities->count()];

        return $data;
    }

    public static function shouldInclude($PageName)
    {
        return $PageName == "/v2/dashboard" || $PageName == "/v2/people/dashboard";
    }
}
