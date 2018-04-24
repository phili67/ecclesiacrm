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
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\Person;

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

    $this->post('/family',function($request, $response, $args) {
        $params = (object)$request->getParsedBody();
                
        $family = FamilyQuery::create()->findPk($params->FamilyID);

        foreach ($family->getPeople() as $person) {
          //return $response->withJson(['person' => $person->getId(),"eventID" => $params->EventID]);
          try {
            if ($person->getId() > 0) {
              $eventAttent = new EventAttend();
        
              $eventAttent->setEventId($params->EventID);
              $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
              $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
              $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
              $eventAttent->setPersonId($person->getId());
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
                
                $values['count'] = 0;
                $values['notes'] = "";
                
                if ($params->eventID > 0) {
                  $eventCounts = EventCountsQuery::Create()->filterByEvtcntCountid($eventCountName->getId())->findOneByEvtcntEventid($params->eventID);
                  
                  if (!empty($eventCounts)) {            
                    $values['count'] = $eventCounts->getEvtcntCountcount();
                    $values['notes'] = $eventCounts->getEvtcntNotes();
                  }
                }
                
                array_push($return, $values);
            }
        }      
        
        return $response->withJson($return);    
    });
  
    $this->post('/', function ($request, $response, $args) {
      if(!$_SESSION['bAddEvent'] && !$_SESSION['user']->isAdmin()) {
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
        
        $begin = new DateTime( str_replace("T"," ",$input->start) );
        $endRecurrance = new DateTime( str_replace("T"," ",$input->endRecurrance) );
        
        $endFirsEvent = new DateTime( str_replace("T"," ",$input->end) );
        $intervalEndStart = $begin->diff($endFirsEvent);

        if ($begin == $endRecurrance) {// we are in the case of a one time event, this is to have only one event
          $endRecurrance = $endRecurrance->modify( '+1 week' );
        }

        $interval = DateInterval::createFromDateString($input->recurranceType);// recurrance type is : 1 week, 1 Month, 3 months, 6 months, 1 Year
        $period = new DatePeriod($begin, $interval, $endRecurrance);// so we create the period
        
        $parent_id = 0;
        $first_event = nil;

        foreach($period as $dt) {        
           $event = new Event; 
           $event->setTitle($input->EventTitle);
           $event->setType($input->eventTypeID);
           $event->setTypeName($eventTypeName);
           $event->setDesc($input->EventDesc);                      
         
           if ($input->EventGroupID>0) {
              $event->setGroupId($input->EventGroupID);
           }
           
           $event->setStart( $dt->format( "Y-m-d H:i:s" ) );
           
           $newEndDate = new DateTime($dt->format( "Y-m-d H:i:s" ));
           $newEndDate->add($intervalEndStart);
           
           $event->setEnd( $newEndDate->format( "Y-m-d H:i:s" ) );
           $event->setText(InputUtils::FilterHTML($input->eventPredication));
           $event->setInActive($input->eventInActive);
           $event->save(); 
           
           if ($parent_id == 0) {
              $parent_id = $event->getID();
              $first_event = $event;
           }
           
           if ($input->recurranceValid) {// we can store the parent id for all the event the first one too
             $event->setEventParentId ($parent_id);
             $event->save(); 
           }
         
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

             if ($persons->count() > 0) {
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
          }
        }
     
        $realCalEvnt = $this->CalendarService->createCalendarItem('event',
            $first_event->getTitle(), $first_event->getStart('Y-m-d H:i:s'), $first_event->getEnd('Y-m-d H:i:s'), ''/*$event->getEventURI()*/,
            $first_event->getId(),$first_event->getType(),$first_event->getGroupId(),$input->EventDesc,$input->eventPredication,
            $first_event->getEventParentId());// only the event id sould be edited and moved and have custom color
      
        return $response->withJson(array_filter($realCalEvnt));
     } 
     else if ($input->evntAction == 'moveEvent')
     {
       $first_event = EventQuery::Create()
            ->findOneById($input->eventID);

       $oldStart = new DateTime($first_event->getStart('Y-m-d H:i:s'));
       $oldEnd = new DateTime($first_event->getEnd('Y-m-d H:i:s'));

       $newStart = new DateTime(str_replace("T"," ",$input->start));
 
       if ($newStart < $oldStart)
       {
         $interval = $oldStart->diff($newStart);
         $action = +1;
       } else {
         $interval = $newStart->diff($oldStart);         
         $action = -1;
       }

       if (isset ($input->parentID)) {
          $events = EventQuery::Create()
            ->findByEventParentId($input->parentID);
          
          foreach ($events as $event) {
            $oldStart = new DateTime($event->getStart('Y-m-d H:i:s'));
            $oldEnd = new DateTime($event->getEnd('Y-m-d H:i:s'));
            
            if ($action == +1) {
              $newStart = $oldStart->add($interval);
              $newEnd = $oldEnd->add($interval);
            } else {
              $newStart = $oldStart->sub($interval);
              $newEnd = $oldEnd->sub($interval);
            }
            
            $event->setStart($newStart->format('Y-m-d H:i:s'));
            $event->setEnd($newEnd->format('Y-m-d H:i:s'));
            
            $event->save();
          }
          
          return $response->withJson(['status' => "success"]);
       } else {
         if ($action == +1) {
           $newStart = $oldStart->add($interval);
           $newEnd = $oldEnd->add($interval);
         } else {
           $newStart = $oldStart->sub($interval);
           $newEnd = $oldEnd->sub($interval);
         }
         
         $first_event->setStart($newStart->format('Y-m-d H:i:s'));
         $first_event->setEnd($newEnd->format('Y-m-d H:i:s'));
         $first_event->save();
         
         $realCalEvnt = $this->CalendarService->createCalendarItem('event',
            $first_event->getTitle(), $first_event->getStart('Y-m-d H:i:s'), $first_event->getEnd('Y-m-d H:i:s'), ''/*$first_event->getEventURI()*/,$first_event->getId(),$first_event->getType(),$first_event->getGroupId(),$first_event->getDesc(),$first_event->getText(),$first_event->getEventParentId());// only the event id sould be edited and moved and have custom color
  
          return $response->withJson(array_filter($realCalEvnt));
       }
     }
     else if (!strcmp($input->evntAction,'retriveEvent'))
     { 
        $event = EventQuery::Create()
          ->findOneById($input->eventID);
    
        $realCalEvnt = $this->CalendarService->createCalendarItem('event',
            $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), ''/*$event->getEventURI()*/,$event->getId(),$event->getType(),$event->getGroupId(),$event->getDesc(),$event->getText());// only the event id sould be edited and moved and have custom color
  
        return $response->withJson(['status' => "success"]);
     }
     else if (!strcmp($input->evntAction,'resizeEvent'))
     {
       $first_event = EventQuery::Create()
          ->findOneById($input->eventID);
          
       $start = new DateTime($first_event->getStart('Y-m-d H:i:s'));
       $end = new DateTime($input->end);
       
       $interval = $start->diff($end);
          
       if (isset ($input->parentID)) {
         $events = EventQuery::Create()
            ->findByEventParentId($input->parentID);
          
         foreach ($events as $event) {
           $start = new DateTime($event->getStart('Y-m-d H:i:s'));
           $newEnd = $start->add($interval);
           
           $event->setEnd($newEnd->format('Y-m-d H:i:s'));
           
           $event->save();
         }
       } else {        
         $first_event->setEnd(str_replace("T"," ",$input->end));
         $first_event->save();
       }
    
       return $response->withJson(['status' => "success"]);
     }
     else if (!strcmp($input->evntAction,'attendeesCheckinEvent'))
     {
        $event = EventQuery::Create()
          ->findOneById($input->eventID);
        
        // for the CheckIn and to add attendees
        $_SESSION['Action'] = 'Add';
        $_SESSION['EID'] = $event->getID();
        $_SESSION['EName'] = $event->getTitle();
        $_SESSION['EDesc'] = $event->getDesc();
        $_SESSION['EDate'] = $event->getStart()->format('Y-m-d H:i:s');
        
        $_SESSION['EventID'] = $event->getID();
  
        return $response->withJson(['status' => "success"]);
     }
     else if (!strcmp($input->evntAction,'suppress'))
     {     
        if (isset ($input->parentID)) {
          $events = EventQuery::Create()
            ->findByEventParentId($input->parentID);
            
          if ($events->count() > 0) {
            $events->delete();
          }
        } else {// we delete only one event
          $event = EventQuery::Create()
            ->findOneById($input->eventID);
            
          $new_parent_id = $event->getEventParentId();
          
          if ($event->getEventParentId() == $input->eventID) {
            $new_parent_id = 0;
          }
          
          // we search all 
          $events = EventQuery::Create()
            ->findByEventParentId($input->eventID);
          
          if ($events->count() > 1) {
            foreach ($events as $individual_event) {
              if ($new_parent_id == 0 && $individual_event->getId() != $input->eventID) {
                 $new_parent_id = $individual_event->getId();                 
              }
              
              if ($new_parent_id != 0) {
                $individual_event->setEventParentId ($new_parent_id);              
              }
            }
            
            $events->save();
            
            $event->delete();            
          } else if (!empty($event)) {
            $EventAttends = EventAttendQuery::Create()->findByEventId($input->eventID);
          
            $event->delete();
          }
        }
  
        return $response->withJson(['status' => "success".$new_parent_id]);
     }     
     else if (!strcmp($input->evntAction,'modifyEvent'))
     {
        $event = EventQuery::Create()
          ->findOneById($input->eventID);
        
        $eventTypeName = "";
        
        $EventGroupType = $input->EventGroupType;// for futur dev : personal or group
        
        if ($input->eventTypeID)
        {
           $type = EventTypesQuery::Create()
            ->findOneById($input->eventTypeID);
           $eventTypeName = $type->getName();
        }
     
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
           $eventCouts = EventCountsQuery::Create()->findByEvtcntEventid($event->getID());
           
           if ($eventCouts) {
              $eventCouts->delete();
           }
           
           foreach ($input->Fields as $field) {
             $eventCount = new EventCounts; 
             $eventCount->setEvtcntEventid($input->eventID);
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
           if ($persons->count() > 0) {
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
            
            // for the CheckIn and to add attendees
            $_SESSION['Action'] = 'Add';
            $_SESSION['EID'] = $event->getID();
            $_SESSION['EName'] = $input->EventTitle;
            $_SESSION['EDesc'] = $input->EventDesc;
            $_SESSION['EDate'] = $date->format('Y-m-d H:i:s');
            
            $_SESSION['EventID'] = $event->getID();
          }
        }
     
        $realCalEvnt = $this->CalendarService->createCalendarItem('event',
              $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), ''/*$event->getEventURI()*/,$event->getId(),$event->getType(),
              $event->getGroupId(),$input->EventDesc,$input->eventPredication,
              $event->getEventParentId());// only the event id sould be edited and moved and have custom color
      
        return $response->withJson(array_filter($realCalEvnt));
      }
  });
});
