<?php

//
// CalendarServer 
// CalDAV support
//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/13
//

use Sabre\DAV;
use Sabre\DAV\Auth;

// Include the function library
// Very important this constant !!!!
// be carefull with the webdav constant !!!!
define("webdav", "1");
require dirname(__FILE__).'/Include/Config.php';

use Propel\Runtime\Propel;

use EcclesiaCRM\Auth\BasicAuth;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;


//*****************
// settings
//*****************
date_default_timezone_set(SystemConfig::getValue('sTimeZone')); //<------ Be carefull to set the good Time Zone : 'Europe/Paris'

// If you want to run the SabreDAV server in a custom location (using mod_rewrite for instance)

/* Database */
// Propel connection : pdo

$pdo = Propel::getConnection()->getWrappedConnection();

// Normal Sabre way : be carefull to connect in UTF8 mode
/*$pdo = new PDO('mysql:dbname='.$sDATABASE.';host='.$sSERVERNAME.';charset=utf8', $sUSER, $sPASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);*/

//Mapping PHP errors to exceptions
// problem with the webdav constant, this can't be used

/*function exception_error_handler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");*/

// The autoloader is unusefull : because it's done in the Config.php
//require_once 'vendor/autoload.php';

// Backends
//$authBackend = new DAV\Auth\Backend\PDO($pdo);
$authBackend = new BasicAuth();
$authBackend->setRealm('EcclesiaCRM_DAV');

$calendarBackend = new CalDavPDO($pdo);
$principalBackend = new PrincipalPDO($pdo);


// Directory structure
$tree = [
    new Sabre\CalDAV\Principal\Collection($principalBackend),
    new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
];

$server = new Sabre\DAV\Server($tree);

$server->setBaseUri(SystemURLs::getRootPath().'/calendarserver.php');

// Server Plugins 
$authPlugin = new Auth\Plugin($authBackend);
$server->addPlugin($authPlugin);

$aclPlugin = new Sabre\DAVACL\Plugin();
$server->addPlugin($aclPlugin);

// CalDAV support 
$caldavPlugin = new Sabre\CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

// Calendar subscription support 
$server->addPlugin(
    new Sabre\CalDAV\Subscriptions\Plugin()
);

// Calendar scheduling support 
$server->addPlugin(
    new Sabre\CalDAV\Schedule\Plugin()
);

$server->addPlugin(
    new Sabre\CalDAV\Schedule\IMipPlugin('philippe.logel@imathgeo.com')
);

// WebDAV-Sync plugin 
$server->addPlugin(new Sabre\DAV\Sync\Plugin());

// CalDAV Sharing support 
$server->addPlugin(new Sabre\DAV\Sharing\Plugin());
$server->addPlugin(new Sabre\CalDAV\SharingPlugin());

// Support for html frontend
$browser = new Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);

// the ICS export
$icsPlugin = new \Sabre\CalDAV\ICSExportPlugin();
$server->addPlugin($icsPlugin);

// And off we go!*/
$server->exec();
