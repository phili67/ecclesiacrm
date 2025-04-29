<?php

//
// AddressBookServer
// CardDAV support
//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2025/04/28
//

use Sabre\DAV\Auth;

// Include the function library
// Very important this constant !!!!
// be carefull with the davserver constant !!!!
define("davserver", "1");
require dirname(__FILE__).'/Include/Config.php';

use Propel\Runtime\Propel;

use EcclesiaCRM\Auth\BasicAuth;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\MyPDO\CardDavPDO;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\CardDav\VCFExportPluginExtension;
use EcclesiaCRM\CardDav\CardDavACLPluginExtension;

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

$principalBackend = new PrincipalPDO();
$carddavBackend   = new CardDavPDO();

// Directory structure
$tree = [
    new Sabre\DAVACL\PrincipalCollection($principalBackend),
    new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
];

$server = new Sabre\DAV\Server($tree);

$server->setBaseUri(SystemURLs::getRootPath().'/addressbookserver.php');

// Server Plugins
$authPlugin = new Auth\Plugin($authBackend);
$server->addPlugin($authPlugin);

// acls
$aclPlugin = new CardDavACLPluginExtension();
$server->addPlugin($aclPlugin);

// add the carDav
$cardDavPlugin = new Sabre\CardDAV\Plugin();
$server->addPlugin($cardDavPlugin);

// the VCF export
$vcfPlugin = new VCFExportPluginExtension();
$server->addPlugin($vcfPlugin);

// the sharing plugin
$server->addPlugin(new Sabre\DAV\Sharing\Plugin());

// CardDAV-Sync plugin
$server->addPlugin(new Sabre\DAV\Sync\Plugin());

// Support for html frontend : normally this had to be removed
if (SystemConfig::getBooleanValue('bEnabledDavWebBrowser') ) {
  $browser = new Sabre\DAV\Browser\Plugin();
  $server->addPlugin($browser);
}


// And off we go!*/
$server->start();
