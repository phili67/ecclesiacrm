<?php

declare(strict_types=1);

namespace Sabre\WebDAV\Backend;

use Sabre\WebDAV;
use Sabre\DAV;
use Sabre\DAV\Xml\Element\Sharee;

/**
 * PDO CalDAV backend.
 *
 * This backend is used to store calendar-data in a PDO database, such as
 * sqlite or MySQL
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class PDO extends AbstractBackend implements  SharingSupport //,SyncSupport, SubscriptionSupport, SchedulingSupport
{
    /**
     * We need to specify a max date, because we need to stop *somewhere*.
     *
     * On 32 bit system the maximum for a signed integer is 2147483647, so
     * MAX_DATE cannot be higher than date('Y-m-d', 2147483647) which results
     * in 2038-01-19 to avoid problems when the date is converted
     * to a unix timestamp.
     */
    const MAX_DATE = '2038-01-01';

    /**
     * pdo.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The table name that will be used for calendars.
     *
     * @var string
     */
    public $collectionsTableName = 'collections';

    public $collectionsinstancesTableName = 'collectionsinstances';


    /**
     * List of CalDAV properties, and how they map to database fieldnames
     * Add your own properties by simply adding on to this array.
     *
     * Note that only string-based properties are supported here.
     *
     * @var array
     */
    public $propertyMap = [
        '{DAV:}displayname' => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order' => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color' => 'calendarcolor',
    ];

    /**
     * List of subscription properties, and how they map to database fieldnames.
     *
     * @var array
     */
    public $subscriptionPropertyMap = [
        '{DAV:}displayname' => 'displayname',
        '{http://apple.com/ns/ical/}refreshrate' => 'refreshrate',
        '{http://apple.com/ns/ical/}calendar-order' => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color' => 'calendarcolor',
        '{http://calendarserver.org/ns/}subscribed-strip-todos' => 'striptodos',
        '{http://calendarserver.org/ns/}subscribed-strip-alarms' => 'stripalarms',
        '{http://calendarserver.org/ns/}subscribed-strip-attachments' => 'stripattachments',
    ];

    /**
     * Creates the backend.
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /*
     * This code is usefull for webdav part
     */

    /**
     * Returns the 'access level' for the instance of this shared resource.
     *
     * The value should be one of the Sabre\DAV\Sharing\Plugin::ACCESS_
     * constants.
     *
     * @return int
     */
    public function getShareAccess($mycol)
    {
        $coucou = "toto";
        // return isset($this->calendarInfo['share-access']) ? $this->calendarInfo['share-access'] : SPlugin::ACCESS_NOTSHARED;
        return 1;
    }

    /**
     * This function must return a URI that uniquely identifies the shared
     * resource. This URI should be identical across instances, and is
     * also used in several other XML bodies to connect invites to
     * resources.
     *
     * This may simply be a relative reference to the original shared instance,
     * but it could also be a urn. As long as it's a valid URI and unique.
     *
     * @return string
     */
    public function getShareResourceUri($mycol)
    {
        $coucou = "toto";
        return "";
    }

    /**
     * Updates the list of sharees.
     *
     * Every item must be a Sharee object.
     *
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     */
    public function updateInvites($mycol, array $sharees)
    {
        $coucou = "toto";
    }

    /**
     * Returns the list of people whom this resource is shared with.
     *
     * Every item in the returned array must be a Sharee object with
     * at least the following properties set:
     *
     * * $href
     * * $shareAccess
     * * $inviteStatus
     *
     * and optionally:
     *
     * * $properties
     *
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */
    public function getInvites($mycol)
    {
        return [new Sharee([
            'href' => 'toto',
            'access' => \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS,
        ])];

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to getInvites() is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $query = <<<SQL
            SELECT
                principaluri,
                access,
                share_href,
                share_displayname,
                share_invitestatus
            FROM {$this->collectionsTableName}
            WHERE
                calendarid = ?
            SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$calendarId]);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = new Sharee([
                'href' => isset($row['share_href']) ? $row['share_href'] : \Sabre\HTTP\encodePath($row['principaluri']),
                'access' => (int) $row['access'],
                /// Everyone is always immediately accepted, for now.
                'inviteStatus' => (int) $row['share_invitestatus'],
                'properties' => !empty($row['share_displayname'])
                    ? ['{DAV:}displayname' => $row['share_displayname']]
                    : [],
                'principal' => $row['principaluri'],
            ]);
        }

        return $result;
    }

    /**
     * Publishes a calendar.
     *
     * @param mixed $calendarId
     * @param bool  $value
     */
    public function setPublishStatus($calendarId, $value)
    {
        throw new DAV\Exception\NotImplemented('Not implemented');
    }
}
