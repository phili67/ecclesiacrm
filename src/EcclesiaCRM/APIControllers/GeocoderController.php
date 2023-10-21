<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;


use EcclesiaCRM\Utils\GeoUtils;

class GeocoderController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * A method that return GeoLocation based on an address.
     *
     * @param \Slim\Http\Request $p_request   The request.
     * @param \Slim\Http\Response $p_response The response.
     * @param array $p_args Arguments
     * @return \Slim\Http\Response The augmented response.
     */
    function getGeoLocals (ServerRequest $request, Response $response, array $args): Response {
        $input = json_decode($request->getBody());
        if (!empty($input)) {
            return $response->withJson(GeoUtils::getLatLong($input->address));
        }
        return $response->withStatus(404);
    }
}
