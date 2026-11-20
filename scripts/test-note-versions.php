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

namespace {
    use EcclesiaCRM\APIControllers\DocumentDocumentController;
    use EcclesiaCRM\Note;
    use EcclesiaCRM\NoteShareQuery;
    use EcclesiaCRM\Map\PersonTableMap;
    use Propel\Runtime\Connection\ConnectionManagerSingle;
    use Propel\Runtime\Propel;
    use Slim\Http\Response;
    use Slim\Http\ServerRequest;

    require __DIR__ . '/../src/vendor/autoload.php';
    require __DIR__ . '/../src/EcclesiaCRM/loadDatabase.php';

    function expect(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }
    function callEndpoint($controller, string $method, array $body): Response
    {
        return $controller->$method(new ServerRequest($body), new Response(), []);
    }

    $container = Propel::getServiceContainer();
    $container->setAdapterClass('main', 'sqlite');
    $manager = new ConnectionManagerSingle('main');
    $manager->setConfiguration(['dsn' => 'sqlite::memory:']);
    $container->setConnectionManager($manager);
    $container->setDefaultDatasource('main');
    $db = Propel::getWriteConnection('main');
    $db->exec('PRAGMA foreign_keys = ON');
    $db->exec('CREATE TABLE note_nte (
        nte_ID INTEGER PRIMARY KEY AUTOINCREMENT, nte_per_ID INTEGER, nte_fam_ID INTEGER,
        nte_Private INTEGER NOT NULL DEFAULT 0, nte_Title TEXT, nte_Text TEXT,
        nte_DateEntered TEXT NOT NULL, nte_DateLastEdited TEXT, nte_EnteredBy INTEGER NOT NULL DEFAULT 0,
        nte_EditedBy INTEGER NOT NULL DEFAULT 0, nte_Type TEXT, nte_Info TEXT,
        nte_isEditedBy INTEGER NOT NULL DEFAULT 0, nte_isEditedByDate TEXT
    )');
    $db->exec('CREATE TABLE note_nte_share (
        nte_sh_id INTEGER PRIMARY KEY AUTOINCREMENT, nte_sh_note_ID INTEGER,
        nte_sh_share_to_person_ID INTEGER, nte_sh_share_to_family_ID INTEGER,
        nte_sh_share_rights INTEGER NOT NULL DEFAULT 1
    )');
    $db->exec('CREATE TABLE note_revision (
        id INTEGER PRIMARY KEY AUTOINCREMENT, note_id INTEGER NOT NULL,
        author_id INTEGER NOT NULL, created_at TEXT NOT NULL, title TEXT, type TEXT, content TEXT,
        FOREIGN KEY (note_id) REFERENCES note_nte(nte_ID) ON DELETE CASCADE
    )');
    $personColumns = [];
    foreach ((new PersonTableMap())->getColumns() as $column) {
        $personColumns[] = '"' . $column->getName() . '" TEXT';
    }
    $db->exec('CREATE TABLE person_per (' . implode(', ', $personColumns) . ')');

