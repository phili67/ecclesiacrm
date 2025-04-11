<?php

//
// Philippe Logel :
// I re-put the code at the right place it was :
// Menu events should be in MenuEventsCount.php
// It's important for a new dev
// It was my code ...
// Last this code was two times in different parts
//

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\dto\MenuEventsCount;

class EventsDashboardItem implements DashboardItemInterface {

  public static function getDashboardItemName() {
    return "EventsCounters";
  }

  public static function getDashboardItemValue() {
    $activeEvents = array (
        "Events" => MenuEventsCount::getNumberEventsOfToday(),
        "Birthdays" => MenuEventsCount::getNumberBirthDates(),
        "Anniversaries" => MenuEventsCount::getNumberAnniversaries()
    );

    return $activeEvents;
  }

  public static function shouldInclude($PageName) {
    return true; // this ID would be found on all pages.
  }
}
