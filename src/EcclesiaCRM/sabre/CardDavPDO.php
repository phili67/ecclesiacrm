<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without any authorizaion
//
//  Updated : 2018/12/16
//

namespace EcclesiaCRM\MyPDO;

use EcclesiaCRM\PersonQuery;

use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAV\Xml\Element\Sharee;

use EcclesiaCRM\Bootstrapper;

use Sabre\CardDAV\Backend as SabreCardDavBase;

class CardDavPDO extends SabreCardDavBase\PDO {

    var $addressBookShareObjectTableName;

    function __construct($pdo=null) {

        if (is_null($pdo)) {
            $pdo = Bootstrapper::GetPDO();
        }

        parent::__construct($pdo);

        $this->addressBookShareObjectTableName = 'addressbookshare';
    }

    /**
     * Merges all vcard objects, and builds one big vcf export
     *
     * @param array $nodes
     * @return string
     */
    function generateVCFForAddressBook($addressBookId) {

        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->cardsTableName . ' WHERE addressbookid = ?');
        $stmt->execute([$addressBookId]);

        $output = "";

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $person = PersonQuery::create()->findOneById ($row['personId']);

            if (!is_null ($person) && !$person->isDeactivated()) {
              $output .= $row['carddata']."\n";
            }
        }

        return $output;

    }


    /**
     * Returns a specific card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return array
     */
    function getAddressBookForGroup($groupId) {

        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->addressBooksTableName . ' WHERE groupId = ?');
        $stmt->execute([$groupId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) return false;

        return $result;

    }

    /**
     * Returns the list of addressbooks for a specific user and the share too
     *
     * @param string $principalUri
     * @return array
     */
    function getAddressBooksForUser($principalUri) {

        $stmt = $this->pdo->prepare('SELECT id, uri, displayname, principaluri, description, synctoken FROM ' . $this->addressBooksTableName . ' WHERE principaluri = ?');
        $stmt->execute([$principalUri]);

        $addressBooks = [];

        foreach ($stmt->fetchAll() as $row) {

            $addressBooks[] = [
                'id'                                                          => $row['id'],
                'uri'                                                         => $row['uri'],
                'principaluri'                                                => $row['principaluri'],
                '{DAV:}displayname'                                           => $row['displayname'],
                '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $row['description'],
                '{http://calendarserver.org/ns/}getctag'                      => $row['synctoken'],
                '{http://sabredav.org/ns}sync-token'                          => $row['synctoken'] ? $row['synctoken'] : '0',
            ];
        }

        // now we've to work with the shares
        $stmt = $this->pdo->prepare(<<<SQL
SELECT {$this->addressBooksTableName}.id as addressBookid, {$this->addressBooksTableName}.uri as uri, {$this->addressBooksTableName}.principaluri as realprincipaluri,
       {$this->addressBooksTableName}.synctoken as synctoken,
       {$this->addressBookShareObjectTableName}.id as addressBookShareid, {$this->addressBookShareObjectTableName}.displayname as shareDisplayname,
       {$this->addressBookShareObjectTableName}.principaluri as shareprincipaluri,
       {$this->addressBookShareObjectTableName}.description as shareDescription,
       {$this->addressBookShareObjectTableName}.access as access
         FROM {$this->addressBookShareObjectTableName}
    LEFT JOIN {$this->addressBooksTableName} ON
        {$this->addressBookShareObjectTableName}.addressbooksid = {$this->addressBooksTableName}.id
WHERE {$this->addressBookShareObjectTableName}.principaluri = ? ORDER BY {$this->addressBookShareObjectTableName}.displayname ASC
SQL
        );
        $stmt->execute([$principalUri]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            if ($row['access'] > 1) {
              $addressBook = [
                  'id'                                                          => $row['addressBookShareid'],
                  'uri'                                                         => $row['uri'],
                  'principaluri'                                                => $row['shareprincipaluri'],
                  '{DAV:}displayname'                                           => $row['shareDisplayname'],
                  '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $row['shareDescription'],
                  '{http://calendarserver.org/ns/}getctag'                      => $row['synctoken'],
                  '{http://sabredav.org/ns}sync-token'                          => $row['synctoken'] ? $row['synctoken'] : '0',
                  'realprincipaluri'                                            => $row['realprincipaluri'],
              ];

              $addressBook['share-access'] = (int)$row['access'];
              // 1 = owner, 2 = readonly, 3 = readwrite
                // We need to find more information about the original owner.
                // the future.
                $addressBook['read-only'] = (int)$row['access'] === \Sabre\DAV\Sharing\Plugin::ACCESS_READ;
                $addressBook['read-write'] = (int)$row['access'] === \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE;

              // we can add the addressBook
              $addressBooks[] = $addressBook;
            }
        }

        return $addressBooks;
    }

    /**
     * Creates a new address book
     *
     * @param string $principalUri
     * @param string $url Just the 'basename' of the url.
     * @param array $properties
     * @return int Last insert id
     */
    function createAddressBook($principalUri, $url, array $properties, $group=-1) {

        $values = [
            'displayname'  => null,
            'description'  => null,
            'principaluri' => $principalUri,
            'uri'          => $url,
            'groupId'      => $group,
        ];

        foreach ($properties as $property => $newValue) {

            switch ($property) {
                case '{DAV:}displayname' :
                    $values['displayname'] = $newValue;
                    break;
                case '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' :
                    $values['description'] = $newValue;
                    break;
                default :
                    throw new DAV\Exception\BadRequest('Unknown property: ' . $property);
            }

        }

        $query = 'INSERT INTO ' . $this->addressBooksTableName . ' (uri, displayname, description, principaluri, synctoken, groupId) VALUES (:uri, :displayname, :description, :principaluri, 1, :groupId)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
        return $this->pdo->lastInsertId(
            $this->addressBooksTableName . '_id_seq'
        );

    }

   /**
     * Deletes an entire addressbook and all its contents
     *
     * @param int $addressBookId
     * @return void
     */
    function deleteAddressBook($addressBookId) {

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->cardsTableName . ' WHERE addressbookid = ?');
        $stmt->execute([$addressBookId]);

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->addressBooksTableName . ' WHERE id = ?');
        $stmt->execute([$addressBookId]);

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->addressBookChangesTableName . ' WHERE addressbookid = ?');
        $stmt->execute([$addressBookId]);

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->addressBookShareObjectTableName . ' WHERE addressbookid = ?');
        $stmt->execute([$addressBookId]);
    }

   /**
     * Updates the list of shares.
     *
     * @param mixed $calendarId
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     * @return void
     */
    function updateInvites($addressBookId, array $sharees) {

        if (is_null($addressBookId)) {
            throw new \InvalidArgumentException('The value passed to $addressBookId is expected');
        }
        $currentInvites = $this->getInvites($addressBookId);

        $removeStmt = $this->pdo->prepare("DELETE FROM " . $this->addressBookShareObjectTableName . " WHERE addressBookId = ? AND href = ? AND access IN (2,3)");
        $updateStmt = $this->pdo->prepare("UPDATE " . $this->addressBookShareObjectTableName . " SET access = ?, displayname = ? WHERE addressBookId = ? AND href = ?");


    $insertStmt = $this->pdo->prepare('
INSERT INTO ' . $this->addressBookShareObjectTableName . '
    (
        addressbooksid,
        principaluri,
        access,
        displayname,
        description,
        href
    )');

        foreach ($sharees as $sharee) {

            if ($sharee->access === \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS) {
                // if access was set no NOACCESS, it means access for an
                // existing sharee was removed.
                $removeStmt->execute([$addressBookId, $sharee->href]);
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
                        $addressBookId,
                        $sharee->href
                    ]);
                    continue 2;
                }

            }
            // If we got here, it means it was a new sharee
            $insertStmt->execute([
                $addressBookId,
                $sharee->principal,
                $sharee->access,
                isset($sharee->properties['{DAV:}displayname']) ? $sharee->properties['{DAV:}displayname'] : null,
                '',
                $sharee->href
            ]);

        }
    }

    /**
     * Returns the list of people whom a calendar is shared with.
     *
     * Every item in the returned list must be a Sharee object with at
     * least the following properties set:
     *   $href
     *   $Access
     *
     * and optionally:
     *   $displayname
     *
     * @param mixed $addressBookId
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */
    function getInvites($addressBookId) {

        if (is_null($addressBookId)) {
            throw new \InvalidArgumentException('The value passed to getInvites() is expected');
        }

        $query = <<<SQL
SELECT
    principaluri,
    access,
    href,
    displayname,
FROM {$this->addressBookShareObjectTableName}
WHERE
    addressbooksid = ?
SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$addressBookId]);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $result[] = new Sharee([
                'href'   => isset($row['href']) ? $row['href'] : \Sabre\HTTP\encodePath($row['principaluri']),
                'access' => (int)$row['access'],
                /// Everyone is always immediately accepted, for now.
                'displayname'   =>
                    !empty($row['displayname'])
                    ? ['{DAV:}displayname' => $row['displayname']]
                    : [],
                'principal' => $row['principaluri'],
            ]);

        }
        return $result;

    }


