<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without authorization
//
//  Updated : 2020/01/26
//

namespace EcclesiaCRM\MyPDO;

use EcclesiaCRM\Bootstrapper;

use Sabre\DAV\Xml\Element\Sharee;
use Sabre\WebDAV\Backend as SabreWebDavBase;

class WebDavPDO extends SabreWebDavBase\PDO
{
    function __construct($pdo=null)
    {
        if (is_null($pdo)) {
            $pdo = Bootstrapper::GetPDO();
        }

        parent::__construct($pdo);
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
FROM {$this->calendarInstancesTableName}
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
}
