<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated : 2022/01/01
//

namespace EcclesiaCRM\PersonalServer;

// Include the function library
// Very important this constant !!!!
// be carefull with the webdav constant !!!!
require dirname(__FILE__) . '/../../Include/Config.php';

use EcclesiaCRM\EventQuery;
use EcclesiaCRM\UserQuery;
use Sabre\DAV;

class EcclesiaCRMCalendarServer extends DAV\Server
{
    function __construct($treeOrNode = null)
    {
        parent::__construct($treeOrNode);

        $this->on('beforeUnbind', array($this, 'beforeUnbind'));
        //$this->on('beforeBind',array($this, 'beforeBind'));
    }

    function beforeUnbind($uri)
    {
        // explode the $uri
        $uriDecomposition = explode("/", $uri);

        // get the event uri
        $eventURI = $uriDecomposition[count($uriDecomposition)-1];

        // get the user login name
        $userName = $uriDecomposition[1];
        $user = UserQuery::create()->findOneByUserName($userName);

        // get the the event from
        $event = EventQuery::create()->findOneByUri($eventURI);
        if (is_null($event)) {
            $event = EventQuery::create()->findOneByUri($eventURI.".ics");
        }

        //LoggerUtils::getAppLogger()->info("unbind : ".$uri." user : ". $userName ." " . $eventURI." ".$event->getCreatorUserId());

        if ( !is_null($event->getCreatorUserId()) and !is_null($user) and !$user->isAdmin() ) {
            // TO DO : $this->permitted();
            return false;
        }

        return true;
    }
}

