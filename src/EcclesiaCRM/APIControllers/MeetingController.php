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

use EcclesiaCRM\SessionUser;

use EcclesiaCRM\PersonLastMeeting;
use EcclesiaCRM\PersonLastMeetingQuery;
use EcclesiaCRM\PersonMeetingQuery;
use EcclesiaCRM\PersonMeeting;

class MeetingController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function deleteAllMeetingRooms(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $personId = SessionUser::getUser()->getPersonId();

        $all_pms = PersonMeetingQuery::create()->findByPersonId($personId);

        if (!is_null($all_pms)) {
            $all_pms->delete();
        }

        return $response->withJson(['status' => "success"]);
    }

    public function selectMeetingRoom(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->roomId)) {
            $personId = SessionUser::getUser()->getPersonId();

            $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

            if (is_null($lpm)) {
                $lpm = new PersonLastMeeting();
            }

            $lpm->setPersonMeetingId($input->roomId);
            $lpm->setPersonId($personId);
            $lpm->save();

            return $response->withJson($lpm->toArray());
        }

        return $response;
    }

    public function createMeetingRoom(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->roomName)) {
            $personId = SessionUser::getUser()->getPersonId();


            $pm = new PersonMeeting();
            $pm->setCode(basename($input->roomName));
            $pm->setPersonId($personId);

            $date = new DateTime('now');
            $pm->setCreationDate($date->format('Y-m-d h:m'));
            $pm->save();

            $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

            if (is_null($lpm)) {
                $lpm = new PersonLastMeeting();
            }

            $lpm->setPersonMeetingId($pm->getId());
            $lpm->setPersonId($personId);
            $lpm->save();

            return $response->withJson($pm->toArray());
        }

        return $response;
    }

    public function getLastMeeting(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $personId = SessionUser::getUser()->getPersonId();

        $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

        if (!is_null($lpm)) {
            $pm = PersonMeetingQuery::create()->findOneById($lpm->getPersonMeetingId());
            return $response->withJson($pm->toArray());
        } else {
            return $response;
        }
    }

    public function getAllMettings(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $personId = SessionUser::getUser()->getPersonId();

        $meetings = PersonMeetingQuery::create()
            ->findByPersonId($personId);

        return $response->withJson(['PersonMeetings' => $meetings->toArray()]);
    }
}
