<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use Slim\Views\PhpRenderer;


class VIEWErrorController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function render404Error(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/error/');

        return $renderer->render($response, '404.php', $this->argumentsError404ListArray($args['method'], $args['uri']));
    }

    public function argumentsError404ListArray($method, $uri)
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

}
