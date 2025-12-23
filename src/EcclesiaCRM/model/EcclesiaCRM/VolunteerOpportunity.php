<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\VolunteerOpportunity as BaseVolunteerOpportunity;
use EcclesiaCRM\PersonVolunteerOpportunity as ChildPersonVolunteerOpportunity;
use EcclesiaCRM\CardDav\VcardUtils;
use EcclesiaCRM\MyPDO\CardDavPDO;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for representing a row from the 'volunteeropportunity_vol' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class VolunteerOpportunity extends BaseVolunteerOpportunity
{
    public function addPersonVolunteerOpportunity(ChildPersonVolunteerOpportunity $l)
    {
        // We set the BackEnd for sabre Backends
        $carddavBackend = new CardDavPDO();

        $personId = $l->getPersonId();
        $person = $l->getPerson();

        $addressbook = $carddavBackend->getAddressBookForVolunteers($this->getId());

        if ($addressbook['id'] != 0 && !$carddavBackend->getCardForPerson($addressbook['id'], $personId)) {
            // we've checked that we'll insert only one card per user
            $vcard = VcardUtils::Person2Vcard($person);

            $card = $vcard->serialize();            

            $carddavBackend->createCard($addressbook['id'], 'UUID-' . \Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());
        }

        return parent::addPersonVolunteerOpportunity($l);
    }

    public function postInsert(?ConnectionInterface $con = null): void
    {        
        parent::postInsert($con);

        $userAdmin = UserQuery::Create()->findOneByPersonId(1);

        // we add the addressbook
        $carddavBackend = new CardDavPDO();

        $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());

        $addresbookid = $carddavBackend->createAddressBook(
            'principals/' . strtolower($userAdmin->getUserName()),
            $uuid,
            [
                '{DAV:}displayname' => $this->getName(),
                '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $this->getDescription()
            ],
            -1,
            $this->getId()
        );


        // we filter all the user who are admin or group manager and not the principal admin
        $users = UserQuery::Create()
            ->filterByManageGroups(true)
            ->_or()->filterByAdmin(true)
            ->filterByPersonId(1, Criteria::NOT_EQUAL)
            ->find();

        // now we can share the new calendar to the users
        foreach ($users as $user) {
            $carddavBackend->createAddressBookShare(
                'principals/'.$user->getUserName(),
                [
                    'addressbookid'=> $addresbookid, // require
                    '{DAV:}displayname'  => $this->getName(),
                    '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description'  => $this->getDescription(),
                    'href'         => 0,
                    'user_id'      => $user->getId(), // require
                    'access'       => 3 // '1 = owner, 2 = read, 3 = readwrite',                    
                ]
            );
        }
    }
}
