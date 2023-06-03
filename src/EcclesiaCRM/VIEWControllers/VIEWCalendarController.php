<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\EventCountNameQuery;

use EcclesiaCRM\Map\EventTypesTableMap;
use EcclesiaCRM\Map\EventTableMap;

use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;


use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;


use Slim\Views\PhpRenderer;

class VIEWCalendarController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderCalendar (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        return $renderer->render($response, 'calendar.php', $this->argumentsCalendarArray());
    }

    public function argumentsCalendarArray ()
    {
        $eventTypes = EventTypesQuery::Create()
            ->orderByName()
            ->find();

        $lat = OutputUtils::number_dot(ChurchMetaData::getChurchLatitude());
        $lng = OutputUtils::number_dot(ChurchMetaData::getChurchLongitude());

        $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
        $sMapProvider   = SystemConfig::getValue('sMapProvider');
        $sGoogleMapKey  = SystemConfig::getValue('sGoogleMapKey');

        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => _('Church Calendar'),
            'eventTypes'  => $eventTypes,
            'contentsExternalCssFont' => SystemConfig::getValue("sMailChimpContentsExternalCssFont"),
            'extraFont' => SystemConfig::getValue("sMailChimpExtraFont"),
            'coordinates' => [
                'lat' => $lat,
                'lng' => $lng
            ],
            'iLittleMapZoom' => $iLittleMapZoom,
            'sGoogleMapKey'  => $sGoogleMapKey,
            'sMapProvider'   => $sMapProvider,
            'sessionUsr'     => SessionUser::getUser()
        ];

        return $paramsArguments;
    }

    public function renderCalendarEventsList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        return $renderer->render($response, 'eventslist.php', $this->argumentsCalendarEventsListArray());
    }

    public function argumentsCalendarEventsListArray ()
    {
        $eventTypes = EventTypesQuery::Create()
            ->addJoin(EventTypesTableMap::COL_TYPE_ID, EventTableMap::COL_EVENT_TYPE,Criteria::RIGHT_JOIN)
            ->setDistinct(EventTypesTableMap::COL_TYPE_ID)
            ->orderById()
            ->find();


        // year selector
        $eType = 'All';

        if ($eType == 'All') {
            $connection = Propel::getConnection();

            $aSQL = 'SELECT YEAR(events_event.event_start) as year FROM events_event GROUP BY year';

            $raOpps = $connection->prepare($aSQL);
            $raOpps->execute();

            $years = [];
            while ($aRow = $raOpps->fetch( \PDO::FETCH_ASSOC )) {
                $years[] = $aRow['year'];
            }
        }

        $yVal = date('Y');

        $lat = OutputUtils::number_dot(ChurchMetaData::getChurchLatitude());
        $lng = OutputUtils::number_dot(ChurchMetaData::getChurchLongitude());

        $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
        $sMapProvider   = SystemConfig::getValue('sMapProvider');
        $sGoogleMapKey  = SystemConfig::getValue('sGoogleMapKey');

        $sPageTitle = _('Listing All Church Events');

        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => $sPageTitle,
            'eventTypes'  => $eventTypes,
            'eType'       => $eType,
            'yVal'        => $yVal,
            'years'       => $years,
            'EventMonth'  => 0,
            'coordinates' => [
                'lat' => $lat,
                'lng' => $lng
            ],
            'iLittleMapZoom' => $iLittleMapZoom,
            'sGoogleMapKey'  => $sGoogleMapKey,
            'sMapProvider'   => $sMapProvider,
            'sessionUsr'     => SessionUser::getUser()
        ];

        return $paramsArguments;
    }

    public function renderCalendarEventAttendeesEdit (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        return $renderer->render($response, 'eventattendeesedit.php', $this->argumentsCalendarEventAttendeesEditListArray());
    }

    public function argumentsCalendarEventAttendeesEditListArray ()
    {
        if (isset($_POST['Action'])) {
            $sAction = $_POST['Action'];
            $EventID = $_POST['EID']; // from ListEvents button=Attendees
            $EvtName = $_POST['EName'];
            $EvtDesc = $_POST['EDesc'];
            $EvtDate = $_POST['EDate'];

            $_SESSION['Action'] = $sAction;
            $_SESSION['EID'] = $EventID;
            $_SESSION['EName'] = $EvtName;
            $_SESSION['EDesc'] = $EvtDesc;
            $_SESSION['EDate'] = $EvtDate;
        } else if (isset($_SESSION['Action'])) {
            $sAction = $_SESSION['Action'];
            $EventID = $_SESSION['EID'];
            $EvtName = $_SESSION['EName'];
            $EvtDesc = $_SESSION['EDesc'];
            $EvtDate = $_SESSION['EDate'];
        }

        $sPageTitle = _('Event Attendees'). ":" . $EvtName;

        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => $sPageTitle,
            'sAction'     => $sAction,
            'EventID'     => $EventID,
            'EvtName'     => $EvtName,
            'EvtDesc'     => $EvtDesc,
            'EvtDate'     => $EvtDate
        ];

        return $paramsArguments;
    }

    

    public function renderCalendarEventCheckin (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        return $renderer->render($response, 'checkin.php', $this->argumentsCalendarCheckinArray());
    }

    public function argumentsCalendarCheckinArray ()
    {
        $EventID = 0;
        $event = null;

        // for ckeditor fonts
        $contentsExternalCssFont = SystemConfig::getValue("sMailChimpContentsExternalCssFont");
        $extraFont = SystemConfig::getValue("sMailChimpExtraFont");

        if (array_key_exists('EventID', $_POST)) {
            // from ListEvents button=Attendees
            $EventID = InputUtils::FilterInt($_POST['EventID']);
            $_SESSION['EventID'] = $EventID;
        } else if (isset ($_SESSION['EventID'])) {
            // from api/routes/events.php
            $EventID = InputUtils::FilterInt($_SESSION['EventID']);
        } else {
            $Event = EventQuery::create()
                ->filterByStart(date("Y-m-d 00:00:00"), Criteria::GREATER_EQUAL)
                ->filterByEnd(date("Y-m-d 23:59:59"), Criteria::LESS_EQUAL)
                ->findOne();

            if (!is_null($Event)) {
                $_SESSION['EventID'] = $Event->getId();
                $EventID = $_SESSION['EventID'];
            }
        }

        if ($EventID > 0) {
            $event = EventQuery::Create()
                ->findOneById($EventID);

            if ($event == null) {
                $_SESSION['EventID'] = 0;
                $EventID = 0;
            } else {
                // for EditEventAttendees.php
                $_SESSION['Action'] = "EditEvent";
                $_SESSION['EID'] = $EventID;
                $_SESSION['EName'] = $event->getTitle();
                $_SESSION['EDesc'] = $event->getDesc();
                $_SESSION['EDate'] = $event->getStart()->format('YYYY-MM-DD');
            }
        }

        $bSundaySchool = false;

        if (!is_null($event) && $event->getGroupId() > 0) {
            $bSundaySchool = GroupQuery::Create()->findOneById($event->getGroupId())->isSundaySchool();
        }

        if (isset($_SESSION['CartToEventEventID'])) {
            $EventID = InputUtils::LegacyFilterInput($_SESSION['CartToEventEventID'], 'int');
        }


        //
        // process the action inputs
        //


        //Start off by first picking the event to check people in for
        $activeEvents = EventQuery::Create()
            ->filterByInActive(1, Criteria::NOT_EQUAL)
            ->Where('MONTH(event_start) = ' . date('m') . ' AND YEAR(event_start)=' . date('Y'))// We filter only the events from the current month
            ->orderByStart('desc')
            ->find();

        $searchEventInActivEvent = EventQuery::Create()
            ->filterByInActive(1, Criteria::NOT_EQUAL)
            ->Where('MONTH(event_start) = ' . date('m') . ' AND YEAR(event_start)=' . date('Y'))// We filter only the events from the current month
            ->findOneById($EventID);

        //get Event Details
        $event = EventQuery::Create()
            ->findOneById($EventID);

        if (!is_null($event)) {
            $sTitle = $event->getTitle();
            $sNoteText = $event->getText();
        }

        $eventCountNames = null;

        if ($EventID > 0) {
            $eventCountNames = EventCountNameQuery::Create()
                ->leftJoinEventTypes()
                ->Where('type_id=' . $event->getType())
                ->find();
        }

        if (!is_null($event)) {
            $sPageTitle = _('Call the Register'). " : " . $event->getTitle()." (".$event->getStart()->format(SystemConfig::getValue('sDatePickerFormat')).")";
        } else {
            $sPageTitle = _('Call the Register');
        }

        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'CSPNonce' => SystemURLs::getCSPNonce(),
            'sPageTitle'  => $sPageTitle,
            'contentsExternalCssFont' => $contentsExternalCssFont,
            'extraFont'   => $extraFont,
            'EventID'     => $EventID,
            'event'       => $event,
            'bSundaySchool' => $bSundaySchool,
            'activeEvents' => $activeEvents,
            'searchEventInActivEvent' => $searchEventInActivEvent,
            'sTitle'      => $sTitle,
            'sNoteText'   => $sNoteText,
            'eventCountNames' => $eventCountNames,
        ];

        return $paramsArguments;
    }

    

    public function renderCalendarEventNamesEdit (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'eventnames.php', $this->argumentsEventNamesArray());
    }

    public function argumentsEventNamesArray ()
    {
        $sPageTitle = _('Edit Event Types');
    

        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'CSPNonce' => SystemURLs::getCSPNonce(),
            'sPageTitle'  => $sPageTitle
        ];

        return $paramsArguments;
    }


}
