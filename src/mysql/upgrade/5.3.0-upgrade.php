<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.3.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\GroupQuery;
  use EcclesiaCRM\Person2group2roleP2g2rQuery;
  
  use Sabre\CardDAV;
  use Sabre\DAV;
  use EcclesiaCRM\MyPDO\CardDavPDO;



  $logger = LoggerUtils::getAppLogger();
  
  // new way to manage events
  // we get the PDO for the Sabre connection from the Propel connection
  $pdo = Propel::getConnection();

  $carddavBackend   = new CardDavPDO($pdo->getWrappedConnection());

  $logger->info("Start to delete : start group add to addressbook");
  
  $groups = GroupQuery::create()->find();
  
  foreach ($groups as $group) {
     // first we add the adress book
      $addressbookId = $carddavBackend->createAddressBook(
            'principals/admin',
            \Sabre\DAV\UUIDUtil::getUUID(),
            [
                '{DAV:}displayname'                                       => $group->getName(),
                '{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'AddressBook description',
            ],
            $group->getId()
      );
      
      $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->usePersonQuery()
              ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->endUse()
            ->findByGroupId($group->getId());
            
      foreach ($members as $member) {
        $person = $member->getPerson();
        
        // now we'll create all the cards
        $card = 'BEGIN:VCARD
VERSION:3.0
PRODID:-//Apple Inc.//Mac OS X 10.12.6//EN
N:'.$person->getLastName().';'.$person->getFirstName().';'.$person->getMiddleName().';;
FN:'.$person->getFirstName().' '.$person->getLastName();

        if ( !empty($person->getWorkEmail()) ) {
          $card .="\nEMAIL;type=INTERNET;type=WORK;type=pref:".$person->getWorkEmail();
        }
        if ( !empty($person->getEmail()) ) {
          $card .="\nEMAIL;type=INTERNET;type=HOME;type=pref:".$person->getEmail();
        }

        if ( !empty($person->getHomePhone()) ) {
          $card .="\nTEL;type=HOME;type=VOICE;type=pref:".$person->getHomePhone();
        }

        if ( !empty($person->getCellPhone()) ) {
          $card .="\nTEL;type=CELL;type=VOICE:".$person->getCellPhone();
        }

        if ( !empty($person->getWorkPhone()) ) {
          $card .="\nTEL;type=WORK;type=VOICE:".$person->getWorkPhone();
        }

        if ( !empty($person->getAddress1()) || !empty($person->getCity()) || !empty($person->getZip()) ) {
          $card .="\nitem1.ADR;type=HOME;type=pref:;;".$person->getAddress1().';'.$person->getCity().';;'.$person->getZip();
        } else if (!is_null ($person->getFamily())) {
          $card .="\nitem1.ADR;type=HOME;type=pref:;;".$person->getFamily()->getAddress1().';'.$person->getFamily()->getCity().';;'.$person->getFamily()->getZip();        
        }

        $card .= "\nitem1.X-ABADR:fr
UID:".\Sabre\DAV\UUIDUtil::getUUID().'
END:VCARD';
        
        $carddavBackend->createCard($addressbookId, 'UUID-'.\Sabre\DAV\UUIDUtil::getUUID(), $card);
      }
  }


  $logger->info("End of Reset VolunteerOpportunityQuery");
?>
