<?php

//
// Philippe Logel :
// I re-put the code at the right place it was :
// Menu events should be in MenuEventsCount.php
// It's important for a new dev
// It was my code ...
// Last this code was two times in different parts
//

namespace EcclesiaCRM\Dashboard;

use EcclesiaCRM\Dashboard\DashboardItemInterface;

class CalendarDashboardItem implements DashboardItemInterface {

    public static function getDashboardItemName() {
        return "CalendarDisplay";
    }

    public static function getDashboardItemValue() {
        $calendarUpdate = array ();

        return $calendarUpdate;
    }

    public static function shouldInclude($PageName) {
        return $PageName=="/v2/calendar"; // this ID would be found on all pages.
    }
}
