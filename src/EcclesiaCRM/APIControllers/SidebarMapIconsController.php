<?php


//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\SessionUser;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;


use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\ListOptionIcon;

class SidebarMapIconsController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    function getAllMapIcons(ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isMenuOptionsEnabled() ) {
            return $response->withStatus(401);
        }

        $files = scandir('../skin/icons/markers');

        return $response->withJson(array_values(array_diff($files, array(".", "..", 'shadow'))));
    }

    function checkOnlyPersonView(ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isMenuOptionsEnabled() ) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->onlyPersonView) && isset ($params->lstID) && isset ($params->lstOptionID)) {
            $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);

            if (!empty($icon)) {
                $icon->setOnlyVisiblePersonView($params->onlyPersonView);
                $icon->save();
            } else {
                $icon = new ListOptionIcon();

                $icon->setListId($params->lstID);
                $icon->setListOptionId($params->lstOptionID);
                $icon->setOnlyVisiblePersonView($params->onlyPersonView);
                $icon->save();
            }

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    function setIconName(ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isMenuOptionsEnabled() ) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->name) && isset ($params->lstID) && isset ($params->lstOptionID)) {

            $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);

            if (!empty($icon)) {
                $icon->setUrl($params->name);
                $icon->save();
            } else {
                $icon = new ListOptionIcon();

                $icon->setListId($params->lstID);
                $icon->setListOptionId($params->lstOptionID);
                $icon->setUrl($params->name);
                $icon->save();
            }

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    function removeIcon(ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isMenuOptionsEnabled() ) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->lstID) && isset ($params->lstOptionID)) {

            $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);

            if (!empty($icon)) {
                $icon->delete();

                return $response->withJson(['status' => "success"]);
            }

            return $response->withJson(['status' => "failed"]);

        }

        return $response->withJson(['status' => "failed"]);
    }
}
