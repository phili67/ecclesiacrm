<?php
// Copyright 2018 Philippe Logel all right reserved
use EcclesiaCRM\ListOptionQuery;
use Slim\Http\Request;
use Slim\Http\Response;

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
use EcclesiaCRM\SessionUser;


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

    $this->post('/checkinstudent', 'attendeesCheckInStudent' );
    $this->post('/checkoutstudent', 'attendeesCheckOutStudent' );
    $this->post('/student', 'attendeesStudent' );
    $this->post('/delete', 'attendeesDelete' );
    $this->post('/deleteAll', 'attendeesDeleteAll' );
    $this->post('/checkAll', 'attendeesCheckAll' );
    $this->post('/uncheckAll', 'attendeesUncheckAll' );
    $this->post('/groups', 'attendeesGroups' );

});

function attendeesCheckInStudent (Request $request, Response $response, array $args) {
    /*if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
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

        $returnData = "";

        if ($eventAttent) {
            $eventAttent->setCheckoutId (SessionUser::getUser()->getPersonId());
            if ($cartPayload->checked) {
                $eventAttent->getEvent()->checkInPerson($cartPayload->personID);
                $returnData = OutputUtils::FormatDate($date->format('Y-m-d H:i:s'),1);
            } else {
                $eventAttent->getEvent()->unCheckInPerson($cartPayload->personID);
            }
            $eventAttent->save();
        }
    }
    else
    {
        throw new \Exception(_("POST to cart requires a personID and an eventID"),500);
    }
    $person = PersonQuery::Create()->findOneById(SessionUser::getUser()->getPersonId());

    return $response->withJson(['status' => "success","name" => $person->getFullName(),"date" => $returnData]);
}

function attendeesCheckOutStudent (Request $request, Response $response, array $args) {
/*if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
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

    $returnData = "";

    if ($eventAttent) {
          $eventAttent->setCheckoutId (SessionUser::getUser()->getPersonId());
          if ($cartPayload->checked) {
              $eventAttent->getEvent()->checkOutPerson($cartPayload->personID);
              $returnData = OutputUtils::FormatDate($date->format('Y-m-d H:i:s'),1);
          } else {
              $eventAttent->getEvent()->unCheckOutPerson($cartPayload->personID);
          }
          $eventAttent->save();
    }
  }
  else
  {
    throw new \Exception(_("POST to cart requires a personID and an eventID"),500);
  }
  $person = PersonQuery::Create()->findOneById(SessionUser::getUser()->getPersonId());

  return $response->withJson(['status' => "success","name" => $person->getFullName(),"date" => $returnData]);
}

function attendeesStudent (Request $request, Response $response, array $args) {
/*if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
    return $response->withStatus(401);
}*/

  $cartPayload = (object)$request->getParsedBody();

  if ( isset ($cartPayload->eventTypeID) && isset ($cartPayload->groupID) && isset($cartPayload->rangeInHours))
  {
     $group = GroupQuery::Create()
        ->findOneById($cartPayload->groupID);

     $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));

     $dateTime_End = new \DateTime($cartPayload->dateTime);

     $interval = new DateInterval("PT".$cartPayload->rangeInHours."H");

     $dateTime_End->add($interval);

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
         'DTEND' => ($dateTime_End)->format('Ymd\THis'),
         'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
         'DESCRIPTION' => _("Create From sunday school class view"),
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

       $event->setDesc(_("Create From sunday school class view"));
       $event->setStart($date->format('Y-m-d H:i:s'));
       $event->setEnd($dateTime_End->format('Y-m-d H:i:s'));
       $event->setText(_("Attendance"));
       $event->setInActive(false);
       $event->save();

       $sundaySchoolService = new SundaySchoolService();
       $thisClassChildren = $sundaySchoolService->getKidsFullDetails($cartPayload->groupID);

       foreach ($thisClassChildren as $child) {
          try {
            $eventAttent = new EventAttend();
            $eventAttent->setEventId($event->getID());
            $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
            $eventAttent->setCheckinDate(NULL);
            $eventAttent->setPersonId($child['kidId']);

            if (SystemConfig::getValue("bCheckedAttendees")) {
              $eventAttent->setCheckoutDate(NULL);
            }
            if (SystemConfig::getValue("bCheckedAttendeesCurrentUser")) {
              $eventAttent->setCheckoutId (SessionUser::getUser()->getPersonId());
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
    throw new \Exception(_("POST to cart requires a EventID"),500);
  }
  return $response->withJson(['status' => "success"]);
}

function attendeesDelete (Request $request, Response $response, array $args) {
if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
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
    throw new \Exception(_("POST to cart requires a EventID"),500);
  }
  return $response->withJson(['status' => "success"]);
}

function attendeesDeleteAll (Request $request, Response $response, array $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
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
        throw new \Exception(_("POST to cart requires a EventID"),500);
      }
      return $response->withJson(['status' => "success"]);
}

function attendeesCheckAll (Request $request, Response $response, array $args) {
    /*if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }*/

    $cartPayload = (object)$request->getParsedBody();

    if ( isset ($cartPayload->eventID) && isset($cartPayload->type) )
    {
      $eventAttents = EventAttendQuery::Create()
        ->filterByEventId($cartPayload->eventID)
        ->find();

      $_SESSION['EventID'] = $cartPayload->eventID;

      $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));

      foreach ($eventAttents as $eventAttent) {
        $eventAttent->setCheckinId( SessionUser::getUser()->getPersonId() );

        if ($cartPayload->type == 1) {
            $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
        } else if ($cartPayload->type == 2) {
            $eventAttent->setCheckoutDate($date->format('Y-m-d H:i:s'));
        }

        $eventAttent->save();
      }
    }
    else
    {
      throw new \Exception(_("POST to cart requires a EventID"),500);
    }
    return $response->withJson(['status' => "success"]);
}

