<?php

namespace EcclesiaCRM\Service;
class NewDashboardService
{
  
  public static function getDashboardItems($PageName) {
     $DashboardItems = array (
       "EcclesiaCRM\Dashboard\EventsDashboardItem",
       "EcclesiaCRM\Dashboard\ClassificationDashboardItem",
       "EcclesiaCRM\Dashboard\FamilyDashboardItem",
       "EcclesiaCRM\Dashboard\GroupsDashboardItem",
       "EcclesiaCRM\Dashboard\PersonDashboardItem",
       "EcclesiaCRM\Dashboard\PersonDemographicDashboardItem",
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