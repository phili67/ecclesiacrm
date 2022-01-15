<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\Utils\LoggerUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Base\EventQuery;
use EcclesiaCRM\Base\EventTypesQuery;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\EventCounts;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserQuery;

use EcclesiaCRM\CalendarinstancesQuery;

use Sabre\VObject;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\VObjectExtract;
use Propel\Runtime\ActiveQuery\Criteria;

class CalendarEventV2Controller
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllEvents (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $Events = EventQuery::create()
            ->find();

        return $response->withJson($Events->toArray());
    }

    public function getNotDoneEvents(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $Events = EventQuery::create()
            ->filterByEnd(new \DateTime(), Criteria::GREATER_EQUAL)
            ->find();

        $return = [];

        foreach ($Events as $event) {
            $values['Id'] = $event->getID();
            $values['Title'] = $event->getTitle();
            $values['Type'] = $event->getType();
            $values['InActive'] = $event->getInActive();
            $values['Text'] = $event->getText();
            $values['Start'] = $event->getStart();
            $values['End'] = $event->getEnd();
            $values['TypeName'] = $event->getTypeName();
            $values['GroupId'] = $event->getGroupId();
            $values['LastOccurence'] = $event->getLastOccurence();
            $values['Location'] = $event->getLocation();
            $values['Coordinates'] = $event->getCoordinates();

            array_push($return, $values);
        }

        /*if (!is_null($Events)) {
            return $response->write($Events->toJSON());
        }*/

        return $response->withJson(["Events" =>$return]);
    }

    public function numbersOfEventOfToday(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $response->withJson(MenuEventsCount::getNumberEventsOfToday());
    }

    public function getEventTypes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
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
    }

    public function eventNames(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $ormEvents = EventQuery::Create()->orderByTitle()->find();

        $return = [];
        foreach ($ormEvents as $ormEvent) {
            $values['eventTypeID'] = $ormEvent->getID();
            $values['name'] = $ormEvent->getTitle() . " (" . $ormEvent->getDesc() . ")";

            array_push($return, $values);
        }

        return $response->withJson($return);
    }

    public function deleteeventtype(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->typeID)) {
            $eventType = EventTypesQuery::Create()
                ->filterById(InputUtils::LegacyFilterInput($input->typeID))
                ->limit(1)
                ->findOne();

            if (!empty($eventType)) {
                $eventType->delete();
            }

            $eventCountNames = EventCountNameQuery::Create()
                ->findByTypeId(InputUtils::LegacyFilterInput($input->typeID));

            if (!empty($eventCountNames)) {
                $eventCountNames->delete();
            }


            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function eventInfo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->eventID)) {
            $event = EventQuery::Create()->findOneById($input->eventID);

            if (is_null($event)) {
                return $response->withJson(["status" => "failed"]);
            }

            $arr['eventID'] = $event->getId();
            $arr['Title'] = $event->getTitle();
            $arr['Desc'] = $event->getDesc();
            $arr['Text'] = $event->getText();
            $arr['start'] = $event->getStart('Y-m-d H:i:s');
            $arr['end'] = $event->getEnd('Y-m-d H:i:s');
            $arr['calendarID'] = [$event->getEventCalendarid(), 0];
            $arr['eventTypeID'] = $event->getType();
            $arr['inActive'] = $event->getInActive();
            $arr['location'] = $event->getLocation();
            $arr['latitude'] = $event->getLatitude();
            $arr['longitude'] = $event->getLongitude();
            $arr['alarm'] = $event->getAlarm();

            return $response->withJson($arr);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function personCheckIn(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = (object)$request->getParsedBody();

        try {
            $eventAttent = new EventAttend();

            $eventAttent->setEventId($params->EventID);
            $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
            $eventAttent->setCheckinDate( NULL);
            $eventAttent->setPersonId($params->PersonId);
            $eventAttent->save();
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
            return $response->withJson(['status' => $errorMessage]);
        }

        return $response->withJson(['status' => "success"]);
    }

    public function groupCheckIn(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = (object)$request->getParsedBody();

        $persons = Person2group2roleP2g2rQuery::create()
            ->usePersonQuery()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->endUse()
            ->filterByGroupId($params->GroupID)
            ->find();

        foreach ($persons as $person) {
            try {
                if ($person->getPersonId() > 0) {
                    $eventAttent = new EventAttend();

                    $eventAttent->setEventId($params->EventID);
                    $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
                    $eventAttent->setCheckinDate(NULL);
                    $eventAttent->setPersonId($person->getPersonId());
                    $eventAttent->save();
                }
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
                //return $response->withJson(['status' => $errorMessage]);
            }
        }

        return $response->withJson(['status' => "success"]);
    }

    public function familyCheckIn(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = (object)$request->getParsedBody();

        $family = FamilyQuery::create()
            ->findPk($params->FamilyID);

        foreach ($family->getPeople() as $person) {
            //return $response->withJson(['person' => $person->getId(),"eventID" => $params->EventID]);
            try {
                if ($person->getId() > 0 && $person->getDateDeactivated() == null) {// GDRP, when a person is completely deactivated
                    $eventAttent = new EventAttend();

                    $eventAttent->setEventId($params->EventID);
                    $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
                    $eventAttent->setCheckinDate(NULL);
                    $eventAttent->setPersonId($person->getId());
                    $eventAttent->save();
                }
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
                //return $response->withJson(['status' => $errorMessage]);
            }
        }

        return $response->withJson(['status' => "success"]);
    }

    public function eventCount(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
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
                $aDefStartTime = $eventCountName->getEventTypes()->getDefStartTime()->format('H:i:s');
                $aStartTimeTokens = explode(':', $aDefStartTime);
                $aEventStartHour = (int)$aStartTimeTokens[0];
                $aEventStartMins = (int)$aStartTimeTokens[1];
                $aDefRecurDOW = $eventCountName->getEventTypes()->getDefRecurDOW();
                $aDefRecurDOM = $eventCountName->getEventTypes()->getDefRecurDOM();


                $values['countID'] = $eventCountName->getId();
                $values['countName'] = $eventCountName->getName();
                $values['typeID'] = $params->typeID;
                $values['startHour'] = sprintf("%02d",$aEventStartHour);
                $values['startMin'] = sprintf("%02d",$aEventStartMins);
                $values['DefRecurDOW'] = $aDefRecurDOW;// unusefull actually
                $values['DefRecurDOM'] = $aDefRecurDOM;// unusefull actually

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
    }

    public function manageEvent(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        $the_event = Null;

        if ( isset($input->eventID) ) {
            $the_event = EventQuery::create()->findOneById($input->eventID);
        }

        if (!strcmp($input->eventAction, 'createEvent')) {
            $calendarService = new CalendarService();

            if ( !$calendarService->createEventForCalendar($input->calendarID, $input->start, $input->end,
                $input->recurrenceType, $input->endrecurrence, $input->EventDesc, $input->EventTitle, $input->location,
                $input->recurrenceValid, $input->addGroupAttendees, $input->alarm, $input->eventTypeID, $input->eventNotes,
                $input->eventInActive, $input->Fields, $input->EventCountNotes) ) {
                return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
            }

            return $response->withJson(["status" => "success"]);

        } else if (!strcmp($input->eventAction, 'moveEvent')) {

            // this part allows to create a resource without being in collision on another one
            if ( $the_event->getCreatorUserId() != 0 and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources() ) {
                return $response->withJson(["status" => "failed", "message" => _("This resource reservation was not created by you. You cannot edit, move or delete a resource that you do not own.")]);
            }

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $event = $calendarBackend->getCalendarObjectById($input->calendarID, $input->eventID);

            $vcalendar = VObject\Reader::read($event['calendardata']);

            if (isset($input->allEvents) && isset($input->reccurenceID)) {

                if ($input->allEvents == true) { // we'll move all the events

                    $exdates = [];

                    $oldStart = new \DateTime ($vcalendar->VEVENT->DTSTART->getDateTime()->format('Y-m-d H:i:s'));
                    $oldEnd = new \DateTime ($vcalendar->VEVENT->DTEND->getDateTime()->format('Y-m-d H:i:s'));

                    $oldSubStart = new \DateTime($input->reccurenceID);
                    $newSubStart = new \DateTime($input->start);

                    if ($newSubStart < $oldSubStart) {
                        $interval = $oldSubStart->diff($newSubStart);

                        $newStart = $oldStart->add($interval);
                        $newEnd = $oldEnd->add($interval);

                        $action = +1;
                    } else {
                        $interval = $newSubStart->diff($oldSubStart);

                        $newStart = $oldStart->sub($interval);
                        $newEnd = $oldEnd->sub($interval);

                        $action = -1;
                    }


                    $oldrule = $vcalendar->VEVENT->RRULE;
                    $oldruleSplit = explode("UNTIL=", $oldrule);

                    $oldRuleFreq = $oldruleSplit[0];
                    $oldRuleFinishDate = new \DateTime($oldruleSplit[1]);

                    if ($action == +1) {
                        $newrule = $oldRuleFreq . "UNTIL=" . $oldRuleFinishDate->add($interval)->format('Ymd\THis');
                    } else {
                        $newrule = $oldRuleFreq . "UNTIL=" . $oldRuleFinishDate->sub($interval)->format('Ymd\THis');
                    }

                    foreach ($vcalendar->VEVENT->EXDATE as $exdate) {

                        $ex_date = new \DateTime ($exdate);

                        if ($action == +1) {
                            $new_ex_date = $ex_date->add($interval);
                        } else {
                            $new_ex_date = $ex_date->sub($interval);
                        }

                        array_push($exdates, $new_ex_date->format('Y-m-d H:i:s'));
                    }

                    $vcalendar->VEVENT->remove('EXDATE');

                    foreach ($exdates as $exdate) {
                        $vcalendar->VEVENT->add('EXDATE', (new \DateTime($exdate))->format('Ymd\THis'));
                    }

                    //$i = 0;
                    foreach ($vcalendar->VEVENT as $sevent) {
                        $old_recID = new \DateTime ($sevent->{'RECURRENCE-ID'});

                        if ($action == +1) {
                            $new_recID = $old_recID->add($interval);
                        } else {
                            $new_recID = $old_recID->sub($interval);
                        }

                        //if ($i++ > 0) {// the first event is the main event and must not have the RECURRENCE-ID !!!!
                        $sevent->{'RECURRENCE-ID'} = $new_recID->format('Ymd\THis');
                        //}
                    }

                    // this part allows to create a resource without being in collision on another one
                    if ( is_array( $input->calendarID ) ) {
                        $calIDs = $input->calendarID;
                    } else {
                        $calIDs = explode(",", $input->calendarID);
                    }

                    if ($calendarBackend->isCalendarResource($calIDs)
                        and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                            $calIDs, $newStart->format('Ymd\THis'), $newEnd->format('Ymd\THis'),
                            $input->eventID,
                            $oldRuleFreq,
                            $oldRuleFinishDate->format('Y-m-d'))) {

                        return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                    }
                    // now we can apply the changes

                    // we remove only the first one in the main event.
                    $vcalendar->VEVENT->remove('RECURRENCE-ID');

                    $vcalendar->VEVENT->remove('RRULE');

                    $vcalendar->VEVENT->add('RRULE', $newrule);

                    $vcalendar->VEVENT->DTSTART = $newStart->format('Ymd\THis');
                    $vcalendar->VEVENT->DTEND = $newEnd->format('Ymd\THis');
                    $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                    $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                    return $response->withJson(["status" => "success"]);

                } else {
                    // new code :
                    // we've to search the dates
                    $old_RECURRENCE_ID = '';
                    $old_SUMMARY = '';
                    $old_LOCATION = '';
                    $old_UID = '';

                    $returnValues = VObjectExtract::calendarData($event['calendardata']);

                    foreach ($returnValues as $key => $value) {
                        if ($key == 'freqEvents') {
                            foreach ($value as $sevent) {
                                if ($sevent['RECURRENCE-ID'] == (new \DateTime($input->reccurenceID))->format('Y-m-d H:i:s')) {
                                    $old_RECURRENCE_ID = $sevent['RECURRENCE-ID'];
                                    $old_SUMMARY = $sevent['SUMMARY'];
                                    $old_DESCRIPTION = $sevent['DESCRIPTION'];
                                    $old_LOCATION = $sevent['DESCRIPTION'];
                                    $old_UID = $sevent['UID'];

                                    // we have to delete the last occurence
                                    $calendarBackend->searchAndDeleteOneEvent($vcalendar, $old_RECURRENCE_ID);
                                    break;
                                }
                            }
                        }
                    }

                    if (!empty($old_UID)) {
                        //first we have to exclude the date
                        //$vcalendar->VEVENT->add('EXDATE', (new \DateTime($input->reccurenceID))->format('Ymd\THis'));

                        // only in the case we've found something
                        // the location
                        $coordinates = "";
                        $location = '';

                        if (isset($input->location)) {
                            $location = str_replace("\n", " ", $old_LOCATION);
                            $latLng = GeoUtils::getLatLong($old_LOCATION);
                            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                                $coordinates = $latLng['Latitude'] . ' commaGMAP ' . $latLng['Longitude'];
                            }
                        }

                        $new_vevent = [
                            'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                            'DTSTART' => (new \DateTime($input->start))->format('Ymd\THis'),
                            'DTEND' => (new \DateTime($input->end))->format('Ymd\THis'),
                            'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                            'DESCRIPTION' => $old_DESCRIPTION,
                            'SUMMARY' => $old_SUMMARY,
                            'LOCATION' => $old_LOCATION,
                            'UID' => $old_UID,
                            'SEQUENCE' => '0',
                            'RECURRENCE-ID' => (new \DateTime($input->reccurenceID))->format('Ymd\THis'),
                            'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                            "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
                            //'X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-MAPKIT-HANDLE=CAESvAEaEglnaQKg5U5IQBFCfLuA8gIfQCJdCgZGcmFuY2USAkZSGgZBbHNhY2UqCEJhcy1SaGluMglCaXNjaGhlaW06BTY3ODAwUhJSdWUgUm9iZXJ0IEtpZWZmZXJaATFiFDEgUnVlIFJvYmVydCBLaWVmZmVyKhQxIFJ1ZSBSb2JlcnQgS2llZmZlcjIUMSBSdWUgUm9iZXJ0IEtpZWZmZXIyDzY3ODAwIEJpc2NoaGVpbTIGRnJhbmNlODlAAA==;X-APPLE-RADIUS=70.58736571013601;X-TITLE="1 Rue Robert Kieffer\nBischheim, France":geo' => '48.616383,7.752878'
                        ];

                        // check if there is no slot with another resource event
                        if ($calendarBackend->isCalendarResource($input->calendarID)
                            and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                                $input->calendarID, $input->start, $input->end, $input->eventID)) {

                            return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                        }
                        // now we can apply the changes

                        $vcalendar->add('VEVENT', $new_vevent);

                        $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                        return $response->withJson(["status" => "success"]);
                    } else {
                        return $response->withJson(["status" => "failed"]);
                    }
                }

            } else {
                // check if there is no slot with another resource event
                if ($calendarBackend->isCalendarResource($input->calendarID)
                    and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                        $input->calendarID, $input->start, $input->end, $input->eventID)) {

                    return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                }
                // now we can apply the changes

                $vcalendar->VEVENT->DTSTART = (new \DateTime($input->start))->format('Ymd\THis');
                $vcalendar->VEVENT->DTEND = (new \DateTime($input->end))->format('Ymd\THis');
                $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                return $response->withJson(["status" => "success"]);
            }

            return $response->withJson(["status" => "failed"]);
        } else if (!strcmp($input->eventAction, 'resizeEvent')) {

            // this part allows to create a resource without being in collision on another one
            if ( $the_event->getCreatorUserId() != 0 and  SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources() ) {
                return $response->withJson(["status" => "failed", "message" => _("This resource reservation was not created by you. You cannot edit, move or delete a resource that you do not own.")]);
            }

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $event = $calendarBackend->getCalendarObjectById($input->calendarID, $input->eventID);

            $vcalendar = VObject\Reader::read($event['calendardata']);

            if (isset($input->allEvents) && isset($input->reccurenceID) && isset($input->start) && isset($input->end)) {
                if ($input->allEvents == true) { // we'll resize all the events

                    $oldStart = new \DateTime ($vcalendar->VEVENT->DTSTART->getDateTime()->format('Y-m-d H:i:s'));
                    $oldEnd = new \DateTime ($vcalendar->VEVENT->DTEND->getDateTime()->format('Y-m-d H:i:s'));

                    $newSubStart = new \DateTime($input->start);
                    $newSubEnd = new \DateTime($input->end);

                    $interval = $newSubStart->diff($newSubEnd);

                    $vcalendar->VEVENT->DTSTART = ($oldStart)->format('Ymd\THis');
                    $vcalendar->VEVENT->DTEND = ($oldStart->add($interval))->format('Ymd\THis');
                    $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                    // this part allows to create a resource without being in collision on another one
                    if ($calendarBackend->isCalendarResource($input->calendarID)
                        and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                            $input->calendarID, $input->start, $input->end, $input->eventID)) {

                        return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                    }
                    // end of collision test

                    $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                    return $response->withJson(["status" => "success"]);
                } else {
                    // new code : first we have to exclude the date
                    // we've to search the dates
                    $old_RECURRENCE_ID = '';
                    $old_SUMMARY = '';
                    $old_LOCATION = '';
                    $old_UID = '';

                    $returnValues = VObjectExtract::calendarData($event['calendardata']);

                    foreach ($returnValues as $key => $value) {
                        if ($key == 'freqEvents') {
                            foreach ($value as $sevent) {
                                if ($sevent['RECURRENCE-ID'] == (new \DateTime($input->reccurenceID))->format('Y-m-d H:i:s')) {
                                    $old_RECURRENCE_ID = $sevent['RECURRENCE-ID'];
                                    $old_SUMMARY = $sevent['SUMMARY'];
                                    $old_DESCRIPTION = $sevent['DESCRIPTION'];
                                    $old_LOCATION = $sevent['DESCRIPTION'];
                                    $old_UID = $sevent['UID'];

                                    // we have to delete the last occurence
                                    $calendarBackend->searchAndDeleteOneEvent($vcalendar, $old_RECURRENCE_ID);
                                    break;
                                }
                            }
                        }
                    }

                    if (!empty($old_UID)) {
                        // only in the case we've found something
                        // the location
                        $coordinates = "";
                        $location = '';

                        if (isset($input->location)) {
                            $location = str_replace("\n", " ", $old_LOCATION);
                            $latLng = GeoUtils::getLatLong($old_LOCATION);
                            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                                $coordinates = $latLng['Latitude'] . ' commaGMAP ' . $latLng['Longitude'];
                            }
                        }

                        $new_vevent = [
                            'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                            'DTSTART' => (new \DateTime($input->start))->format('Ymd\THis'),
                            'DTEND' => (new \DateTime($input->end))->format('Ymd\THis'),
                            'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                            'DESCRIPTION' => $old_DESCRIPTION,
                            'SUMMARY' => $old_SUMMARY,
                            'LOCATION' => $old_LOCATION,
                            'UID' => $old_UID,
                            'SEQUENCE' => '0',
                            'RECURRENCE-ID' => (new \DateTime($input->reccurenceID))->format('Ymd\THis'),
                            'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                            "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
                            //'X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-MAPKIT-HANDLE=CAESvAEaEglnaQKg5U5IQBFCfLuA8gIfQCJdCgZGcmFuY2USAkZSGgZBbHNhY2UqCEJhcy1SaGluMglCaXNjaGhlaW06BTY3ODAwUhJSdWUgUm9iZXJ0IEtpZWZmZXJaATFiFDEgUnVlIFJvYmVydCBLaWVmZmVyKhQxIFJ1ZSBSb2JlcnQgS2llZmZlcjIUMSBSdWUgUm9iZXJ0IEtpZWZmZXIyDzY3ODAwIEJpc2NoaGVpbTIGRnJhbmNlODlAAA==;X-APPLE-RADIUS=70.58736571013601;X-TITLE="1 Rue Robert Kieffer\nBischheim, France":geo' => '48.616383,7.752878'
                        ];

                        // this part allows to create a resource without being in collision on another one
                        if ($calendarBackend->isCalendarResource($input->calendarID)
                            and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                                $input->calendarID, $input->start, $input->end, $input->eventID)) {

                            return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                        }
                        // end of collision test

                        $vcalendar->add('VEVENT', $new_vevent);

                        $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                        return $response->withJson(["status" => "success"]);
                    } else {
                        return $response->withJson(["status" => "failed"]);
                    }
                }
            } else {

                // this part allows to create a resource without being in collision on another one
                if ($calendarBackend->isCalendarResource($input->calendarID)
                    and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                        $input->calendarID, $input->start, $input->end, $input->eventID)) {

                    return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                }
                // end of collision test

                $vcalendar->VEVENT->DTSTART = (new \DateTime($input->start))->format('Ymd\THis');
                $vcalendar->VEVENT->DTEND = (new \DateTime($input->end))->format('Ymd\THis');
                $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                return $response->withJson(['status' => "success1"]);
            }

            return $response->withJson(['status' => "failed"]);
        } else if (!strcmp($input->eventAction, 'attendeesCheckinEvent')) {
            $event = EventQuery::Create()
                ->findOneById($input->eventID);

            // for the CheckIn and to add attendees
            $_SESSION['Action'] = 'Add';
            $_SESSION['EID'] = $event->getID();
            $_SESSION['EName'] = $event->getTitle();
            $_SESSION['EDesc'] = $event->getDesc();
            $_SESSION['EDate'] = ( !is_null($event->getStart()) )?$event->getStart()->format('Y-m-d H:i:s'):'';

            $_SESSION['EventID'] = $event->getID();

            return $response->withJson(['status' => "success"]);
        } else if (!strcmp($input->eventAction, 'suppress')) {

            // this part allows to create a resource without being in collision on another one
            if ( $the_event->getCreatorUserId() != 0 and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources() ) {
                return $response->withJson(["status" => "failed", "message" => _("This resource reservation was not created by you. You cannot edit, move or delete a resource that you do not own.")]);
            }

            // new way to manage events
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();
            $event = $calendarBackend->getCalendarObjectById($input->calendarID, $input->eventID);

            if (isset ($input->reccurenceID)) {

                try {

                    $vcalendar = VObject\Reader::read($event['calendardata']);

                    $calendarBackend->searchAndDeleteOneEvent($vcalendar, $input->reccurenceID);

                    $vcalendar->VEVENT->add('EXDATE', (new \DateTime($input->reccurenceID))->format('Ymd\THis'));
                    $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                    $calendarBackend->updateCalendarObject($input->calendarID, $event['uri'], $vcalendar->serialize());

                } catch (\Exception $ex) {
                    $calendarBackend->deleteCalendarObject($input->calendarID, $event['uri']);
                }

            } else {// we delete only one event

                // We have to use the sabre way to ensure the event is reflected in external connection : CalDav
                $calendarBackend->deleteCalendarObject($input->calendarID, $event['uri']);

            }

            return $response->withJson(['status' => "success"]);
        } else if (!strcmp($input->eventAction, 'modifyEvent')) {

            if ( $the_event->getCreatorUserId() != 0 and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources() ) {
                return $response->withJson(["status" => "failed", "message" => _("This resource reservation was not created by you. You cannot edit, move or delete a resource that you do not own.")]);
            }

            $old_event = EventQuery::Create()->findOneById($input->eventID);

            if (is_null($old_event)) {
                return $response->withJson(["status" => "failed"]);
            }

            $oldCalendarID = [$old_event->getEventCalendarid(), 0];

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $event = $calendarBackend->getCalendarObjectById($oldCalendarID, $input->eventID);

            $vcalendar = VObject\Reader::read($event['calendardata']);
            $eventFullInfos = VObjectExtract::calendarData($event['calendardata']);

            $freqEventsCount = 1;

            if ( array_key_exists ('freqEvents', $eventFullInfos) ) {
                $freqEventsCount = count($eventFullInfos['freqEvents']);
            }

            $calendarService = new CalendarService();

            if ( isset($input->reccurenceID) && $input->reccurenceID != '' ) {// we're in a recursive event

                try {
                    // we have to delete the old event from the reccurence event
                    if ($freqEventsCount > 1) {// in the case of real multiple event
                        $vcalendar = VObject\Reader::read($event['calendardata']);

                        $calendarBackend->searchAndDeleteOneEvent($vcalendar, $input->reccurenceID);

                        $vcalendar->VEVENT->add('EXDATE', (new \DateTime($input->reccurenceID))->format('Ymd\THis'));
                        $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                        $calendarBackend->updateCalendarObject($oldCalendarID, $event['uri'], $vcalendar->serialize());

                        // now we add the new event
                        $calendarService->createEventForCalendar(
                            $input->calendarID, $input->start, $input->end,
                            "", "", $input->EventDesc, $input->EventTitle, $input->location,
                            false, $input->addGroupAttendees, $input->alarm, $input->eventTypeID, $input->eventNotes,
                            $input->eventInActive, $input->Fields, $input->EventCountNotes
                        );

                        LoggerUtils::getAppLogger()->info("ici");

                        return $response->withJson(["status" => "success"]);
                    } else {
                        $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);
                        // now we add the new event
                        $calendarService->createEventForCalendar(
                            $input->calendarID, $input->start, $input->end,
                            "", "", $input->EventDesc, $input->EventTitle, $input->location,
                            false, $input->addGroupAttendees, $input->alarm, $input->eventTypeID, $input->eventNotes,
                            $input->eventInActive, $input->Fields, $input->EventCountNotes
                        );
                        return $response->withJson(["status" => "success"]);
                    }
                } catch (Exception $e) {
                    // in this case we change only the date
                    $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                    $calendarBackend->updateCalendarObject($oldCalendarID, $event['uri'], $vcalendar->serialize());

                    // now we add the new event
                    $calendarService->createEventForCalendar(
                        $input->calendarID, $input->start, $input->end,
                        "", "", $input->EventDesc, $input->EventTitle, $input->location,
                        false, $input->addGroupAttendees, $input->alarm, $input->eventTypeID, $input->eventNotes,
                        $input->eventInActive, $input->Fields, $input->EventCountNotes
                    );
                }
            } /*else {
            // We have to use the sabre way to ensure the event is reflected in external connection : CalDav
            $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);
        }*/


            // Now we start to work with the new calendar
            if ( is_array( $input->calendarID ) ) {
                $calIDs = $input->calendarID;
            } else {
                $calIDs = explode(",", $input->calendarID);
            }

            $calendarId = $calIDs[0];
            $Id = $calIDs[1];

            // get the calendar we want to work with
            $calendar = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneById($Id);

            $coordinates = "";
            $location = '';

            if (isset($input->location)) {
                $location = str_replace("\n", " ", $input->location);

                $latLng = GeoUtils::getLatLong($input->location);
                if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                    $coordinates = $latLng['Latitude'] . ' commaGMAP ' . $latLng['Longitude'];
                }
            }

            $uuid = $vcalendar->VEVENT->UID;

            unset($vcalendar->VEVENT);
            if ( !empty($input->recurrenceValid) ) {
                $vevent = [
                    'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                    'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                    'DTSTART' => (new \DateTime($input->start))->format('Ymd\THis'),
                    'DTEND' => (new \DateTime($input->end))->format('Ymd\THis'),
                    'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                    'DESCRIPTION' => $input->EventDesc,
                    'SUMMARY' => $input->EventTitle,
                    'UID' => $uuid,//'CE4306F2-8CC0-41DF-A971-1ED88AC208C7',// attention tout est en majuscules
                    'RRULE' => $input->recurrenceType . ';' . 'UNTIL=' . (new \DateTime($input->endrecurrence))->format('Ymd\THis'),
                    'SEQUENCE' => '0',
                    'LOCATION' => $input->location,
                    'TRANSP' => 'OPAQUE',
                    'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                    "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
                ];

                // this part allows to create a resource without being in collision on another one
                if ($calendarBackend->isCalendarResource($calIDs)
                    and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                        $calIDs, $input->start, $input->end, $input->eventID, $input->recurrenceType, $input->endrecurrence)) {

                    return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                }
                // end of collision test

            } else {
                $vevent = [
                    'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                    'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                    'DTSTART' => (new \DateTime($input->start))->format('Ymd\THis'),
                    'DTEND' => (new \DateTime($input->end))->format('Ymd\THis'),
                    'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                    'DESCRIPTION' => $input->EventDesc,
                    'SUMMARY' => $input->EventTitle,
                    'UID' => $uuid,
                    'SEQUENCE' => '0',
                    'LOCATION' => $input->location,
                    'TRANSP' => 'OPAQUE',
                    'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                    "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
                ];

                // this part allows to create a resource without being in collision on another one
                if ($calendarBackend->isCalendarResource($calIDs)
                    and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                        $calIDs, $input->start, $input->end, $input->eventID)) {

                    return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
                }
                // end of collision test
            }

            $realVevent = $vcalendar->add('VEVENT', $vevent);

            unset($vcalendar->ORGANIZER);
            unset($vcalendar->ATTENDEE);
            if ($calendar->getGroupId() && $input->addGroupAttendees) {// add Attendees with sabre connection
                $persons = Person2group2roleP2g2rQuery::create()
                    ->filterByGroupId($calendar->getGroupId())
                    ->find();

                $res = $persons->count();

                if ($persons->count() > 0) {

                    $realVevent->add('ORGANIZER', 'mailto:' . SessionUser::getUser()->getEmail());

                    foreach ($persons as $person) {
                        $user = UserQuery::Create()->findOneByPersonId($person->getPersonId());
                        if (!empty($user)) {
                            $realVevent->add('ATTENDEE', 'mailto:' . $user->getEmail());
                        }
                    }
                }
            }

            if ($input->alarm != _("NONE")) {
                $realVevent->add('VALARM', ['TRIGGER' => $input->alarm, 'DESCRIPTION' => 'Event reminder', 'ACTION' => 'DISPLAY']);
            }

            /*if ($old_event->getEventCalendarid() != $calendarId) {
                // in the case the calendar is changing we've to delete the last entry in the calendar
                $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);

                // now we create a new event in the calendar
                $etag = $calendarBackend->createCalendarObject($calIDs, $uuid, $vcalendar->serialize());

                $old_event = EventQuery::Create()->findOneByEtag(str_replace('"', "", $etag));
            } else {*/
            // we simply update the event in the current calendar
            $calendarBackend->updateCalendarObject($calIDs, $event['uri'], $vcalendar->serialize());
            //}

            // Now we move to propel, to finish the put extra infos
            $eventTypeName = "";

            if ($input->eventTypeID) {
                $type = EventTypesQuery::Create()
                    ->findOneById($input->eventTypeID);
                $eventTypeName = $type->getName();
            }

            $old_event->setType($input->eventTypeID);
            $old_event->setText($input->eventNotes);
            $old_event->setTypeName($eventTypeName);
            $old_event->setInActive($input->eventInActive);

            $old_event->setLocation($input->location);
            $old_event->setCoordinates($coordinates);


            // we set the groupID to manage correctly the attendees : Historical
            $old_event->setGroupId($calendar->getGroupId());
            $old_event->setEventCalendarid($calendarId);

            // we first delete the old attendences
            $eventCountsOld = EventCountsQuery::create()->findByEvtcntEventid($old_event->getID());
            $eventCountsOld->delete();

            if (!empty($input->Fields)) {
                foreach ($input->Fields as $field) {
                    $eventCount = new EventCounts;
                    $eventCount->setEvtcntEventid($old_event->getID());
                    $eventCount->setEvtcntCountid($field['countid']);
                    $eventCount->setEvtcntCountname($field['name']);
                    $eventCount->setEvtcntCountcount($field['value']);
                    $eventCount->setEvtcntNotes($input->EventCountNotes);
                    $eventCount->save();
                }
            }

            $old_event->save();

            if ($old_event->getGroupId() && $input->addGroupAttendees) {// add Attendees
                $persons = Person2group2roleP2g2rQuery::create()
                    ->filterByGroupId($old_event->getGroupId())
                    ->find();

                if ($persons->count() > 0) {
                    foreach ($persons as $person) {
                        try {
                            if ($person->getPersonId() > 0) {
                                $eventAttent = new EventAttend();

                                $eventAttent->setEventId($old_event->getID());
                                $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
                                if (SystemConfig::getBooleanValue('bCheckedAttendees') ) {
                                    $date = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
                                    $eventAttent->setCheckinDate($date);
                                } else {
                                    $eventAttent->setCheckinDate(NULL);
                                }
                                $eventAttent->setPersonId($person->getPersonId());
                                $eventAttent->save();
                            }
                        } catch (\Exception $ex) {
                            $errorMessage = $ex->getMessage();
                            //return $response->withJson(['status' => $errorMessage]);
                        }
                    }

                    $date = new \DateTime ($vcalendar->VEVENT->DTSTART->getDateTime()->format('Y-m-d H:i:s'));

                    //
                    $_SESSION['Action'] = 'Add';
                    $_SESSION['EID'] = $old_event->getID();
                    $_SESSION['EName'] = $input->EventTitle;
                    $_SESSION['EDesc'] = $input->EventDesc;
                    $_SESSION['EDate'] = ( !is_null($date) )?$date->format('Y-m-d H:i:s'):'';

                    $_SESSION['EventID'] = $old_event->getID();
                }
            }

            return $response->withJson(["status" => "success", "res2" => $calendar->getGroupId()]);
        }

        return $response->withJson(["status" => "failed"]);
    }
}
