<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;

$csp = array(
    "default-src 'self'",
    "script-src 'self' 'unsafe-eval' 'unsafe-inline' sidecar.gitter.im browser-update.org maps.googleapis.com www.bing.com  dev.virtualearth.net t.ssl.ak.dynamic.tiles.virtualearth.net cdnjs.cloudflare.com", /*'nonce-".SystemURLs::getCSPNonce()."' // replacement of : 'unsafe-inline' */
    "object-src 'none'",
    "style-src 'self' 'unsafe-inline' fonts.googleapis.com www.bing.com",
    "img-src 'self' www.google.com d maps.gstatic.com maps.googleapis.com a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org www.bing.com t.ssl.ak.dynamic.tiles.virtualearth.net data:",
    "media-src 'self'",
    "frame-src 'self' www.youtube.com",
    "font-src 'self' fonts.gstatic.com",
    "connect-src 'self' www.bing.com  nominatim.openstreetmap.org",
    "report-uri ".SystemURLs::getRootPath()."/api/system/csp-report"
);
if (SystemConfig::getBooleanValue("bHSTSEnable")) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy-Report-Only:".join(";", $csp));
header("Access-Control-Allow-Origin", "*");
