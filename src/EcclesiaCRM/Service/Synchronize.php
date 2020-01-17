<?php

namespace EcclesiaCRM\Service;

class Synchronize
{

  public static function getDashboardItems($PageName) {
     $DashboardItems = array (
       "EcclesiaCRM\Synchronize\EventsDashboardItem",
       "EcclesiaCRM\Synchronize\ClassificationDashboardItem",
       "EcclesiaCRM\Synchronize\FamilyDashboardItem",
       "EcclesiaCRM\Synchronize\GroupsDashboardItem",
       "EcclesiaCRM\Synchronize\PersonDashboardItem",
       "EcclesiaCRM\Synchronize\PersonDemographicDashboardItem",
       "EcclesiaCRM\Synchronize\MailchimpDashboardItem",
       "EcclesiaCRM\Synchronize\SundaySchoolDashboardItem",
       "EcclesiaCRM\Synchronize\CalendarPageItem",
       "EcclesiaCRM\Synchronize\EDrivePageItem"
    );
    $ReturnValues = array ();
    Foreach ($DashboardItems as $DashboardItem) {
      if ($DashboardItem::shouldInclude($PageName)){
        array_push($ReturnValues, $DashboardItem);
      }
    }
    return $ReturnValues;

  }
  public static function getValues($PageName) {
    $ReturnValues = array ();
    Foreach (self::getDashboardItems($PageName) as $DashboardItem) {
        $ReturnValues[$DashboardItem::getDashboardItemName()] = $DashboardItem::getDashboardItemValue();
    }
    return $ReturnValues;
  }

}
