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
use Sabre\VObject;

use EcclesiaCRM\Bootstrapper;

use Sabre\CardDAV\Backend as SabreCardDavBase;

use Sabre\DAV\PropPatch;

class CardDavPDO extends SabreCardDavBase\PDO {

    var $addressBookShareTableName;

    function __construct($pdo=null) {

        if (is_null($pdo)) {
            $pdo = Bootstrapper::GetPDO();
        }

        parent::__construct($pdo);

        $this->addressBookShareTableName = 'addressbookshare';
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
    function getAddressBooksForUser($principalUri) 
    {

        $stmt = $this->pdo->prepare('SELECT id, uri, displayname, principaluri, description, synctoken FROM '.$this->addressBooksTableName.' WHERE principaluri = ?');
        $stmt->execute([$principalUri]);

        $addressBooks = [];

        foreach ($stmt->fetchAll() as $row) {
            $addressBooks[] = [
                'id' => $row['id'],
                'uri' => $row['uri'],
                'principaluri' => $row['principaluri'],
                '{DAV:}displayname' => $row['displayname'],
                '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' => $row['description'],
                '{http://calendarserver.org/ns/}getctag' => $row['synctoken'],
                '{http://sabredav.org/ns}sync-token' => $row['synctoken'] ? $row['synctoken'] : '0',
            ];
        }

        // now we loop inside de the share calendar
        $sharetmt = $this->pdo->prepare('SELECT addressbooks.id, addressbooks.uri, addressbookshare.displayname, addressbookshare.principaluri, addressbookshare.description, addressbooks.synctoken, access, addressbookid
FROM addressbookshare
LEFT JOIN addressbooks ON addressbookshare.addressbookid = addressbooks.id
WHERE addressbookshare.principaluri = ?');
        $sharetmt->execute([$principalUri]);

        foreach ($sharetmt->fetchAll() as $row) {
            $addressBooks[] = [
                'id' => $row['id'],
                'uri' => $row['uri'],
                'principaluri' => $row['principaluri'],
                'access' => $row['access'],
                'addressbookid' => $row['addressbookid'],
                '{DAV:}displayname' => $row['displayname'],
                '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' => $row['description'],
                '{http://calendarserver.org/ns/}getctag' => $row['synctoken'],
                '{http://sabredav.org/ns}sync-token' => $row['synctoken'] ? $row['synctoken'] : '0'
            ];
        }

        return $addressBooks;
    }

    /**
     * Creates a new address book
     *
     * @param string $principalUri
     * @param string $uri Just the 'basename' of the uri.
     * @param array $properties
     * @return int Last insert id
     */
    function createAddressBook($principalUri, $uri, array $properties, $group=-1) {

        $values = [
            'displayname'  => null,
            'description'  => null,
            'principaluri' => $principalUri,
            'uri'          => $uri,
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

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->addressBookShareTableName . ' WHERE addressbookid = ?');
        $stmt->execute([$addressBookId]);
    }

    /**
     * Creates a new shared address book
     *
     * @param int $addressbookid
     * @param string $principalUri 
     * @param string $uri Just the 'basename' of the uri.
     * @param array $properties
     *          string $uri Just the 'basename' of the uri.
     * @return int Last insert id
     */
    function createAddressBookShare($principalUri, array $properties) {

        $values = [
            'addressbookid'=> 0, // require
            'displayname'  => null,
            'description'  => null,
            'principaluri' => $principalUri, // require person principals/admin for example
            'href'         => 0,
            'user_id'      => -1, // require
            'access'       => 3 // '1 = owner, 2 = read, 3 = readwrite',
        ];

        foreach ($properties as $property => $newValue) {

            switch ($property) {
                case 'addressbookid':
                    $values['addressbookid'] = $newValue;
                    break;
                case '{DAV:}displayname' :
                    $values['displayname'] = $newValue;
                    break;
                case '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' :
                    $values['description'] = $newValue;
                    break;       
                case 'href':
                    $values['href'] = $newValue;
                case 'user_id':
                    $values['user_id'] = $newValue;
                    break;
                case 'access':
                    $values['access'] = $newValue;
                    break;
                default :
                    throw new DAV\Exception\BadRequest('Unknown property: ' . $property);
            }

        }

        $query = 'INSERT INTO ' . $this->addressBookShareTableName . ' (addressbookid, displayname, description, principaluri, user_id, href, access) VALUES (:addressbookid, :displayname, :description, :principaluri, :user_id, :href, :access)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
        return $this->pdo->lastInsertId(
            $this->addressBookShareTableName . '_id_seq'
        );

    }

    /**
     * Updates properties for an address book.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documentation for more info and examples.
     *
     * @param string $addressBookId
     */
    public function updateAddressBookShare($id, PropPatch $propPatch)
    {
        $supportedProperties = [
            '{DAV:}displayname',
            '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description',
        ];

        $propPatch->handle($supportedProperties, function ($mutations) use ($id) {
            $updates = [];
            foreach ($mutations as $property => $newValue) {
                switch ($property) {
                    case '{DAV:}displayname':
                        $updates['displayname'] = $newValue;
                        break;
                    case '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description':
                        $updates['description'] = $newValue;
                        break;
                }
            }
            $query = 'UPDATE '.$this->addressBookShareTableName.' SET ';
            $first = true;
            foreach ($updates as $key => $value) {
                if ($first) {
                    $first = false;
                } else {
                    $query .= ', ';
                }
                $query .= ' '.$key.' = :'.$key.' ';
            }
            $query .= ' WHERE id = :id';

            $stmt = $this->pdo->prepare($query);
            $updates['id'] = $id;

            $stmt->execute($updates);

            return true;
        });
    }

    /**
     * Deletes an entire addressbook and all its contents
     *
     * @param int $addressBookId
     * @return void
     */
    function deleteAddressBookShare($id) {

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->addressBookShareTableName . ' WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Returns the list of addressbooks for a specific user and the share too
     *
     * @param string $principalUri
     * @return array
     */
    function getAddressBooksShareForUser($principalUri) 
    {
        $stmt = $this->pdo->prepare('SELECT id, addressbookid, displayname, description, principaluri, user_id, href, access FROM '.$this->addressBookShareTableName.' WHERE principaluri = ?');
        $stmt->execute([$principalUri]);

        $addressBooks = [];

        foreach ($stmt->fetchAll() as $row) {
            $addressBooks[] = [
                'id' => $row['id'],
                'addressbookid' => $row['addressbookid'],
                '{DAV:}displayname' => $row['displayname'],
                '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' => $row['description'],
                'principaluri' => $row['principaluri'],
                'user_id' => $row['user_id'],
                'href' => $row['href'],
                'acces' => $row['access']
            ];
        }

        return $addressBooks;
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

        $removeStmt = $this->pdo->prepare("DELETE FROM " . $this->addressBookShareTableName . " WHERE addressBookId = ? AND href = ? AND access IN (2,3)");
        $updateStmt = $this->pdo->prepare("UPDATE " . $this->addressBookShareTableName . " SET access = ?, displayname = ? WHERE addressBookId = ? AND href = ?");


    $insertStmt = $this->pdo->prepare('
INSERT INTO ' . $this->addressBookShareTableName . '
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
FROM {$this->addressBookShareTableName}
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
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return array
     */
    public function getCard($addressBookId, $cardUri)
    {
        $stmt = $this->pdo->prepare('SELECT id, carddata, uri, lastmodified, etag, size, personId FROM '.$this->cardsTableName.' WHERE addressbookid = ? AND uri = ? LIMIT 1');
        $stmt->execute([$addressBookId, $cardUri]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        $result['etag'] = '"'.$result['etag'].'"';
        $result['lastmodified'] = (int) $result['lastmodified'];

        return $result;
    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique uri
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also omit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     *
     * @return array
     */
    public function getCards($addressbookId)
    {
        $stmt = $this->pdo->prepare('SELECT id, carddata, uri, lastmodified, etag, size FROM '.$this->cardsTableName.' WHERE addressbookid = ?');
        $stmt->execute([$addressbookId]);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['etag'] = '"'.$row['etag'].'"';
            $row['lastmodified'] = (int) $row['lastmodified'];
            $result[] = $row;
        }

        return $result;
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
     * Returns all cards for a personId.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param string $personId
     * @return array
     */
    function getCardsForPerson($personId) {

        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->cardsTableName . ' WHERE personId = ?');
        $stmt->execute([$personId]);

        $cards = [];

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $cards[] = $row;
        }

        return $cards;
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
    function updateCard($addressBookId, $cardUri, $cardData, $person_is_saved = false) 
    {   
        if (!$person_is_saved) {
            $vcard = VObject\Reader::read($cardData);
            //$vcard = $vcard->convert(VObject\Document::VCARD40);

            $realCard = $this->getCard($addressBookId, $cardUri);
            $person = PersonQuery::create()->findOneById($realCard['personId']);

            $family = $person->getFamily();

            if (isset($vcard->TITLE)) {// function
                $Title = $vcard->TITLE->getValue();
                $person->setSuffix($Title);
            }
            if (isset ($vcard->FN)) {// view named firstaname ....
                $FN = $vcard->FN; // unusefull
            }
            if (isset ($vcard->ROLE)) {// role
                $role = $vcard->ROLE;
            }
            if (isset ($vcard->NICKNAME)) {// pseudo
                $nickname = $vcard->NICKNAME;
            }
            if (isset ($vcard->N)) {// lastname, firstname, title, etc ...
                $N = explode(";",$vcard->N->getValue());
                $person->setTitle($N[3]);
                $person->setLastName($N[0]);
                $person->setFirstName($N[1]);

                // change family name ... we've to check if the entire family has to change name !!!
                $family->setName($N[0]);
            }
            $tels = [];
            if (isset($vcard->BDAY)) {// the birthdate
                $sBirthDayDate = $vcard->BDAY->getDateTime();
                
                $iBirthMonth = $sBirthDayDate->format('m');
                $iBirthDay = $sBirthDayDate->format('d');
                $iBirthYear = $sBirthDayDate->format('Y');

                $person->setBirthMonth($iBirthMonth);
                $person->setBirthDay($iBirthDay);
                $person->setBirthYear($iBirthYear);
            
            }
            if (isset($vcard->TEL)) {// all the phone numbers
                
                foreach($vcard->TEL as $tel) {
                    $param =  $tel['TYPE'];
                    $type = 'None';
                    foreach ($param as $value) {
                        if ($value != "VOICE" and $value != "pref")
                            $type = $value;                
                    }
                    if ($type == 'CELL') {
                        $person->setCellPhone($tel->getValue());
                    } else if ($type == 'HOME') {
                        $person->setHomePhone($tel->getValue());
                    } else if ($type == 'WORK') {
                        $person->setWorkPhone($tel->getValue());
                    }                    
                }
            }
            $adrElts = null;
            if (isset($vcard->ADR)) {// the address
                $param = $vcard->ADR['TYPE'];// by type home and private
                $adrElts = explode(";",$vcard->ADR->getValue());
                foreach ($param as $value) {
                    $ADR = $value;                
                }
                
                $family->setAddress1($adrElts[2]);
                $family->setCity($adrElts[3]);
                $family->setState($adrElts[4]);
                $family->setZip($adrElts[5]);
                $family->setCountry($adrElts[6]);            


                $person->setAddress1($adrElts[2]);
                $person->setCity($adrElts[3]);
                $person->setState($adrElts[4]);
                $person->setZip($adrElts[5]);
                $person->setCountry($adrElts[6]);                            
            }
            if (isset($vcard->ORG)) {// the firm name
                $firmName = $vcard->ORG; // value is an array !!!!
            }
            if (isset($vcard->EMAIL)) {// the firm name
                foreach($vcard->EMAIL as $email) {
                    $param =  $email['TYPE'];
                    $type = 'None';
                    foreach ($param as $value) {
                        if ($value != "VOICE" and $value != "pref") {
                            $type = $value;                
                        }
                    }
                    if ($type == 'WORK') {
                        $person->setWorkEmail($email->getValue());
                    } else if ($type == 'HOME') {
                        $person->setEmail($email->getValue());
                    }                                    
                }
            }

            $person->save(null, true);
            $family->save();
        }

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