function attendeesUncheckAll (Request $request, Response $response, array $args) {
    /*if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }*/

    $cartPayload = (object)$request->getParsedBody();

    if ( isset ($cartPayload->eventID)  && isset($cartPayload->type) )
    {
      $eventAttents = EventAttendQuery::Create()
        ->filterByEventId($cartPayload->eventID)
        ->find();

      $_SESSION['EventID'] = $cartPayload->eventID;


      foreach ($eventAttents as $eventAttent) {
        $eventAttent->setCheckoutId (SessionUser::getUser()->getPersonId());

          if ($cartPayload->type == 1) {
              $eventAttent->setCheckinDate(NULL);
              $eventAttent->setCheckoutDate(NULL);
          } else if ($cartPayload->type == 2) {
              $eventAttent->setCheckoutDate(NULL);
          }

        $eventAttent->save();
      }
    }
    else
    {
      throw new \Exception(_("POST to cart requires a EventID"),500);
    }
    return $response->withJson(['status' => "success"]);
}

function attendeesGroups (Request $request, Response $response, array $args) {
    /*if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }*/

    $cartPayload = (object)$request->getParsedBody();

    if ( isset ($cartPayload->dateTime) && isset ($cartPayload->eventTypeID) && isset ($cartPayload->rangeInHours) )
    {
        $listOptions = ListOptionQuery::Create()
            ->filterById(3) // the group category
            ->filterByOptionType('sunday_school')
            ->orderByOptionSequence()
            ->find();

        $dateTime = new \DateTime($cartPayload->dateTime);

        $dateTime_End = new \DateTime($cartPayload->dateTime);

        $interval = new DateInterval("PT".$cartPayload->rangeInHours."H");

        $dateTime_End->add($interval);

        foreach ($listOptions as $listOption) {
            $groups = GroupQuery::Create()
                ->useGroupTypeQuery()
                ->filterByListOptionId($listOption->getOptionId())
                ->endUse()
                ->filterByType(4)// sunday groups
                ->orderByName()
                ->find();

            foreach ($groups as $group) {
                $type = null;

                if ($cartPayload->eventTypeID) {
                    $type = EventTypesQuery::Create()
                        ->findOneById($cartPayload->eventTypeID);
                }

                $event = EventQuery::Create()
                    ->filterByGroupId($cartPayload->groupID)
                    ->filterByInActive(1, Criteria::NOT_EQUAL)
                    ->Where('YEAR(event_start)=' . $dateTime->format('Y') . ' AND MONTH(event_start)=' . $dateTime->format('m') . ' AND Day(event_start)=' . $dateTime->format('d'))// We filter only the events from the current month : date('Y')
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
                        'CREATED' => ($dateTime)->format('Ymd\THis'),
                        'DTSTAMP' => ($dateTime)->format('Ymd\THis'),
                        'DTSTART' => ($dateTime)->format('Ymd\THis'),
                        'DTEND' => ($dateTime_End)->format('Ymd\THis'),
                        'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                        'DESCRIPTION' => _("Create From sunday school class view"),
                        'SUMMARY' => $group->getName() . " " . $dateTime->format(SystemConfig::getValue('sDatePickerFormat')),
                        'UID' => $uuid,
                        'SEQUENCE' => '0',
                        'TRANSP' => 'OPAQUE'
                    ]);


                    $calendar = CalendarinstancesQuery::Create()->findOneByGroupId($group->getId());

                    $etag = $calendarBackend->createCalendarObject([$calendar->getCalendarid(), $calendar->getId()], $uuid, $vcalendar->serialize());

                    $event = EventQuery::Create()->findOneByEtag(str_replace('"', "", $etag));

                    $event->setTitle($group->getName() . " " . $dateTime->format(SystemConfig::getValue('sDatePickerFormat')));

                    if (!is_null($type)) {
                        $event->setType($type->getId());
                        $event->setTypeName($type->getName());
                    }

                    $event->setDesc(_("Create From sunday school class view"));
                    $event->setStart($dateTime->format('Y-m-d H:i:s'));
                    $event->setEnd($dateTime_End->format('Y-m-d H:i:s'));
                    $event->setText(_("Attendance"));
                    $event->setInActive(false);
                    $event->save();

                    $sundaySchoolService = new SundaySchoolService();
                    $thisClassChildren = $sundaySchoolService->getKidsFullDetails($group->getId());

                    foreach ($thisClassChildren as $child) {
                        try {
                            $eventAttent = new EventAttend();
                            $eventAttent->setEventId($event->getID());
                            $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
                            $eventAttent->setCheckinDate(NULL);
                            $eventAttent->setPersonId($child['kidId']);

                            if (SystemConfig::getValue("bCheckedAttendees")) {
                                $eventAttent->setCheckoutDate(NULL);
                            }
                            if (SystemConfig::getValue("bCheckedAttendeesCurrentUser")) {
                                $eventAttent->setCheckoutId(SessionUser::getUser()->getPersonId());
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
                    $_SESSION['EDate'] = $dateTime->format('Y-m-d H:i:s');

                    $_SESSION['EventID'] = $event->getID();
                }
            }
        }
    }
    else
    {
        throw new \Exception(_("POST to cart requires an EventID"),500);
    }
    return $response->withJson(['status' => "success"]);
}


