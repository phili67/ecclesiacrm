<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use Slim\Views\PhpRenderer;

$app->group('/error', function (RouteCollectorProxy $group) {
    $group->get('/404/{method}/{uri}', 'render404Error');
});



function render404Error(Request $request, Response $response, array $args)
{
    $renderer = new PhpRenderer('templates/error/');

    return $renderer->render($response, '404.php', argumentsError404ListArray($args['method'], $args['uri']));
}

function argumentsError404ListArray($method, $uri)
{
    $sPageTitle = _("Error");

    $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
        'sRootDocument' => SystemURLs::getDocumentRoot(),
        'sPageTitle' => $sPageTitle,
        'Method' => $method,
        'uri' => $uri
    ];

    return $paramsArguments;
}
