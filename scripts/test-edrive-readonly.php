<?php

namespace Slim\Http {
    class ServerRequest
    {
        public function __construct(private array $body = []) {}
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

namespace EcclesiaCRM {
    class User
    {
        public string $currentPath = '/';
        public function __construct(private string $name, private string $root) {}
        public function getUserName(): string { return $this->name; }
        public function getUserRootDir(): string { return $this->root; }
        public function getUserDir(): string { return $this->root . '/' . $this->name; }
        public function getCurrentpath(): string { return $this->currentPath; }
        public function getPersonId(): int { return 7; }
        public function isEDriveEnabled(): bool { return true; }
    }
    class SessionUser
    {
        public static User $user;
        public static function getUser(): User { return self::$user; }
        public static function getId(): int { return 7; }
    }
    class UserQuery
    {
        public static function create(): self { return new self(); }
        public function findPk($id): ?User { return $id == 7 ? SessionUser::$user : null; }
        public function findOneByPersonId($id): ?User { return $this->findPk($id); }
    }
    class NoteQuery
    {
        public static function create(): self { return new self(); }
        public function filterByPerId($id): self { return $this; }
        public function findOneByText($text) { return null; }
    }
}

namespace EcclesiaCRM\Base {
    class UserQuery
    {
        public static function create(): self { return new self(); }
        public function findOneByUserName($name) { return \EcclesiaCRM\SessionUser::$user; }
        public function findOneByPersonId($id) { return new \EcclesiaCRM\User('owner', 'private/userdir/owner-key'); }
    }
    class CollectionsQuery
    {
        public static function create(): self { return new self(); }
        public function findOneByOwnerpath($path) { return null; }
    }
    class CollectionsinstancesQuery
    {
        public static array $shares = [];
        public static function create(): self { return new self(); }
        public function findOneByGuestpath($path) { return self::$shares[$path] ?? null; }
    }
    class TestCollection
    {
        public function getOwnerpath(): string { return 'private/userdir/owner-key/owner/folder'; }
        public function getPerson() { return new class { public function getId(): int { return 8; } }; }
    }
    class TestShare
    {
        public function __construct(private int $access) {}
        public function getCollections(): TestCollection { return new TestCollection(); }
        public function getAccess(): int { return $this->access; }
    }
}

namespace EcclesiaCRM\dto {
    class SystemURLs
    {
        public static string $root;
        public static function getDocumentRoot(): string { return self::$root; }
        public static function getRootPath(): string { return ''; }
    }
    class SystemConfig
    {
        public static function getValue($key): string { return 'Y-m-d'; }
    }
}

namespace EcclesiaCRM\Service {
    class CollaboraService
    {
        public function isEditable(string $name): bool { return true; }
    }
}

namespace EcclesiaCRM\Utils {
    class MiscUtils
    {
        public static function FileIcon($name): string { return 'icon'; }
        public static function FileSizeConvert($size): string { return (string)$size; }
        public static function convertUTF8AccentuedString2Unicode($value): string { return $value; }
    }
}

namespace Sabre\DAV\Sharing {
    class Plugin
    {
        public const ACCESS_READ = 2;
        public const ACCESS_READWRITE = 3;
    }
}

namespace {
    use EcclesiaCRM\APIControllers\DocumentFileManagerController;
    use EcclesiaCRM\APIControllers\DocumentShareController;
    use EcclesiaCRM\Base\CollectionsinstancesQuery;
    use EcclesiaCRM\Base\TestShare;
    use EcclesiaCRM\dto\SystemURLs;
    use EcclesiaCRM\SessionUser;
    use EcclesiaCRM\User;
    use Slim\Http\Response;
    use Slim\Http\ServerRequest;

