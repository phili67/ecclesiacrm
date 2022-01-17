<?php
/*******************************************************************************
 *
 *  filename    : CalendarService.php
 *  last change : 2020-01-25
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

namespace EcclesiaCRM\Service;

use EcclesiaCRM\Base\EventTypesQuery;
use EcclesiaCRM\CalendarinstancesQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\EventCounts;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\FamilyQuery;

use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;

use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\CalendarinstancesTableMap;

use EcclesiaCRM\MyVCalendar\VCalendarExtension;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\GeoUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\MyPDO\VObjectExtract;

use Sabre\DAV\UUIDUtil;
use Sabre\VObject;


class CalendarService
{
    public function getEventTypes()
    {
        $eventTypes = [];
        array_push($eventTypes, ['Name' => gettext('Event'), 'backgroundColor' => '#f39c12']);
        array_push($eventTypes, ['Name' => gettext('Birthday'), 'backgroundColor' => '#f56954']);
        array_push($eventTypes, ['Name' => gettext('Anniversary'), 'backgroundColor' => '#0000ff']);
        return $eventTypes;
    }

    public function getEvents($start, $end, $isBirthdayActive, $isAnniversaryActive)
    {
        $origStart = $start;
        $origEnd = $end;

        $dtOrigStart = new \DateTime($origStart);
        $dtOrigEnd = new \DateTime($origEnd);

        $events = [];
        $startDate = date_create($start);
        $endDate = date_create($end);
        $startYear = $endYear = '1900';
        $endsNextYear = false;
        if ($endDate->format('Y') > $startDate->format('Y')) {
            $endYear = '1901';
            $endsNextYear = true;
        }
        $firstYear = $startDate->format('Y');


        if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
            if ($isBirthdayActive) {
                $peopleWithBirthDays = PersonQuery::create()
                    ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
                    ->JoinWithFamily();

                // get the first and the last month
                $firstMonth = $startDate->format('m');
                $endMonth = $endDate->format('m');

                $month = $firstMonth;

                $peopleWithBirthDays->filterByBirthMonth($firstMonth);// the event aren't more than a month

                while ($month != $endMonth) {// we loop to have all the months from the first in the start to the end
                    $month += 1;
                    if ($month == 13) {
                        $month = 1;
                    }
                    if ($month == 0) {
                        $month = 1;
                    }
                    $peopleWithBirthDays->_or()->filterByBirthMonth($month);// the event aren't more than a month
                }


                $peopleWithBirthDays->find();
                foreach ($peopleWithBirthDays as $person) {
                    $year = $firstYear;
                    if ($person->getBirthMonth() == 1 && $endsNextYear) {
                        $year = $firstYear + 1;
                    }

                    $dtStart = new \DateTime($year . '-' . $person->getBirthMonth() . '-' . $person->getBirthDay());

                    if ($dtOrigStart <= $dtStart and $dtStart <= $dtOrigEnd) {
                        $event = $this->createCalendarItemForGetEvents('birthday', '<i class="fa fa-birthday-cake"></i>',
                            $person->getFullName() . " " . $person->getAge(), $dtStart->format(\DateTimeInterface::ATOM), '', $person->getViewURI());
                        array_push($events, $event);
                    }
                }
            }

            if ($isAnniversaryActive) {
                // we search the Anniversaries
                $Anniversaries = FamilyQuery::create()
                    ->filterByWeddingDate(['min' => '0001-00-00']) // a Wedding Date
                    ->filterByDateDeactivated(null, Criteria::EQUAL) //Date Deactivated is null (active)
                    ->find();

                $curYear = date('Y');
                $curMonth = date('m');
                foreach ($Anniversaries as $anniversary) {
                    $year = $curYear;
                    if ($anniversary->getWeddingMonth() < $curMonth) {
                        $year = $year + 1;
                    }

                    $dtStart = new \DateTime($year . '-' . $anniversary->getWeddingMonth() . '-' . $anniversary->getWeddingDay());

                    if ($dtOrigStart <= $dtStart and $dtStart <= $dtOrigEnd) {
                        $event = $this->createCalendarItemForGetEvents('anniversary', '<i class="fa fa-birthday-cake"></i>',
                            $anniversary->getName(), $dtStart->format(\DateTimeInterface::ATOM), '', $anniversary->getViewURI());
                        array_push($events, $event);
                    }
                }
            }
        }


        // new way to manage events

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        $principalBackend = new PrincipalPDO();
        // get all the calendars for the current user

        $calendars = $calendarBackend->getCalendarsForUser('principals/' . strtolower(SessionUser::getUser()->getUserName()), "displayname", false);

        foreach ($calendars as $calendar) {
            $calendarName = $calendar['{DAV:}displayname'];
            $calendarColor = $calendar['{http://apple.com/ns/ical/}calendar-color'];
            $writeable = ($calendar['share-access'] == 1 || $calendar['share-access'] == 3) ? true : false;
            $calendarUri = $calendar['uri'];
            $calendarID = $calendar['id'];
            $groupID = $calendar['grpid'];

            $icon = "";

            if ($writeable) {
                $icon = '<i class="fa fa-pencil"></i>';
            }

            if ($groupID > 0) {
                $icon .= '<i class="fa fa-users"></i>';
            }

            if ($calendar['share-access'] == 2 || $calendar['share-access'] == 3) {
                $icon .= '<i class="fa  fa-share"></i>';
            } else if ($calendar['share-access'] == 1 && $groupID == 0 && $calendar['cal_type'] == 1) {
                $icon .= '<i class="fa fa-user"></i>';
            }

            // we test the resources
            if ($calendar['cal_type'] == 2) {// room
                $icon .= ' <i class="fa fa-building"></i>&nbsp';
            } else if ($calendar['cal_type'] == 3) {// computer
                $icon .= ' <i class="fa fa-windows"></i>&nbsp;';
            } else if ($calendar['cal_type'] == 4) {// video
                $icon .= ' <i class="fa fa-video-camera"></i>&nbsp;';
            }

            if ($calendar['present'] == 0 || $calendar['visible'] == 0) {// this ensure the calendars are present or not
                continue;
            }

            // we get all the events for the Cal
            $eventsForCal = $calendarBackend->getCalendarObjects($calendar['id']);

            foreach ($eventsForCal as $eventForCal) {
                $evnt = EventQuery::Create()
                    ->addJoin(EventTableMap::COL_EVENT_TYPE, EventTypesTableMap::COL_TYPE_ID,Criteria::LEFT_JOIN)
                    ->addJoin(EventTableMap::COL_EVENT_GRPID, GroupTableMap::COL_GRP_ID,Criteria::LEFT_JOIN)
                    ->addJoin(EventTableMap::COL_EVENT_CALENDARID, CalendarinstancesTableMap::COL_CALENDARID,Criteria::LEFT_JOIN)
                    ->addAsColumn('EventTypeName',EventTypesTableMap::COL_TYPE_NAME)
                    ->addAsColumn('GroupName',GroupTableMap::COL_GRP_NAME)
                    ->addAsColumn('CalendarName',CalendarinstancesTableMap::COL_DISPLAYNAME)
                    ->addAsColumn('rights',CalendarinstancesTableMap::COL_ACCESS)
                    ->filterByInActive('false')->findOneById($eventForCal['id']);

                if ($evnt != null) {

                    $calObj = $calendarBackend->getCalendarObject($calendar['id'], $eventForCal['uri']);

                    $cal_category = ($calendar['grpid'] != "0") ? 'group' : 'personal';

                    if ($calendar['share-access'] >= 2) {
                        $cal_type = 5;
                    } else {
                        $cal_type = $calendar['cal_type'];
                    }

                    $freqEvents = VObjectExtract::calendarData($calObj['calendardata'], $origStart, $origEnd);

                    if ($freqEvents == null) {
                        continue;
                    }

                    $title = $evnt->getTitle();
                    $desc = $evnt->getDesc();
                    $start = $evnt->getStart('Y-m-d H:i:s');
                    $end = $evnt->getEnd('Y-m-d H:i:s');
                    $id = $evnt->getID();
                    $type = $evnt->getType();
                    $grpID = $evnt->getGroupId();
                    $loc = $evnt->getLocation();
                    $lat = $evnt->getLatitude();
                    $long = $evnt->getLongitude();
                    $text = $evnt->getText();
                    $calID = $calendar['id'];
                    $alarm = $evnt->getAlarm();
                    $rrule = $evnt->getFreqLastOccurence();
                    $freq = $evnt->getFreq();
                    $eventTypeName = $evnt->getEventTypeName();
                    $eventGroupName = $evnt->getGroupName();
                    $eventCalendarName = $evnt->getCalendarName();

                    if (!(SessionUser::getUser()->isAdmin())) {
                        $eventRights = ($evnt->getRights() == 1 || $evnt->getRights() == 3)?true:false;
                    } else {
                        $eventRights = true;
                    }

                    $fEvnt = false;
                    $subid = 1;

                    foreach ($freqEvents as $key => $value) {
                        if ($key == 'freq' && $value != 'none') {
                            $fEvnt = true;
                        } elseif ($key == 'freqEvents' && $fEvnt == true) { // we are in front of a recurrence event !!!
                            foreach ($value as $freqValue) {
                                $title = $freqValue['SUMMARY'];
                                $start = $freqValue['DTSTART'];
                                $end = $freqValue['DTEND'];
                                $reccurenceID = $freqValue['RECURRENCE-ID'];

                                $dtStart = new \DateTime($start);
                                $dtEnd = new \DateTime($end);

                                if ($dtOrigStart <= $dtStart and $dtStart <= $dtOrigEnd
                                    and $dtOrigStart <= $dtEnd and $dtEnd <= $dtOrigEnd) {

                                    $event = $this->createCalendarItemForGetEvents('event', $icon,
                                        $title, $start, $end,
                                        '', $id, $type, $grpID,
                                        $desc, $text, $calID, $calendarColor,
                                        $subid++, 1, $reccurenceID, $rrule, $freq, $writeable,
                                        $loc, $lat, $long, $alarm, $cal_type, $cal_category, $eventTypeName,
                                        $eventGroupName, $eventCalendarName, $eventRights);// only the event id sould be edited and moved and have custom color

                                    array_push($events, $event);
                                }
                            }
                        }
                    }

                    if ($fEvnt == false) {

                        $dtStart = new \DateTime($start);
                        $dtEnd = new \DateTime($end);

                        if ($dtOrigStart <= $dtStart and $dtStart <= $dtOrigEnd
                            and $dtOrigStart <= $dtEnd and $dtEnd <= $dtOrigEnd) {

                            $event = $this->createCalendarItemForGetEvents('event', $icon,
                                $title, $start, $end,
                                '', $id, $type, $grpID,
                                $desc, $text, $calID, $calendarColor, 0, 0, 0, $rrule, $freq,
                                $writeable, $loc, $lat, $long, $alarm, $cal_type, $cal_category,
                                $eventTypeName, $eventGroupName, $eventCalendarName, $eventRights);// only the event id sould be edited and moved and have custom color

                            array_push($events, $event);
                        }
                    }
                }
            }
        }

        return $events;
    }

    public function createCalendarItemForGetEvents($type, $icon, $title, $start, $end, $uri, $eventID = 0, $eventTypeID = 0, $groupID = 0, $desc = "", $text = "",
                                                   $calendarid = null, $backgroundColor = null, $subid = 0,
                                                   $recurrent = 0, $reccurenceID = '', $rrule = '', $freq = '',
                                                   $writeable = false, $location = "", $latitude = 0, $longitude = 0, $alarm = "", $cal_type = "0",
                                                   $cal_category = "personal", $eventTypeName="all", $eventGroupName="None", $eventCalendarName = "None", $eventRights=false)
    {
        $event = [];
        switch ($type) {
            case 'birthday':
                $event['backgroundColor'] = '#dd4b39';
                break;
            case 'anniversary':
                $event['backgroundColor'] = '#3c8dbc';
                break;
            default:
                $event['backgroundColor'] = '#eeeeee';
        }

        $event['title'] = $title;
        $event['start'] = $start;
        $event['month'] = (int)explode('-',$start)[1];
        $event['origStart'] = $start;
        $event['icon'] = $icon;
        $event['realType'] = $event['type'] = $type;
        $event['TypeName'] = $eventTypeName;
        $event['GroupName'] = $eventGroupName;
        $event['CalendarName'] = $eventCalendarName;
        $event['Rights'] = $eventRights;

        if ($end != '') {
            $event['end'] = $end;
            $event['allDay'] = false;
        } else {
            $event['allDay'] = true;
        }
        if ($uri != '') {
            $event['url'] = $uri;
        }

        if ($type == 'event') {
            $event['eventID'] = $eventID;
            $event['eventTypeID'] = $eventTypeID;
            $event['groupID'] = $groupID;
            $event['Desc'] = $desc;
            $event['Text'] = $text;
            $event['recurrent'] = $recurrent;
            $event['rrule'] = $rrule;
            $event['freq'] = $freq;
            $event['writeable'] = $writeable;
            $event['location'] = $location;
            $event['longitude'] = $longitude;
            $event['latitude'] = $latitude;
            $event['alarm'] = $alarm;
            $event['calType'] = intval($cal_type);
            $event['cal_category'] = $cal_category;

            switch ($cal_category) {
                case 'personal':
                    $event['cal_category_translated'] = _("Personal Calendar");
                    break;
                case 'group':
                    $event['cal_category_translated'] = _("Group");
                    break;
                case 'share':
                    $event['cal_category_translated'] = _("Share");
                    break;
            }

            if ($calendarid != null) {
                $event['calendarID'] = $calendarid;//[$calendarid[0],$calendarid[1]];//$calendarid;
            }

            if ($backgroundColor != null) {
                $event['backgroundColor'] = $backgroundColor;
            }

            $event['subID'] = $subid;

            $event['reccurenceID'] = '';
            if (!empty($reccurenceID)) {
                $event['reccurenceID'] = $reccurenceID;
            }


            $eventCounts = EventCountsQuery::Create()->findByEvtcntEventid($eventID);

            $event['EventCounts'] = $eventCounts->toArray();
        }

        return $event;
    }

    public function createEventForCalendar($calendarID, $start, $end, $recurrenceType, $endrecurrence, $EventDesc, $EventTitle, $inputlocation,
                                           $recurrenceValid, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes, $eventInActive, $Fields,
                                           $EventCountNotes)
    {
        // New way to manage events
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();

        $uuid = strtoupper(UUIDUtil::getUUID());

        $vcalendar = new VCalendarExtension();

        if (is_array($calendarID)) {
            $calIDs = $calendarID;
        } else {
            $calIDs = explode(",", $calendarID);
        }

        // We move to propel, to find the calendar
        $calendarId = $calIDs[0];
        $Id = $calIDs[1];
        $calendar = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneById($Id);

        // this part allows to create a resource without being in collision on another one
        $isCalendarResource = $calendarBackend->isCalendarResource($calIDs);

        if ($isCalendarResource
            and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                $calIDs, $start, $end,
                0,
                $recurrenceType,
                $endrecurrence)) {

            return false;
        }

        $coordinates = "";
        $location = '';

        if (isset($inputlocation)) {
            $location = str_replace("\n", " ", $inputlocation);
            $latLng = GeoUtils::getLatLong($inputlocation);
            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                $coordinates = $latLng['Latitude'] . ' commaGMAP ' . $latLng['Longitude'];
            }
        }

        // we remove to Sabre
        if (!empty($recurrenceValid)) {

            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'LOCATION' => $inputlocation,
                'UID' => $uuid,
                'RRULE' => $recurrenceType . ';' . 'UNTIL=' . (new \DateTime($endrecurrence))->format('Ymd\THis'),
                'SEQUENCE' => '0',
                'TRANSP' => 'OPAQUE',
                'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
            ];

        } else {

            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'LOCATION' => $inputlocation,
                'UID' => $uuid,
                'SEQUENCE' => '0',
                'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
                //'X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-MAPKIT-HANDLE=CAESvAEaEglnaQKg5U5IQBFCfLuA8gIfQCJdCgZGcmFuY2USAkZSGgZBbHNhY2UqCEJhcy1SaGluMglCaXNjaGhlaW06BTY3ODAwUhJSdWUgUm9iZXJ0IEtpZWZmZXJaATFiFDEgUnVlIFJvYmVydCBLaWVmZmVyKhQxIFJ1ZSBSb2JlcnQgS2llZmZlcjIUMSBSdWUgUm9iZXJ0IEtpZWZmZXIyDzY3ODAwIEJpc2NoaGVpbTIGRnJhbmNlODlAAA==;X-APPLE-RADIUS=70.58736571013601;X-TITLE="1 Rue Robert Kieffer\nBischheim, France":geo' => '48.616383,7.752878'
            ];

        }

        $realVevent = $vcalendar->add('VEVENT', $vevent);

        //$res = '';

        if ($isCalendarResource) {
            // in resource : room, computer and videos we've have to include the organizer, and himself at least
            $realVevent->add('ORGANIZER', 'mailto:' . SessionUser::getUser()->getEmail());
            $realVevent->add('ATTENDEE', 'mailto:' . SessionUser::getUser()->getEmail());
        }

        if ($calendar->getGroupId() && $addGroupAttendees) {// add Attendees with sabre connection
            $persons = Person2group2roleP2g2rQuery::create()
                ->filterByGroupId($calendar->getGroupId())
                ->find();

            $res = $persons->count();

            if ($persons->count() > 0) {

                if (!$isCalendarResource) { // it's yet done over
                    $realVevent->add('ORGANIZER', 'mailto:' . SessionUser::getUser()->getEmail());
                }

                //$res .= SessionUser::getUser()->getEmail();

                foreach ($persons as $person) {
                    $user = UserQuery::Create()->findOneByPersonId($person->getPersonId());
                    if (!empty($user)) {
                        $vevent = array_merge($vevent, ['ATTENDEE;CN=' . $user->getFullName() . ';CUTYPE=INDIVIDUAL;EMAIL=' . $user->getEmail() . ';PARTSTAT=ACCEPTED;SCHEDULE-STATUS=3.7:mailto' => $user->getEmail()]);
                        $realVevent->add('ATTENDEE', 'mailto:' . $user->getEmail());
                        $res .= " " . $user->getEmail();
                    }
                }
            }
        }

        if ($alarm != _("NONE")) {
            $realVevent->add('VALARM', ['TRIGGER' => $alarm, 'DESCRIPTION' => 'Event reminder', 'ACTION' => 'DISPLAY']);
        }

        // Now we move to propel, to finish the put extra infos
        $etag = $calendarBackend->createCalendarObject($calIDs, $uuid, $vcalendar->serialize());

        // we get the real event in th DB
        $event = \EcclesiaCRM\Base\EventQuery::Create()->findOneByEtag(str_replace('"', "", $etag));
        $eventTypeName = "";

        if ($eventTypeID) {
            $type = EventTypesQuery::Create()
                ->findOneById($eventTypeID);
            $eventTypeName = $type->getName();
        }

        $event->setType($eventTypeID);
        $event->setText($eventNotes);
        $event->setTypeName($eventTypeName);
        $event->setInActive($eventInActive);

        if ($isCalendarResource) {
            $event->setCreatorUserId(SessionUser::getId());
        }

        // we set the groupID to manage correctly the attendees : Historical
        $event->setGroupId($calendar->getGroupId());
        $event->setLocation($inputlocation);
        $event->setCoordinates($coordinates);

        $event->save();

        if (!empty($Fields)) {
            foreach ($Fields as $field) {
                $eventCount = new EventCounts;
                $eventCount->setEvtcntEventid($event->getID());
                $eventCount->setEvtcntCountid($field['countid']);
                $eventCount->setEvtcntCountname($field['name']);
                $eventCount->setEvtcntCountcount($field['value']);
                $eventCount->setEvtcntNotes($EventCountNotes);
                $eventCount->save();
            }
        }

        $event->save();

        if ($event->getGroupId() && $addGroupAttendees) {// add Attendees
            $persons = Person2group2roleP2g2rQuery::create()
                ->filterByGroupId($event->getGroupId())
                ->find();

            if ($persons->count() > 0) {
                foreach ($persons as $person) {
                    try {
                        if ($person->getPersonId() > 0) {
                            $eventAttent = new EventAttend();

                            $eventAttent->setEventId($event->getID());
                            $eventAttent->setCheckinId(SessionUser::getUser()->getPersonId());

                            if (SystemConfig::getBooleanValue('bCheckedAttendees')) {
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

                //
                $_SESSION['Action'] = 'Add';
                $_SESSION['EID'] = $event->getID();
                $_SESSION['EName'] = $EventTitle;
                $_SESSION['EDesc'] = $EventDesc;
                $_SESSION['EDate'] = (!is_null($date)) ? $date->format('Y-m-d H:i:s') : '';

                $_SESSION['EventID'] = $event->getID();
            }
        }

        return true;
    }

    public function removeEventFromCalendar($calendarID, $eventID, $reccurenceID = null)
    {
        // new way to manage events
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        $event = $calendarBackend->getCalendarObjectById($calendarID, $eventID, $reccurenceID);

        if (!is_null($reccurenceID)) {

            try {

                $vcalendar = VObject\Reader::read($event['calendardata']);

                $calendarBackend->searchAndDeleteOneEvent($vcalendar, $reccurenceID);

                $vcalendar->VEVENT->add('EXDATE', (new \DateTime($reccurenceID))->format('Ymd\THis'));
                $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                $calendarBackend->updateCalendarObject($calendarID, $event['uri'], $vcalendar->serialize());

            } catch (\Exception $ex) {
                $calendarBackend->deleteCalendarObject($calendarID, $event['uri']);
            }

        } else {// we delete only one event

            // We have to use the sabre way to ensure the event is reflected in external connection : CalDav
            $calendarBackend->deleteCalendarObject($calendarID, $event['uri']);

        }

        return ['status' => "success"];
    }

    public function modifyEventFromCalendar($calendarID, $eventID, $reccurenceID, $start, $end, $EventTitle,
                                            $EventDesc, $location, $addGroupAttendees, $alarm, $eventTypeID,
                                            $eventNotes, $eventInActive, $Fields, $EventCountNotes, $recurrenceValid, $recurrenceType, $endrecurrence)
    {
        $old_event = EventQuery::Create()->findOneById($eventID);

        if (is_null($old_event)) {
            return ["status" => "failed"];
        }

        $oldCalendarID = [$old_event->getEventCalendarid(), 0];

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();

        $event = $calendarBackend->getCalendarObjectById($oldCalendarID, $eventID);

        $vcalendar = VObject\Reader::read($event['calendardata']);
        $eventFullInfos = VObjectExtract::calendarData($event['calendardata']);

        $freqEventsCount = 1;

        if (array_key_exists('freqEvents', $eventFullInfos)) {
            $freqEventsCount = count($eventFullInfos['freqEvents']);
        }

        if ( isset($reccurenceID) && $reccurenceID != '' ) {// we're in a recursive event

            try {
                // we have to delete the old event from the reccurence event
                if ($freqEventsCount > 1) {// in the case of real multiple event
                    $vcalendar = VObject\Reader::read($event['calendardata']);

                    $calendarBackend->searchAndDeleteOneEvent($vcalendar, $reccurenceID);

                    $vcalendar->VEVENT->add('EXDATE', (new \DateTime($reccurenceID))->format('Ymd\THis'));
                    $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                    $calendarBackend->updateCalendarObject($oldCalendarID, $event['uri'], $vcalendar->serialize());

                    // now we add the new event
                    $this->createEventForCalendar(
                        $calendarID, $start, $end,
                        "", "", $EventDesc, $EventTitle, $location,
                        false, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                        $eventInActive, $Fields, $EventCountNotes
                    );

                    return ["status" => "success"];
                } else {
                    $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);

                    // now we add the new event
                    $this->createEventForCalendar(
                        $calendarID, $start, $end,
                        $recurrenceType, $endrecurrence, $EventDesc, $EventTitle, $location,
                        $recurrenceValid, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                        $eventInActive, $Fields, $EventCountNotes
                    );
                    return ["status" => "success"];
                }
            } catch (Exception $e) {
                // in this case we change only the date
                $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                $calendarBackend->updateCalendarObject($oldCalendarID, $event['uri'], $vcalendar->serialize());

                // now we add the new event
                $this->createEventForCalendar(
                    $calendarID, $start, $end,
                    "", "", $EventDesc, $EventTitle, $location,
                    false, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                    $eventInActive, $Fields, $EventCountNotes
                );
            }
        } else {
            // We have to use the sabre way to ensure the event is reflected in external connection : CalDav
            $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);

            // now we add the new event
            $this->createEventForCalendar(
                $calendarID, $start, $end,
                $recurrenceType, $endrecurrence, $EventDesc, $EventTitle, $location,
                $recurrenceValid, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                $eventInActive, $Fields, $EventCountNotes
            );

            return ["status" => "success"];
        }

        // this code is normally dead !!!!

        // Now we start to work with the new calendar
        if (is_array($calendarID)) {
            $calIDs = $calendarID;
        } else {
            $calIDs = explode(",", $calendarID);
        }

        $calendarId = $calIDs[0];
        $Id = $calIDs[1];

        // get the calendar we want to work with
        $calendar = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneById($Id);

        $coordinates = "";
        $location = '';

        if (isset($location)) {
            $location = str_replace("\n", " ", $location);

            $latLng = GeoUtils::getLatLong($location);
            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                $coordinates = $latLng['Latitude'] . ' commaGMAP ' . $latLng['Longitude'];
            }
        }

        $uuid = $vcalendar->VEVENT->UID;

        unset($vcalendar->VEVENT);
        if (!empty($input->recurrenceValid)) {
            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'UID' => $uuid,//'CE4306F2-8CC0-41DF-A971-1ED88AC208C7',// attention tout est en majuscules
                'RRULE' => $input->recurrenceType . ';' . 'UNTIL=' . (new \DateTime($endrecurrence))->format('Ymd\THis'),
                'SEQUENCE' => '0',
                'LOCATION' => $location,
                'TRANSP' => 'OPAQUE',
                'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
            ];

            // this part allows to create a resource without being in collision on another one
            if ($calendarBackend->isCalendarResource($calIDs)
                and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                    $calIDs, $start, $end, $eventID, $input->recurrenceType, $endrecurrence)) {

                return ["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")];
            }
            // end of collision test

        } else {
            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'UID' => $uuid,
                'SEQUENCE' => '0',
                'LOCATION' => $location,
                'TRANSP' => 'OPAQUE',
                'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
            ];

            // this part allows to create a resource without being in collision on another one
            if ($calendarBackend->isCalendarResource($calIDs)
                and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                    $calIDs, $start, $end, $eventID)) {

                return ["status" => "failed", "message" => _("Two resource reservations cannot be in the same time slot.")];
            }
            // end of collision test
        }

        $realVevent = $vcalendar->add('VEVENT', $vevent);

        unset($vcalendar->ORGANIZER);
        unset($vcalendar->ATTENDEE);
        if ($calendar->getGroupId() && $addGroupAttendees) {// add Attendees with sabre connection
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

        if ($alarm != _("NONE")) {
            $realVevent->add('VALARM', ['TRIGGER' => $alarm, 'DESCRIPTION' => 'Event reminder', 'ACTION' => 'DISPLAY']);
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

        if ($eventTypeID) {
            $type = EventTypesQuery::Create()
                ->findOneById($eventTypeID);
            $eventTypeName = $type->getName();
        }

        $old_event->setType($eventTypeID);
        $old_event->setText($eventNotes);
        $old_event->setTypeName($eventTypeName);
        $old_event->setInActive($eventInActive);

        $old_event->setLocation($location);
        $old_event->setCoordinates($coordinates);


        // we set the groupID to manage correctly the attendees : Historical
        $old_event->setGroupId($calendar->getGroupId());
        $old_event->setEventCalendarid($calendarId);

        // we first delete the old attendences
        $eventCountsOld = EventCountsQuery::create()->findByEvtcntEventid($old_event->getID());
        $eventCountsOld->delete();

        if (!empty($Fields)) {
            foreach ($Fields as $field) {
                $eventCount = new EventCounts;
                $eventCount->setEvtcntEventid($old_event->getID());
                $eventCount->setEvtcntCountid($field['countid']);
                $eventCount->setEvtcntCountname($field['name']);
                $eventCount->setEvtcntCountcount($field['value']);
                $eventCount->setEvtcntNotes($EventCountNotes);
                $eventCount->save();
            }
        }

        $old_event->save();

        if ($old_event->getGroupId() && $addGroupAttendees) {// add Attendees
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
                            if (SystemConfig::getBooleanValue('bCheckedAttendees')) {
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
                $_SESSION['EName'] = $EventTitle;
                $_SESSION['EDesc'] = $EventDesc;
                $_SESSION['EDate'] = (!is_null($date)) ? $date->format('Y-m-d H:i:s') : '';

                $_SESSION['EventID'] = $old_event->getID();
            }
        }

        return ["status" => "success", "res2" => $calendar->getGroupId()];
    }
}
