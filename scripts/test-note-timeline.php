<?php

namespace EcclesiaCRM {
    class SessionUser
    {
        public static $user;
        public static function getUser() { return self::$user; }
    }
    class Person
    {
        const SELF_REGISTER = -1;
        const SELF_VERIFY = -2;
    }
    class PersonQuery
    {
        public static function create() { return new self(); }
        public function findPk($id) { return null; }
        public function findOneById($id) { return null; }
    }
    class FamilyQuery
    {
        public static function create() { return new self(); }
        public function findPk($id) { return null; }
    }
    class Note
    {
        public function __construct(private bool $private, private array $viewers) {}
        public function isNoteDocument() { return true; }
        public function isPrivate() { return $this->private; }
        public function canViewNote($id) { return in_array($id, $this->viewers, true); }
        public function canEditNote($id) { return $id === 7; }
        public function isCreator($id) { return $id === 7; }
        public function getId() { return 1; }
        public function getPerId() { return 10; }
        public function getFamId() { return 0; }
        public function getType() { return 'note'; }
        public function getTitle() { return 'Title'; }
        public function getText() { return 'Content'; }
        public function getEnteredBy() { return 7; }
        public function getDisplayEditedBy() { return 7; }
        public function getDisplayEditedDate($format = '') { return $format === 'Y' ? '2026' : '2026-11-20 12:00:00'; }
        public function getEditLink() { return 'edit'; }
        public function getDeleteLink() { return 'delete'; }
        public function getInfo() { return ''; }
        public function isShared() { return count($this->viewers) > 1; }
    }
}

namespace EcclesiaCRM\dto {
    class SystemURLs
    {
        public static string $root;
        public static function getDocumentRoot() { return self::$root; }
    }
}

namespace EcclesiaCRM\Utils {
    class OutputUtils
    {
        public static function FormatDate($date, $time) { return $date; }
    }
}

namespace {
    use EcclesiaCRM\Note;
    use EcclesiaCRM\SessionUser;
    use EcclesiaCRM\Service\TimelineService;
    use EcclesiaCRM\dto\SystemURLs;

    $root = sys_get_temp_dir() . '/note-timeline-' . bin2hex(random_bytes(8));
    mkdir($root . '/Include', 0700, true);
    file_put_contents($root . '/Include/Functions.php', '<?php');
    SystemURLs::$root = $root;
    try {
        require __DIR__ . '/../src/EcclesiaCRM/Service/TimelineService.php';
        SessionUser::$user = new class {
            public int $id = 7;
            public function getPersonId() { return $this->id; }
        };
        $service = new TimelineService();
        $private = new Note(true, [7, 8]);
        $public = new Note(false, [7, 8, 9]);
        if (!$service->noteToTimelineItem($private)['isPrivate']
            || $service->noteToTimelineItem($public)['isPrivate']) {
            throw new \RuntimeException('Timeline must distinguish private and public notes');
        }
        SessionUser::$user->id = 8;
        if (!$service->noteToTimelineItem($private)['isPrivate']) {
            throw new \RuntimeException('An authorized recipient must see the private indicator');
        }
        SessionUser::$user->id = 9;
        if ($service->noteToTimelineItem($private) !== null) {
            throw new \RuntimeException('A private note must remain hidden from other users');
        }
        echo "Note timeline tests passed\n";
    } finally {
        unlink($root . '/Include/Functions.php');
        rmdir($root . '/Include');
        rmdir($root);
    }
}
