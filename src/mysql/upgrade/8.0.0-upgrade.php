<?php
// pour le debug on se met au bon endroit : https://192.168.151.205/mysql/upgrade/8.0.0-post-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\MyPDO\CardDavPDO;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\UserQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Utils\LoggerUtils;

use EcclesiaCRM\CardDav\VcardUtils;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\VolunteerOpportunityQuery;

$logger = LoggerUtils::getAppLogger();

// we get the PDO for the Sabre connection from the Propel connection
// now we update the CardDav 
// every person in group should have a view on the addrebooks of the group
$carddavBackend   = new CardDavPDO();

$userAdmin = UserQuery::Create()->findOneByPersonId(1);  


$logger->info("start group add to addressbook");

$groups = GroupQuery::create()->find();      

foreach ($groups as $group) {
  // first we add the adress book
  $addressbookId = $carddavBackend->createAddressBook(
    'principals/'.strtolower($userAdmin->getUserName()),
    \Sabre\DAV\UUIDUtil::getUUID(),
    [
      '{DAV:}displayname'                                       => $group->getName(),
      '{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'AddressBook description',
    ],
    $group->getId()
  );

  // we filter all the user who are admin or group manager and not the principal admin
  $users = UserQuery::Create()
    ->filterByManageGroups(true)
    ->_or()->filterByAdmin(true)
    ->filterByPersonId(1, CRITERIA::NOT_EQUAL)
    ->find();

  // now we can share the new calendar to the users
  foreach ($users as $user) {
    $carddavBackend->createAddressBookShare(
      'principals/' . $user->getUserName(),
      [
        'addressbookid' => $addressbookId, // require
        '{DAV:}displayname'  => $group->getName(),
        '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description'  => '',
        'href'         => 0,
        'user_id'      => $user->getId(), // require
        'access'       => 3 // '1 = owner, 2 = read, 3 = readwrite',                    
      ]
    );
  }

  // we add all the members to 
  $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
    ->joinWithPerson()
    ->usePersonQuery()
    ->filterByDateDeactivated(null) // RGPD, when a person is completely deactivated
    ->endUse()
    ->findByGroupId($group->getId());

  foreach ($members as $member) {
    $person = $member->getPerson();

    $vcard = VcardUtils::Person2Vcard($person);

    $card = $vcard->serialize();

    $carddavBackend->createCard($addressbookId, 'UUID-' . \Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());
  }
}

$logger->info("end group add to addressbook");

$logger->info("start volunteers add to addressbook");

$vols = VolunteerOpportunityQuery::create()->find ();

foreach ($vols as $vol) {

  $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());

  $addresbookid = $carddavBackend->createAddressBook(
      'principals/' . strtolower($userAdmin->getUserName()),
      $uuid,
      [
          '{DAV:}displayname' => $this->getName(),
          '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $this->getDescription()
      ],
      -1,
      $vol->getId() // it's a volunteer addressbook
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

  // we have to create the cards
  $members = PersonVolunteerOpportunityQuery::create()
      ->filterByVolunteerOpportunityId($vol->getId())
      ->find();

  foreach ($members as $member) {
    $person = $member->getPerson();

    $vcard = VcardUtils::Person2Vcard($person);

    $card = $vcard->serialize();

    $carddavBackend->createCard($addressbookId, 'UUID-' . \Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());
  }
}

$logger->info("end volunteers add to addressbook");
