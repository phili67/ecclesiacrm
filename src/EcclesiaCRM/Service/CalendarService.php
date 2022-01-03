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
use EcclesiaCRM\MyVCalendar\VCalendarExtension;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\EventCountsQuery;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\MyPDO\VObjectExtract;
use Sabre\DAV\UUIDUtil;

class CalendarService
{
    public function getEventTypes()
    {
        $eventTypes = [];
        array_push($eventTypes, ['Name' => gettext('Event'), 'backgroundColor' =>'#f39c12']);
        array_push($eventTypes, ['Name' => gettext('Birthday'), 'backgroundColor' =>'#f56954']);
        array_push($eventTypes, ['Name' => gettext('Anniversary'), 'backgroundColor' =>'#0000ff']);
        return $eventTypes;
    }
    public function getEvents($start, $end, $isBirthdayActive, $isAnniversaryActive)
    {
        $origStart = $start;
        $origEnd   = $end;

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
                    $start = date_create($year . '-' . $person->getBirthMonth() . '-' . $person->getBirthDay());
                    $event = $this->createCalendarItemForGetEvents('birthday', '<i class="fa fa-birthday-cake"></i>',
                        $person->getFullName() . " " . $person->getAge(), $start->format(DATE_ATOM), '', $person->getViewURI());
                    array_push($events, $event);
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
                    $start = $year . '-' . $anniversary->getWeddingMonth() . '-' . $anniversary->getWeddingDay();
                    $event = $this->createCalendarItemForGetEvents('anniversary', '<i class="fa fa-birthday-cake"></i>',
                        $anniversary->getName(), $start, '', $anniversary->getViewURI());
                    array_push($events, $event);
                }
            }
        }


        // new way to manage events

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        $principalBackend = new PrincipalPDO();
        // get all the calendars for the current user

        $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower(SessionUser::getUser()->getUserName()),"displayname",false);

        foreach ($calendars as $calendar) {
          $calendarName        = $calendar['{DAV:}displayname'];
          $calendarColor       = $calendar['{http://apple.com/ns/ical/}calendar-color'];
          $writeable           = ($calendar['share-access'] == 1 || $calendar['share-access'] == 3)?true:false;
          $calendarUri         = $calendar['uri'];
          $calendarID          = $calendar['id'];
          $groupID             = $calendar['grpid'];

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
            $evnt = EventQuery::Create()->filterByInActive('false')->findOneById($eventForCal['id']);

            if ($evnt != null) {

              $calObj = $calendarBackend->getCalendarObject($calendar['id'],$eventForCal['uri']);


              $cal_category = ($calendar['grpid'] != "0")?'group':'personal';

              if ($calendar['share-access'] >= 2) {
                  $cal_type               = 5;
              } else {
                   $cal_type = $calendar['cal_type'];
              }

              $freqEvents = VObjectExtract::calendarData($calObj['calendardata'],$origStart,$origEnd);

              if ($freqEvents == null) {
                continue;
              }

              $title = $evnt->getTitle();
              $desc  = $evnt->getDesc();
              $start = $evnt->getStart('Y-m-d H:i:s');
              $end   = $evnt->getEnd('Y-m-d H:i:s');
              $id    = $evnt->getID();
              $type  = $evnt->getType();
              $grpID = $evnt->getGroupId();
              $loc   = $evnt->getLocation();
              $lat   = $evnt->getLatitude();
              $long  = $evnt->getLongitude();
              $text  = $evnt->getText();
              $calID = $calendar['id'];
              $alarm = $evnt->getAlarm();
              $rrule = $evnt->getFreqLastOccurence();
              $freq  = $evnt->getFreq();

              $fEvnt = false;
              $subid = 1;

              foreach ($freqEvents as $key => $value) {
                if ($key == 'freq' && $value != 'none') {
                  $fEvnt = true;
                } elseif ($key == 'freqEvents' && $fEvnt == true) { // we are in front of a recurrence event !!!
                  foreach ($value as $freqValue) {
                    $title          = $freqValue['SUMMARY'];
                    $start          = $freqValue['DTSTART'];
                    $end            = $freqValue['DTEND'];
                    $reccurenceID   = $freqValue['RECURRENCE-ID'];

                    $event = $this->createCalendarItemForGetEvents('event',$icon,
                      $title, $start, $end,
                     '',$id,$type,$grpID,
                      $desc,$text,$calID,$calendarColor,
                      $subid++,1,$reccurenceID,$rrule, $freq, $writeable,
                      $loc,$lat,$long,$alarm,$cal_type,$cal_category);// only the event id sould be edited and moved and have custom color

                    array_push($events, $event);
                  }
                }
              }

              if ($fEvnt == false) {
                $event = $this->createCalendarItemForGetEvents('event',$icon,
                  $title, $start, $end,
                 '',$id,$type,$grpID,
                  $desc,$text,$calID,$calendarColor,0,0,0,$rrule,$freq,
                  $writeable,$loc,$lat,$long,$alarm,$cal_type,$cal_category);// only the event id sould be edited and moved and have custom color

                array_push($events, $event);
              }
            }
          }
        }
        return $events;
    }

    public function createCalendarItemForGetEvents ($type, $icon, $title, $start, $end, $uri,$eventID=0,$eventTypeID=0,$groupID=0,$desc="",$text="",
                                       $calendarid=null,$backgroundColor = null,$subid = 0,
                                       $recurrent=0,$reccurenceID = '',$rrule = '',$freq = '',
                                       $writeable=false,$location = "",$latitude = 0,$longitude = 0,$alarm = "",$cal_type="0",
                                       $cal_category = "personal")
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

        $event['title']     = $title;
        $event['start']     = $start;
        $event['origStart'] = $start;
        $event['icon']      = $icon;
        $event['realType']  = $event['type'] = $type;

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
          $event['eventID']         = $eventID;
          $event['eventTypeID']     = $eventTypeID;
          $event['groupID']         = $groupID;
          $event['Desc']            = $desc;
          $event['Text']            = $text;
          $event['recurrent']       = $recurrent;
          $event['rrule']           = $rrule;
          $event['freq']            = $freq;
          $event['writeable']       = $writeable;
          $event['location']        = $location;
          $event['longitude']       = $longitude;
          $event['latitude']        = $latitude;
          $event['alarm']           = $alarm;
          $event['calType']         = intval($cal_type);
          $event['cal_category']    = $cal_category;

          if ($calendarid != null) {
            $event['calendarID'] = $calendarid;//[$calendarid[0],$calendarid[1]];//$calendarid;
          }

          if ($backgroundColor != null) {
            $event['backgroundColor'] = $backgroundColor;
          }

          $event['subID'] = $subid;

          $event['reccurenceID'] = '';
          if (!empty($reccurenceID) ) {
            $event['reccurenceID'] = $reccurenceID;
          }


          $eventCounts = EventCountsQuery::Create()->findByEvtcntEventid($eventID);

          $event['EventCounts'] = $eventCounts->toArray();
        }

        return $event;
    }

    public function createEventForCalendar ($calendarID, $start, $end, $recurrenceType, $endrecurrence, $EventDesc, $EventTitle, $inputlocation,
                                            $recurrenceValid, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes, $eventInActive, $Fields,
                                            $EventCountNotes)
    {
// new way to manage events
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();

        $uuid = strtoupper(UUIDUtil::getUUID());

        $vcalendar = new VCalendarExtension();

        if ( is_array( $calendarID ) ) {
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

                //
                $_SESSION['Action'] = 'Add';
                $_SESSION['EID'] = $event->getID();
                $_SESSION['EName'] = $EventTitle;
                $_SESSION['EDesc'] = $EventDesc;
                $_SESSION['EDate'] = ( !is_null($date) )?$date->format('Y-m-d H:i:s'):'';

                $_SESSION['EventID'] = $event->getID();
            }
        }

        return true;
    }
}
