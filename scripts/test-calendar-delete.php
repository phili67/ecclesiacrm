<?php

require __DIR__.'/../src/vendor/autoload.php';

use EcclesiaCRM\MyPDO\CalDavPDO;
use Sabre\DAV\Exception\NotFound;

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE calendars (id INTEGER PRIMARY KEY)');
$pdo->exec('CREATE TABLE calendarinstances (id INTEGER PRIMARY KEY, calendarid INTEGER, access INTEGER)');
$pdo->exec('CREATE TABLE events_event (event_calendarid INTEGER)');
$pdo->exec('CREATE TABLE calendarchanges (calendarid INTEGER)');
$pdo->exec('INSERT INTO calendars VALUES (1), (2)');
$pdo->exec('INSERT INTO calendarinstances VALUES (1, 1, 1), (3, 1, 2), (4, 2, 1)');
$pdo->exec('INSERT INTO events_event VALUES (1), (2)');
$pdo->exec('INSERT INTO calendarchanges VALUES (1), (2)');

$backend = new CalDavPDO($pdo);
foreach ([[2, 1], [2, 3], [1, 999]] as $ids) {
    try {
        $backend->deleteCalendar($ids);
        throw new RuntimeException('Mismatched or missing instance was accepted');
    } catch (NotFound $e) {
    }
}
if ((int)$pdo->query('SELECT COUNT(*) FROM calendars')->fetchColumn() !== 2
    || (int)$pdo->query('SELECT COUNT(*) FROM calendarinstances')->fetchColumn() !== 3
    || (int)$pdo->query('SELECT COUNT(*) FROM events_event')->fetchColumn() !== 2) {
    throw new RuntimeException('Rejected deletion modified calendar data');
}

$backend->deleteCalendar([1, 3]);
if ((int)$pdo->query('SELECT COUNT(*) FROM calendarinstances WHERE id = 3')->fetchColumn() !== 0
    || (int)$pdo->query('SELECT COUNT(*) FROM events_event WHERE event_calendarid = 1')->fetchColumn() !== 1) {
    throw new RuntimeException('Deleting a shared instance affected its calendar');
}

$backend->deleteCalendar([1, 1]);
if ((int)$pdo->query('SELECT COUNT(*) FROM calendars WHERE id = 1')->fetchColumn() !== 0
    || (int)$pdo->query('SELECT COUNT(*) FROM events_event WHERE event_calendarid = 1')->fetchColumn() !== 0
    || (int)$pdo->query('SELECT COUNT(*) FROM calendarchanges WHERE calendarid = 1')->fetchColumn() !== 0
    || (int)$pdo->query('SELECT COUNT(*) FROM calendars WHERE id = 2')->fetchColumn() !== 1) {
    throw new RuntimeException('Owner deletion did not preserve the other calendar');
}

echo "Calendar deletion tests passed\n";
