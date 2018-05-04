<?php 

// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.0.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\CalendarinstancesQuery;
use EcclesiaCRM\EventQuery;

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

$logger = LoggerUtils::getAppLogger();

// new way to manage events
// we get the PDO for the Sabre connection from the Propel connection
$pdo = Propel::getConnection();         
        
// We set the BackEnd for sabre Backends
$calendarBackend = new CalDavPDO($pdo->getWrappedConnection());

$logger->info("Start to translate the event from the old system to the new");
  
// we peek all the groups
$groups = GroupQuery::Create()->find();
  
  foreach ($groups as $group) {
    if ($group->getId()) {
      // first we create all the calendars
      $returnedId = $calendarBackend->createCalendar('principals/admin', \Sabre\DAV\UUIDUtil::getUUID() , [
              '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
              '{DAV:}displayname'                                               => $group->getName(),
              '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),
      ]);
      
      print_r($returnedId);
       
      $calendar = CalendarinstancesQuery::Create()->findOneByCalendarid($returnedId[0]);
      if ($calendar != null) {
        $calendar->setGroupId ($group->getId());
        $calendar->save();
      }
      
      $events = EventQuery::Create()->filterBySize(0)->findByGroupId($group->getId());
      
      foreach ($events as $event) {
        $uuid = \Sabre\DAV\UUIDUtil::getUUID();
        
        $vcalendar = new EcclesiaCRM\MyVCalendar\VCalendarExtension();
        $vcalendar->add(
           'VEVENT', [
            'CREATED'=> (new \DateTime('Now'))->format('Ymd\THis'),
            'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
            'DTSTART' => $event->getStart()->format('Ymd\THis'),
            'DTEND' => $event->getEnd()->format('Ymd\THis'),
            'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
            'DESCRIPTION' => $event->getDesc(),              
            'SUMMARY' => $event->getTitle(),
            'UID' => $uuid,
            'SEQUENCE' => '0',
            'TRANSP' => 'OPAQUE'
          ]);
          
          $calendar = CalendarinstancesQuery::Create()->findOneByGroupId($group->getId());
          
          $calIDs = [$calendar->getCalendarid(),0];
          
          $etag = $calendarBackend->createCalendarObject($calIDs, $uuid, $vcalendar->serialize());
          
          //echo $etag."<br>";
          
          $newEvent = EventQuery::Create()->findOneByEtag(str_replace('"','',$etag));
          
          $calData = $newEvent->getCalendardata();
          $uri = $newEvent->getUri();
          $calid = $newEvent->getEventCalendarid();
          $lastModify = $newEvent->getLastmodified();
          $etag = $newEvent->getEtag();
          $sze = $newEvent->getSize();
          $componentType = $newEvent->getComponenttype();
          $Uid = $newEvent->getUid();
          
          $newEvent->delete();
          
          $event->setCalendardata($calData);
          $event->setUri($uri);
          $event->setEventCalendarid($calid);
          $event->setLastmodified($lastModify);
          $event->setEtag($etag);
          $event->setSize($sze);
          $event->setComponenttype($componentType);
          $event->setUid($Uid);
          
          $event->save();
      }
      
    }
  }

  
  $logger->info("End of translate");
?>