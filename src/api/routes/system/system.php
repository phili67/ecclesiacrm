<?php

use Slim\Routing\RouteCollectorProxy;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use EcclesiaCRM\APIControllers\SystemController;

$app->group('/system', function (RouteCollectorProxy $group) {

    /*
    * @! send csp report
    * #! param: ref->stdClass :: field (for example)
        document-uri = {} "https://dev...."
        referrer = {} "https://dev.....
        violated-directive = {} "img-src"
        effective-directive = {} "img-src"
        original-policy = {} "default-src 'self';script-src 'self' 'unsafe-eval' 'nonce-YDrei8bwmfVbzNk4QvCzdg==' sidecar.gitter.im browser-update.org maps.googleapis.com www.bing.com  dev.virtualearth.net t.ssl.ak.dynamic.tiles.virtualearth.net cdnjs.cloudflare.com meet.jit.si;object-src 'self' 'nonce-YDrei8bwmfVbzNk4QvCzdg==';style-src 'self' 'unsafe-inline' fonts.googleapis.com www.bing.com;img-src 'self' 'nonce-YDrei8bwmfVbzNk4QvCzdg==' www.google.com d maps.gstatic.com maps.googleapis.com a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org www.bing.com t.ssl.ak.dynamic.tiles.virtualearth.net data:;media-src 'self';frame-src 'self' www.youtube.com;font-src 'self' fonts.gstatic.com;connect-src 'self' www.bing.com  nominatim.openstreetmap.org;report-uri https://dev.ecclesiacrm.com/api/system/csp-report"
        disposition = {} "report"
        blocked-uri = {} "https://erp.epis-strasbourg.eu/public/userdir/114EDC23-7781-4373-B18C-F877A935D6D7/Nouveau_logo.jpg"
    */
    $group->post('/csp-report', SystemController::class . ':cspReport' );
    /*
    * @! delete a file
    * #! param: ref->string :: name
    * #! param: ref->string :: path
    */
    $group->post('/deletefile', SystemController::class . ':deleteFile' );

});


