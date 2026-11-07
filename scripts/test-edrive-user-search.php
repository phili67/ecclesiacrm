<?php

namespace Psr\Container {
    interface ContainerInterface
    {
        public function get(string $id);
    }
}

namespace Slim\Http {
    class ServerRequest
    {
        public function getParsedBody(): array { return []; }
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
        public function withStatus(int $status): self
        {
            $this->status = $status;
            return $this;
        }
    }
}

namespace Propel\Runtime\ActiveQuery {
    class Criteria
    {
        public const LIKE = 'LIKE';
        public const NOT_EQUAL = '<>';
        public const LOGICAL_OR = 'OR';
    }
}

namespace EcclesiaCRM {
    class SessionUser
    {
        public static int $id = 7;
        public static bool $eDriveEnabled = true;
        public static function getId(): int { return self::$id; }
        public static function getUser(): self { return new self(); }
        public function isEDriveEnabled(): bool { return SessionUser::$eDriveEnabled; }
    }

    class Person
    {
        public function __construct(private int $id, private string $name) {}
        public function getId(): int { return $this->id; }
        public function getFullName(): string { return $this->name; }
        public function getDateDeactivated() { return null; }
    }

    class PersonQuery
    {
        public static array $people = [];
        public static array $calls = [];
        private array $excludedIds = [];

        public static function create(): self
        {
            self::$calls = [];
            return new self();
        }
        public function condition(string $name, string $clause, $value): self
        {
            self::$calls[] = ['condition', $name, $clause, $value];
            return $this;
        }
        public function where($conditions, $operator): self
        {
            self::$calls[] = ['where', $conditions, $operator];
            return $this;
        }
        public function filterById(int $id, string $comparison): self
        {
            self::$calls[] = ['exclude', $id, $comparison];
            $this->excludedIds[] = $id;
            return $this;
        }
        public function filterByFirstName(string $value, string $comparison): self { return $this; }
        public function _or(): self { return $this; }
        public function filterByLastName(string $value, string $comparison): self { return $this; }
        public function limit(int $limit): self { return $this; }
        public function find(): array
        {
            return array_values(array_filter(self::$people, fn($person) => !in_array($person->getId(), $this->excludedIds, true)));
        }
    }
}

namespace EcclesiaCRM\dto {
    class SystemConfig
    {
        public static function getValue(string $key): int { return 50; }
        public static function getBooleanValue(string $key): bool { return false; }
    }

    class SystemURLs
    {
        public static function getRootPath(): string { return ''; }
    }
}

namespace {
    use EcclesiaCRM\Person;
    use EcclesiaCRM\PersonQuery;
    use EcclesiaCRM\SessionUser;
    use Psr\Container\ContainerInterface;
    use Slim\Http\Response;
    use Slim\Http\ServerRequest;

    require __DIR__ . '/../src/EcclesiaCRM/APIControllers/PeopleController.php';

    $container = new class implements ContainerInterface {
        public function get(string $id)
        {
            return new class {
                public function warn(string $message): void {}
            };
        }
    };
    $controller = new \EcclesiaCRM\APIControllers\PeopleController($container);
    PersonQuery::$people = [new Person(7, 'Current User'), new Person(8, 'Other User')];

    $response = $controller->searchpeople(new ServerRequest(), new Response(), [
        'query' => 'User', 'type' => 'edrive-share'
    ]);
    $results = $response->body[0]['children'] ?? [];
    if (count($results) !== 1 || $results[0]['personID'] !== 8
        || !in_array(['exclude', SessionUser::getId(), '<>'], PersonQuery::$calls, true)) {
        throw new \RuntimeException('eDrive search did not exclude the current person in the API query');
    }

    $response = $controller->searchpeople(new ServerRequest(), new Response(), [
        'query' => 'User', 'type' => 'person'
    ]);
    if (count($response->body[0]['children'] ?? []) !== 2) {
        throw new \RuntimeException('The general people search was unexpectedly changed');
    }

    SessionUser::$eDriveEnabled = false;
    $response = $controller->searchpeople(new ServerRequest(), new Response(), [
        'query' => 'User', 'type' => 'edrive-share'
    ]);
    if ($response->status !== 403) {
        throw new \RuntimeException('eDrive search was not denied to a user without eDrive access');
    }

    echo "eDrive user search tests passed\n";
}
