<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without authorization
//
//  Updated : 2020/01/26
//

namespace EcclesiaCRM\MyPDO;

use EcclesiaCRM\EventQuery;
use EcclesiaCRM\MyVCalendar\VCalendarExtension;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use Sabre\CalDAV;
use Sabre\DAV;

use Sabre\VObject;

use EcclesiaCRM\Bootstrapper;

use Sabre\CalDAV\Backend as SabreCalDavBase;
use Sabre\DAV\UUIDUtil;


class CalDavPDO extends SabreCalDavBase\PDO
{
    function __construct($pdo=null)
    {
        if (is_null($pdo)) {
            $pdo = Bootstrapper::GetPDO();
        }

        parent::__construct($pdo);

        $this->calendarObjectTableName = 'events_event';
    }

    /**
     * getFullCalendar
     *
     * @param mixed $calendarId
     * @return array : all the informations relativ to $calendarId
     */

    function getFullCalendar($calendarId)
    {
        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }

        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->calendarInstancesTableName . ' WHERE calendarid = ? AND id = ?');
        $stmt->execute([$calendarId, $instanceId]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'id' => $row['id'],
            'uri' => $row['uri'],
            'lastmodified' => (int)$row['lastmodified'],
            'etag' => '"' . $row['etag'] . '"',
            'size' => (int)$row['size'],
            'calendardata' => $row['calendardata'],
            'description' => $row['description'],
            'present' => $row['present'],
            'visible' => $row['visible'],
            'grpid' => $row['grpid'],
            'cal_type' => $row['cal_type']
        ];
    }

    /**
     * Updates the list of shares.
     *
     * @param mixed $calendarId
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     * @return void
     */
    function updateInvites($calendarId, array $sharees)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        $currentInvites = $this->getInvites($calendarId);
        list($calendarId, $instanceId) = $calendarId;

        $removeStmt = $this->pdo->prepare("DELETE FROM " . $this->calendarInstancesTableName . " WHERE calendarid = ? AND share_href = ? AND access IN (2,3)");
        $updateStmt = $this->pdo->prepare("UPDATE " . $this->calendarInstancesTableName . " SET access = ?, share_displayname = ?, share_invitestatus = ? WHERE calendarid = ? AND share_href = ?");

        $insertStmt = $this->pdo->prepare('
INSERT INTO ' . $this->calendarInstancesTableName . '
    (
        calendarid,
        principaluri,
        access,
        displayname,
        grpid,
        cal_type,
        uri,
        description,
        calendarorder,
        calendarcolor,
        timezone,
        transparent,
        share_href,
        share_displayname,
        share_invitestatus
    )
    SELECT
        ?,
        ?,
        ?,
        displayname,
        grpid,
        cal_type,
        ?,
        description,
        calendarorder,
        calendarcolor,
        timezone,
        1,
        ?,
        ?,
        ?
    FROM ' . $this->calendarInstancesTableName . ' WHERE id = ?');

        foreach ($sharees as $sharee) {

            if ($sharee->access === \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS) {
                // if access was set no NOACCESS, it means access for an
                // existing sharee was removed.
                $removeStmt->execute([$calendarId, $sharee->href]);
                continue;
            }

            if (is_null($sharee->principal)) {
                // If the server could not determine the principal automatically,
                // we will mark the invite status as invalid.
                $sharee->inviteStatus = \Sabre\DAV\Sharing\Plugin::INVITE_INVALID;
            } else {
                // Because sabre/dav does not yet have an invitation system,
                // every invite is automatically accepted for now.
                $sharee->inviteStatus = \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED;
            }

            foreach ($currentInvites as $oldSharee) {

                if ($oldSharee->href === $sharee->href) {
                    // This is an update
                    $sharee->properties = array_merge(
                        $oldSharee->properties,
                        $sharee->properties
                    );
                    $updateStmt->execute([
                        $sharee->access,
                        isset($sharee->properties['{DAV:}displayname']) ? $sharee->properties['{DAV:}displayname'] : null,
                        $sharee->inviteStatus ?: $oldSharee->inviteStatus,
                        $calendarId,
                        $sharee->href
                    ]);
                    continue 2;
                }

            }
            // If we got here, it means it was a new sharee
            $insertStmt->execute([
                $calendarId,
                $sharee->principal,
                $sharee->access,
                \Sabre\DAV\UUIDUtil::getUUID(),
                $sharee->href,
                isset($sharee->properties['{DAV:}displayname']) ? $sharee->properties['{DAV:}displayname'] : null,
                $sharee->inviteStatus ?: \Sabre\DAV\Sharing\Plugin::INVITE_NORESPONSE,
                $instanceId
            ]);

        }

    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param mixed $calendarId
     * @return void
     */
    function deleteCalendar($calendarId)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT access FROM ' . $this->calendarInstancesTableName . ' where id = ?');
        $stmt->execute([$instanceId]);
        $access = (int)$stmt->fetchColumn();

        if ($access === \Sabre\DAV\Sharing\Plugin::ACCESS_SHAREDOWNER) {

            /**
             * If the user is the owner of the calendar, we delete all data and all
             * instances.
             **/
            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ?');
            $stmt->execute([$calendarId]);

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarChangesTableName . ' WHERE calendarid = ?');
            $stmt->execute([$calendarId]);

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarInstancesTableName . ' WHERE calendarid = ?');
            $stmt->execute([$calendarId]);

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarTableName . ' WHERE id = ?');
            $stmt->execute([$calendarId]);

        } else {

            /**
             * If it was an instance of a shared calendar, we only delete that
             * instance.
             */
            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarInstancesTableName . ' WHERE id = ?');
            $stmt->execute([$instanceId]);

        }
    }

    function searchAndDeleteOneEvent($vcalendar, $reccurenceID)
    {
        $i = 0;

        foreach ($vcalendar->VEVENT as $sevent) {
            if ($sevent->{'RECURRENCE-ID'} == (new \DateTime($reccurenceID))->format('Ymd\THis')) {
                $vcalendar->remove($vcalendar->VEVENT[$i]);
                break;
            }
            $i++;
        }
    }


    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri. This is just the 'base uri' or 'filename' of the calendar.
     *  * principaluri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * Many clients also require:
     * {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
     * For this property, you can just return an instance of
     * Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet.
     *
     * If you return {http://sabredav.org/ns}read-only and set the value to 1,
     * ACL will automatically be put in read-only mode.
     *
     * @param string $principalUri
     * @param bool $all (for all the calendar : order by type)
     * @return array
     */
    function getCalendarsForUser($principalUri, $all = false)
    {

        $fields = array_values($this->propertyMap);
        $fields[] = 'calendarid';
        $fields[] = 'uri';
        $fields[] = 'synctoken';
        $fields[] = 'components';
        $fields[] = 'principaluri';
        $fields[] = 'transparent';
        $fields[] = 'access';
        $fields[] = 'present';
        $fields[] = 'visible';
        $fields[] = 'grpid';
        $fields[] = 'cal_type';
        $fields[] = 'description';

        $ordering = 'displayname';//'calendarorder';

        if ($all) {// this is usefull for the calendar popup in the EventEditor window
            $ordering = 'cal_type';
        }

        // Making fields a comma-delimited list
        $fields = implode(', ', $fields);
        $stmt = $this->pdo->prepare(<<<SQL
SELECT {$this->calendarInstancesTableName}.id as id, $fields FROM {$this->calendarInstancesTableName}
    LEFT JOIN {$this->calendarTableName} ON
        {$this->calendarInstancesTableName}.calendarid = {$this->calendarTableName}.id
WHERE principaluri = ? ORDER BY cal_type ASC,$ordering ASC
SQL
        );
        $stmt->execute([$principalUri]);

        $calendars = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $components = [];
            if ($row['components']) {
                $components = explode(',', $row['components']);
            }

            $calendar = [
                'id' => [(int)$row['calendarid'], (int)$row['id']],
                'uri' => $row['uri'],
                'principaluri' => $row['principaluri'],
                '{' . CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => 'http://sabre.io/ns/sync/' . ($row['synctoken'] ? $row['synctoken'] : '0'),
                '{http://sabredav.org/ns}sync-token' => $row['synctoken'] ? $row['synctoken'] : '0',
                '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet($components),
                '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp($row['transparent'] ? 'transparent' : 'opaque'),
                'share-resource-uri' => '/ns/share/' . $row['calendarid'],
                'present' => $row['present'],
                'visible' => $row['visible'],
                'grpid' => $row['grpid'],
                'cal_type' => $row['cal_type'],
                'description' => $row['description'],
            ];

            $calendar['share-access'] = (int)$row['access'];
            // 1 = owner, 2 = readonly, 3 = readwrite
            if ($row['access'] > 1) {
                // We need to find more information about the original owner.
                //$stmt2 = $this->pdo->prepare('SELECT principaluri FROM ' . $this->calendarInstancesTableName . ' WHERE access = 1 AND id = ?');
                //$stmt2->execute([$row['id']]);

                // read-only is for backwards compatbility. Might go away in
                // the future.
                $calendar['read-only'] = (int)$row['access'] === \Sabre\DAV\Sharing\Plugin::ACCESS_READ;
            }

            foreach ($this->propertyMap as $xmlName => $dbName) {
                $calendar[$xmlName] = $row[$dbName];
            }

            $calendars[] = $calendar;

        }

        return $calendars;

    }

    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can
     *     be any arbitrary string, but making sure it ends with '.ics' is a
     *     good idea. This is only the basename, or filename, not the full
     *     path.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * size - The size of the calendar objects, in bytes.
     *   * component - optional, a string containing the type of object, such
     *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
     *     the Content-Type header.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * If neither etag or size are specified, the calendardata will be
     * used/fetched to determine these numbers. If both are specified the
     * amount of times this is needed is reduced by a great degree.
     *
     * @param mixed $calendarId
     * @return array
     */
    function getCalendarObjects($calendarId)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ?');
        $stmt->execute([$calendarId]);

        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'id' => $row['event_id'],
                'uri' => $row['event_uri'],
                'lastmodified' => (int)$row['event_lastmodified'],
                'etag' => '"' . $row['event_etag'] . '"',
                'size' => (int)$row['event_size'],
                'location' => $row['event_location'],
                'component' => strtolower($row['event_componenttype']),
            ];
        }

        return $result;

    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * This method must return null if the object did not exist.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @return array|null
     */
    function getCalendarObjectById($calendarId, $objectId)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_calendardata, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_id = ?');
        $stmt->execute([$calendarId, $objectId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'id' => $row['event_id'],
            'uri' => $row['event_uri'],
            'lastmodified' => (int)$row['event_lastmodified'],
            'etag' => '"' . $row['event_etag'] . '"',
            'size' => (int)$row['event_size'],
            'location' => $row['event_location'],
            'calendardata' => $row['event_calendardata'],
            'component' => strtolower($row['event_componenttype']),
        ];

    }

    function getCalendarObject($calendarId, $objectUri)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_calendardata, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$calendarId, $objectUri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'id' => $row['event_id'],
            'uri' => $row['event_uri'],
            'lastmodified' => (int)$row['event_lastmodified'],
            'etag' => '"' . $row['event_etag'] . '"',
            'size' => (int)$row['event_size'],
            'location' => $row['event_location'],
            'calendardata' => $row['event_calendardata'],
            'component' => strtolower($row['event_componenttype']),
        ];

    }

    /**
     * Returns a list of calendar objects.
     *
     * This method should work identical to getCalendarObject, but instead
     * return all the calendar objects in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $calendarId
     * @param array $uris
     * @return array
     */
    function getMultipleCalendarObjects($calendarId, array $uris)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $result = [];
        foreach (array_chunk($uris, 900) as $chunk) {
            $query = 'SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_calendardata, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_uri IN (';
            // Inserting a whole bunch of question marks
            $query .= implode(',', array_fill(0, count($chunk), '?'));
            $query .= ')';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_merge([$calendarId], $chunk));

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

                $result[] = [
                    'id' => $row['event_id'],
                    'uri' => $row['event_uri'],
                    'lastmodified' => (int)$row['event_lastmodified'],
                    'etag' => '"' . $row['event_etag'] . '"',
                    'size' => (int)$row['event_size'],
                    'location' => $row['event_location'],
                    'calendardata' => $row['event_calendardata'],
                    'component' => strtolower($row['event_componenttype']),
                ];

            }
        }
        return $result;

    }

    /**
     * Returns the list of people whom a calendar is shared with.
     *
     * Every item in the returned list must be a Sharee object with at
     * least the following properties set:
     *   $href
     *   $shareAccess
     *   $inviteStatus
     *
     * and optionally:
     *   $properties
     *
     * @param mixed $calendarId
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */

    function getByGroupid($groupID)
    {
        $query = <<<SQL
SELECT
    calendarid, id
FROM {$this->calendarInstancesTableName}
WHERE
    grpid = ?
SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$groupID]);
        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * Returns the list of people whom a calendar is shared with.
     *
     * Every item in the returned list must be a Sharee object with at
     * least the following properties set:
     *   $href
     *   $shareAccess
     *   $inviteStatus
     *
     * and optionally:
     *   $properties
     *
     * @param mixed $calendarId
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */
    function getGroupId($calendarId)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to getGroupId() is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $query = <<<SQL
