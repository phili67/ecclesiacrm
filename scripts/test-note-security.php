<?php

namespace Slim\Http {
    class ServerRequest
    {
        public function __construct(private array $body) {}
        public function getParsedBody(): array { return $this->body; }
    }
    class Response
    {
        public array $body = [];
        public int $status = 200;
        public function withJson($body, $status = 200): self
        {
            $this->body = $body;
            $this->status = $status;
            return $this;
        }
    }
}

namespace EcclesiaCRM\Base {
    class Note
    {
        protected array $data = ['Id' => 1, 'Private' => 0, 'EnteredBy' => 7, 'Type' => 'note',
            'CurrentEditedBy' => 0, 'CurrentEditedDate' => null, 'PerId' => 99, 'FamId' => 0,
            'Title' => 'title', 'Text' => 'secret', 'EditedBy' => 0];
        public function __call($method, $args)
        {
            $field = substr($method, 3);
            if (str_starts_with($method, 'get')) { return $this->data[$field] ?? null; }
            if (str_starts_with($method, 'set')) { $this->data[$field] = $args[0]; return $this; }
            throw new \BadMethodCallException($method);
        }
        public function save() { \EcclesiaCRM\NoteQuery::$notes[$this->getId()] = $this; }
        public function delete() { unset(\EcclesiaCRM\NoteQuery::$notes[$this->getId()]); }
        public function toArray() { return $this->data; }
    }
}

namespace EcclesiaCRM {
    class SessionUser
    {
        public static int $id = 7;
        public static function getId() { return self::$id; }
        public static function getUser() { return new class {
            public function getPersonId() { return SessionUser::$id; }
        }; }
    }
    class NoteQuery
    {
        public static array $notes = [];
        public static function create() { return new self(); }
        public function findOneById($id) { return self::$notes[$id] ?? null; }
    }
    class NoteShare
    {
        public int $noteId;
        public int $personId;
        public int $rights = 1;
        public function setNoteId($id) { $this->noteId = (int)$id; }
        public function setSharePerId($id) { $this->personId = (int)$id; }
        public function getSharePerId() { return $this->personId; }
        public function getRights() { return $this->rights; }
        public function setRights($rights) { $this->rights = (int)$rights; }
        public function save() { NoteShareQuery::$shares[$this->noteId][$this->personId] = $this; }
        public function delete() { unset(NoteShareQuery::$shares[$this->noteId][$this->personId]); }
    }
    class NoteShareQuery
    {
        public static array $shares = [];
        private ?int $noteId = null;
        private ?int $personId = null;
        public static function create() { return new self(); }
        public function filterByNoteId($id) { $this->noteId = (int)$id; return $this; }
        public function filterBySharePerId($id) { $this->personId = (int)$id; return $this; }
        public function findOneBySharePerId($id) { return self::$shares[$this->noteId][$id] ?? null; }
        public function findOneByNoteId($id) { return self::$shares[$id][$this->personId] ?? null; }
        public function findOne() { return array_values(self::$shares[$this->noteId] ?? [])[0] ?? null; }
        public function findByNoteId($id) { return new ShareCollection((int)$id); }
    }
    class ShareCollection
    {
        public function __construct(private int $noteId) {}
        public function delete() { unset(NoteShareQuery::$shares[$this->noteId]); }
        public function count() { return count(NoteShareQuery::$shares[$this->noteId] ?? []); }
    }
    class UserQuery
    {
        public static function create() { return new self(); }
        public function findOneByPersonId($id) { return in_array((int)$id, [7, 8, 9], true) ? new \stdClass() : null; }
    }
    class FamilyQuery
    {
        public static function create() { return new self(); }
        public function findOneById($id) { return new class {
            public function getActivatedPeople() { return [new Member(7), new Member(8), new Member(10)]; }
        }; }
    }
    class GroupQuery
    {
        public static function create() { return new self(); }
        public function findOneById($id) { return new class {
            public function getPerson2group2roleP2g2rs() { return [new Member(7), new Member(9)]; }
        }; }
    }
    class Member
    {
        public function __construct(private int $id) {}
        public function getId() { return $this->id; }
        public function getPersonId() { return $this->id; }
    }
}

namespace EcclesiaCRM\Utils {
    class InputUtils
    {
        public static function FilterHTML($text) { return $text; }
        public static function FilterPresentationHTML($text) { return $text; }
    }
}

namespace EcclesiaCRM\dto {
    class SystemConfig
    {
        public static function getValue($key) { return 10; }
    }
}

namespace {
    use EcclesiaCRM\APIControllers\DocumentDocumentController;
    use EcclesiaCRM\APIControllers\DocumentShareController;
    use EcclesiaCRM\Note;
    use EcclesiaCRM\NoteQuery;
    use EcclesiaCRM\NoteShareQuery;
    use EcclesiaCRM\SessionUser;
    use Slim\Http\Response;
    use Slim\Http\ServerRequest;

    require __DIR__ . '/../src/EcclesiaCRM/model/EcclesiaCRM/Note.php';
    require __DIR__ . '/../src/EcclesiaCRM/APIControllers/DocumentDocumentController.php';
    require __DIR__ . '/../src/EcclesiaCRM/APIControllers/DocumentShareController.php';

    function expect(bool $condition, string $message): void
    {
        if (!$condition) { throw new \RuntimeException($message); }
    }
    function callEndpoint($controller, string $method, array $body): Response
    {
        return $controller->$method(new ServerRequest($body), new Response(), []);
    }

