<?php
/*
 * File : MenuEventsCount.php
 *
 * Created by   : Philippe by Hand.
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 * Time         : 2020/07/04 3:00 AM.
 */

namespace EcclesiaCRM\dto;

use EcclesiaCRM\EventQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\CalendarinstancesTableMap;
use EcclesiaCRM\Map\PrincipalsTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;


use Sabre\VObject;
use EcclesiaCRM\MyPDO\CalDavPDO;
use Propel\Runtime\Propel;


class MenuEventsCount
{
    // this code won't work because of the reccurence event
    // it's hear for eventually next Devs
    public static function getEventsOfToday()
    {
        $start_date = date("Y-m-d ") . " 00:00:00";
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 day'));

        $activeEvents = EventQuery::create()
            ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID, Criteria::RIGHT_JOIN) // we have to filter only the user calendars
            ->addJoin(CalendarinstancesTableMap::COL_PRINCIPALURI, PrincipalsTableMap::COL_URI, Criteria::RIGHT_JOIN)       // so we have to retrieve the principal user
            ->where("event_start <= '" . $start_date . "' AND event_end >= '" . $end_date . "'" . " AND " . PrincipalsTableMap::COL_URI . "='principals/" . strtolower(SessionUser::getUser()->getUserName()) . "'") // the large events
            ->_or()->where("event_start>='" . $start_date . "' AND event_end <= '" . $end_date . "'" . " AND " . PrincipalsTableMap::COL_URI . "='principals/" . strtolower(SessionUser::getUser()->getUserName()) . "'") // the events of the day
            ->find();
        return $activeEvents;
    }

    public static function getNumberEventsOfToday()
    {
        //return count(self::getEventsOfToday()); // old code

        // this isn't really optimized but this is the real answer of the question
        $start_date = date("Y-m-d ") . " 00:00:00";
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 day'));

        // new way to manage events
        // we get the PDO for the Sabre connection from the Propel connection
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();

        // get all the calendars for the current user
        $calendars = $calendarBackend->getCalendarsForUser('principals/' . strtolower(SessionUser::getUser()->getUserName()), "displayname", false);

        $count = 0;

        $events = [];

        foreach ($calendars as $calendar) {
            $eventsForCal = $calendarBackend->getCalendarObjects($calendar['id']);

            foreach ($eventsForCal as $eventForCal) {

                $calObj = $calendarBackend->getCalendarObject($calendar['id'], $eventForCal['uri']);
                $vcalendar = VObject\Reader::read($calObj['calendardata']);

                // we have to expand the event to get the right events in the range fixed over
                // the events are formatted with expand in Z DateTimeZone, so we have to change DateTimeZone, to the right date
                // TO DO : in rare cases the DateTimeZone isn't the same as the now time !!!!
                $newVCalendar = $vcalendar->expand(new \DateTime($start_date), new \DateTime($end_date), new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
                if (!is_null($newVCalendar->VEVENT)) {
                    $count += count($newVCalendar->VEVENT);
                }

                if (!is_null($newVCalendar->VEVENT)) {
                    foreach ($newVCalendar->VEVENT->VALARM as $alarm) {
                        $attr = $alarm->children();
                        foreach ($attr as $attrInfo) {
                            if ($attrInfo->name == 'TRIGGER' && $attrInfo->getValue() != 'NONE') {
                                $trigger = $attrInfo->getValue();
                                $summary = $newVCalendar->VEVENT->summary->getValue();
                                $event_Date = $newVCalendar->VEVENT->dtstart->getValue();

                                $event_dateDT1 = new \DateTime($event_Date);
                                $event_dateDT0 = new \DateTime($event_Date);

                                // we've to format the DateTime in Z DateTimeZone, to compare them to the $newVCalendar
                                $date_now = new \DateTime("now", new \DateTimeZone('Z'));

                                $difference = $date_now->diff($event_dateDT1);

                                $in_time = '';
                                $diplay_alarm = false;
                                switch ($trigger) {
                                    case '-PT5M':
                                        $in_time = '5'." "._("minutes");
                                        $interval = new \DateInterval("PT5M");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > 4*60 && $diff < 5*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'PT10M':
                                        $in_time ='10'." "._("minutes");
                                        $interval = new \DateInterval("PT10M");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > 9*60 && $diff < 10*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'PT15M':
                                        $in_time ='15'." "._("minutes");
                                        $interval = new \DateInterval("PT15M");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > 14*60 && $diff < 15*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'PT30M':
                                        $in_time ='30'." "._("minutes");
                                        $interval = new \DateInterval("PT30M");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > 29*60 && $diff < 30*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'PT1H':
                                        $in_time ='1'." "._("hour");
                                        $interval = new \DateInterval("PT1H");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > 59*60 && $diff < 60*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'PT2H':
                                        $in_time ='2'." "._("hour");
                                        $interval = new \DateInterval("PT2H");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > (60+59)*60 && $diff < 2*60*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'P1D':
                                        $in_time ='1'." "._("day");
                                        $interval = new \DateInterval("P1D");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > (23*60+59)*60 && $diff < 24*60*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                    case 'P2D':
                                        $in_time ='2'." "._("day");
                                        $interval = new \DateInterval("P2D");
                                        $event_dateDT0->sub($interval);
                                        $diff = $difference->format("%s");
                                        $diff += $difference->format("%i")*60;
                                        if ( $diff > ((24+23)*60+59)*60 && $diff < 2*24*60*60 || $diff < SystemConfig::getValue('iDashboardPageServiceIntervalTime') ) {
                                            $diplay_alarm = true;
                                        }
                                        break;
                                }

                                if ($event_dateDT0 < $date_now && $date_now < $event_dateDT1) {
                                    $res = ['summary' => $summary,
                                        'TRIGGER' => $trigger,
                                        'in_time' => $in_time,
                                        'diplayAlarm' => $diplay_alarm,
                                        'diff' => $diff];

                                    $events[] = $res;
                                }

                                //$events[] = [$trigger, $event_dateDT0, $date_now, $event_dateDT1];
                            }
                        }
                    }
                }
            }
        }

        return ['count' => $count, 'alarms' => $events];
    }

    public static function getBirthDates()
    {
        $peopleWithBirthDays = PersonQuery::create()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->filterByBirthMonth(date('m'))
            ->filterByBirthDay(date('d'))
            ->find();

        return $peopleWithBirthDays;
    }

    public static function getNumberBirthDates()
    {
        return count(self::getBirthDates());
    }

    public static function getAnniversaries()
    {
        $Anniversaries = FamilyQuery::create()
            ->filterByWeddingDate(['min' => '0001-00-00']) // a Wedding Date
            ->filterByDateDeactivated(null, Criteria::EQUAL) // GDRP, Date Deactivated is null (active)
            ->find();

        $curDay = date('d');
        $curMonth = date('m');

        $families = [];
        foreach ($Anniversaries as $anniversary) {
            if ($anniversary->getWeddingMonth() == $curMonth && $curDay == $anniversary->getWeddingDay()) {
                $families[] = $anniversary;
            }
        }

        return $families;
    }

    public static function getNumberAnniversaries()
    {
        return count(self::getAnniversaries());
    }
}
