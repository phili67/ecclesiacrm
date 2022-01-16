<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\CalendarinstancesQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Emails\CalendarEmail;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PrincipalsQuery;


use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\DAV\PropPatch;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;

class CalendarV2Controller
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getallCalendarEvents (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        $CalendarService = $this->container->get('CalendarService');

        return $response->withJson($CalendarService->getEvents($params->start, $params->end, $params->isBirthdayActive, $params->isAnniversaryActive));;
    }

    public function getallCalendarEventsForEventsList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        $CalendarService = $this->container->get('CalendarService');

        return $response->withJson(['EventsListResults' => $CalendarService->getEvents($params->start, $params->end, $params->isBirthdayActive, $params->isAnniversaryActive)]);;
    }

    public function numberOfCalendars (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        // we get the PDO for the Sabre connection from the Propel connection
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        $principalBackend = new PrincipalPDO();

        // get all the calendars for the current user present or not
        $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower(SessionUser::getUser()->getUserName()),"displayname",true);

        $return = [];

        foreach ($calendars as $calendar) {
            $values['calendarName']       = $calendar['{DAV:}displayname'];
            $values['calendarColor']      = $calendar['{http://apple.com/ns/ical/}calendar-color'];
            $values['calendarShareAccess']= $calendar['share-access'];
            $values['calendarUri']        = $calendar['uri'];

            $id                           = $calendar['id'];
            $values['calendarID']         = $id[0].",".$id[1];;
            $values['visible']            = ($calendar['visible'] == "1")?true:false;
            //$values['present']            = $calendar['present'];
            $values['type']               = ($calendar['grpid'] != "0")?'group':'personal';
            if ($values['calendarShareAccess'] >= 2) {
                $values['type']               = 'share';
            }

            $values['grpid']               = $calendar['grpid'];

            if ( $calendar['present'] && $calendar['visible'] ) {
                array_push($return, $values);
            }
        }


        return $response->withJson(["CalendarNumber" => count($return)]);
    }

    public function showHideCalendars (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset($params->isPresent) ) {

            $calIDs = explode(",",$params->calIDs);

            $calendarId = $calIDs[0];
            $Id = $calIDs[1];

            $calendar = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneById($Id);

            $calendar->setPresent ($params->isPresent);

            $calendar->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function setCalendarDescriptionType (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        $return = [];

        if ( isset ($params->calIDs) && isset ($params->desc) && isset ($params->type) && SessionUser::getUser()->isAdmin() ) { // only an admin can change the calendarinstance description
            $calIDs = explode(",",$params->calIDs);

            $calendarInstance = CalendarinstancesQuery::Create()->findOneById( $calIDs[1] );

            $calendarInstance->setDescription($params->desc);
            $calendarInstance->setType($params->type);

            $calendarInstance->save();

            // we'll connect to sabre
            /*
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $calendarInstance = CalendarinstancesQuery::Create()->findOneByCalendarid( $calIDs[0] );

            // Updating the calendar
            $propPatch = new PropPatch([
              '{DAV:}description' => $params->desc
            ]);

            $calendarBackend->updateCalendar($calIDs, $propPatch);

            $result = $propPatch->commit();*/

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function getAllCalendarsForUser (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        $return = [];

        if ( isset ($params->type) && isset($params->onlyvisible) && isset($params->allCalendars) ) {
            // new way to manage events
            // we get the PDO for the Sabre connection from the Propel connection
            // We set the BackEnd for sabre Backends
            $calendarBackend  = new CalDavPDO();

            // get all the calendars for the current user
            $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower(SessionUser::getUser()->getUserName()),($params->type == 'all')?true:false);

            foreach ($calendars as $calendar) {
                $values['calendarName']       = $calendar['{DAV:}displayname'];
                $values['calendarColor']      = $calendar['{http://apple.com/ns/ical/}calendar-color'];
                $values['calendarShareAccess']= $calendar['share-access'];
                $values['calendarUri']        = $calendar['uri'];
                $values['icon']               = ($calendar['share-access'] == 1 || $calendar['share-access'] == 3)?'&nbsp;<i class="fa fa-pencil"></i>&nbsp;':'&nbsp;<i class="fa fa-eye"></i>&nbsp;';

                $id                           = $calendar['id'];
                $values['calendarID']         = $id[0].",".$id[1];
                $values['present']            = ($calendar['present'] == "1")?true:false;
                $values['visible']            = ($calendar['visible'] == "1")?true:false;
                $values['type']               = ($calendar['grpid'] > 0)?'group':'personal';
                $values['grpid']              = $calendar['grpid'];
                $values['calType']            = $calendar['cal_type'];
                $values['desc']               = ($calendar['description'] == null)?_("None"):$calendar['description'];
                $values['isAdmin']            = SessionUser::getUser()->isAdmin();

                if ($calendar['cal_type'] > 1) {
                    $values['type'] = 'reservation';
                }

                if ($values['calendarShareAccess'] >= 2 && $values['grpid'] == 0 && $calendar['cal_type'] == 1) {
                    $values['type'] = 'share';
                }

                if (
                    (
                        ($params->onlyvisible == true && $calendar['visible'] && $calendar['present'] ) // when a calendar is only visible
                        || $params->onlyvisible == false  && $calendar['present']
                        || $params->allCalendars
                    )
                    && ($params->type == $values['type'] || $params->type == 'all')
                )
                {
                    array_push($return, $values);
                }
            }
        }

        return $response->withJson($return);
    }

    public function calendarInfo (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset ($params->type) ) {

            $calIDs = explode(",",$params->calIDs);

            $calendarId = $calIDs[0];
            $Id = $calIDs[1];

            $calendar    = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneByAccess(1); // we search the owner of this calendar
            $calendarCU  = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneById($Id);   // current user calendar

            //$principal   = PrincipalsQuery::create()->findOneByDisplayname (str_replace("principals/","",$calendar->getPrincipaluri()));

            $user = UserQuery::Create()->findOneByUserName (str_replace("principals/","",$calendar->getPrincipaluri()));

            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

            $root = '/';

            if ( !empty(SystemURLs::getRootPath()) ) {
                $root = SystemURLs::getRootPath()."/";
            }

            $message = "";

            if ( $params->type != "personal" ) {
                $message.= "<p><label>"._("Owner")."</label> : ".$user->getPerson()->getFullName()."</p>";

                if ( $calendarCU->getAccess() == 3 ) {
                    $message.= "<p><label>"._("Access")."</label> : "._("Full access in Read and write for all the events of this calendar.")."</p>";
                } else {
                    $message.= "<p><label>"._("Access")."</label> : "._("You can only read the events of this calendar.")."</p>";
                }
            }

            $message .= "<p><label>"._("This address can be used only with a CalDav server.")." "._("For thunderbird the URL is")." : </label><br>".$protocol."://".$_SERVER['HTTP_HOST'].$root."calendarserver.php/calendars/".strtolower(str_replace("principals/","",$calendar->getPrincipaluri()))."/".$calendar->getUri()."/<p>";
            $message .= "<p><label>"._("For a share calendar (only in read mode)")." : </label><br>".$protocol."://".$_SERVER['HTTP_HOST'].$root."external/calendar/events/".strtolower(str_replace("principals/","",$calendar->getPrincipaluri()))."/".$calendar->getUri()."<p>";
            if (SessionUser::getUser()->isAdmin()) {
                $message .= "<p><label>"._("You've to activate the \"bEnableExternalCalendarAPI\" setting in")." <a href=\"".$root."SystemSettings.php\">"._("General Settings/Integration")."</a>.";
            }

            $title = $calendar->getDisplayname();

            $isAdmin = (SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())?true:false;

            return $response->withJson(["status" => "success","title"=> $title, "message" => $message, "isAdmin" => $isAdmin, "access" => $calendarCU->getAccess()/*, "URI" => $calendar->getPrincipaluri(), "Owner" => $user->getPerson()->getFullName()*/]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function setCalendarColor (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if (isset ($params->calIDs) && isset ($params->color)) {

            $calIDs = explode(",",$params->calIDs);
            $color = $params->color;

            // new way to manage events
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            // get all the calendars for the current user

            $return = [];

            $propPatch = new PropPatch([
                '{http://apple.com/ns/ical/}calendar-color' => $color
            ]);

            // Updating the calendar
            $calendarBackend->updateCalendar($calIDs, $propPatch);

            $result = $propPatch->commit();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function setCheckedCalendar (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if (isset ($params->calIDs) && isset ($params->isChecked)) {

            $calIDs = explode(",",$params->calIDs);

            $calendarId = $calIDs[0];
            $Id = $calIDs[1];

            $calendar = CalendarinstancesQuery::Create()->filterByCalendarid($calendarId)->findOneById($Id);

            $calendar->setVisible ($params->isChecked);

            $calendar->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function newCalendar (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->title) ) {
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            // we create the uuid name
            $uuid = strtoupper( \Sabre\DAV\UUIDUtil::getUUID() );

            // get all the calendars for the current user

            $returnID = $calendarBackend->createCalendar('principals/'.strtolower(SessionUser::getUser()->getUserName()), $uuid, [
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
                '{DAV:}displayname'                                               => $params->title,
                '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),
            ]);

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function newCalendarReservation (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->title) && isset ($params->type) && isset ($params->desc) ) {
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            // we create the uuid name
            $uuid = strtoupper( \Sabre\DAV\UUIDUtil::getUUID() );

            // get all the calendars for the current user

            $returnID = $calendarBackend->createCalendar('principals/'.strtolower(SessionUser::getUser()->getUserName()), $uuid, [
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
                '{DAV:}displayname'                                               => $params->title,
                '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),
            ],$params->type,$params->desc);

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function modifyCalendarName (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->title) && isset ($params->calIDs) ) {

            $calIDs = explode(",",$params->calIDs);

            // we check if it isn't a calendar
            $calendarInstance = CalendarinstancesQuery::Create()->findOneByCalendarid( $calIDs[0] );

            if ( $calendarInstance != null && $calendarInstance->getGroupId() != 0 ) {// we are in a group calendar
                $group = GroupQuery::Create()->findOneById($calendarInstance->getGroupId());
                $group->setName($params->title);
                $group->save();
            } else {

                // We set the BackEnd for sabre Backends
                $calendarBackend = new CalDavPDO();

                // Updating the calendar
                $propPatch = new PropPatch([
                    '{DAV:}displayname'                                       => $params->title
                ]);

                $calendarBackend->updateCalendar($calIDs, $propPatch);

                $result = $propPatch->commit();

            }

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function getCalendarInvites (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) ) {

            $calendarId = explode(",",$params->calIDs);

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $result = $calendarBackend->getInvites($calendarId);

            return $response->withJson($result);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function shareCalendarDelete (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset ($params->principal) ) {

            $calendarId = explode(",",$params->calIDs);

            // we'll connect to sabre
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $shares = $calendarBackend->getInvites($calendarId);

            foreach ($shares as $share) {
                if ($share->principal == $params->principal) {
                    $share->access = DAV\Sharing\Plugin::ACCESS_NOACCESS;
                }
            }

            $calendarBackend->updateInvites($calendarId,$shares);

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);

    }

    public function shareCalendarPerson (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset ($params->personID) && isset ($params->notification) ) {
            $user = UserQuery::Create()->findOneByPersonId ($params->personID);

            if (!empty($user)) {

                $calendarId = explode(",",$params->calIDs);

                // We set the BackEnd for sabre Backends
                $calendarBackend = new CalDavPDO();

                // Add a new invite
                $calendarBackend->updateInvites(
                    $calendarId,
                    [
                        new Sharee([
                            'href'         => 'mailto:'.$user->getEmail(),
                            'principal'    => 'principals/'.strtolower( $user->getUserName() ),
                            'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READ,//ACCESS_READWRITE,
                            'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                            'properties'   => ['{DAV:}displayname' => strtolower( $user->getFullName() )],
                        ])
                    ]
                );

                if ($params->notification) {
                    $email = new CalendarEmail($user, _("You can visualize it in your account, in the Calendar."));
                    $email->send();
                }

                $result = $calendarBackend->getInvites($calendarId);

                return $response->withJson($result);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function shareCalendarFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset ($params->familyID) && isset ($params->notification) ) {

            $calendarId = explode(",",$params->calIDs);

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();


            $fam = FamilyQuery::Create()->findOneById ($params->familyID);
            $persons = $fam->getActivatedPeople();

            $res_global = "";

            foreach ($persons as $person) {
                $user = UserQuery::Create()->findOneByPersonId ($person->getId());

                if (!empty($user)) {

                    // Add a new invite
                    $calendarBackend->updateInvites(
                        $calendarId,
                        [
                            new Sharee([
                                'href'         => 'mailto:'.$user->getEmail(),
                                'principal'    => 'principals/'.strtolower( $user->getUserName() ),
                                'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READ,//ACCESS_READWRITE,
                                'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                                'properties'   => ['{DAV:}displayname' => strtolower( $user->getFullName() )],
                            ])
                        ]
                    );

                }

                if ($params->notification) {
                    $email = new CalendarEmail($user, _("You can visualize it in your account, in the Calendar."));
                    $email->send();
                }

                $result = $calendarBackend->getInvites($calendarId);

                $res_global .= $result." ";

            }

            return $response->withJson($res_global);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function shareCalendarGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset ($params->groupID) && isset ($params->notification) ) {

            $group = GroupQuery::Create()->findOneById ($params->groupID);

            $members = Person2group2roleP2g2rQuery::create()
                ->findByGroupId($params->groupID);

            $calendarId = explode(",",$params->calIDs);

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            foreach ($members as $member) {
                $user = UserQuery::Create()->findOneByPersonId ($member->getPersonId());

                if (!empty($user)) {

                    // Add a new invite
                    $calendarBackend->updateInvites(
                        $calendarId,
                        [
                            new Sharee([
                                'href'         => 'mailto:'.$user->getEmail(),
                                'principal'    => 'principals/'.strtolower( $user->getUserName() ),
                                'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READ,//ACCESS_READWRITE,
                                'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                                'properties'   => ['{DAV:}displayname' => strtolower( $user->getFullName() )],
                            ])
                        ]
                    );

                    if ($params->notification) {
                        $email = new CalendarEmail($user, _("You can visualize it in your account, in the Calendar."));
                        $email->send();
                    }

                }

            }

            $result = $calendarBackend->getInvites($calendarId);

            return $response->withJson($result);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function shareCalendarStop (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) ) {

            $calendarId = explode(",",$params->calIDs);

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $shares = $calendarBackend->getInvites($calendarId);

            foreach ($shares as $share) {
                if ($share->access != 1) {
                    $share->access = DAV\Sharing\Plugin::ACCESS_NOACCESS;
                }
            }

            $calendarBackend->updateInvites($calendarId,$shares);

            $result = $calendarBackend->getInvites($calendarId);

            return $response->withJson($result);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function setCalendarRights (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) && isset ($params->principal) && isset ($params->rightAccess) ) {
            $calendarId = explode(",",$params->calIDs);

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $shares = $calendarBackend->getInvites($calendarId);

            foreach ($shares as $share) {
                if ($share->principal == $params->principal) {
                    if ($params->rightAccess == 1)
                        $share->access = DAV\Sharing\Plugin::ACCESS_READ;
                    elseif ($params->rightAccess == 2)
                        $share->access = DAV\Sharing\Plugin::ACCESS_READWRITE;
                }
            }

            $calendarBackend->updateInvites($calendarId,$shares);

            $result = $calendarBackend->getInvites($calendarId);

            return $response->withJson($result);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function deleteCalendar (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->calIDs) ) {
            $calendarId = explode(",",$params->calIDs);

            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO();

            $calendarBackend->deleteCalendar($calendarId);;

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }
}