    $notes = (new \ReflectionClass(DocumentDocumentController::class))->newInstanceWithoutConstructor();
    $shares = (new \ReflectionClass(DocumentShareController::class))->newInstanceWithoutConstructor();
    $note = new Note();
    $note->setPrivate(1);
    $note->save();
    expect($note->canViewNote(7) && !$note->canViewNote(99), 'Private note must belong to its creator, not its subject');
    expect(callEndpoint($notes, 'getDocument', ['docID' => 1, 'personID' => 7, 'famID' => 0])->body['success'], 'Creator can read');
    SessionUser::$id = 99;
    $result = callEndpoint($notes, 'getDocument', ['docID' => 1, 'personID' => 7, 'famID' => 0]);
    expect($result->status === 403 && !isset($result->body['note']), 'Spoofed person ID must not disclose a note');
    expect(callEndpoint($shares, 'addPersonToShare', ['noteId' => 1, 'personID' => 8, 'currentPersonID' => 7, 'notification' => false])->status === 403, 'Only creator can share');
    expect(callEndpoint($shares, 'clearDocument', ['noteId' => 1])->status === 403, 'Only creator can revoke shares');
    expect(callEndpoint($shares, 'getAllShareForPerson', ['noteId' => 1])->status === 403, 'Only creator can list recipients');
    expect(callEndpoint($notes, 'leaveDocument', ['docID' => 1])->status === 403, 'Other users cannot release the edit lock');

    SessionUser::$id = 7;
    expect(callEndpoint($shares, 'addPersonToShare', ['noteId' => 1, 'personID' => 7, 'currentPersonID' => 99, 'notification' => false])->status === 400, 'Cannot share with self');
    expect(callEndpoint($shares, 'addPersonToShare', ['noteId' => 1, 'personID' => 8, 'currentPersonID' => 99, 'notification' => false])->body['status'] === 'success', 'Can share with another user');
    expect($note->canViewNote(8) && !$note->canEditNote(8) && !$note->canViewNote(9), 'Read-only recipient and outsider permissions');
    SessionUser::$id = 8;
    $result = callEndpoint($notes, 'getDocument', ['docID' => 1, 'personID' => 99, 'famID' => 0]);
    expect($result->body['canEdit'] === false && $note->getCurrentEditedBy() === 7, 'Read-only access must not take the edit lock');
    $update = ['docID' => 1, 'title' => 'new', 'type' => 'note', 'text' => 'new', 'bPrivate' => true];
    expect(callEndpoint($notes, 'updateDocument', $update)->status === 403, 'Read-only recipient cannot edit');
    expect(callEndpoint($shares, 'setRightsForPerson', ['noteId' => 1, 'personID' => 8, 'rightAccess' => 2])->status === 403, 'Recipient cannot grant rights');

    SessionUser::$id = 7;
    expect(callEndpoint($shares, 'setRightsForPerson', ['noteId' => 1, 'personID' => 8, 'rightAccess' => 3])->status === 400, 'Invalid rights rejected');
    callEndpoint($shares, 'setRightsForPerson', ['noteId' => 1, 'personID' => 8, 'rightAccess' => 2]);
    callEndpoint($notes, 'leaveDocument', ['docID' => 1]);
    SessionUser::$id = 8;
    expect(callEndpoint($notes, 'getDocument', ['docID' => 1, 'personID' => 99, 'famID' => 0])->body['canEdit'], 'Writable recipient can open');
    $changePrivacy = $update;
    $changePrivacy['bPrivate'] = false;
    expect(callEndpoint($notes, 'updateDocument', $changePrivacy)->status === 403, 'Recipient cannot change privacy');
    expect(callEndpoint($notes, 'updateDocument', array_merge($update, ['type' => 'file']))->status === 400, 'Cannot escape note access policy by changing type');
    expect(callEndpoint($notes, 'updateDocument', $update)->body['success'], 'Writable recipient can edit');
    expect($note->getEnteredBy() === 7 && $note->getEditedBy() === 8, 'Editing preserves creator identity');
    expect(callEndpoint($notes, 'deleteDocument', ['docID' => 1])->status === 403, 'Recipient cannot delete');

    SessionUser::$id = 7;
    callEndpoint($shares, 'addFamilyToShare', ['noteId' => 1, 'familyID' => 1, 'notification' => false]);
    callEndpoint($shares, 'addGroupToShare', ['noteId' => 1, 'groupID' => 1, 'notification' => false]);
    expect(!isset(NoteShareQuery::$shares[1][7]) && !isset(NoteShareQuery::$shares[1][10]) && isset(NoteShareQuery::$shares[1][9]), 'Family and group shares exclude creator and non-users');
    callEndpoint($shares, 'clearDocument', ['noteId' => 1]);
    expect(!$note->canViewNote(8) && !$note->canViewNote(9), 'Revoked recipients lose access');
    $note->setPrivate(0);
    expect($note->canViewNote(9), 'Unshared public notes remain visible');
    callEndpoint($shares, 'addPersonToShare', ['noteId' => 1, 'personID' => 8, 'notification' => false]);
    expect($note->isPrivate() && !$note->canViewNote(9), 'Sharing a public note restricts it');
    $note->setPrivate(0);
    expect(!$note->canViewNote(9) && $note->canViewNote(8), 'Existing public shared notes are restricted too');
    callEndpoint($shares, 'deletePersonFromShare', ['noteId' => 1, 'personID' => 8]);
    expect(!$note->canViewNote(8) && !$note->canViewNote(9), 'Removing the last share does not make the note public');
    echo "Note security tests passed\n";
}
