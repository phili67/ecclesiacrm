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
use EcclesiaCRM\EventCountNameQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\EventCountsQuery;

use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;
use EcclesiaCRM\Map\GroupTableMap;

use EcclesiaCRM\Map\CalendarinstancesTableMap;
use EcclesiaCRM\Map\PrincipalsTableMap;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\EventCounts;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use http\Client\Curl\User;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\MyVCalendar\VCalendarExtension;

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

    public function getEvents($start, $end, $isBirthdayActive, $isAnniversaryActive, $for_events_list = false)
    {
        $origStart = $start;
        $origEnd = $end;

        $dtOrigStart = new \DateTime($origStart);
        $dtOrigEnd = new \DateTime($origEnd);

        // get the first and the last month
        $firstMonth = $real_firstMonth = (int)$dtOrigStart->format('m') - 1;
        $endMonth = (int)$dtOrigEnd->format('m') - 1;

        $all_months = $firstMonth + 1;

        $i = 0;
        while ($firstMonth != $endMonth and $i < 13) {
            $firstMonth = ($firstMonth + 1) % 12;
            $all_months .= "," . ($firstMonth + 1);
            $i++;
        }

        $events = [];
        $startDate = date_create($start);
        $endDate = date_create($end);
        $endsNextYear = false;
        if ($endDate->format('Y') > $startDate->format('Y')) {
            $endsNextYear = true;
        }
        $firstYear = $startDate->format('Y');

        if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
            if ($isBirthdayActive) {
                $peopleWithBirthDays = PersonQuery::create()
                    ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
                    ->JoinWithFamily()
                    ->filterByBirthMonth(explode(",", $all_months))// the event aren't more than a month
                    ->find();

                foreach ($peopleWithBirthDays as $person) {
                    $year = $firstYear;
                    if ($person->getBirthMonth() == 1 && $endsNextYear) {
                        $year = $firstYear + 1;
                    }

                    $dtStart = new \DateTime($year . '-' . $person->getBirthMonth() . '-' . $person->getBirthDay());

                    $event = $this->createCalendarItemForGetEvents('birthday', '<i class="fas fa-birthday-cake"></i>',
                        $person->getFullName() . " " . $person->getAge(), $dtStart->format(\DateTimeInterface::ATOM), '', $person->getViewURI());
                    array_push($events, $event);
                }
            }

            if ($isAnniversaryActive) {
                // we search the Anniversaries
                $Anniversaries = FamilyQuery::create()
                    ->filterByDateDeactivated(null, Criteria::EQUAL) //Date Deactivated is null (active)
                    ->Where('MONTH(fam_WeddingDate) IN (' . $all_months . ')')
                    ->find();

                $curYear = date('Y');
                $curMonth = date('m');
                foreach ($Anniversaries as $anniversary) {
                    $year = $curYear;
                    if ($anniversary->getWeddingMonth() < $curMonth) {
                        $year = $year + 1;
                    }

                    $dtStart = new \DateTime($year . '-' . $anniversary->getWeddingMonth() . '-' . $anniversary->getWeddingDay());

                    $event = $this->createCalendarItemForGetEvents('anniversary', '<i class="fas fa-birthday-cake"></i>',
                        $anniversary->getName(), $dtStart->format(\DateTimeInterface::ATOM), '', $anniversary->getViewURI());
                    array_push($events, $event);
                }
            }
        }


        // new way to manage events

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        $principalBackend = new PrincipalPDO();
        // get all the calendars for the current user

        $calendars = $calendarBackend->getCalendarsForUser('principals/' . strtolower(SessionUser::getUser()->getUserName()), "displayname", false);

        // for the globas stats : v2/calendar/events/list
        // only in case of monthly view
        // for : $for_events_list
        $AVG_stats = [
            '1' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '2' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '3' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '4' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '5' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '6' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '7' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '8' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '9' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '10' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '11' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0],
            '12' => ['numAVGAtt' => 0, 'numAVG_CheckIn' => 0, 'numAVG_CheckOut' => 0]
        ];

        foreach ($calendars as $calendar) {
            $calendarName = $calendar['{DAV:}displayname'];
            $calendarColor = $calendar['{http://apple.com/ns/ical/}calendar-color'];
            $writeable = ($calendar['share-access'] == 1 || $calendar['share-access'] == 3) ? true : false;
            $calendarUri = $calendar['uri'];
            $calendarID = $calendar['id'];
            $groupID = $calendar['grpid'];

            $icon = "";

            if ($writeable) {
                $icon = '<i class="fas fa-pencil-alt"></i>';
            }

            if ($groupID > 0) {
                $icon .= '<i class="fas fa-users"></i>';
            }

            if ($calendar['share-access'] == 2 || $calendar['share-access'] == 3) {
                $icon .= '<i class="fa  fa-share"></i>';
            } else if ($calendar['share-access'] == 1 && $groupID == 0 && $calendar['cal_type'] == 1) {
                $icon .= '<i class="fas fa-user"></i>';
            }

            // we test the resources
            if ($calendar['cal_type'] == 2) {// room
                $icon .= ' <i class="fas fa-building"></i>&nbsp';
            } else if ($calendar['cal_type'] == 3) {// computer
                $icon .= ' <i class="fab fa-windows"></i>&nbsp;';
            } else if ($calendar['cal_type'] == 4) {// video
                $icon .= ' <i class="fas fa-video"></i>&nbsp;';
            }

            if ($calendar['present'] == 0 || $calendar['visible'] == 0) {// this ensure the calendars are present or not
                continue;
            }

            // we get all the events for the Cal
            $eventsForCal = $calendarBackend->getCalendarObjects($calendar['id']);

            $criteria = [0];
            if ( $for_events_list ) {
                $criteria = [0,1];
            }

            foreach ($eventsForCal as $eventForCal) {
                $evnt = EventQuery::Create()
                    ->filterByInActive($criteria)
                    ->findOneById($eventForCal['id']);

                if ($evnt != null) {
                    $calObj = $calendarBackend->getCalendarObject($calendar['id'], $eventForCal['uri']);

                    $cal_category = ($calendar['grpid'] != "0") ? 'group' : 'personal';

                    if ($calendar['share-access'] >= 2 ) {
                        $cal_type = 5;
                    } else {
                        $cal_type = $calendar['cal_type'];
                    }

                    $freqEvents = VObjectExtract::calendarData($calObj['calendardata'], $origStart, $origEnd);

                    if ($freqEvents == null) {
                        continue;
                    }

                    // search the organizer of the even
                     if ( !is_null($evnt->getCreatorUserId()) ) {
                        $user = UserQuery::create()->findOneByPersonId($evnt->getCreatorUserId());
                         $organizer = $user->getPerson()->getFullName()."<br>".$user->getPerson()->getEmail();
                     } else {
                         if (is_null($freqEvents['organiser'])) {
                             $username = str_replace("principals/", "", $evnt->getLogin());
                             $user = UserQuery::create()->findOneByUserName($username);

                             $organizer = $user->getPerson()->getFullName()."<br>".$user->getPerson()->getEmail();
                         } else {
                             $organizer = $freqEvents['organiser'];
                         }
                     }

                    $title = $evnt->getTitle();
                    $desc = $evnt->getDesc();
                    $allDay = $evnt->getAllday();
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
                    $link = $evnt->getLink();
                    $eventTypeName = $evnt->getEventTypeName();
                    $eventGroupName = $evnt->getGroupName();
                    $eventCalendarName = $evnt->getCalendarName();
                    $loginName = $evnt->getLogin();
                    $status = ($evnt->getInactive() != 0)? _('No') : _('Yes');
                    $calendarType = $evnt->getCalendarType();// 1 : normal; 2: room; etc ...
                    $attentees = $freqEvents['attentees'];

                    if (!(SessionUser::getUser()->isAdmin())) {
                        $eventRights = ($evnt->getRights() == 1 || $evnt->getRights() == 3) ? true : false;
                    } else {
                        $eventRights = true;
                    }

                    $fEvnt = false;
                    $subid = 1;

                    // stats for each month
                    $month = $evnt->getStart()->format('m');
                    $freeStats = [];
                    $realStats = [];


                    // only for v2/calendar/events/list
                    if ($for_events_list) {

                        $attendees = EventAttendQuery::create()
                            ->findByEventId($evnt->getId());

                        if (!is_null($attendees)) {
                            $realStats['attNumRows'] = $attendees->count();

                            $attendees1 = EventAttendQuery::create()
                                ->filterByCheckoutDate(NULL, Criteria::NOT_EQUAL)
                                ->findByEventId($evnt->getId());

                            if (!is_null($realStats)) {
                                $realStats['attCheckOut'] = $attendees1->count();
                            }

                            $attendees2 = EventAttendQuery::create()
                                ->filterByCheckoutId(NULL, Criteria::NOT_EQUAL)
                                ->findByEventId($evnt->getId());

                            if (!is_null($realStats)) {
                                $realStats['realAttCheckOut'] = $attendees2->count();
                            }

                            if (is_array($AVG_stats[$month]) && array_key_exists('numAVG_CheckIn', $AVG_stats[$month])) {
                                $AVG_stats[$month]['numAVG_CheckIn'] += $realStats['attNumRows'];
                            } else {
                                $AVG_stats[$month]['numAVG_CheckIn'] = 0;
                            }

                            if (is_array($AVG_stats[$month]) &&  array_key_exists('numAVG_CheckOut', $AVG_stats[$month])) {
                                $AVG_stats[$month]['numAVG_CheckOut'] += $realStats['attCheckOut'];
                            } else {
                                $AVG_stats[$month]['numAVG_CheckOut'] = 0;
                            }
                        }

                        if ($realStats['attNumRows']) {
                            $AVG_stats[$month]['numAVGAtt']++;
                        }

                        // RETRIEVE THE list of counts associated with the current event
                        // Free Attendance Counts without Attendees

                        $eventCounts = EventCountsQuery::Create()
                            ->filterByEvtcntEventid($evnt->getId())
                            ->orderByEvtcntCountid(Criteria::ASC)
                            ->find();

                        // the count is is inside the count of elements of $freeStats
                        //$aNumCounts = $eventCounts->count();

                        if ( $eventCounts->count() == 0) {
                            $eventCountNames = EventCountNameQuery::Create()
                                ->leftJoinEventTypes()
                                ->Where('type_id=' . $evnt->getType())
                                ->find();

                            foreach ($eventCountNames as $eventCountName) {
                                $eventCount = EventCountsQuery::Create()
                                    ->filterByEvtcntEventid($evnt->getId())
                                    ->findOneByEvtcntCountid($eventCountName->getId());

                                if (is_null($eventCount)) {
                                    $eventCount = new EventCounts;
                                    $eventCount->setEvtcntEventid($evnt->getId());
                                    $eventCount->setEvtcntCountid($eventCountName->getId());
                                    $eventCount->setEvtcntCountname($eventCountName->getName());
                                    $eventCount->setEvtcntCountcount(0);
                                    $eventCount->setEvtcntNotes("");
                                    $eventCount->save();
                                }
                            }

                            $eventCounts = EventCountsQuery::Create()
                                ->filterByEvtcntEventid($evnt->getId())
                                ->orderByEvtcntCountid(Criteria::ASC)
                                ->find();
                        }

                        foreach ($eventCounts as $eventCount) {
                            $freeStats[] = [
                                'cCountID' => $eventCount->getEvtcntCountid(),
                                'cCountName' => $eventCount->getEvtcntCountname(),
                                'cCount' => $eventCount->getEvtcntCountcount(),
                                'cCountNotes' => $eventCount->getEvtcntNotes()
                            ];
                        }

                    }

                    $evntType = EventTypesQuery::create()->findOneById($type);

                    if ( !is_null($evntType) ) {
                        $color = $evntType->getColor();

                        if (!is_null($color) and $color != '#000000') {
                            $calendarColor = $color;
                        }
                    }

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
                                        $eventGroupName, $eventCalendarName, $eventRights, $loginName,
                                        $realStats, $freeStats, $status, $link, $allDay);// only the event id sould be edited and moved and have custom color

                                    array_push($events, $event);
                                }
                            }
                        }
                    }

                    if ($fEvnt == false) {

                        $dtStart = new \DateTime($start);
                        $dtEnd = new \DateTime($end);


                        if ($dtOrigStart <= $dtStart and $dtStart <= $dtOrigEnd
                            and $dtOrigStart <= $dtEnd and $dtEnd <= $dtOrigEnd and $for_events_list) {// this code slow down the calendar

                            $event = $this->createCalendarItemForGetEvents('event', $icon,
                                $title, $start, $end,
                                '', $id, $type, $grpID,
                                $desc, $text, $calID, $calendarColor, 0, 0, 0, $rrule, $freq,
                                $writeable, $loc, $lat, $long, $alarm, $cal_type, $cal_category,
                                $eventTypeName, $eventGroupName, $eventCalendarName, $eventRights, $loginName,
                                $realStats, $freeStats, $status, $link, $allDay, $organizer, $attentees, $calendarType);// only the event id sould be edited and moved and have custom color

                            array_push($events, $event);
                        } elseif ($for_events_list == false) {
                            $event = $this->createCalendarItemForGetEvents('event', $icon,
                                $title, $start, $end,
                                '', $id, $type, $grpID,
                                $desc, $text, $calID, $calendarColor, 0, 0, 0, $rrule, $freq,
                                $writeable, $loc, $lat, $long, $alarm, $cal_type, $cal_category,
                                $eventTypeName, $eventGroupName, $eventCalendarName, $eventRights, $loginName,
                                $realStats, $freeStats, $status, $link, $allDay, $organizer, $attentees, $calendarType);// only the event id sould be edited and moved and have custom color

                            array_push($events, $event);
                        }
                    }
                }
            }
        }

        return ['EventsListResults' => $events, 'AVG_stats' => $AVG_stats];
    }

    public function createCalendarItemForGetEvents($type, $icon, $title, $start, $end, $uri, $eventID = 0, $eventTypeID = 0, $groupID = 0, $desc = "", $text = "",
                                                   $calendarid = null, $backgroundColor = null, $subid = 0,
                                                   $recurrent = 0, $reccurenceID = '', $rrule = '', $freq = '',
                                                   $writeable = false, $location = "", $latitude = 0, $longitude = 0, $alarm = "", $cal_type = "0",
                                                   $cal_category = "personal", $eventTypeName = "all", $eventGroupName = "None", $eventCalendarName = "None",
                                                   $eventRights = false, $loginName = "", $realStats = [], $freeStats = [], $status='no', $link = null, $allDay = false,
                                                   $organizer = null, $attentees = null, $calendarType = 1)
    {
        $event = [];
        switch ($type) {
            case 'birthday':
                $event['backgroundColor'] = '#dd4b39';
                $allDay = true;
                break;
            case 'anniversary':
                $event['backgroundColor'] = '#3c8dbc';
                $allDay = true;
                break;
            default:
                $event['backgroundColor'] = '#eeeeee';
        }

        $event['title'] = $event['title_desc'] = $title;

        if ( !empty($desc) ) {
            $event['title_desc'] .= "<br/>(" . $desc  . ")";
        }

        $event['title_full'] =

        $event['start'] = $start;
        $event['start_name'] = (new \DateTime($start))->format(SystemConfig::getValue('sDateFormatLong') . ' H:i');
        $event['origStart'] = $start;
        $event['organizer'] = (!is_null($organizer)? str_replace('mailto:','', $organizer):'');
        $event['attentees'] = $attentees;

        $event['month'] = (int)explode('-', $start)[1];

        $datefmt = new \IntlDateFormatter(SystemConfig::getValue('sLanguage'), NULL, NULL, NULL, NULL, 'MMMM');
        $event['month_name'] = MiscUtils::mb_ucfirst($datefmt->format(\DateTime::createFromFormat('!m', $event['month'])));



        if ( !is_null($link) ) {
            $icon .= ' <i class="fas fa-link"></i>';
        }

        $event['icon'] = $icon;

        if ( is_array($calendarid) ) {
            $calendarid = implode(",",$calendarid);
        }

        $event['icon_full'] = '<table class="table-responsive outer" style="width:120px">'.
        '                <tbody><tr class="no-background-theme">'.
        '                  <td style="width:100px;padding: 7px 2px;border:none;text-align: center">'.
        '                     <div class="btn-group" role="group" aria-label="Basic example">'.
        '                       <button type="submit"  name="Action" data-link="' . $link . '" data-id="' . $eventID .  '" title="' . _('Edit') . '" style="color:' . (($eventRights != "")?'blue':'gray') . '" class="EditEvent btn btn-default btn-xs" ' . (($eventRights)?'':'disabled') . '>' .
            $icon .
        '                        </button>'.
        '                      <button type="submit" name="Action" data-dateStart="' . $start . '" data-reccurenceid="' . $reccurenceID . '" data-recurrent="' . $recurrent . '" data-calendarid="' . $calendarid . '" data-id="' . $eventID . '" title="' . _('Delete') . '"  style="color:' . (($eventRights != "")?'red':'gray') . '" class="DeleteEvent btn btn-default btn-xs" ' . (($eventRights)?'':'disabled') . '>'.
        '                        <i class="fas fa-trash-alt"></i>'.
        '                      </button>'.
        '                      <button type="submit" name="Action" data-id="' . $eventID . '" title="' . _('Info') . '" style="color:' . (($text != "" && $eventRights)?'green':'gray') . '" class="EventInfo btn btn-default btn-xs" ' . (($text != "")?'':'disabled') . '>'.
        '                        <i class="far fa-file"></i>'.
        '                      </button>'.
        '                    </div>'.
        '                  </td>' .
        '                </tr>' .
        '              </tbody></table>';

        $event['realType'] = $event['type'] = $type;
        $event['TypeName'] = $eventTypeName;
        $event['GroupName'] = $eventGroupName;
        $event['CalendarName'] = $eventCalendarName;
        $event['calendarType'] = $calendarType; // 1 : normal; 2: room;  3 : video
        $event['Rights'] = $eventRights;
        $event['Link'] = $link;


        if ($status == _('No')) {
            $event['Status'] = '<span style="color:red;text-align:center">'.$status.'</span>';
        } else {
            $event['Status'] = '<span style="color:green;text-align:center">'.$status.'</span>';
        }

        // only for v2/calendar/events/list
        $event['RealStats'] = '';

        if (!empty($realStats)) {
            $ret = '';

            if ($realStats['attNumRows']) {
                $ret = '<table width="100%" class="outer" align="center" style="font-size: 10px;padding: 0px;border-spacing: 0px;">'
                    . '<tr class="no-background-theme">'
                    . '   <td style="padding: 7px 2px;border:none;"><b>' . _("Check-in") . '</b></td>'
                    . '   <td style="padding: 7px 2px;border:none;"><b>' . _("Check-out") . '</b></td>'
                    . '   <td style="padding: 7px 2px;border:none;" ><b>' . _("Rest") . '</b></td>'
                    . '</tr>'
                    . '<tr class="no-background-theme">'
                    . '  <td style="padding: 7px 2px;border:none;" id="allEventAttendees-' .$eventID. '">' . $realStats['attNumRows'] . '</td>'
                    . '  <td style="padding: 7px 2px;border:none;" id="checkoutEventAttendees-' .$eventID. '">' . $realStats['attCheckOut'] . '</td>'
                    . '  <td style="padding: 7px 2px;border:none;" id="differenceEventAttendees-' .$eventID. '">' . ($realStats['attNumRows'] - $realStats['attCheckOut']) . '</td>'
                    . '</tr>'
                    . '<tr class="no-background-theme">'
                    . '    <td colspan="3" style="padding: 7px 0;border:none;">'
                    . '        <table style="width:330px" class="outer">'
                    . '            <tr class="no-background-theme">'
                    . '                <td style="padding: 7px 0;border:none;">';

                if ($eventRights) {
                    $ret .= '<form name="EditAttendees" action="' . SystemURLs::getRootPath() . '/v2/calendar/events/Attendees/Edit" method="POST">';
                }

                $ret .= '         <input type="hidden" name="EID" value="' . $eventID . '">'
                    . '       <input type="hidden" name="EName" value="' . $title . '">'
                    . '       <input type="hidden" name="EDesc" value="' . $desc . '">'
                    . '       <input type="hidden" name="EDate" value="' . OutputUtils::FormatDate($start, 1) . '">'
                    . '       <input type="submit" name="Action" value="' . _('Attendees') . '(' . $realStats['attNumRows'] . ')' . '" class="btn btn-info btn-xs ' . (!($eventRights) ? "disabled" : "") . '" >';

                if ($eventRights) {
                    $ret .= ' </form>';
                }

                $ret .= '           </td>'
                    . '           <td style="padding: 7px 0;border:none;">';


                if ($eventRights) {
                    $ret .= '                       <button data-id="'.$eventID .'" title="' . _('Make Check-out') . '" data-tooltip value="' . _('Make Check-out') . '" class="btn btn-' . (($realStats['attNumRows'] - $realStats['realAttCheckOut'] > 0) ? "danger" : "success") . ' btn-xs checkout-event checkout-button-'.$eventID .'" >'
                        . '                                 <i class="fas fa-check-circle"></i> ' . (($realStats['attNumRows'] - $realStats['realAttCheckOut'] > 0) ? _("Make Check-out") : _("Check-out done"))
                        . '                         </button>';
                } else {
                    $ret .= '                       <button type="submit"  data-id="" title="' . _('Make Check-out') . '" data-tooltip value="' . _('Make Check-out') . '" class="btn btn-' . (($realStats['attNumRows'] - $realStats['realAttCheckOut'] > 0) ? "danger" : "success") . ' btn-xs" >'
                        . '                                 <i class="fas fa-check-circle"></i> ' . (($realStats['attNumRows'] - $realStats['realAttCheckOut'] > 0) ? _("Make Check-out") : _("Check-out done"))
                        . '                         </button>';
                }

                $ret .= '          </td>'
                    . '         </tr>'
                    . '     </table>'
                    . '    </td>'
                    . '</tr>'
                    . '</table>';
            } else {
                $ret .= '<form name="EditAttendees" action="' . SystemURLs::getRootPath() . '/v2/calendar/events/Attendees/Edit" method="POST">'
                    . '  <input type="hidden" name="EID" value="' . $eventID . '">'
                    . '  <input type="hidden" name="EName" value="' . $title . '">'
                    . '  <input type="hidden" name="EDesc" value="' . $desc . '">'
                    . '  <input type="hidden" name="EDate" value="'.  OutputUtils::FormatDate($start, 1) . '">'
                    //. '<span style="font-size: 12px;">' ._('No Attendance Recorded') . '</span><br>'
                    . '  <input type="submit" name="Action" value="' . _('Attendees') . '(' . $realStats['attNumRows'] . ')' . '" class="btn btn-info btn-xs" >'
                    . '</form>';

            }

            $event['RealStats'] = $ret;
        }


        // only for v2/calendar/events/list
        $event['FreeStats'] = '';

        if ( !empty($freeStats) ) {
            $ret = '<table width="100%" class="table-simple-padding outer" style="font-size: 10px;padding: 0px;border-spacing: 0px;">'
                . '<tr class="no-background-theme">';

            if ( !empty($freeStats) ) {
                foreach ($freeStats as $freeStat) {
                    $ret .= '<td style="padding: 7px 2px;border:none;">'
                        . '    <div class="text-bold">' . $freeStat['cCountName'] . '</div>'
                        . '    <div>' . $freeStat['cCount'] . '</div>'
                        . '</td>';
                }
            } else {
                $ret .= '<td style="padding: 7px 2px;border:none;">'
                    . '<p class="text-center">'
                    . _('No Attendance Recorded')
                    . '</p>'
                    . '</td>';
            }

            $ret .= '</tr>'
                .'</table>';

            $event['FreeStats'] = $ret;
        }

        // end of : for v2/calendar/events/list only

        if (SessionUser::getUser()->isAdmin()) {
            $event['Login'] = _("login") . " : <b>" . str_replace("principals/", "", $loginName) . "</b>";
        } else {
            $event['Login'] = "";
        }

        $event['end'] = $end;
        $event['end_name'] = (new \DateTime($end))->format(SystemConfig::getValue('sDateFormatLong') . ' H:i');
        $event['allDay'] = $allDay;
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
                                           $EventCountNotes, $allDay = false)
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
        $event->setAllday((is_null($allDay) or $allDay == false)?0:1);

        //if ($isCalendarResource) {
            $event->setCreatorUserId(SessionUser::getId());
        //}

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

        return $event->getId();
    }

    public function removeEventFromCalendar($calendarID, $eventID, $reccurenceID = null)
    {
        // new way to manage events
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        if (is_string($calendarID)) {
            $calendarID = explode(",", $calendarID);
        }
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
                                            $eventNotes, $eventInActive, $Fields, $EventCountNotes, $recurrenceValid, $recurrenceType,
                                            $endrecurrence, $allDay)
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

        if (isset($reccurenceID) && $reccurenceID != '') {// we're in a recursive event

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
                        $eventInActive, $Fields, $EventCountNotes, $allDay
                    );

                    return ["status" => "success"];
                } else {
                    $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);

                    // now we add the new event
                    $this->createEventForCalendar(
                        $calendarID, $start, $end,
                        $recurrenceType, $endrecurrence, $EventDesc, $EventTitle, $location,
                        $recurrenceValid, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                        $eventInActive, $Fields, $EventCountNotes, $allDay
                    );
                    return ["status" => "success"];
                }
            } catch (\Exception $e) {
                // in this case we change only the date
                $vcalendar->VEVENT->{'LAST-MODIFIED'} = (new \DateTime('Now'))->format('Ymd\THis');

                $calendarBackend->updateCalendarObject($oldCalendarID, $event['uri'], $vcalendar->serialize());

                // now we add the new event
                $this->createEventForCalendar(
                    $calendarID, $start, $end,
                    "", "", $EventDesc, $EventTitle, $location,
                    false, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                    $eventInActive, $Fields, $EventCountNotes, $allDay
                );
            }
        } /*else { // bug whith an old recursive calendar event
            // We have to use the sabre way to ensure the event is reflected in external connection : CalDav
            $calendarBackend->deleteCalendarObject($oldCalendarID, $event['uri']);

            // now we add the new event
            $this->createEventForCalendar(
                $calendarID, $start, $end,
                $recurrenceType, $endrecurrence, $EventDesc, $EventTitle, $location,
                $recurrenceValid, $addGroupAttendees, $alarm, $eventTypeID, $eventNotes,
                $eventInActive, $Fields, $EventCountNotes, $allDay
            );

            return ["status" => "success"];
        }*/

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
        if (!empty($recurrenceValid)) {
            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'UID' => $uuid,//'CE4306F2-8CC0-41DF-A971-1ED88AC208C7',// attention tout est en majuscules
                'RRULE' => $recurrenceType . ';' . 'UNTIL=' . (new \DateTime($endrecurrence))->format('Ymd\THis'),
                'SEQUENCE' => '0',
                'LOCATION' => $location,
                'TRANSP' => 'OPAQUE',
                'X-APPLE-TRAVEL-ADVISORY-BEHAVIOR' => 'AUTOMATIC',
                "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-RADIUS=49.91307587029686;X-TITLE=\"" . $location . "\"" => "geo:" . $coordinates
            ];

            // this part allows to create a resource without being in collision on another one
            if ($calendarBackend->isCalendarResource($calIDs)
                and $calendarBackend->checkIfEventIsInResourceSlotCalendar(
                    $calIDs, $start, $end, $eventID, $recurrenceType, $endrecurrence)) {

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
        $old_event->setAllday((is_null($allDay) or $allDay == false)?0:1);

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
