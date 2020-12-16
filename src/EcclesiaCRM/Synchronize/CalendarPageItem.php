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
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\MyPDO\CalDavPDO;

class CalendarPageItem implements DashboardItemInterface {

    public static function getDashboardItemName() {
        return "CalendarDisplay";
    }

    private static function getAllCalendarsMD5 () {
        // new way to manage events
        // We set the BackEnd for sabre Backends
        $calendarBackend  = new CalDavPDO();

        $types = ["personal", "group", "reservation", "share"];
        $only_visible = false;
        $all_calendars = false;

        $return = "";

        // get all the calendars for the current user
        foreach ($types as $type) {
            $calendars = $calendarBackend->getCalendarsForUser('principals/' . strtolower(SessionUser::getUser()->getUserName()), ($type == 'all') ? true : false);

            foreach ($calendars as $calendar) {
                $return .= $calendar['{DAV:}displayname'];
                $return .= $calendar['{http://apple.com/ns/ical/}calendar-color'];
                $return .= $calendar['uri'];
            }
        }

        return md5($return);
    }

    public static function getDashboardItemValue() {
        $signature = self::getAllCalendarsMD5();

        return $signature;
    }

    public static function shouldInclude($PageName) {
        return $PageName=="/v2/calendar"; // this ID would be found on all pages.
    }
}
