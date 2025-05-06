<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//

namespace EcclesiaCRM\MyPDO;

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//  Updated     : 2018/05/13
//

use Sabre\DAV\Sharing\Plugin as SPlugin;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\DAVACL\PrincipalBackend as SabrePrincipalBase;

use EcclesiaCRM\Bootstrapper;


class PrincipalPDO extends SabrePrincipalBase\PDO {

    function __construct($pdo=null) {
        if (is_null($pdo)) {
            $pdo = Bootstrapper::GetPDO();
        }

        parent::__construct($pdo);
    }

   /**
     * Delete a principal.
     *
     * This method receives a full path for the new principal. The mkCol object
     * contains any additional webdav properties specified during the creation
     * of the principal.
     *
     * @param string $path
     * @param MkCol $mkCol
     * @return void
     */
    function deletePrincipal($uri) {

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->tableName . ' WHERE uri = ?');
        $stmt->execute([$uri]);


        $calendarBackend = new CalDavPDO($this->pdo);

        $calendars = $calendarBackend->getCalendarsForUser($uri);

        foreach ($calendars as $calendar) {
           $calendarBackend->deleteCalendar($calendar['id']);
        }

        //
        // we have to delete the CarDav too !!!!
        // Attention !!!
        $carddavBackend  = new \Sabre\CardDAV\Backend\PDO($this->pdo);

        $addressbooks = $carddavBackend->getAddressBooksForUser($uri);

        foreach ($addressbooks as $addressbook) {
           $carddavBackend->deleteAddressBook($addressbook['id']);
        }
    }


   /**
     * Create a new principal.
     *
     * This method receives a full path for the new principal. The mkCol object
     * contains any additional webdav properties specified during the creation
     * of the principal.
     *
     * @param string $path
     * @param MkCol $mkCol
     * @return void
     */
    function createNewPrincipal($uri,$email,$displayname) {

      //if (empty($this->findByUri("mailto:".$email, 'principals'))) {
        $stmt = $this->pdo->prepare('INSERT INTO ' . $this->tableName . ' (uri,email,displayname) VALUES (?, ?, ?)');
        $stmt->execute([$uri,$email,$displayname]);
      //}

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
