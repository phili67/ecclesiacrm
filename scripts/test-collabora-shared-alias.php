<?php

namespace EcclesiaCRM {
    class User
    {
        public function __construct(private string $directory, private string $publicDirectory = '')
        {
        }

        public function getUserDir(): string { return $this->directory; }
        public function getUserPublicDir(): string { return $this->publicDirectory; }
        public function getUserRootDir(): string { return dirname($this->directory); }
        public function getUserName(): string { return basename($this->directory); }
    }

    class SessionUser
    {
        public static function getId(): int { return 7; }
    }

    class CollectionsinstancesQuery
    {
        public static array $shares = [];
        public static function create(): self { return new self(); }
        public function findOneByGuestpath(string $path) { return self::$shares[$path] ?? null; }
    }

    class TestShare
    {
        public function __construct(private int $id, private int $guestId = 7) {}
        public function getId(): int { return $this->id; }
        public function getGuestid(): int { return $this->guestId; }
    }
}

namespace EcclesiaCRM\dto {
    class SystemURLs
    {
        public static string $root;
        public static function getDocumentRoot(): string { return self::$root; }
    }
}

namespace EcclesiaCRM\Service {
    class SharedDocumentService
    {
        public static array $documents = [];
        public function resolve(int $shareId, int $personId): ?array
        {
            return self::$documents[$shareId] ?? null;
        }
    }
}

namespace {
    use EcclesiaCRM\APIControllers\DocumentCollaboraController;
    use EcclesiaCRM\CollectionsinstancesQuery;
    use EcclesiaCRM\dto\SystemURLs;
    use EcclesiaCRM\Service\CollaboraService;
    use EcclesiaCRM\Service\SharedDocumentService;
    use EcclesiaCRM\TestShare;
    use EcclesiaCRM\User;

    require __DIR__ . '/../src/EcclesiaCRM/utils/DocumentSecurityUtils.php';
    require __DIR__ . '/../src/EcclesiaCRM/Service/CollaboraService.php';
    require __DIR__ . '/../src/EcclesiaCRM/APIControllers/DocumentCollaboraController.php';

    function expect(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $root = sys_get_temp_dir() . '/collabora-alias-' . bin2hex(random_bytes(8));
    SystemURLs::$root = $root;
    mkdir($root . '/guest/user', 0777, true);
    mkdir($root . '/owner/user/folder/sub', 0777, true);
    mkdir($root . '/public', 0777, true);
    file_put_contents($root . '/owner/user/folder/sub/file.docx', 'document');
    file_put_contents($root . '/owner/user/outside.docx', 'outside');
    symlink($root . '/owner/user/folder', $root . '/guest/user/share');
    symlink($root . '/owner/user/folder/sub/file.docx', $root . '/guest/user/direct.docx');
    symlink($root . '/owner/user/outside.docx', $root . '/owner/user/folder/escape.docx');
    symlink($root . '/public', $root . '/guest/user/public');
    symlink($root . '/owner/user/folder', $root . '/public/share');

    try {
        $guest = new User('guest/user', 'public');
        $owner = new User('owner/user');
        $share = new TestShare(1);
        CollectionsinstancesQuery::$shares = [
            'guest/user/share' => $share,
            'guest/user/public/share' => $share,
            'guest/user/direct.docx' => new TestShare(2)
        ];
        SharedDocumentService::$documents[1] = [
            'owner' => $owner, 'ownerId' => 8, 'relativePath' => 'folder',
            'path' => realpath($root . '/owner/user/folder'), 'name' => 'share',
            'isDirectory' => true, 'canWrite' => true
        ];
        SharedDocumentService::$documents[2] = [
            'owner' => $owner, 'ownerId' => 8, 'relativePath' => 'folder/sub/file.docx',
            'path' => realpath($root . '/owner/user/folder/sub/file.docx'), 'name' => 'direct.docx',
            'isDirectory' => false, 'canWrite' => true
        ];
        $controller = (new \ReflectionClass(DocumentCollaboraController::class))->newInstanceWithoutConstructor();
        $resolve = new \ReflectionMethod(DocumentCollaboraController::class, 'resolveSharedAlias');
        $collabora = new CollaboraService();

        foreach (['share/sub/file.docx', 'public/share/sub/file.docx'] as $alias) {
            $file = $resolve->invoke($controller, $guest, $alias);
            expect($file !== null, 'Shared folder file was not resolved: ' . $alias);
            expect($file['relativePath'] === 'folder/sub/file.docx', 'Incorrect owner-relative path');
            expect($file['name'] === 'file.docx' && $file['canWrite'], 'Incorrect file metadata');
            expect($collabora->resolveUserFile($owner, $file['relativePath'])[0] === $file['path'], 'Owner path mismatch');
        }

        $direct = $resolve->invoke($controller, $guest, 'direct.docx');
        expect($direct !== null && $direct['name'] === 'direct.docx', 'Direct file share no longer resolves');
        SharedDocumentService::$documents[2]['path'] = realpath($root . '/owner/user/outside.docx');
        expect($resolve->invoke($controller, $guest, 'direct.docx') === null, 'Mismatched share target was accepted');
        expect($resolve->invoke($controller, $guest, 'share/escape.docx') === null, 'Symlink escaped the share');
        expect($resolve->invoke($controller, $guest, 'share/../outside.docx') === null, 'Traversal was accepted');
        expect($resolve->invoke($controller, $guest, 'share/missing.docx') === null, 'Missing file was accepted');
        SharedDocumentService::$documents[1]['canWrite'] = false;
        expect(!$resolve->invoke($controller, $guest, 'share/sub/file.docx')['canWrite'], 'Read-only access was elevated');
        unset(SharedDocumentService::$documents[1]);
        expect($resolve->invoke($controller, $guest, 'share/sub/file.docx') === null, 'Revoked share was accepted');
        CollectionsinstancesQuery::$shares['guest/user/share'] = new TestShare(1, 99);
        expect($resolve->invoke($controller, $guest, 'share/sub/file.docx') === null, 'Wrong guest was accepted');
        echo "Collabora shared alias tests passed\n";
    } finally {
        unlink($root . '/public/share');
        unlink($root . '/guest/user/public');
        unlink($root . '/owner/user/folder/escape.docx');
        unlink($root . '/guest/user/direct.docx');
        unlink($root . '/guest/user/share');
        unlink($root . '/owner/user/outside.docx');
        unlink($root . '/owner/user/folder/sub/file.docx');
        rmdir($root . '/owner/user/folder/sub');
        rmdir($root . '/owner/user/folder');
        rmdir($root . '/owner/user');
        rmdir($root . '/owner');
        rmdir($root . '/guest/user');
        rmdir($root . '/guest');
        rmdir($root . '/public');
        rmdir($root);
    }
}