/// This code should be rewritten too ... because of tight access

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    function createCard($addressBookId, $cardUri, $cardData,$personId=-1) {

        $stmt = $this->pdo->prepare('INSERT INTO ' . $this->cardsTableName . ' (carddata, uri, lastmodified, addressbookid, size, etag, personId) VALUES (?, ?, ?, ?, ?, ?, ?)');

        $etag = md5($cardData);

        $stmt->execute([
            $cardData,
            $cardUri,
            time(),
            $addressBookId,
            strlen($cardData),
            $etag,
            $personId,
        ]);

        $this->addChange($addressBookId, $cardUri, 1);

        return '"' . $etag . '"';

    }

    /**
     * Returns a specific card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param mixed $addressBookId
     * @param string $personId
     * @return array
     */
    function getCardForPerson($addressBookId, $personId) {

        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->cardsTableName . ' WHERE addressbookid = ? AND personId = ? LIMIT 1');
        $stmt->execute([$addressBookId, $personId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) return false;

        $result['etag'] = '"' . $result['etag'] . '"';
        $result['lastmodified'] = (int)$result['lastmodified'];
        return $result;

    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    function updateCard($addressBookId, $cardUri, $cardData) {

        $stmt = $this->pdo->prepare('UPDATE ' . $this->cardsTableName . ' SET carddata = ?, lastmodified = ?, size = ?, etag = ? WHERE uri = ? AND addressbookid =?');

        $etag = md5($cardData);
        $stmt->execute([
            $cardData,
            time(),
            strlen($cardData),
            $etag,
            $cardUri,
            $addressBookId
        ]);

        $this->addChange($addressBookId, $cardUri, 2);

        return '"' . $etag . '"';

    }

    /**
     * Deletes a card
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return bool
     */
    function deleteCard($addressBookId, $cardUri) {

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->cardsTableName . ' WHERE addressbookid = ? AND uri = ?');
        $stmt->execute([$addressBookId, $cardUri]);

        $this->addChange($addressBookId, $cardUri, 3);

        return $stmt->rowCount() === 1;

    }

    /**
     * Deletes a card
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return bool
     */
    function deleteCardForPerson($addressBookId, $personId) {

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->cardsTableName . ' WHERE addressbookid = ? AND personId = ?');
        $stmt->execute([$addressBookId, $personId]);

        $this->addChange($addressBookId, $personId, 3);

        return $stmt->rowCount() === 1;

    }


}
