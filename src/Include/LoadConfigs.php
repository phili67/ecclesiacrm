<?php
/*******************************************************************************
 *
 *  filename    : Include/LoadConfigs.php
 *  website     : http://www.ecclesiacrm.com
 *  description : global configuration
 *                   The code in this file used to be part of part of Config.php
 *
 *  Copyright 2001-2005 Phillip Hullquist, Deane Barker, Chris Gebhardt,
 *                      Michael Wilt, Timothy Dearborn
 *
 *******************************************************************************/
 
 
 /*******************************************************************************
 *
 *  IMPORTANT : For davserver support the constant : davserver is the most important point
 *  Copyright 2018 : Philippe Logel all right reserved
 *
 *******************************************************************************/



require_once dirname(__FILE__).'/../vendor/autoload.php';

use EcclesiaCRM\Bootstrapper;

// enable this line to debug the bootstrapper process (database connections, etc).
// this makes a lot of log noise, so don't leave it on for normal production use.
//$debugBootstrapper = true;

// In the case of an old config, the port is by default : 3306
if (!isset($dbPort)) {
    $dbPort = "3306";
}


Bootstrapper::init($sSERVERNAME, $dbPort, $sUSER, $sPASSWORD, $sDATABASE, $sRootPath, $bLockURL, $URL, defined("davserver"));