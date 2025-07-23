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
use Slim\Http\Response;
use Slim\Http\ServerRequest;


use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\NoteShareQuery;
use EcclesiaCRM\NoteShare;
use EcclesiaCRM\Emails\DocumentEmail;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\WebDav\Utils\SabreUtils;
use Sabre\DAV\Xml\Element\Sharee;

use EcclesiaCRM\Utils\MiscUtils;

class DocumentShareController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllShareForPerson(ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        $result = [];

        if (isset ($params->noteId)) {
            $personShareQuery = NoteShareQuery::create()
                ->joinWithNote()
                ->findByNoteId($params->noteId);

            foreach ($personShareQuery as $noteShare) {
                $id = $noteShare->getSharePerId();
                $name = PersonQuery::Create()->findOneById ($id)->getFullName();

                $person = ['id' => $id,
                    'name' => (($noteShare->getRights() == 1)?gettext("[👀  ]"):gettext("[👀 ✐]"))."   ".$name];

                array_push($result, $person);
            }
        }

        return $response->withJson($result);
    }

    public function getAllShareForPersonSabre(ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        $result = [];

        if (isset ($params->rows)) {
            $currentUser = UserQuery::create()->findOneByPersonId($params->currentPersonID);
            
            foreach ($params->rows as $row) {
                $ownerPaths = $currentUser->getUserRootDir()."/".$row['path'];
                $sharees = SabreUtils::getFileOrDirectoryInfos($ownerPaths);

                foreach ($sharees as $info) {
                    $person = [
                        'id' => $info->principal,
                        'name' => (($info->access == 3)?gettext("[👀 ✐]"):gettext("[👀  ]"))."   ".$info->principal];

                    array_push($result, $person);                
                }
            }
        }

        return $response->withJson($result);
    }

    public function addPersonToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->personID) && isset ($params->noteId) && isset ($params->currentPersonID) && isset ($params->notification) ) {
            $noteShare = NoteShareQuery::Create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);

            if ( empty($noteShare) && $params->currentPersonID != $params->personID && $params->noteId > 0) {
                $noteShare = new NoteShare();

                $noteShare->setSharePerId($params->personID);
                $noteShare->setNoteId($params->noteId);

                $noteShare->save();

                if (isset ($params->notification)  && $params->notification) {
                    $user = UserQuery::Create()->findOneByPersonId($params->personID);

                    if ( !empty($user) ){
                        $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
                        $email->send();
                    }
                }

                return $response->withJson(['status' => "success"]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    // new way through sabre !
    public function addPersonSabreToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->currentPersonID) && isset ($params->rows) && isset($params->personToShareID) && isset ($params->notification) && isset($params->access)) {
            $access = $params->access;

            $currentUser = UserQuery::create()->findOneByPersonId($params->currentPersonID);
            $ownerPersonId = $currentUser->getPersonId();
            $currentUserName = $currentUser->getUserName();
            
            $ownerPrinpals = 'principals/'.$currentUserName;

            $userToShare = UserQuery::create()->findOneByPersonId($params->personToShareID);
            $userToShareUserName = $userToShare->getUserName();

            /*$noteShare = NoteShareQuery::Create()->filterBySharePerId($userToShare->getPersonId())->findOneByNoteId($params->noteId);

            // share in the timeline too
            if ( empty($noteShare) && $params->currentPersonID != $params->personID && $params->noteId > 0) {
                $noteShare = new NoteShare();

                $noteShare->setSharePerId($userToShare->getPersonId());
                $noteShare->setNoteId($params->noteId);

                $noteShare->save();
            }*/
            
            foreach ($params->rows as $row) {
                $ownerPaths = $currentUser->getUserRootDir()."/".$row['path']; ///private/userdir/A99CBDE9-E121-4713-B8D2-D14C50561310/admin/wsl1.png
                $ownerNameCollection = basename($ownerPaths);
                $sharees = [];
                $sharees[] = new Sharee([
                    'href' => "mailto:".$userToShare->getPerson()->getEmail(),
                    'access' => $access,
                    /// Everyone is always immediately accepted, for now.
                    'inviteStatus' => (int) null,
                    'properties' => ['{DAV:}displayname' => $userToShare->getPerson()->getFullName()],
                    'principal' => 'principals/'.$userToShareUserName
                ]);

                SabreUtils::shareFileOrDirectory($ownerPersonId, $ownerPaths, $ownerPrinpals, $ownerNameCollection, $sharees);

                // send notification !!
                if (isset ($params->notification)  && $params->notification) {                    
                    if ( !empty($userToShare) ){
                        $email = new DocumentEmail($userToShare, gettext("You can visualize it in your account, in the time Line or the notes tab."));
                        $email->send();
                    }
                }
            }
            
            return $response->withJson(['status' => "success"]);            
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function addFamilyToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->familyID) && isset ($params->noteId) && isset ($params->currentPersonID) && isset ($params->notification) ) {
            $members = FamilyQuery::Create()->findOneById($params->familyID)->getActivatedPeople();

            foreach ($members as $member) {
                if ($member->getId() > 0) {
                    $noteShare = NoteShareQuery::Create()->filterBySharePerId($member->getId())->findOneByNoteId($params->noteId);

                    if ( empty($noteShare) && $params->currentPersonID != $member->getId() ) {
                        $noteShare = new NoteShare();

                        $noteShare->setSharePerId($member->getId());
                        $noteShare->setNoteId($params->noteId);

                        $noteShare->save();

                        if (isset ($params->notification)  && $params->notification) {
                            $user = UserQuery::Create()->findOneByPersonId($member->getId());

                            if ( !empty($user) ){
                                $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
                                $email->send();
                            }
                        }
                    }
                }
            }
        }

        return $response->withJson(['status' => "success"]);
    }

    public function addGroupToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->groupID) && isset ($params->noteId) && isset ($params->currentPersonID) && isset ($params->notification) ) {
            $members = GroupQuery::Create()->findOneById($params->groupID)->getPerson2group2roleP2g2rs();

            foreach ($members as $member) {
                if ($member->getPersonId() > 0) {
                    $noteShare = NoteShareQuery::Create()->filterBySharePerId($member->getPersonId())->findOneByNoteId($params->noteId);

                    if ( empty($noteShare) && $params->currentPersonID != $member->getPersonId() ) {
                        $noteShare = new NoteShare();

                        $noteShare->setSharePerId($member->getPersonId());
                        $noteShare->setNoteId($params->noteId);

                        $noteShare->save();

                        if (isset ($params->notification) && $params->notification) {
                            $user = UserQuery::Create()->findOneByPersonId($member->getPersonId());

                            if ( !empty($user) ){
                                $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
                                $email->send();
                            }
                        }
                    }
                }
            }
        }

        return $response->withJson(['status' => $params->groupID]);
    }

    public function deletePersonFromShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->personID) && isset ($params->noteId)) {
            $noteShare = NoteShareQuery::Create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);

            $noteShare->delete();
        }

        $noteShare = NoteShareQuery::Create()->findByNoteId($params->noteId);

        return $response->withJson(['status' => "success",'count' => $noteShare->count()]);
    }

    public function deletePersonSabreFromShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->personPrincipal) && isset ($params->rows) and isset($params->currentPersonID)) {     
            $currentUser = UserQuery::create()->findOneByPersonId($params->currentPersonID);
            $currentUserName = $currentUser->getUserName();            
            
            $ownerPrinpals = 'principals/'.$currentUserName;

            foreach ($params->rows as $row) {
                $sabrePath = "home/".$row['path'];
                if (SabreUtils::removeSharedForPersonPrincipal($ownerPrinpals, $sabrePath, $params->personPrincipal)) {
                    return $response->withJson(['status' => "success",'count' => 0]);
                }
            }
        }

        return $response->withJson(['status' => "failed"]);
        
    }

    public function setRightsForPerson (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->personID) && isset ($params->noteId) && isset ($params->rightAccess) && $params->rightAccess > 0) {
            $noteShare = NoteShareQuery::Create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);

            $noteShare->setRights($params->rightAccess);
            $noteShare->save();
        }

        return $response->withJson(['status' => "success"]);
    }

    public function setRightsSabreForPerson (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset($params->currentPersonID) && isset ($params->personToShareID) && isset ($params->rightAccess) && $params->rightAccess > 0) {
            $access = $params->rightAccess;

            $currentUser = UserQuery::create()->findOneByPersonId($params->currentPersonID);
            $ownerPersonId = $currentUser->getPersonId();
            $currentUserName = $currentUser->getUserName();
            
            $ownerPrinpals = 'principals/'.$currentUserName;

            $personToShareUsername = explode("/", $params->personToShareID)[1];
            $userToShare = UserQuery::create()->findOneByUserName($personToShareUsername);
            $userToShareUserName = $userToShare->getUserName();

            // now we can share all the rows from currentPersonID to the personToShareID
            foreach ($params->rows as $row) {            
                $ownerPaths = $currentUser->getUserRootDir()."/".$row['path']; ///private/userdir/A99CBDE9-E121-4713-B8D2-D14C50561310/admin/wsl1.png
                $ownerNameCollection = basename($ownerPaths);
                $sharees = [];
                $sharees[] = new Sharee([
                    'href' => "mailto:".$userToShare->getPerson()->getEmail(),
                    'access' => $access,
                    /// Everyone is always immediately accepted, for now.
                    'inviteStatus' => (int) null,
                    'properties' => ['{DAV:}displayname' => $userToShare->getPerson()->getFullName()],
                    'principal' => 'principals/'.$userToShareUserName
                ]);

                SabreUtils::shareFileOrDirectory($ownerPersonId, $ownerPaths, $ownerPrinpals, $ownerNameCollection, $sharees);

                // send notification !!
                if (isset ($params->notification)  && $params->notification) {                    
                    if ( !empty($userToShare) ){
                        $email = new DocumentEmail($userToShare, gettext("You can visualize it in your account, in the time Line or the notes tab."));
                        $email->send();
                    }
                }
            }
        }

        return $response->withJson(['status' => "success"]);
    }    

    public function clearDocument (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->noteId)) {
            $noteShare = NoteShareQuery::Create()->findByNoteId($params->noteId);

            $noteShare->delete();
        }

        return $response->withJson(['status' => "success"]);
    }

    public function cleardocumentsabre (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (isset ($params->rows) and isset($params->currentPersonID)) {     
            $currentUser = UserQuery::create()->findOneByPersonId($params->currentPersonID);
            $currentUserName = $currentUser->getUserName();            
            
            $ownerPrinpals = 'principals/'.$currentUserName;

            foreach ($params->rows as $row) {
                $sabrePath = "home/".$row['path'];
                if (SabreUtils::removeAllSharedForPersonPrincipal($ownerPrinpals, $sabrePath)) {
                    return $response->withJson(['status' => "success",'count' => 0]);
                }
            }
        }

        return $response->withJson(['status' => "failed"]);
    }
    
    public function getShareInfosSabre(ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        $result = [];

        if (isset ($params->rows)) {
            $currentUser = UserQuery::create()->findOneByPersonId($params->currentPersonID);
            
            foreach ($params->rows as $row) {
                $ownerPaths = $currentUser->getUserRootDir()."/".$row['path'];
            
                $sharees = SabreUtils::getFileOrDirectoryInfos($ownerPaths);
                
                foreach ($sharees as $info) {
                    $person = [
                        'principal' => $info->principal,
                        'fullName' => (($info->access == 3)?gettext("[👀 ✐]"):gettext("[👀  ]"))."   ".$info->properties['{DAV:}displayname']
                    ];

                    array_push($result, $person);                
                }
            }
        }

        return $response->withJson($result);
    }
}
