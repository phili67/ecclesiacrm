<?php
// Copyright 2018 Philippe Logel all right reserved
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Event;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\Utils\OutputUtils;


use EcclesiaCRM\CalendarinstancesQuery;

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

$app->group('/attendees', function () {

  $this->post('/checkoutstudent', function ($request, $response, $args) {
    /*if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }*/

      $cartPayload = (object)$request->getParsedBody();
      
      if ( isset ($cartPayload->personID) && isset ($cartPayload->eventID) && isset($cartPayload->checked) )
      {
        $eventAttent = EventAttendQuery::Create()
            ->filterByEventId($cartPayload->eventID)
            ->filterByPersonId($cartPayload->personID)
            ->findOne();
        
        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        
        if ($eventAttent) {
              $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
              if ($cartPayload->checked) {
                $eventAttent->setCheckoutDate($date->format('Y-m-d H:i:s'));
              } else {
                $eventAttent->setCheckoutDate(NULL);
              }
              $eventAttent->save();
        } else {
          try {
              $eventAttent = new EventAttend();        
              $eventAttent->setEventId($event->getID());
              $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
              $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
              $eventAttent->setPersonId($child['kidId']);
              $eventAttent->save();
          } catch (\Exception $ex) {
              $errorMessage = $ex->getMessage();
          }
        }
      }
      else
      {
        throw new \Exception(gettext("POST to cart requires a personID and an eventID"),500);
      }
      $person = PersonQuery::Create()->findOneById($_SESSION['user']->getPersonId());
      
      return $response->withJson(['status' => "success","name" => $person->getFullName(),"date" => OutputUtils::FormatDate($date->format('Y-m-d H:i:s'),1)]);
  });

  $this->post('/student', function ($request, $response, $args) {
    /*if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }*/

      $cartPayload = (object)$request->getParsedBody();
      
      if ( isset ($cartPayload->eventTypeID) && isset ($cartPayload->groupID))
      {
         $group = GroupQuery::Create()
            ->findOneById($cartPayload->groupID);
            
         $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
         
         $type = null;
         
         if ($cartPayload->eventTypeID)
         {
           $type = EventTypesQuery::Create()
            ->findOneById($cartPayload->eventTypeID);
           $eventTypeName = $type->getName();
         }
     
         $event = EventQuery::Create()
            ->filterByGroupId($cartPayload->groupID)
            ->filterByInActive(1, Criteria::NOT_EQUAL)
            ->Where('YEAR(event_start)='.$date->format('Y').' AND MONTH(event_start)='.$date->format('m').' AND Day(event_start)='.$date->format('d'))// We filter only the events from the current month : date('Y')
            ->findOne();

         if (!empty($event)) {
           $_SESSION['Action'] = 'Add';
           $_SESSION['EID'] = $event->getID();
           $_SESSION['EName'] = $event->getTitle();
           $_SESSION['EDesc'] = $event->getDesc();
           $_SESSION['EDate'] = $event->getStart();
           $_SESSION['EventID'] = $event->getID();         
         } else {         
           // new way to manage events : sabre
           // we get the PDO for the Sabre connection from the Propel connection
           $pdo = Propel::getConnection();         
      
           // We set the BackEnd for sabre Backends
           $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
      
           $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());
    
           $vcalendar = new EcclesiaCRM\MyVCalendar\VCalendarExtension();
          
           $vcalendar->add(
            'VEVENT', [
             'CREATED'=> (new \DateTime('Now'))->format('Ymd\THis'),
             'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
             'DTSTART' => ($date)->format('Ymd\THis'),
             'DTEND' => ($date)->format('Ymd\THis'),
             'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
             'DESCRIPTION' => gettext("Create From sunday school class view"),              
             'SUMMARY' => $group->getName()." ".$date->format(SystemConfig::getValue('sDatePickerFormat')),
             'UID' => $uuid,
             'SEQUENCE' => '0',
             'TRANSP' => 'OPAQUE'
           ]);
           
           
           $calendar = CalendarinstancesQuery::Create()->findOneByGroupId($group->getId());
           
           $etag = $calendarBackend->createCalendarObject([$calendar->getCalendarid(),$calendar->getId()], $uuid, $vcalendar->serialize());
       
           $event = EventQuery::Create()->findOneByEtag(str_replace('"',"",$etag));
           
           $event->setTitle($group->getName()." ".$date->format(SystemConfig::getValue('sDatePickerFormat')));
           
           if ( !is_null($type) ){
             $event->setType($type->getId());
             $event->setTypeName($type->getName());
           }

           $event->setDesc(gettext("Create From sunday school class view"));         
           $event->setStart($date->format('Y-m-d H:i:s'));
           $event->setEnd($date->format('Y-m-d H:i:s'));
           $event->setText(gettext("Attendance"));
           $event->setInActive(false);
           $event->save(); 
         
           $sundaySchoolService = new SundaySchoolService();
           $thisClassChildren = $sundaySchoolService->getKidsFullDetails($cartPayload->groupID);
     
           foreach ($thisClassChildren as $child) {
              try {
                $eventAttent = new EventAttend();        
                $eventAttent->setEventId($event->getID());
                $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
                $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
                $eventAttent->setPersonId($child['kidId']);
              
                if (SystemConfig::getValue("bCheckedAttendees")) {
                  $eventAttent->setCheckoutDate($date->format('Y-m-d H:i:s'));
                }
                if (SystemConfig::getValue("bCheckedAttendeesCurrentUser")) {
                  $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
                }              
                $eventAttent->save();
              } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
              }
           }

           $_SESSION['Action'] = 'Add';
           $_SESSION['EID'] = $event->getID();
           $_SESSION['EName'] = $event->getTitle();
           $_SESSION['EDesc'] = $event->getDesc();
           $_SESSION['EDate'] = $date->format('Y-m-d H:i:s');
            
           $_SESSION['EventID'] = $event->getID();
        }
      }
      else
      {
        throw new \Exception(gettext("POST to cart requires a EventID"),500);
      }
      return $response->withJson(['status' => "success"]);
  });

  $this->post('/delete', function ($request, $response, $args) {
    if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }

      $cartPayload = (object)$request->getParsedBody();
      
      if ( isset ($cartPayload->eventID) )
      {
            $eventAttend = EventAttendQuery::Create()->filterByEventId($cartPayload->eventID)->filterByPersonId($cartPayload->personID)->limit(1)->findOne();
            if ($eventAttend) {
               $eventAttend->delete();
            }
      }
      else
      {
        throw new \Exception(gettext("POST to cart requires a EventID"),500);
      }
      return $response->withJson(['status' => "success"]);
  });
  
    $this->post('/deleteAll', function ($request, $response, $args) {
        if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
            return $response->withStatus(401);
        }

          $cartPayload = (object)$request->getParsedBody();
          
          if ( isset ($cartPayload->eventID) )
          {
              $eventAttends = EventAttendQuery::Create()->filterByEventId($cartPayload->eventID)->find();    
    
              if (!empty($eventAttends)) {
                $eventAttends->delete();
              }
          }
          else
          {
            throw new \Exception(gettext("POST to cart requires a EventID"),500);
          }
          return $response->withJson(['status' => "success"]);
    });
    
    $this->post('/checkAll', function ($request, $response, $args) {
        /*if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
            return $response->withStatus(401);
        }*/

        $cartPayload = (object)$request->getParsedBody();
          
        if ( isset ($cartPayload->eventID) )
        {
          $eventAttents = EventAttendQuery::Create()
            ->filterByEventId($cartPayload->eventID)
            ->find();
          
          $_SESSION['EventID'] = $cartPayload->eventID;
            
          $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));

          foreach ($eventAttents as $eventAttent) {
            $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
        
            $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
            $eventAttent->setCheckoutDate($date->format('Y-m-d H:i:s'));
            $eventAttent->save();
          }
        }
        else
        {
          throw new \Exception(gettext("POST to cart requires a EventID"),500);
        }
        return $response->withJson(['status' => "success"]);
    });
    
     $this->post('/uncheckAll', function ($request, $response, $args) {
        /*if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
            return $response->withStatus(401);
        }*/

        $cartPayload = (object)$request->getParsedBody();
          
        if ( isset ($cartPayload->eventID) )
        {
          $eventAttents = EventAttendQuery::Create()
            ->filterByEventId($cartPayload->eventID)
            ->find();
            
          $_SESSION['EventID'] = $cartPayload->eventID;
            
          $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));

          foreach ($eventAttents as $eventAttent) {
            $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
        
            $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
            //$eventAttent->setCheckoutDate($date->format('Y-m-d H:i:s'));
            $eventAttent->setCheckoutDate(NULL);
            $eventAttent->save();
          }
        }
        else
        {
          throw new \Exception(gettext("POST to cart requires a EventID"),500);
        }
        return $response->withJson(['status' => "success"]);
    });
});