SELECT
    grpid
FROM {$this->calendarInstancesTableName}
WHERE
    calendarid = ?
SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$calendarId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return 0;

        return $row['grpid'];
    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used
     * to reference this calendar in other methods, such as updateCalendar.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @param int : 1 = personal, 2 = room, 3 = computer, 4 = video
     * @return string
     */
    function createCalendar($principalUri, $calendarUri, array $properties, $cal_type = 1, $desc = null, $groupid=0)
    {
        $fieldNames = [
            'principaluri',
            'uri',
            'transparent',
            'cal_type',
            'description',
            'calendarid',
            'grpid'
        ];
        $values = [
            ':principaluri' => $principalUri,
            ':uri' => $calendarUri,
            ':transparent' => 0,
            ':cal_type' => $cal_type,
            ':description' => $desc
        ];


        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        if (!isset($properties[$sccs])) {
            // Default value
            $components = 'VEVENT,VTODO';
        } else {
            if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
                throw new DAV\Exception('The ' . $sccs . ' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
            }
            $components = implode(',', $properties[$sccs]->getValue());
        }
        $transp = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $values[':transparent'] = $properties[$transp]->getValue() === 'transparent' ? 1 : 0;
        }

        $stmt = $this->pdo->prepare("INSERT INTO " . $this->calendarTableName . " (synctoken, components) VALUES (1, ?)");
        $stmt->execute([$components]);


        $calendarId = $this->pdo->lastInsertId(
            $this->calendarTableName . '_id_seq'
        );

        $values[':calendarid'] = $calendarId;
        $values[':grpid'] = $groupid;

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties[$xmlName])) {

                $values[':' . $dbName] = $properties[$xmlName];
                $fieldNames[] = $dbName;
            }
        }

        $stmt = $this->pdo->prepare("INSERT INTO " . $this->calendarInstancesTableName . " (" . implode(', ', $fieldNames) . ") VALUES (" . implode(', ', array_keys($values)) . ")");

        $stmt->execute($values);

        return [
            $calendarId,
            $this->pdo->lastInsertId($this->calendarInstancesTableName . '_id_seq')
        ];
    }

    /**
     * Creates a new calendar object.
     *
     * Method : EcclesiaCRM
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function createCalendarObject($calendarId, $objectUri, $calendarData)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }

        $groupId = $this->getGroupId($calendarId);

        list($calendarId, $instanceId) = $calendarId;

        $extraData = VObjectExtract::calendarData($calendarData);

        $stmt = $this->pdo->prepare('INSERT IGNORE INTO ' . $this->calendarObjectTableName . ' (event_calendarid, event_uri, event_calendardata, event_lastmodified, event_title, event_desc, event_location, event_last_occurence, event_etag, event_size, event_componenttype, event_start, event_end, event_uid, event_grpid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

        try {
            $stmt->execute([
                $calendarId,
                $objectUri,
                $calendarData,
                time(),
                $extraData['title'],
                $extraData['description'],
                $extraData['location'],
                $extraData['freqlastOccurence'],
                $extraData['etag'],
                $extraData['size'],
                $extraData['componentType'],
                $extraData['firstOccurence'],
                $extraData['lastOccurence'],
                $extraData['uid'],
                $groupId,
            ]);
        } catch (PDOException $Exception ) {
            // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
            // String.
            LoggerUtils::getAppLogger()->info( "erreur : ".$Exception->getMessage( ) ." ". $Exception->getCode());
        }

        $this->addChange($calendarId, $objectUri, 1);

        return '"' . $extraData['etag'] . '"';

    }


    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * Method : CALdav sabre
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        //LoggerUtils::getAppLogger()->info("updateCalendarObject");

        $extraData = VObjectExtract::calendarData($calendarData);

        $stmt = $this->pdo->prepare('UPDATE IGNORE ' . $this->calendarObjectTableName . ' SET event_calendardata = ?, event_lastmodified = ?, event_title = ?, event_desc = ?, event_location = ?, event_last_occurence = ?, event_etag = ?, event_size = ?, event_componenttype = ?, event_start = ?, event_end = ?, event_uid = ? WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$calendarData, time(), $extraData['title'], $extraData['description'], $extraData['location'], $extraData['freqlastOccurence'], $extraData['etag'], $extraData['size'], $extraData['componentType'], $extraData['firstOccurence'], $extraData['lastOccurence'], $extraData['uid'], $calendarId, $objectUri]);

        // quand le calendrier est mis à jour on gère la bonne date et la bonne heure
        //error_log("La date est = ".$extraData['firstOccurence']."\n\n", 3, "/var/log/mes-erreurs.log");
        //error_log("Le blob = ".$calendarData."\n\n", 3, "/var/log/mes-erreurs.log");

        /*foreach ($extraData as $key => $val) {
           error_log("Key = ".$key." val = ".$val."\n\n", 3, "/var/log/mes-erreurs.log");
        }*/


        $this->addChange($calendarId, $objectUri, 2);

        return '"' . $extraData['etag'] . '"';

    }

    /**
     * Deletes an existing calendar object.
     *
     * Method : CALDAV sabre
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @return void
     */
    function deleteCalendarObject($calendarId, $objectUri)
    {
        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $event = EventQuery::create()->filterByEventCalendarid($calendarId)->findOneByUri($objectUri);
        //$event = EventQuery::create()->findOneByEventCalendarid($calendarId);

        /*$stmt = $this->pdo->prepare('SELECT event_creator_user_id FROM '. $this->calendarObjectTableName.' WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$objectUri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ( $row['event_creator_user_id'] != SessionUser::getId()) {
            return;
        }*/

        //LoggerUtils::getAppLogger()->info("deleteCalendarObject : ".$calendarId." ".$objectUri." ". $event->getEtag());//.$row['event_creator_user_id']

        //return;

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$calendarId, $objectUri]);

        $this->addChange($calendarId, $objectUri, 3);

    }

    /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * Method : CALDav sabre + EcclesiaCRM
     *
     * The calendar-query is defined in RFC4791 : CalDAV. Using the
     * calendar-query it is possible for a client to request a specific set of
     * object, based on contents of iCalendar properties, date-ranges and
     * iCalendar component types (VTODO, VEVENT).
     *
     * This method should just return a list of (relative) urls that match this
     * query.
     *
     * The list of filters are specified as an array. The exact array is
     * documented by \Sabre\CalDAV\CalendarQueryParser.
     *
     * Note that it is extremely likely that getCalendarObject for every path
     * returned from this method will be called almost immediately after. You
     * may want to anticipate this to speed up these requests.
     *
     * This method provides a default implementation, which parses *all* the
     * iCalendar objects in the specified calendar.
     *
     * This default may well be good enough for personal use, and calendars
     * that aren't very large. But if you anticipate high usage, big calendars
     * or high loads, you are strongly adviced to optimize certain paths.
     *
     * The best way to do so is override this method and to optimize
     * specifically for 'common filters'.
     *
     * Requests that are extremely common are:
     *   * requests for just VEVENTS
     *   * requests for just VTODO
     *   * requests with a time-range-filter on a VEVENT.
     *
     * ..and combinations of these requests. It may not be worth it to try to
     * handle every possible situation and just rely on the (relatively
     * easy to use) CalendarQueryValidator to handle the rest.
     *
     * Note that especially time-range-filters may be difficult to parse. A
     * time-range filter specified on a VEVENT must for instance also handle
     * recurrence rules correctly.
     * A good example of how to interpret all these filters can also simply
     * be found in \Sabre\CalDAV\CalendarQueryFilter. This class is as correct
     * as possible, so it gives you a good idea on what type of stuff you need
     * to think of.
     *
     * This specific implementation (for the PDO) backend optimizes filters on
     * specific components, and VEVENT time-ranges.
     *
     * @param mixed $calendarId
     * @param array $filters
     * @return array
     */
    function calendarQuery($calendarId, array $filters, $eventId = 0)
    {
        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $componentType = null;
        $requirePostFilter = true;
        $timeRange = null;

        // if no filters were specified, we don't need to filter after a query
        if (!$filters['prop-filters'] && !$filters['comp-filters']) {
            $requirePostFilter = false;
        }

        // Figuring out if there's a component filter
        if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
            $componentType = $filters['comp-filters'][0]['name'];

            // Checking if we need post-filters
            if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['time-range'] && !$filters['comp-filters'][0]['prop-filters']) {
                $requirePostFilter = false;
            }
            // There was a time-range filter
            if ($componentType == 'VEVENT' && isset($filters['comp-filters'][0]['time-range'])) {
                $timeRange = $filters['comp-filters'][0]['time-range'];

                // If start time OR the end time is not specified, we can do a
                // 100% accurate mysql query.
                if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['prop-filters'] && (!$timeRange['start'] || !$timeRange['end'])) {
                    $requirePostFilter = false;
                }
            }

        }

        if ($requirePostFilter) {
            $query = "SELECT event_id, event_uri, event_calendardata, event_start, event_end FROM " . $this->calendarObjectTableName . " WHERE event_calendarid = :calendarid";
        } else {
            $query = "SELECT event_id, event_uri FROM " . $this->calendarObjectTableName . " WHERE event_calendarid = :calendarid";
        }

        $values = [
            'calendarid' => $calendarId,
        ];

        if ($eventId > 0) {
            $query .= " AND event_id != :eventid";

            $values = array_merge($values, [
                'eventid' => $eventId
            ]);
        }

        if ($componentType) {
            $query .= " AND event_componenttype = :componenttype";
            $values['componenttype'] = $componentType;
        }

        if ($timeRange && $timeRange['start']) {
            $query .= " AND event_start > :startdate";
            $values['startdate'] = $timeRange['start']->format('Y-m-d');
        }
        if ($timeRange && $timeRange['end']) {
            $query .= " AND event_end < :enddate";
            $values['enddate'] = $timeRange['end']->format('Y-m-d');
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            /*if ($requirePostFilter) {
                if (!$this->validateFilterForObject($row, $filters)) {
                    continue;
                }
            }*/

            // in the case of a reccuring event
            $returnValues = VObjectExtract::calendarData($row['event_calendardata']);

            if ($returnValues['freq'] != 'none') {
                foreach ($returnValues as $key => $value) {
                    if ($key == 'freqEvents') {
                        foreach ($value as $sevent) {
                            $result[] = [
                                'event_uri' => $row['event_uri'],
                                'event_start' => $sevent['DTSTART'],
                                'event_end' => $sevent['DTEND'],
                                'calendardata' => $row['event_calendardata']
                            ];
                        }
                    }
                }
            } else {
                $result[] = [
                    'event_uri' => $row['event_uri'],
                    'event_start' => $row['event_start'],
                    'event_end' => $row['event_end'],
                    'calendardata' => $row['event_calendardata']
                ];
            }
        }

        return $result;

    }

    /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * Method : EcclesiaCRM
     *
     */
    public function isCalendarResource ($calIDs)
    {
        $fullCalendarInfo = $this->getFullCalendar($calIDs);

        $calendar_Type = (int)$fullCalendarInfo['cal_type']; // 2, 3, 4 are room, computer or video (there can't be collision

        // this part allows to create a resource without being in collision on another one
        if ($calendar_Type >= 2 and $calendar_Type <= 4) {
            return true;
        }

        return false;
    }

    /**
     * Performs : check if a specified $calIDs for $start and $end if
     *
     * Method : EcclesiaCRM
     *
     */
    public function checkIfEventIsInResourceSlotCalendar ($calIDs, $start, $end, $eventId = 0, $recurrenceType = "", $endrecurrence = "")
    {

        // 1. we search 6 months before and after, to see any slot collision before
        $startDate = ((new \DateTime($start))->modify('-6 months'))->format('Y-m')."-01";
        $endDate = ((new \DateTime($start))->modify('first day of +6 month'))->format('Y-m-d');

        // we've to find if there isn't any event collision !!!
        $filters = [
            'name' => 'VCALENDAR',
            'comp-filters' => [
                [
                    'name' => 'VEVENT',
                    'comp-filters' => [],
                    'prop-filters' => [],
                    'is-not-defined' => false,
                    'time-range' => ['start' => new \DateTime($startDate), 'end' => new \DateTime($endDate)]
                ],
            ],
            'prop-filters' => [],
            'is-not-defined' => false,
            'time-range' => null,
        ];

        // get all the events with the recurring too
        $calendarEvents = $this->calendarQuery($calIDs, $filters, $eventId);

        // 2. now we search after in the case we've reccurence type enabled
        $uuid = strtoupper(UUIDUtil::getUUID());

        $EventDesc = "A desc";
        $EventTitle = "A title";

        if ( $recurrenceType != "" ) {
            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'UID' => $uuid,
                'RRULE' => $recurrenceType . ';' . 'UNTIL=' . (new \DateTime($endrecurrence))->format('Ymd\THis'),
                'SEQUENCE' => '0',
                'TRANSP' => 'OPAQUE'
            ];
        } else {
            $vevent = [
                'CREATED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DTSTART' => (new \DateTime($start))->format('Ymd\THis'),
                'DTEND' => (new \DateTime($end))->format('Ymd\THis'),
                'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
                'DESCRIPTION' => $EventDesc,
                'SUMMARY' => $EventTitle,
                'UID' => $uuid,
                'SEQUENCE' => '0',
                'TRANSP' => 'OPAQUE'
            ];
        }

        $vcalendar = new VCalendarExtension();

        $realVevent = $vcalendar->add('VEVENT',$vevent);

        $eventSerialized = "BEGIN:VCALENDAR\r\nVERSION:2.0 PRODID:-//EcclesiaCRM.// VObject " . VObject\Version::VERSION ."//EN\r\nCALSCALE:GREGORIAN\r\n".$realVevent->serialize(false)."\r\nEND:VCALENDAR";

        $returnValues = VObjectExtract::calendarData($eventSerialized);

        // it's time to find all the future events
        $futureEvents = [];

        if ($returnValues['freq'] != 'none') {
            foreach ($returnValues as $key => $value) {
                if ($key == 'freqEvents') {
                    foreach ($value as $sevent) {
                        $futureEvents[] = [
                            'event_uri' => $uuid,
                            'event_start' => $sevent['DTSTART'],
                            'event_end' => $sevent['DTEND'],
                            //'calendardata' => $eventSerialized
                        ];
                    }
                }
            }
        } else {
            $futureEvents[] = [
                'event_uri' => $uuid,
                'event_start' => $start,
                'event_end' => $end,
                //'calendardata' => $eventSerialized
            ];
        }

        // 3. we test if all the events as real or not
        foreach ($calendarEvents as $event_in_calendar) {
            $event_in_calendar_start = new \DateTime($event_in_calendar['event_start']);
            $event_in_calendar_end = new \DateTime($event_in_calendar['event_end']);

            foreach ($futureEvents as $future_event) {
                $future_event_start = new \DateTime($future_event['event_start']);
                $future_event_end = new \DateTime($future_event['event_end']);

                if (($future_event_start <= $event_in_calendar_start and $future_event_end >= $event_in_calendar_end)
                    or ($event_in_calendar_start < $future_event_start and $event_in_calendar_end > $future_event_start)
                    or ($event_in_calendar_start < $future_event_end and $event_in_calendar_end > $future_event_end)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Searches through all of a users calendars and calendar objects to find
     * an object with a specific UID.
     *
     * Method : CALDav + EcclesiaCRM too
     *
     * This method should return the path to this object, relative to the
     * calendar home, so this path usually only contains two parts:
     *
     * calendarpath/objectpath.ics
     *
     * If the uid is not found, return null.
     *
     * This method should only consider * objects that the principal owns, so
     * any calendars owned by other principals that also appear in this
     * collection should be ignored.
     *
     * @param string $principalUri
     * @param string $uid
     * @return string|null
     */
    function getCalendarObjectByUID($principalUri, $uid)
    {

        $query = <<<SQL
SELECT
    calendar_instances.uri AS calendaruri, calendarobjects.event_uri as objecturi
FROM
    $this->calendarObjectTableName AS calendarobjects
LEFT JOIN
    $this->calendarInstancesTableName AS calendar_instances
    ON calendarobjects.event_calendarid = calendar_instances.calendarid
WHERE
    calendar_instances.principaluri = ?
    AND
    calendarobjects.uid = ?
SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$principalUri, $uid]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['calendaruri'] . '/' . $row['objecturi'];
        }

    }


    /**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified calendar.
     *
     * Method : CALDav
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'modified.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
     * property this is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param mixed $calendarId
     * @param string $syncToken
     * @param int $syncLevel
     * @param int $limit
     * @return array
     */
    function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null)
    {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        // Current synctoken
        $stmt = $this->pdo->prepare('SELECT synctoken FROM ' . $this->calendarTableName . ' WHERE id = ?');
        $stmt->execute([$calendarId]);
        $currentToken = $stmt->fetchColumn(0);

        if (is_null($currentToken)) return null;

        $result = [
            'syncToken' => $currentToken,
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        if ($syncToken) {

            $query = "SELECT uri, operation FROM " . $this->calendarChangesTableName . " WHERE synctoken >= ? AND synctoken < ? AND calendarid = ? ORDER BY synctoken";
            if ($limit > 0) $query .= " LIMIT " . (int)$limit;

            // Fetching all changes
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$syncToken, $currentToken, $calendarId]);

            $changes = [];

            // This loop ensures that any duplicates are overwritten, only the
            // last change on a node is relevant.
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

                $changes[$row['uri']] = $row['operation'];

            }

            foreach ($changes as $uri => $operation) {

                switch ($operation) {
                    case 1 :
                        $result['added'][] = $uri;
                        break;
                    case 2 :
                        $result['modified'][] = $uri;
                        break;
                    case 3 :
                        $result['deleted'][] = $uri;
                        break;
                }

            }
        } else {
            // No synctoken supplied, this is the initial sync.
            $query = "SELECT event_uri FROM " . $this->calendarObjectTableName . " WHERE event_calendarid = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$calendarId]);

            $result['added'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
        return $result;

    }

    /**
     * The moveCalendarToNewPrincipal
     *
     * Method : EcclesiaCRM
     *
     * @param int $new_principaluri
     * @return nothing
     */
    public function moveCalendarToNewPrincipal($old_principaluri, $new_principaluri)
    {

        $stmt = $this->pdo->prepare('UPDATE ' . $this->calendarInstancesTableName . ' SET principaluri = ? WHERE principaluri = ?');
        $stmt->execute([$new_principaluri, $old_principaluri]);

    }

}
