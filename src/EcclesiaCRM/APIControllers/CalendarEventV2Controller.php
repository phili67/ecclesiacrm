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
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\Base\EventQuery;
use EcclesiaCRM\Base\EventTypesQuery;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\SessionUser;

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

    public function getAllEvents(ServerRequest $request, Response $response, array $args): Response
    {
        $Events = EventQuery::create()
            ->find();

        $return = [];

        foreach ($Events as $event) {
            $values = [
                'Id' => $event->getID(),
                'Title' => $event->getTitle(),
                'Type' => $event->getType(),
                'InActive' => $event->getInActive(),
                'Text' => $event->getText(),
                'Start' => $event->getStart(),
                'End' => $event->getEnd(),
                'TypeName' => $event->getTypeName(),
                'GroupId' => $event->getGroupId(),
                'LastOccurence' => $event->getLastOccurence(),
                'Location' => $event->getLocation(),
                'Coordinates' => $event->getCoordinates(),
            ];

            $return[] = $values;
        }

        return $response->withJson($return);
    }

    public function getNotDoneEvents(ServerRequest $request, Response $response, array $args): Response
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

        return $response->withJson(["Events" => $return]);
    }

    public function numbersOfEventOfToday(ServerRequest $request, Response $response, array $args): Response
    {
        return $response->withJson(MenuEventsCount::getNumberEventsOfToday());
    }

    public function getEventTypes(ServerRequest $request, Response $response, array $args): Response
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

    public function eventNames(ServerRequest $request, Response $response, array $args): Response
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

    public function deleteeventtype(ServerRequest $request, Response $response, array $args): Response
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

    public function eventInfo(ServerRequest $request, Response $response, array $args): Response
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

    public function personCheckIn(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        try {
            $eventAttent = new EventAttend();

            $eventAttent->setEventId($params->EventID);
            $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());
            $eventAttent->setCheckinDate(NULL);
            $eventAttent->setPersonId($params->PersonId);
            $eventAttent->save();
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
            return $response->withJson(['status' => $errorMessage]);
        }

        return $response->withJson(['status' => "success"]);
    }

    public function groupCheckIn(ServerRequest $request, Response $response, array $args): Response
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

    public function familyCheckIn(ServerRequest $request, Response $response, array $args): Response
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

    public function eventCount(ServerRequest $request, Response $response, array $args): Response
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
                $values['startHour'] = sprintf("%02d", $aEventStartHour);
                $values['startMin'] = sprintf("%02d", $aEventStartMins);
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

    public function manageEvent(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        $the_event = null;

        if (isset($input->eventID)) {
            $the_event = EventQuery::create()->findOneById($input->eventID);
        }

        $calendarService = new CalendarService();

        if (!strcmp($input->eventAction, 'createEvent')) {

            if (!$calendarService->createEventForCalendar($input->calendarID, $input->start, $input->end,
                $input->recurrenceType, $input->endrecurrence, $input->EventDesc, $input->EventTitle, $input->location,
                $input->recurrenceValid, $input->addGroupAttendees, $input->alarm, $input->eventTypeID, $input->eventNotes,
                $input->eventInActive, $input->Fields, $input->EventCountNotes, $input->eventAllday)) {
                return $response->withJson(["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")]);
            }

            return $response->withJson(["status" => "success"]);

        } else if (!strcmp($input->eventAction, 'moveEvent')) {

            $the_event->setAllday((is_null($input->eventAllday) or $input->eventAllday == false)?0:1);
            $the_event->save();

            // this part allows to create a resource without being in collision on another one
            if ($the_event->getCalendarType() >= 2 and $the_event->getCreatorUserId() != 0
                and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources()) {
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
                    if (is_array($input->calendarID)) {
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
            if ($the_event->getCalendarType() >= 2 and $the_event->getCreatorUserId() != 0
                and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources()) {
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
            $_SESSION['EDate'] = (!is_null($event->getStart())) ? $event->getStart()->format('Y-m-d H:i:s') : '';

            $_SESSION['EventID'] = $event->getID();

            return $response->withJson(['status' => "success"]);

        } else if (!strcmp($input->eventAction, 'suppress')) {

            // this part allows to create a resource without being in collision on another one
            if ($the_event->getCalendarType() >= 2 and $the_event->getCreatorUserId() != 0
                and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources()) {
                return $response->withJson(["status" => "failed", "message" => _("This resource reservation was not created by you. You cannot edit, move or delete a resource that you do not own.")]);
            }

            if (isset ($input->reccurenceID)) {
                return $response->withJson($calendarService->removeEventFromCalendar($input->calendarID, $input->eventID, $input->reccurenceID));
            } else {
                return $response->withJson($calendarService->removeEventFromCalendar($input->calendarID, $input->eventID));
            }

        } else if (!strcmp($input->eventAction, 'modifyEvent')) {
            if ($the_event->getCalendarType() >= 2 and $the_event->getCreatorUserId() != 0
                and SessionUser::getId() != $the_event->getCreatorUserId() and !SessionUser::isManageCalendarResources()) {
                return $response->withJson(["status" => "failed", "message" => _("This resource reservation was not created by you. You cannot edit, move or delete a resource that you do not own.")]);
            }

            return $response->withJson($calendarService->modifyEventFromCalendar($input->calendarID, $input->eventID, $input->reccurenceID, $input->start,
                $input->end, $input->EventTitle, $input->EventDesc, $input->location, $input->addGroupAttendees, $input->alarm, $input->eventTypeID, $input->eventNotes,
                $input->eventInActive, $input->Fields, $input->EventCountNotes, $input->recurrenceValid, $input->recurrenceType, $input->endrecurrence, $input->eventAllday));
        }

        return $response->withJson(["status" => "failed"]);
    }
}
