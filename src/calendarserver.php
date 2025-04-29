<?php

//
// CalendarServer
// CalDAV support
//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2025/04/28
//

use Sabre\DAV;
use Sabre\DAV\Auth;

// Include the function library
// Very important this constant !!!!
// be carefull with the davserver constant !!!!
define("davserver", "1");
require dirname(__FILE__).'/Include/Config.php';

use Propel\Runtime\Propel;

use EcclesiaCRM\Auth\BasicAuth;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\PersonalServer\EcclesiaCRMCalendarServer;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\RedirectUtils;

if ( !SystemConfig::getBooleanValue('bEnabledDav') ) {
  RedirectUtils::Redirect('members/404.php?type=Dav');
  return;
}

//*****************
// settings
//*****************
date_default_timezone_set(SystemConfig::getValue('sTimeZone')); //<------ Be carefull to set the good Time Zone : 'Europe/Paris'

// Backends
$authBackend = new BasicAuth();
$authBackend->setRealm('EcclesiaCRM_DAV');

$calendarBackend = new CalDavPDO();
$principalBackend = new PrincipalPDO();

// Directory structure
$tree = [
    new Sabre\DAVACL\PrincipalCollection($principalBackend),
    new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
];

//$server = new Sabre\DAV\Server($tree);
$server = new EcclesiaCRMCalendarServer($tree);

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
    new Sabre\CalDAV\Schedule\IMipPlugin(SystemConfig::getValue('sChurchEmail'))
);

// CalDAV-Sync plugin
$server->addPlugin(new Sabre\DAV\Sync\Plugin());

// CalDAV Sharing support
$server->addPlugin(new Sabre\DAV\Sharing\Plugin());
$server->addPlugin(new Sabre\CalDAV\SharingPlugin());

// the ICS export
$icsPlugin = new \Sabre\CalDAV\ICSExportPlugin();
$server->addPlugin($icsPlugin);

// Support for html frontend
if (SystemConfig::getBooleanValue('bEnabledDavWebBrowser') ) {
  $browser = new Sabre\DAV\Browser\Plugin();
  $server->addPlugin($browser);
}

// And off we go!*/
$server->start();
