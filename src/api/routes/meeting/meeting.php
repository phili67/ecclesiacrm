<?php

/*******************************************************************************
 *
 *  filename    : meeting.php
 *  last change : 2020-07-07
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2020 Philippe Logel all right reserved not MIT licence
 *                This code can't be include in another software
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

// Routes
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\SessionUser;

use EcclesiaCRM\PersonLastMeeting;
use EcclesiaCRM\PersonLastMeetingQuery;
use EcclesiaCRM\PersonMeetingQuery;
use EcclesiaCRM\PersonMeeting;


$app->group('/meeting', function (RouteCollectorProxy $group) {
    $group->get('/', 'getAllMettings');
    $group->get('/getLastMeeting', 'getLastMeeting');
    $group->post('/createMeetingRoom', 'createMeetingRoom');
    $group->post('/selectMeetingRoom', 'selectMeetingRoom');
    $group->delete('/deleteAllMeetingRooms', 'deleteAllMeetingRooms');
});

function deleteAllMeetingRooms(Request $request, Response $response, array $args)
{
    $personId = SessionUser::getUser()->getPersonId();

    $all_pms = PersonMeetingQuery::create()->findByPersonId($personId);

    if ( !is_null ($all_pms) ) {
        $all_pms->delete();
    }

    return $response->withJson(['status' => "success"]);
}

function selectMeetingRoom(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if ( isset($input->roomId) ) {
        $personId = SessionUser::getUser()->getPersonId();

        $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

        if ( is_null($lpm) ) {
            $lpm = new PersonLastMeeting();
        }

        $lpm->setPersonMeetingId($input->roomId);
        $lpm->setPersonId($personId);
        $lpm->save();

        return $response->withJson($lpm->toArray());
    }

    return $response;
}

function createMeetingRoom(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if ( isset($input->roomName) ) {
        $personId = SessionUser::getUser()->getPersonId();


        $pm = new PersonMeeting();
        $pm->setCode(basename($input->roomName));
        $pm->setPersonId($personId);

        $date = new DateTime('now');
        $pm->setCreationDate($date->format('Y-m-d h:m'));
        $pm->save();

        $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

        if ( is_null($lpm) ) {
            $lpm = new PersonLastMeeting();
        }

        $lpm->setPersonMeetingId($pm->getId());
        $lpm->setPersonId($personId);
        $lpm->save();

        return $response->withJson($pm->toArray());
    }

    return null;
}

function getLastMeeting(Request $request, Response $response, array $args)
{
    $personId = SessionUser::getUser()->getPersonId();

    $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

    if (!is_null($lpm)) {
        $pm = PersonMeetingQuery::create()->findOneById($lpm->getPersonMeetingId());
        return $response->withJson($pm->toArray());
    } else {
        return null;
    }
}

function getAllMettings(Request $request, Response $response, array $args)
{
    $personId = SessionUser::getUser()->getPersonId();

    $meetings = PersonMeetingQuery::create()
        ->findByPersonId($personId);

    return $response->withJson(['PersonMeetings' => $meetings->toArray()]);
}
