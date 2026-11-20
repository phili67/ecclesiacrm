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
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Emails\DocumentEmail;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\WebDav\Utils\SabreUtils;
use Sabre\DAV\Xml\Element\Sharee;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\DocumentSecurityUtils;

class DocumentShareController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function canModifySabreShares($params): bool
    {
        if (!SessionUser::getUser()->isEDriveEnabled()
            || !isset($params->currentPersonID, $params->rows)
            || (int)$params->currentPersonID !== (int)SessionUser::getId()
            || !is_array($params->rows) || count($params->rows) === 0) {
            return false;
        }

        $user = UserQuery::create()->findOneByPersonId(SessionUser::getId());
        if (is_null($user)) {
            return false;
        }

        $userName = $user->getUserName();
        foreach ($params->rows as $row) {
            if (!is_array($row) || !isset($row['path']) || !is_string($row['path'])) {
                return false;
            }

            $path = DocumentSecurityUtils::normalizeRelativePath($row['path']);
            if (is_null($path) || !str_starts_with($path, $userName . '/')
                || SabreUtils::fileOrCollectionACL('principals/' . $userName, 'home/' . $path) != 3) {
                return false;
            }
        }

        return true;
    }

    private function ownedNote($noteId): ?Note
    {
        $note = NoteQuery::create()->findOneById($noteId);
        return $note !== null && $note->isNoteDocument() && $note->isCreator(SessionUser::getId()) ? $note : null;
    }

    private function shareNoteWithPerson(Note $note, int $personId, bool $notification): bool
    {
        if ($personId <= 0 || $personId === (int)SessionUser::getId()) {
            return false;
        }
        $user = UserQuery::create()->findOneByPersonId($personId);
        if ($user === null) {
            return false;
        }
        if (NoteShareQuery::create()->filterBySharePerId($personId)->findOneByNoteId($note->getId()) !== null) {
            return false;
        }

        // Revoking the last share must not make the note public again.
        if (!$note->isPrivate()) {
            $note->setPrivate(1);
            $note->save();
        }
        $share = new NoteShare();
        $share->setSharePerId($personId);
        $share->setNoteId($note->getId());
        $share->save();

        if ($notification) {
            $email = new DocumentEmail($user, gettext("You can visualize it in your account, in the time Line or the notes tab."));
            $email->send();
        }
        return true;
    }

    public function getAllShareForPerson(ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        $result = [];

        if (!isset($params->noteId) || $this->ownedNote($params->noteId) === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

        if (isset ($params->noteId)) {
            $personShareQuery = NoteShareQuery::create()
                ->joinWithNote()
                ->findByNoteId($params->noteId);

            foreach ($personShareQuery as $noteShare) {
                $id = $noteShare->getSharePerId();
                $name = PersonQuery::Create()->findOneById ($id)->getFullName();

                $person = ['id' => $id,
                    'name' => (($noteShare->getRights() == 1)?"[R ]":"[RW]")."   ".$name];

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
                        'name' => $info->properties['{DAV:}displayname'] ?? $info->principal,
                        'access' => (int)$info->access
                    ];

                    array_push($result, $person);                
                }
            }
        }

        return $response->withJson($result);
    }

    public function addPersonToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!isset($params->noteId, $params->personID, $params->notification)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $note = $this->ownedNote($params->noteId);
        if ($note === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }
        if (!$this->shareNoteWithPerson($note, (int)$params->personID, (bool)$params->notification)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        return $response->withJson(['status' => 'success']);
    }    

    // new way through sabre !
    public function addPersonSabreToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!$this->canModifySabreShares($params)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

        if (!isset($params->personToShareID)
            || (int)$params->personToShareID === (int)SessionUser::getId()
            || is_null(UserQuery::create()->findOneByPersonId((int)$params->personToShareID))) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }

        if (isset ($params->currentPersonID) 
            && isset ($params->rows) 
            && isset($params->personToShareID) 
            && isset ($params->notification) 
            && isset($params->access)) {
            $access = $params->access;
            $rows = $params->rows;
            $notification = $params->notification;

            SabreUtils::processShareToPerson($params->currentPersonID, $params->personToShareID, $access, $rows, $notification);
            
            return $response->withJson(['status' => "success"]);            
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function addFamilyToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!isset($params->familyID, $params->noteId, $params->notification)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $note = $this->ownedNote($params->noteId);
        if ($note === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }
        $family = FamilyQuery::create()->findOneById($params->familyID);
        if ($family === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $added = 0;
        foreach ($family->getActivatedPeople() as $member) {
            $added += (int)$this->shareNoteWithPerson($note, (int)$member->getId(), (bool)$params->notification);
        }
        return $response->withJson(['status' => $added ? 'success' : 'failed', 'count' => $added]);
    }

    public function addFamilyToShareSabre (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!$this->canModifySabreShares($params)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

        if (isset ($params->currentPersonID)
            && isset ($params->familyToShareID) 
            && isset ($params->rows)                          
            && isset ($params->notification)
            && isset($params->access) ) {

            $access = $params->access;
            $rows = $params->rows;
            $notification = $params->notification;
            
            $members = FamilyQuery::Create()->findOneById($params->familyToShareID)->getActivatedPeople();

            foreach ($members as $member) {
                if ($member->getId() > 0) {
                    SabreUtils::processShareToPerson($params->currentPersonID, $member->getId(), $access, $rows, $notification);                                                                         
                }
            }
        }

        return $response->withJson(['status' => "success"]);
    }

    public function addGroupToShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!isset($params->groupID, $params->noteId, $params->notification)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $note = $this->ownedNote($params->noteId);
        if ($note === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }
        $group = GroupQuery::create()->findOneById($params->groupID);
        if ($group === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $added = 0;
        foreach ($group->getPerson2group2roleP2g2rs() as $member) {
            $added += (int)$this->shareNoteWithPerson($note, (int)$member->getPersonId(), (bool)$params->notification);
        }
        return $response->withJson(['status' => $added ? 'success' : 'failed', 'count' => $added]);
    }

    public function addGroupToShareSabre (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!$this->canModifySabreShares($params)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

        if (isset ($params->currentPersonID)
            && isset ($params->groupToShareID) 
            && isset ($params->rows)                          
            && isset ($params->notification)
            && isset($params->access) ) {

            $access = $params->access;
            $rows = $params->rows;
            $notification = $params->notification;
            
            $members = GroupQuery::Create()->findOneById($params->groupToShareID)->getPerson2group2roleP2g2rs();

            foreach ($members as $member) {
                if ($member->getPersonId() > 0) {
                    SabreUtils::processShareToPerson($params->currentPersonID, $member->getPersonId(), $access, $rows, $notification);                                                                         
                }
            }
        }

        return $response->withJson(['status' => "success"]);
    } 

    public function deletePersonFromShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        $note = isset($params->noteId) ? $this->ownedNote($params->noteId) : null;
        if ($note === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }
        $noteShare = isset($params->personID) ? NoteShareQuery::create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId) : null;
        if ($noteShare === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }

        $note->setPrivate(1);
        $note->save();
        $noteShare->delete();
        $noteShare = NoteShareQuery::Create()->findByNoteId($params->noteId);

        return $response->withJson(['status' => "success",'count' => $noteShare->count()]);
    }

    public function deletePersonSabreFromShare (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!$this->canModifySabreShares($params)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

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

        if (!isset($params->noteId) || $this->ownedNote($params->noteId) === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }
        if (!isset($params->personID, $params->rightAccess) || !in_array((string)$params->rightAccess, ['1', '2'], true)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $noteShare = NoteShareQuery::create()->filterBySharePerId($params->personID)->findOneByNoteId($params->noteId);
        if ($noteShare === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Invalid person')], 400);
        }
        $noteShare->setRights($params->rightAccess);
        $noteShare->save();
        return $response->withJson(['status' => "success"]);
    }

    public function setRightsSabreForPerson (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!$this->canModifySabreShares($params)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

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

                SabreUtils::shareFileOrDirectory($ownerPersonId, $ownerPaths, $ownerPrinpals, $ownerNameCollection, $sharees, true);

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

        $note = isset($params->noteId) ? $this->ownedNote($params->noteId) : null;
        if ($note === null) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }
        $note->setPrivate(1);
        $note->save();
        NoteShareQuery::create()->findByNoteId($params->noteId)->delete();

        return $response->withJson(['status' => "success"]);
    }

    public function cleardocumentsabre (ServerRequest $request, Response $response, array $args): Response {
        $params = (object)$request->getParsedBody();

        if (!$this->canModifySabreShares($params)) {
            return $response->withJson(['status' => 'failed', 'message' => _('Forbidden')], 403);
        }

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
                        'fullName' => $info->properties['{DAV:}displayname'] ?? $info->principal,
                        'access' => (int)$info->access
                    ];

                    array_push($result, $person);                
                }
            }
        }

        return $response->withJson($result);
    }
}
