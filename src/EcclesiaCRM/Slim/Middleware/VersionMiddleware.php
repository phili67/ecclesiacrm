<?php

namespace EcclesiaCRM\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Http\Response as Response;

use EcclesiaCRM\Service\SystemService;

class VersionMiddleware {

	public function __invoke( Request $request, RequestHandler $handler): Response
	{
        $request = $request->withAttribute("CRM_VERSION", SystemService::getInstalledVersion() );

        $response = $handler->handle($request);

        return $response;
	}
}
