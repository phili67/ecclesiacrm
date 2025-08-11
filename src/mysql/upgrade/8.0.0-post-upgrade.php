<?php
// pour le debug on se met au bon endroit : https://192.168.151.205/mysql/upgrade/8.0.0-post-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\RedirectUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("post upgrade");

// we have to logout, to validate new db entries
// You never have to do this !!!
// RedirectUtils::Redirect('session/logout');