    $user = new class {
        public int $id = 7;
        public function getId() { return $this->id; }
        public function getPersonId() { return $this->id; }
    };
    $_SESSION['user'] = $user;
    $note = new Note();
    $note->setPerId(null);
    $note->setFamId(null);
    $note->setPrivate(1);
    $note->setTitle('Original');
    $note->setText('<p>Original</p>');
    $note->setType('note');
    $note->setEntered(7);
    $note->save($db);
    $db->exec('INSERT INTO note_nte_share (nte_sh_note_ID, nte_sh_share_to_person_ID, nte_sh_share_rights) VALUES (1, 8, 2), (1, 9, 1)');
    $controller = (new \ReflectionClass(DocumentDocumentController::class))->newInstanceWithoutConstructor();
    $get = ['docID' => 1, 'personID' => 0, 'famID' => 0];
    $ownerVersion = callEndpoint($controller, 'getDocument', $get)->body['version'];
    $user->id = 8;
    $recipientVersion = callEndpoint($controller, 'getDocument', $get)->body['version'];
    expect($ownerVersion === $recipientVersion, 'Both editors open the same version');
    $update = ['docID' => 1, 'title' => 'Recipient', 'type' => 'note',
        'text' => '<p>Recipient edit</p>', 'bPrivate' => true, 'version' => $recipientVersion];
    $saved = callEndpoint($controller, 'updateDocument', $update);
    expect($saved->body['success'], 'Recipient can save');
    $user->id = 7;
    $stale = callEndpoint($controller, 'updateDocument', array_merge($update, ['title' => 'Owner', 'version' => $ownerVersion]));
    expect($stale->status === 409 && $stale->body['note']['Title'] === 'Recipient', 'Stale edits conflict without overwriting');
    $history = callEndpoint($controller, 'history', ['docID' => 1])->body['history'];
    expect(count($history) === 2 && $history[0]['author_id'] == 8 && $history[1]['author_id'] == 7, 'Legacy initial state and new revision retain authors');
    $restore = callEndpoint($controller, 'restore', ['docID' => 1, 'revisionId' => $history[1]['id'], 'version' => $saved->body['version']]);
    expect($restore->body['success'] && $restore->body['note']['Title'] === 'Original' && $restore->body['note']['Private'] == 1, 'Restore creates a new revision without changing privacy');
    expect(count(callEndpoint($controller, 'history', ['docID' => 1])->body['history']) === 3, 'Restore is itself recorded');
    expect(callEndpoint($controller, 'restore', ['docID' => 1, 'revisionId' => $history[0]['id'], 'version' => $saved->body['version']])->status === 409, 'Restoring a stale version cannot overwrite a newer edit');
    expect(callEndpoint($controller, 'restore', ['docID' => 1, 'revisionId' => 999, 'version' => $restore->body['version']])->status === 400, 'Unknown revision rejected');
    $db->exec('UPDATE note_nte SET nte_Private = 0 WHERE nte_ID = 1');
    $privacyConflict = callEndpoint($controller, 'updateDocument', array_merge($update, ['version' => $restore->body['version']]));
    expect($privacyConflict->status === 409, 'Changing privacy invalidates an open editor version');
    $db->exec('UPDATE note_nte SET nte_Private = 1 WHERE nte_ID = 1');
    $user->id = 9;
    expect(callEndpoint($controller, 'history', ['docID' => 1])->status === 200, 'Read-only recipient can view history');
    expect(callEndpoint($controller, 'restore', ['docID' => 1, 'revisionId' => $history[0]['id'], 'version' => $restore->body['version']])->status === 403, 'Read-only recipient cannot restore');
    $user->id = 10;
    expect(callEndpoint($controller, 'history', ['docID' => 1])->status === 403, 'Outsider cannot view history');
    $db->exec('DELETE FROM note_nte_share WHERE nte_sh_share_to_person_ID = 8');
    $user->id = 8;
    expect(callEndpoint($controller, 'history', ['docID' => 1])->status === 403, 'Revoked recipient cannot view history');
    expect(callEndpoint($controller, 'updateDocument', array_merge($update, ['version' => $restore->body['version']]))->status === 403, 'Revoked recipient cannot save');
    $user->id = 7;
    $created = callEndpoint($controller, 'createDocument', [
        'personID' => 0, 'famID' => 0, 'title' => 'New', 'type' => 'note',
        'text' => '<p>New note</p>', 'bPrivate' => true
    ]);
    expect($created->body['success'], 'New note is created');
    $createdHistory = callEndpoint($controller, 'history', ['docID' => 2])->body['history'];
    expect(count($createdHistory) === 1 && $createdHistory[0]['author_id'] == 7, 'Creation records its first version');
    $current = callEndpoint($controller, 'getDocument', ['docID' => 2, 'personID' => 0, 'famID' => 0]);
    $db->exec("CREATE TRIGGER reject_revision BEFORE INSERT ON note_revision WHEN NEW.title = 'FAIL' BEGIN SELECT RAISE(ABORT, 'revision failed'); END");
    $failed = false;
    try {
        callEndpoint($controller, 'updateDocument', [
            'docID' => 2, 'title' => 'FAIL', 'type' => 'note', 'text' => 'changed',
            'bPrivate' => true, 'version' => $current->body['version']
        ]);
    } catch (\Throwable $error) {
        $failed = true;
    }
    $statement = $db->prepare('SELECT nte_Title FROM note_nte WHERE nte_ID = ?');
    $statement->execute([2]);
    expect($failed && $statement->fetchColumn() === 'New', 'A failed revision rolls back the note update');
    $db->exec('DELETE FROM note_nte WHERE nte_ID = 2');
    $statement = $db->prepare('SELECT COUNT(*) FROM note_revision WHERE note_id = ?');
    $statement->execute([2]);
    expect((int)$statement->fetchColumn() === 0, 'Deleting a note deletes its versions');
    echo "Note version tests passed\n";
}