    require __DIR__ . '/../src/EcclesiaCRM/utils/DocumentSecurityUtils.php';
    require __DIR__ . '/../src/EcclesiaCRM/utils/SabreUtils.php';
    require __DIR__ . '/../src/EcclesiaCRM/APIControllers/DocumentFileManagerController.php';
    require __DIR__ . '/../src/EcclesiaCRM/APIControllers/DocumentShareController.php';

    function expect(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $root = sys_get_temp_dir() . '/edrive-readonly-' . bin2hex(random_bytes(8));
    SystemURLs::$root = $root;
    mkdir($root . '/private/userdir/key/alice', 0777, true);
    mkdir($root . '/shared/sub', 0777, true);
    file_put_contents($root . '/shared/sub/file.docx', 'document');
    symlink($root . '/shared', $root . '/private/userdir/key/alice/read');
    symlink($root . '/shared', $root . '/private/userdir/key/alice/write');
    SessionUser::$user = new User('alice', 'private/userdir/key');
    CollectionsinstancesQuery::$shares = [
        'private/userdir/key/alice/read' => new TestShare(2),
        'private/userdir/key/alice/write' => new TestShare(3)
    ];

    try {
        $fileManager = (new \ReflectionClass(DocumentFileManagerController::class))->newInstanceWithoutConstructor();
        $shareController = (new \ReflectionClass(DocumentShareController::class))->newInstanceWithoutConstructor();
        $canShare = new \ReflectionMethod(DocumentShareController::class, 'canModifySabreShares');
        foreach (['read' => true, 'write' => false] as $folder => $locked) {
            SessionUser::$user->currentPath = '/' . $folder . '/sub/';
            $response = $fileManager->getAllFileNoteForPerson(new ServerRequest(), new Response(), ['personID' => 7]);
            $item = $response->body['files'][0];
            expect($item['locked'] === $locked && $item['canEdit'] === !$locked, 'Incorrect inherited rights for ' . $folder);
            $params = (object)['currentPersonID' => 7, 'rows' => [['path' => 'alice/' . $folder . '/sub/file.docx']]];
            expect($canShare->invoke($shareController, $params) === !$locked, 'Incorrect sharing rights for ' . $folder);
        }

        SessionUser::$user->currentPath = '/read/sub/';
        $response = $fileManager->deleteFiles(new ServerRequest(['personID' => 7, 'files' => ['file.docx']]), new Response(), []);
        expect(!$response->body['success'] && is_file($root . '/shared/sub/file.docx'), 'Read-only deletion was not blocked');
        $rows = [['path' => 'alice/write/sub/file.docx'], ['path' => 'alice/read/sub/file.docx']];
        expect(!$canShare->invoke($shareController, (object)['currentPersonID' => 7, 'rows' => $rows]), 'Mixed selection was allowed');
        expect(!$canShare->invoke($shareController, (object)['currentPersonID' => 8, 'rows' => [$rows[0]]]), 'Wrong user was allowed');
        expect(!$canShare->invoke($shareController, (object)['currentPersonID' => 7, 'rows' => [['path' => 'alice/../other/file']]]), 'Traversal was allowed');
        foreach (['addPersonSabreToShare', 'addFamilyToShareSabre', 'addGroupToShareSabre',
            'setRightsSabreForPerson', 'deletePersonSabreFromShare', 'cleardocumentsabre'] as $method) {
            $response = $shareController->$method(new ServerRequest(['currentPersonID' => 7, 'rows' => [$rows[1]]]), new Response(), []);
            expect($response->status === 403, $method . ' did not reject read-only access');
        }
        echo "eDrive read-only tests passed\n";
    } finally {
        unlink($root . '/private/userdir/key/alice/read');
        unlink($root . '/private/userdir/key/alice/write');
        unlink($root . '/shared/sub/file.docx');
        rmdir($root . '/shared/sub');
        rmdir($root . '/shared');
        rmdir($root . '/private/userdir/key/alice');
        rmdir($root . '/private/userdir/key');
        rmdir($root . '/private/userdir');
        rmdir($root . '/private');
        rmdir($root);
    }
}
