<?php

namespace EcclesiaCRM\Service;

class SynchronizeService
{

  public static function getDashboardItems($PageName) {
     $DashboardItems = array (
       "EcclesiaCRM\Synchronize\FamilyDashboardItem",
       "EcclesiaCRM\Synchronize\GroupsDashboardItem",
       "EcclesiaCRM\Synchronize\PersonDashboardItem",
       "EcclesiaCRM\Synchronize\PersonDemographicDashboardItem",
       "EcclesiaCRM\Synchronize\SundaySchoolDashboardItem",
       "EcclesiaCRM\Synchronize\EventsDashboardItem",
       "EcclesiaCRM\Synchronize\ClassificationDashboardItem",
       "EcclesiaCRM\Synchronize\MailchimpDashboardItem",
       "EcclesiaCRM\Synchronize\CalendarPageItem",
       "EcclesiaCRM\Synchronize\EDrivePageItem",
       "EcclesiaCRM\Synchronize\AttendeesPageItem"
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
