<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without any authorizaion
//
//  Updated : 2018/06/23
//

namespace EcclesiaCRM\MyPDO;

use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\VObject;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Xml\Element\Sharee;

use Sabre\CardDAV\Backend as SabreCardDavBase;

class CardDavPDO extends SabreCardDavBase\PDO {

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

        return $addressBooks;

    }

}