<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/09/10
//


namespace Plugins\APIControllers;

use EcclesiaCRM\Utils\InputUtils;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;

spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../model', '/'), $className) . '.php';
});

use PluginStore\NoteDashboardQuery;

class NoteDashboardController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function modify(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->note) ) {
            $note = NoteDashboardQuery::create()
                ->findOneByUserId(SessionUser::getId());

            $note->setNote(InputUtils::FilterHTML($input->note));
            $note->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }
}
