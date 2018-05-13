<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//

namespace EcclesiaCRM\MyPDO;

use Sabre\DAVACL;
use Sabre\DAV;
use EcclesiaCRM\MyPDO\CalDavPDO;

use Sabre\CalDAV\Backend as SabreBase;

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated     : 2018/05/13
//

use Sabre\DAVACL\PrincipalBackend as SabrePrincipalBase;

class PrincipalPDO extends SabrePrincipalBase\PDO {        

    function __construct(\PDO $pdo) {

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

}