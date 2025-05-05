<?php

//
// WebDavServer
// WebDAV support
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

use EcclesiaCRM\Auth\BasicAuth;
use EcclesiaCRM\PersonalServer\EcclesiaCRMServer;
use EcclesiaCRM\MyPDO\PrincipalPDO;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\WebDav\WebDavACLPlugin;

if ( !SystemConfig::getBooleanValue('bEnabledDav') ) {
  RedirectUtils::Redirect('members/404.php?type=Dav');
  return;
}


// authentication
$authBackend = new BasicAuth();
$authBackend->setRealm('EcclesiaCRM_DAV');

$authPlugin = new Auth\Plugin($authBackend);

$principalBackend = new PrincipalPDO();

// On entre dans le répertoire courant du user
$tree = [
    new Sabre\DAVACL\PrincipalCollection($principalBackend),
    new Sabre\DAVACL\FS\MyHomeCollection($principalBackend, $authBackend)
];

// we create the server
$server = new EcclesiaCRMServer($tree,$authBackend);

// If your server is not on your webroot, make sure the following line has the
// correct information
$server->setBaseUri(SystemURLs::getRootPath().'/server.php');

// Adding the plugin to the server : authentication
$server->addPlugin($authPlugin);

// The lock manager is reponsible for making sure users don't overwrite
// each others changes.
$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

//$aclPlugin = new Sabre\DAVACL\Plugin();
$aclPlugin = new WebDavACLPlugin();
$server->addPlugin($aclPlugin);

// to share folder and files
$server->addPlugin(new Sabre\DAV\Sharing\Plugin());

// sync plugin
$server->addPlugin(new Sabre\DAV\Sync\Plugin());

// This ensures that we get a pretty index in the browser, but it is
// optional.
if (SystemConfig::getBooleanValue('bEnabledDavWebBrowser') ) {
  $server->addPlugin(new DAV\Browser\Plugin());
}

//
// All we need to do now, is to fire up the server
//
$server->start();
