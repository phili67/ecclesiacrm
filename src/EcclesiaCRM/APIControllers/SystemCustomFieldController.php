<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Interop\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\PersonCustomMasterQuery;

class SystemCustomFieldController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * A method that does the work to handle getting an existing person custom fields by type.
     *
     * @param \Slim\Http\Request $p_request The request.
     * @param \Slim\Http\Response $p_response The response.
     * @param array $p_args Arguments
     * @return \Slim\Http\Response The augmented response.
     */
    public function getPersonFieldsByType(ServerRequest $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $typeId = $params['typeId'];

        $fields = PersonCustomMasterQuery::create()->filterByTypeId($typeId)->find();

        $keyValue = [];

        foreach ($fields as $field) {
            array_push($keyValue, ["id" => $field->getId(), "value" => $field->getCustomName()]);
        }

        return $response->withJson($keyValue);
    }
}
