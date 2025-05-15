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

use EcclesiaCRM\UserQuery;

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
    public function getShareResourceUri($mycol):string
    {
        $coucou = "titi";

        return $coucou;
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
        foreach($sharees as $sharee) {
            $res = explode(':', $sharee->href);
            if (count($res) != 2) {
                continue;            
            }
            $email = $res[1];

            $user = UserQuery::create()
                ->usePersonQuery()
                    ->filterByWorkEmail($email)
                    ->_or()
                    ->filterByEmail($email)
                ->endUse()
                ->findOne();

            if (is_null($user)) {
                continue;
            }

            $username = $user->getUserName();

            $removeStmt = $this->pdo->prepare("DELETE FROM " . $this->collectionsTableName . " WHERE ownerPath = ? AND ownerPath = ? AND access IN (2,3)");
            $updateStmt = $this->pdo->prepare("UPDATE " . $this->collectionsTableName . " SET access = ?, ownerPath = ?, share_href = ? WHERE ownerPath = ? AND ownerId = ?");

            $insertStmt = $this->pdo->prepare('
                INSERT INTO ' . $this->collectionsTableName . '
                    (
                        uri,
                        email,
                        ownerId,
                        ownerPath,
                        access,
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
                    FROM ' . $this->collectionsTableName . ' WHERE id = ?');

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

                    if ($oldSharee->href === $sharee->href or $oldSharee->principal === $sharee->principal) {
                        // This is an update
                        $sharee->properties = array_merge(
                            $oldSharee->properties,
                            $sharee->properties
                        );
                        $updateStmt->execute([
                            $sharee->access,
                            isset($sharee->properties['{DAV:}displayname']) ? $sharee->properties['{DAV:}displayname'] : null,
                            $sharee->inviteStatus ?: $oldSharee->inviteStatus,
                            $sharee->href,
                            $calendarId,
                            $sharee->principal
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
            'href' => 'admin',
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
}
