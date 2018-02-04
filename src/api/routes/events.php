<?php

/*******************************************************************************
 *
 *  filename    : events.php
 *  last change : 2017-11-16
 *  description : manage the full calendar with events
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2018 Logel Philippe all rights reserved
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Base\EventQuery;
use EcclesiaCRM\Base\EventTypesQuery;
use EcclesiaCRM\Event;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\EventCounts;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventAttend;

$app->group('/events', function () {

    $this->get('/', function ($request, $response, $args) {
        $Events= EventQuery::create()
                ->find();
        return $response->write($Events->toJSON());
    });
   
    $this->get('/notDone', function ($request, $response, $args) {
        $Events= EventQuery::create()
                 ->filterByEnd(new DateTime(),  Propel\Runtime\ActiveQuery\Criteria::GREATER_EQUAL)
                ->find();
        return $response->write($Events->toJSON());
    });
    
    $this->get('/numbers', function ($request, $response, $args) {        
        $response->withJson(MenuEventsCount::getNumberEventsOfToday());       
    });    
    
    $this->get('/types', function ($request, $response, $args) {
        $eventTypes = EventTypesQuery::Create()
              ->orderByName()
              ->find();
             
        $return = [];           
        foreach ($eventTypes as $eventType) {
            $values['eventTypeID'] = $eventType->getID();
            $values['name'] = $eventType->getName();
            
            array_push($return, $values);
        }
        
        return $response->withJson($return);    
    });
    
    $this->get('/names', function ($request, $response, $args) {
        $ormEvents = EventQuery::Create()->orderByTitle()->find();
             
        $return = [];           
        foreach ($ormEvents as $ormEvent) {
            $values['eventTypeID'] = $ormEvent->getID();
            $values['name'] = $ormEvent->getTitle()." (".$ormEvent->getDesc().")";
            
            array_push($return, $values);
        }
        
        return $response->withJson($return);    
    });
    
    $this->post('/person',function($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        try {
            $eventAttent = new EventAttend();
        
            $eventAttent->setEventId($params->EventID);
            $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
            $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
            $eventAttent->setPersonId($params->PersonId);
            $eventAttent->save();
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
            return $response->withJson(['status' => $errorMessage]);    
        }
        
       return $response->withJson(['status' => "success"]);
    });
    
    $this->post('/group',function($request, $response, $args) {
        $params = (object)$request->getParsedBody();
                
        $persons = Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($params->GroupID)
            ->find();

        foreach ($persons as $person) {
          try {
            if ($person->getPersonId() > 0) {
              $eventAttent = new EventAttend();
        
              $eventAttent->setEventId($params->EventID);
              $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
              $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
              $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
              $eventAttent->setPersonId($person->getPersonId());
              $eventAttent->save();
            }
          } catch (\Exception $ex) {
              $errorMessage = $ex->getMessage();
              //return $response->withJson(['status' => $errorMessage]);    
          }
        }
        
       return $response->withJson(['status' => "success"]);
    });

    
    $this->post('/attendees', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        // Get a list of the attendance counts currently associated with thisevent type
        $eventCountNames = EventCountNameQuery::Create()
                               ->filterByTypeId($params->typeID)
                               ->orderById()
                               ->find();
                       
        $numCounts = count($eventCountNames);

        $return = [];           
        
        if ($numCounts) {
            foreach ($eventCountNames as $eventCountName) {
                $values['countID'] = $eventCountName->getId();
                $values['countName'] = $eventCountName->getName();
                $values['typeID'] = $params->typeID;
                
                array_push($return, $values);
            }
        }      
        
        return $response->withJson($return);    
    });
  
    $this->post('/', function ($request, $response, $args) {
      if(!$_SESSION['bAddEvent'] && !$_SESSION['bAdmin']) {
        return $response->withStatus(401);
      }
      
      $input = (object) $request->getParsedBody();
      
      if (!strcmp($input->evntAction,'createEvent'))
      {
        $eventTypeName = "";
        
        $EventGroupType = $input->EventGroupType;// for futur dev : personal or group
        
        if ($input->eventTypeID)
        {
           $type = EventTypesQuery::Create()
            ->findOneById($input->eventTypeID);
           $eventTypeName = $type->getName();
        }
     
         $event = new Event; 
         $event->setTitle($input->EventTitle);
         $event->setType($input->eventTypeID);
         $event->setTypeName($eventTypeName);
         $event->setDesc($input->EventDesc);
         
         if ($input->EventGroupID>0)
           $event->setGroupId($input->EventGroupID);  
           
         $event->setStart(str_replace("T"," ",$input->start));
         $event->setEnd(str_replace("T"," ",$input->end));
         $event->setText(InputUtils::FilterHTML($input->eventPredication));
         $event->setInActive($input->eventInActive);
         $event->save(); 
         
         if (!empty($input->Fields)){         
           foreach ($input->Fields as $field) {
             $eventCount = new EventCounts; 
             $eventCount->setEvtcntEventid($event->getID());
             $eventCount->setEvtcntCountid($field['countid']);
             $eventCount->setEvtcntCountname($field['name']);
             $eventCount->setEvtcntCountcount($field['value']);
             $eventCount->setEvtcntNotes($input->EventCountNotes);
             $eventCount->save();
           }
         }
         
         if ($input->EventGroupID && $input->addGroupAttendees) {// add Attendees
           $persons = Person2group2roleP2g2rQuery::create()
              ->filterByGroupId($input->EventGroupID)
              ->find();

            foreach ($persons as $person) {
              try {
                if ($person->getPersonId() > 0) {
                  $eventAttent = new EventAttend();
        
                  $eventAttent->setEventId($event->getID());
                  $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
                  $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
                  $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
                  $eventAttent->setPersonId($person->getPersonId());
                  $eventAttent->save();
                }
              } catch (\Exception $ex) {
                  $errorMessage = $ex->getMessage();
                  //return $response->withJson(['status' => $errorMessage]);    
              }
            }
            
            // 
            $_SESSION['Action'] = 'Add';
            $_SESSION['EID'] = $event->getID();
            $_SESSION['EName'] = $input->EventTitle;
            $_SESSION['EDesc'] = $input->EventDesc;
            $_SESSION['EDate'] = $date->format('Y-m-d H:i:s');
            
            $_SESSION['EventID'] = $event->getID();
          }
     
         $realCalEvnt = $this->CalendarService->createCalendarItem('event',
            $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(),$event->getId(),$event->getType(),$event->getGroupId());// only the event id sould be edited and moved and have custom color
      
         return $response->withJson(array_filter($realCalEvnt));
     
     } 
     else if ($input->evntAction == 'moveEvent')
     {
        $event = EventQuery::Create()
          ->findOneById($input->eventID);
   
   
       $oldStart = new DateTime($event->getStart('Y-m-d H:i:s'));     
       $oldEnd = new DateTime($event->getEnd('Y-m-d H:i:s'));

       $newStart = new DateTime(str_replace("T"," ",$input->start));     
   
       if ($newStart < $oldStart)
       {
        $interval = $oldStart->diff($newStart);
        $newEnd = $oldEnd->add($interval);          
       }
       else 
       {
        $interval = $newStart->diff($oldStart);
        $newEnd = $oldEnd->sub($interval);          
       }

       $event->setStart($newStart->format('Y-m-d H:i:s'));
       $event->setEnd($newEnd->format('Y-m-d H:i:s'));
       $event->save();
  
        $realCalEvnt = $this->CalendarService->createCalendarItem('event',
          $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(),$event->getId(),$event->getType(),$event->getGroupId());// only the event id sould be edited and moved and have custom color
  
        return $response->withJson(array_filter($realCalEvnt));
     }
     else if (!strcmp($input->evntAction,'retriveEvent'))
     { 
        $event = EventQuery::Create()
          ->findOneById($input->eventID);
    
        $realCalEvnt = $this->CalendarService->createCalendarItem('event',
            $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(),$event->getId(),$event->getType(),$event->getGroupId());// only the event id sould be edited and moved and have custom color
  
        return $response->withJson(array_filter($realCalEvnt));
    }
    else if (!strcmp($input->evntAction,'resizeEvent'))
     {
        $event = EventQuery::Create()
          ->findOneById($input->eventID);
        
       $event->setEnd(str_replace("T"," ",$input->end));
       $event->save();
  
        $realCalEvnt = $this->CalendarService->createCalendarItem('event',
          $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(),$event->getId(),$event->getType(),$event->getGroupId());// only the event id sould be edited and moved and have custom color
  
        return $response->withJson(array_filter($realCalEvnt));
     }
  });
});
