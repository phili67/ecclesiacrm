<?php
/*
 * File : MenuEventsCount.php
 *
 * Created by : Philippe by Hand.
 * User: copyright Philippe Logel all right reserved not MIT licence
 * Date: 2018/05/02
 * Time: 3:00 AM.
 */

namespace EcclesiaCRM\dto;
 
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Event;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\CalendarinstancesTableMap;
use EcclesiaCRM\Map\PrincipalsTableMap;
use Propel\Runtime\ActiveQuery\Criteria;


class MenuEventsCount
{
    public static function getEventsOfToday()
    {
        $start_date = date("Y-m-d ")." 00:00:00";
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 day'));

        $activeEvents = EventQuery::create()
            ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID,Criteria::RIGHT_JOIN) // we have to filter only the user calendars
            ->addJoin(CalendarinstancesTableMap::COL_PRINCIPALURI, PrincipalsTableMap::COL_URI,Criteria::RIGHT_JOIN)       // so we have to retrieve the principal user
            ->where("event_start <= '".$start_date ."' AND event_end >= '".$end_date."'"." AND ".PrincipalsTableMap::COL_URI."='principals/".strtolower($_SESSION['user']->getUserName())."'") /* the large events */
            ->_or()->where("event_start>='".$start_date."' AND event_end <= '".$end_date."'"." AND ".PrincipalsTableMap::COL_URI."='principals/".strtolower($_SESSION['user']->getUserName())."'") /* the events of the day */
            ->find();

        return  $activeEvents;
    }

    public static function getNumberEventsOfToday()
    {
        return count(self::getEventsOfToday());
    }

    public static function getBirthDates()
    {
        $peopleWithBirthDays = PersonQuery::create()
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
              ->filterByDateDeactivated(null, Criteria::EQUAL) //Date Deactivated is null (active)
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
