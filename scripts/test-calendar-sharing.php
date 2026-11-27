<?php

namespace Psr\Container {
    interface ContainerInterface { public function get(string $id); }
}

namespace Slim\Http {
    class ServerRequest {
        public function __construct(private array $body) {}
        public function getParsedBody(): array { return $this->body; }
    }
    class Response {
        public int $status = 200;
        public $body;
        public function withJson($body, $status = 200): self {
            $this->body = $body;
            $this->status = $status;
            return $this;
        }
    }
}

namespace Sabre\DAV\Sharing {
    class Plugin {
        public const ACCESS_SHAREDOWNER = 1;
        public const ACCESS_READ = 2;
        public const ACCESS_READWRITE = 3;
        public const ACCESS_NOACCESS = 4;
        public const INVITE_ACCEPTED = 2;
    }
}

namespace Sabre\DAV\Xml\Element {
    class Sharee {
        public $href;
        public $principal;
        public $access;
        public $inviteStatus;
        public $properties;
        public function __construct(array $values) {
            foreach ($values as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}

namespace EcclesiaCRM {
    class User {
        public function __construct(private string $name) {}
        public function getUserName(): string { return $this->name; }
        public function getEmail(): string { return $this->name.'@example.test'; }
        public function getFullName(): string { return $this->name; }
    }
    class SessionUser {
        public static string $name = 'owner';
        public static function getUser(): User { return new User(self::$name); }
    }
    class Calendarinstances {
        public $visible = 0;
        public $present = 0;
        public int $saves = 0;
        public function __construct(public string $calendar, public string $id, public int $access, public string $principal) {}
        public function getAccess(): int { return $this->access; }
        public function setVisible($value): void { $this->visible = $value; }
        public function setPresent($value): void { $this->present = $value; }
        public function save(): void { $this->saves++; }
    }
    class CalendarinstancesQuery {
        public static array $instances = [];
        private array $filters = [];
        public static function create(): self { return new self(); }
        public function filterByCalendarid($id): self { $this->filters['calendar'] = $id; return $this; }
        public function filterById($id): self { $this->filters['id'] = $id; return $this; }
        public function filterByPrincipaluri($principal): self { $this->filters['principal'] = $principal; return $this; }
        public function findOne() {
            foreach (self::$instances as $instance) {
                if ($this->filters === ['calendar' => $instance->calendar, 'id' => $instance->id, 'principal' => $instance->principal]) {
                    return $instance;
                }
            }
            return null;
        }
    }
    class UserQuery {
        public static function create(): self { return new self(); }
        public function findOneByPersonId($id): ?User {
            return [1 => new User('OWNER'), 2 => new User('other')][$id] ?? null;
        }
    }
    class Person {
        public function __construct(private int $id) {}
        public function getId(): int { return $this->id; }
        public function getPersonId(): int { return $this->id; }
    }
    class FamilyQuery {
        public static function create(): self { return new self(); }
        public function findOneById($id): self { return $this; }
        public function getActivatedPeople(): array { return [new Person(1), new Person(2), new Person(3)]; }
    }
    class GroupQuery {
        public static function create(): self { return new self(); }
        public function findOneById($id): self { return $this; }
    }
    class Person2group2roleP2g2rQuery {
        public static function create(): self { return new self(); }
        public function findByGroupId($id): array { return [new Person(1), new Person(2), new Person(3)]; }
    }
}

namespace EcclesiaCRM\MyPDO {
    class CalDavPDO {
        public static array $updates = [];
        public static array $deletes = [];
        public static $invites = '';
        public function updateInvites($id, array $shares): void { self::$updates[] = [$id, $shares]; }
        public function getInvites($id) { return self::$invites; }
        public function deleteCalendar($id): void { self::$deletes[] = $id; }
    }
}

namespace EcclesiaCRM\Emails {
    class CalendarEmail {
        public static array $recipients = [];
        public function __construct(private $user, $message) {}
        public function send(): void { self::$recipients[] = $this->user->getUserName(); }
    }
}

namespace {
    use EcclesiaCRM\APIControllers\CalendarV2Controller;
    use EcclesiaCRM\Calendarinstances;
    use EcclesiaCRM\CalendarinstancesQuery;
    use EcclesiaCRM\Emails\CalendarEmail;
    use EcclesiaCRM\MyPDO\CalDavPDO;
    use EcclesiaCRM\SessionUser;
    use Psr\Container\ContainerInterface;
    use Slim\Http\Response;
    use Slim\Http\ServerRequest;

    require __DIR__.'/../src/EcclesiaCRM/APIControllers/CalendarV2Controller.php';

    function check($condition, string $message): void {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $container = new class implements ContainerInterface {
        public function get(string $id) {}
    };
    $controller = new CalendarV2Controller($container);
    $ownerInstance = new Calendarinstances('10', '20', 1, 'principals/owner');
    $sharedInstance = new Calendarinstances('10', '21', 2, 'principals/other');
    $otherCalendar = new Calendarinstances('11', '22', 1, 'principals/other');
    CalendarinstancesQuery::$instances = [$ownerInstance, $sharedInstance, $otherCalendar];
    $call = function (string $method, array $body) use ($controller): Response {
        return $controller->$method(new ServerRequest($body), new Response(), []);
    };
    $person = ['calIDs' => '10,20', 'personID' => 2, 'notification' => 1];

    foreach (['other', 'owner'] as $name) {
        SessionUser::$name = $name;
        foreach (['10,21', '11,20', '10,20,30', '10'] as $ids) {
            $response = $call('shareCalendarPerson', array_replace($person, ['calIDs' => $ids]));
            check($response->status === 403, 'Invalid or mismatched calendar identifiers were accepted');
        }
    }
    SessionUser::$name = 'other';
    foreach ([
        'shareCalendarPerson' => $person,
        'shareCalendarFamily' => ['calIDs' => '10,20', 'familyID' => 1, 'notification' => 1],
        'shareCalendarGroup' => ['calIDs' => '10,20', 'groupID' => 1, 'notification' => 1],
        'shareCalendarDelete' => ['calIDs' => '10,20', 'principal' => 'principals/other'],
        'shareCalendarStop' => ['calIDs' => '10,20'],
        'setCalendarRights' => ['calIDs' => '10,20', 'principal' => 'principals/other', 'rightAccess' => 2],
    ] as $method => $body) {
        check($call($method, $body)->status === 403, "$method allowed a non-owner");
    }
    check(CalDavPDO::$updates === [] && CalendarEmail::$recipients === [], 'Rejected shares had side effects');

    SessionUser::$name = 'owner';
    check($call('shareCalendarPerson', array_replace($person, ['personID' => 1]))->status === 400, 'Self-sharing was accepted');
    check($call('setCalendarRights', ['calIDs' => '10,20', 'principal' => 'principals/OWNER', 'rightAccess' => 2])->status === 400, 'Owner access was changed');
    check(CalDavPDO::$updates === [] && CalendarEmail::$recipients === [], 'Self-sharing had side effects');

    check($call('shareCalendarPerson', $person)->status === 200, 'Owner could not share with another person');
    foreach ([
        'shareCalendarFamily' => ['calIDs' => '10,20', 'familyID' => 1, 'notification' => 1],
        'shareCalendarGroup' => ['calIDs' => '10,20', 'groupID' => 1, 'notification' => 1],
    ] as $method => $body) {
        check($call($method, $body)->status === 200, "$method failed for owner");
    }
    check(count(CalDavPDO::$updates) === 3, 'Unexpected number of shares');
    foreach (CalDavPDO::$updates as [$ids, $shares]) {
        check($ids === ['10', '20'] && $shares[0]->principal === 'principals/other', 'Wrong share recipient');
    }
    check(CalendarEmail::$recipients === ['other', 'other', 'other'], 'Unexpected notification recipients');

    $invite = new \Sabre\DAV\Xml\Element\Sharee(['principal' => 'principals/other', 'access' => 2]);
    $otherInvite = new \Sabre\DAV\Xml\Element\Sharee(['principal' => 'principals/third', 'access' => 2]);
    CalDavPDO::$invites = [$invite, $otherInvite];
    check($call('setCalendarRights', ['calIDs' => '10,20', 'principal' => 'principals/other', 'rightAccess' => 2])->status === 200
        && $invite->access === 3, 'Owner could not change a share recipient\'s rights');
    $lastUpdate = CalDavPDO::$updates[count(CalDavPDO::$updates) - 1];
    check(count($lastUpdate[1]) === 1 && $lastUpdate[1][0] === $invite,
        'Changing rights also updated unrelated invitations');
    $updates = count(CalDavPDO::$updates);
    check($call('setCalendarRights', ['calIDs' => '10,20', 'principal' => 'principals/other', 'rightAccess' => 0])->status === 400
        && count(CalDavPDO::$updates) === $updates, 'Invalid rights were accepted');
    check($call('setCalendarRights', ['calIDs' => '10,20', 'principal' => 'principals/missing', 'rightAccess' => 1])->status === 404
        && count(CalDavPDO::$updates) === $updates, 'Missing share was accepted');

    SessionUser::$name = 'other';
    check($call('setCheckedCalendar', ['calIDs' => '10,21', 'isChecked' => 1])->status === 200
        && $sharedInstance->visible === 1, 'Shared instance could not be made visible');
    $invite->access = 2;
    $updates = count(CalDavPDO::$updates);
    check($call('setCalendarRights', ['calIDs' => '10,20', 'principal' => 'principals/other', 'rightAccess' => 2])->status === 403
        && $invite->access === 2 && count(CalDavPDO::$updates) === $updates,
        'Shared user changed the owner calendar rights');
    check($call('setCalendarRights', ['calIDs' => '10,21', 'principal' => 'principals/other', 'rightAccess' => 2])->status === 403
        && $invite->access === 2 && count(CalDavPDO::$updates) === $updates,
        'Shared user changed rights through their own instance');
    check($call('deleteCalendar', ['calIDs' => '10,20'])->status === 403 && CalDavPDO::$deletes === [],
        'Shared user deleted the owner calendar');

    foreach (['10,20', '11,21', '10,21,22', '10', ['10', '21']] as $invalid) {
        check($call('setCheckedCalendar', ['calIDs' => $invalid, 'isChecked' => 0])->status === 403, 'Invalid visibility change was accepted');
        check($call('showHideCalendars', ['calIDs' => $invalid, 'isPresent' => 1])->status === 403, 'Invalid presence change was accepted');
        check($call('deleteCalendar', ['calIDs' => $invalid])->status === 403, 'Invalid deletion was accepted');
    }
    $saves = $sharedInstance->saves;
    check($call('setCheckedSelectedCalendar', ['allCalIDs' => ['10,21', '10,20'], 'isChecked' => 0])->status === 403
        && $sharedInstance->saves === $saves, 'A mixed batch made a partial visibility change');
    check($call('setCheckedSelectedCalendar', ['allCalIDs' => '10,21', 'isChecked' => 0])->status === 400,
        'Malformed visibility batch was accepted');
    check($call('setCheckedSelectedCalendar', ['allCalIDs' => ['10,21', '11,22'], 'isChecked' => 0])->status === 200
        && $sharedInstance->visible === 0 && $otherCalendar->visible === 0, 'Valid visibility batch failed');
    check($call('showHideCalendars', ['calIDs' => '10,21', 'isPresent' => 1])->status === 200
        && $sharedInstance->present === 1, 'Shared instance presence could not be changed');
    check($call('deleteCalendar', ['calIDs' => '10,21'])->status === 200
        && CalDavPDO::$deletes === [['10', '21']], 'User could not remove their shared instance');
    SessionUser::$name = 'owner';
    check($call('deleteCalendar', ['calIDs' => '10,20'])->status === 200
        && CalDavPDO::$deletes[1] === ['10', '20'], 'Owner could not delete their calendar');

    echo "Calendar sharing tests passed\n";
}
