<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace Plugins\APIControllers;

use PluginStore\PluginPrefJitsiMeetingQuery;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\SessionUser;

spl_autoload_register(function ($className) {
    $res = str_replace(array('PluginStore', '\\'), array(__DIR__.'/../model', '/'), $className) . '.php';
    if (is_file($res)) {    
        include_once $res;
    }    
});

use PluginStore\PersonLastJitsiMeeting;
use PluginStore\PersonLastJitsiMeetingQuery;
use PluginStore\PersonJitsiMeeting;
use PluginStore\PersonJitsiMeetingQuery;


class MeetingController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function deleteAllMeetingRooms(ServerRequest $request, Response $response, array $args): Response
    {
        $personId = SessionUser::getUser()->getPersonId();

        $all_pms = PersonJitsiMeetingQuery::create()->findByPersonId($personId);

        if (!is_null($all_pms)) {
            $all_pms->delete();
        }

        return $response->withJson(['status' => "success"]);
    }

    public function selectMeetingRoom(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->roomId)) {
            $personId = SessionUser::getUser()->getPersonId();

            $lpm = PersonLastJitsiMeetingQuery::create()->findOneByPersonId($personId);

            if (is_null($lpm)) {
                $lpm = new PersonLastJitsiMeeting();
            }

            $lpm->setPersonMeetingId($input->roomId);
            $lpm->setPersonId($personId);
            $lpm->save();

            return $response->withJson($lpm->toArray());
        }

        return $response;
    }

    public function createMeetingRoom(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->roomName)) {
            $personId = SessionUser::getUser()->getPersonId();


            $pm = new PersonJitsiMeeting();
            $pm->setCode(basename($input->roomName));
            $pm->setPersonId($personId);

            $date = new \DateTime('now');
            $pm->setCreationDate($date->format('Y-m-d h:m'));
            $pm->save();

            $lpm = PersonLastJitsiMeetingQuery::create()->findOneByPersonId($personId);

            if (is_null($lpm)) {
                $lpm = new PersonLastJitsiMeeting();
            }

            $lpm->setPersonMeetingId($pm->getId());
            $lpm->setPersonId($personId);
            $lpm->save();

            return $response->withJson($pm->toArray());
        }

        return $response;
    }

    public function getLastMeeting(ServerRequest $request, Response $response, array $args): Response
    {
        $personId = SessionUser::getUser()->getPersonId();

        $lpm = PersonLastJitsiMeetingQuery::create()->findOneByPersonId($personId);

        if (!is_null($lpm)) {
            $pm = PersonJitsiMeetingQuery::create()->findOneById($lpm->getPersonJitsiMeetingId());
            return $response->withJson($pm->toArray());
        } else {
            return $response;
        }
    }

    public function getAllMettings(ServerRequest $request, Response $response, array $args): Response
    {
        $personId = SessionUser::getUser()->getPersonId();

        $meetings = PersonJitsiMeetingQuery::create()
            ->findByPersonId($personId);

        return $response->withJson(['PersonJitsiMeetings' => $meetings->toArray()]);
    }


    public function changeSettings(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->domain) && isset($input->domainscriptpath)) {
            $setting = PluginPrefJitsiMeetingQuery::create()->findOne();

            $setting->setDomain($input->domain);
            $setting->setDomainScriptPath($input->domainscriptpath);

            $setting->save();

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }
}
