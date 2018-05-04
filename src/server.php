<?php

/* copyright 2018 Philippe Logel all rights reserved */

use Sabre\DAV;
use Sabre\DAV\Auth;

// Include the function library
// Very important this constant !!!!
// be carefull with the webdav constant !!!!
define("webdav", "1");
require dirname(__FILE__).'/Include/Config.php';

use EcclesiaCRM\Auth\BasicAuth;
use EcclesiaCRM\PersonalServer\EcclesiaCRMServer;

use EcclesiaCRM\dto\SystemURLs;

//Mapping PHP errors to exceptions
// problem with the webdav constant

/*function exception_error_handler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");*/

// The autoloader is unusefull : because it's done in the Config.php
//require 'vendor/autoload.php';

// authentication
$authBackend = new BasicAuth();
$authBackend->setRealm('EcclesiaCRM_DAV');

$authPlugin = new Auth\Plugin($authBackend);

// On entrer dans le répertoire courant du user
$tree = [
    new PersonalServer\HomeCollection($authBackend),
];

// we create the server
$server = new EcclesiaCRM\PersonalServer\EcclesiaCRMServer($tree,$authBackend);

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

// This ensures that we get a pretty index in the browser, but it is
// optional.
$server->addPlugin(new DAV\Browser\Plugin());

//
// All we need to do now, is to fire up the server
//
$server->exec();

?>