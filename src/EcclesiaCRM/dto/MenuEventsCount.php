<?php
/*
 * File : MenuEventsCount.php
 *
 * Created by   : Philippe by Hand.
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 * Time         : 2018/05/13 3:00 AM.
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


use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\VObject;
use EcclesiaCRM\MyVCalendar;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use Propel\Runtime\Propel;


class MenuEventsCount
{
    // this code won't work because of the reccurence event
    // it's hear for eventually next Devs
    public static function getEventsOfToday()
    {
        $start_date = date("Y-m-d ")." 00:00:00";
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 day'));

        $activeEvents = EventQuery::create()
            ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID,Criteria::RIGHT_JOIN) // we have to filter only the user calendars
            ->addJoin(CalendarinstancesTableMap::COL_PRINCIPALURI, PrincipalsTableMap::COL_URI,Criteria::RIGHT_JOIN)       // so we have to retrieve the principal user
            ->where("event_start <= '".$start_date ."' AND event_end >= '".$end_date."'"." AND ".PrincipalsTableMap::COL_URI."='principals/".strtolower($_SESSION['user']->getUserName())."'") // the large events 
            ->_or()->where("event_start>='".$start_date."' AND event_end <= '".$end_date."'"." AND ".PrincipalsTableMap::COL_URI."='principals/".strtolower($_SESSION['user']->getUserName())."'") // the events of the day
            ->find();
        return  $activeEvents;
    }

    public static function getNumberEventsOfToday()
    {
        //return count(self::getEventsOfToday()); // old code
        
        // this isn't really optimized but this is the real answer of the question
        $start_date = date("Y-m-d ")." 00:00:00";
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 day'));

        // new way to manage events
        // we get the PDO for the Sabre connection from the Propel connection
        $pdo = Propel::getConnection();         
        
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());
        // get all the calendars for the current user
    
        $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower($_SESSION['user']->getUserName()),"displayname",false);
        
        $count = 0;
        
        foreach ($calendars as $calendar) {
          $eventsForCal = $calendarBackend->getCalendarObjects($calendar['id']);
          
          foreach ($eventsForCal as $eventForCal) {
          
            $calObj = $calendarBackend->getCalendarObject($calendar['id'],$eventForCal['uri']);
            $vcalendar = VObject\Reader::read($calObj['calendardata']);
            
            // we have to expand the event to get the right events in the range fixed over
            $newVCalendar = $vcalendar->expand(new \DateTime($start_date), new \DateTime($end_date));
            $count += count($newVCalendar->VEVENT);
            
          }
          
        }

        return $count;
    }

    public static function getBirthDates()
    {
        $peopleWithBirthDays = PersonQuery::create()
            ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
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
