<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;

use Slim\Views\PhpRenderer;

class VIEWSessionController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    

    public function renderLoginLock (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/');

        $session = 'Lock';
        $username = '';
        $timeout = '';

        return $renderer->render($response, 'session.php', $this->argumentsLoginArray($session, $username, $timeout));
    }

    public function renderLogin (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/');

        $session = 'none';

        $username = '';

        if (isset($args['usr_name'])) {
            $username = InputUtils::LegacyFilterInput($args['usr_name']);
        }

        $timeout = '';

        if (isset($args['time'])) {
            $timeout = InputUtils::LegacyFilterInput($args['time']);
        }

        

        return $renderer->render($response, 'session.php', $this->argumentsLoginArray($session, $username, $timeout));
    }

    public function argumentsLoginArray ($session, $username, $timeout)
    {        
        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => _('Login'),
            'session'     => $session,
            'username'    => $username,
            'timeout'     => $timeout
        ];

        return $paramsArguments;
    }
}
