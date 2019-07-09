<?php 
// pour le debug on se met au bon endroit : http://192.168.151.207/mysql/upgrade/5.6.1-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;
  
  use Sabre\CardDAV;
  use Sabre\DAV;
  use EcclesiaCRM\MyPDO\CardDavPDO;

  $logger = LoggerUtils::getAppLogger();
  
  // new way to manage events
  // we get the PDO for the Sabre connection from the Propel connection
  $pdo = Propel::getConnection();

  $carddavBackend   = new CardDavPDO($pdo->getWrappedConnection());

  $logger->info("Start to delete : start group add to addressbook");
  
  $sSQL = "SELECT `grp_ID`, `grp_Name` FROM `group_grp` WHERE 1";
  
  $statement = $pdo->prepare($sSQL);
  $statement->execute();

  $groups = $statement->fetchAll(PDO::FETCH_ASSOC);// permet de récupérer le tableau associatif
  
  foreach ($groups as $group) {
     // first we add the adress book
      $addressbook = $carddavBackend->getAddressBookForGroup ($group['grp_ID']);
      
      if ($addressbook['id'] != 0) continue;
      
      // we have to create the adress book and add the members
     
      $addressbookId = $carddavBackend->createAddressBook(
            'principals/admin',
            \Sabre\DAV\UUIDUtil::getUUID(),
            [
                '{DAV:}displayname'                                       => $group['grp_Name'],
                '{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'AddressBook description',
            ],
            $group['grp_ID']
      );
      
      $sSQL = "SELECT per.per_FirstName as perFirstName, per.per_MiddleName as perMiddleName, per.per_LastName as perLastName, 
      per.per_Address1  as perAddress1, per.per_City  as perCity, per.per_State  as perState, 
      per.per_Zip as perZip, per.per_Country as perCountry, per.per_HomePhone as perHomePhone, 
      per.per_WorkPhone as perWorkPhone, per.per_CellPhone  as perCellPhone, 
      per.per_Email as perEmail, per.per_WorkEmail  as perWorkEmail, 
      per.per_fam_ID as perFamID, fam.fam_Address1 as famAddress1, 
      fam.fam_City as famCity, fam.fam_Zip as famZip 
      FROM person2group2role_p2g2r as p2g2r
      INNER JOIN person_per as per ON (p2g2r.p2g2r_per_ID=per.per_ID)
      LEFT JOIN family_fam as fam ON (fam.fam_ID=per.per_fam_ID) 
      WHERE per.per_DateDeactivated IS NULL 
      AND p2g2r.p2g2r_grp_ID=".$group['grp_ID'];
      
      $statement = $pdo->prepare($sSQL);
      $statement->execute();
      
      $members = $statement->fetchAll(PDO::FETCH_ASSOC);// permet de récupérer le tableau associatif
            
      foreach ($members as $person) {        
        // now we'll create all the cards
        $card = 'BEGIN:VCARD
VERSION:3.0
PRODID:-//Apple Inc.//Mac OS X 10.12.6//EN
N:'.$person['perLastName'].';'.$person['perFirstName'].';'.$person['perMiddleName'].';;
FN:'.$person['perFirstName'].' '.$person['perLastName'];

        if ( !empty($person['perWorkEmail']) ) {
          $card .="\nEMAIL;type=INTERNET;type=WORK;type=pref:".$person['perWorkEmail'];
        }
        if ( !empty($person['perEmail']) ) {
          $card .="\nEMAIL;type=INTERNET;type=HOME;type=pref:".$person['perEmail'];
        }

        if ( !empty($person['perHomePhone']) ) {
          $card .="\nTEL;type=HOME;type=VOICE;type=pref:".$person['perHomePhone'];
        }

        if ( !empty($person['perCellPhone']) ) {
          $card .="\nTEL;type=CELL;type=VOICE:".$person['perCellPhone'];
        }

        if ( !empty($person['perWorkPhone']) ) {
          $card .="\nTEL;type=WORK;type=VOICE:".$person['perWorkPhone'];
        }

        if ( !empty($person['perAddress1']) || !empty($person['perCity']) || !empty($person['perZip']) ) {
          $card .="\nitem1.ADR;type=HOME;type=pref:;;".$person['perAddress1'].';'.$person['perCity'].';;'.$person['perZip'];
        } else if (!is_null ($person['perFamID'])) {
          $card .="\nitem1.ADR;type=HOME;type=pref:;;".$person['famAddress1'].';'.$person['famCity'].';;'.$person['famZip'];
        }

        $card .= "\nitem1.X-ABADR:fr
UID:".\Sabre\DAV\UUIDUtil::getUUID().'
END:VCARD';
        
        $carddavBackend->createCard($addressbookId, 'UUID-'.\Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());
      }
  }


  $logger->info("End of Reset addressbook");
?>
