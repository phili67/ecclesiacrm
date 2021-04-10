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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\UserRole;
use EcclesiaCRM\SessionUser;

class UserRoleController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addUserRole(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->name) && isset ($params->global) && isset ($params->userPerms) && isset ($params->userValues)) {
            $userCFGs = UserRoleQuery::Create()
                ->filterByName($params->name)
                ->_and()->filterByGlobal($params->global)
                ->_and()->filterByPermissions($params->userPerms)
                ->_and()->filterByValue($params->userValues)
                ->find();

            if ($userCFGs->count()) {
                return $response->withJson(['status' => "error"]);
            }

            $userCFG = new UserRole();

            $userCFG->setName($params->name);
            $userCFG->setGlobal($params->global);
            $userCFG->setPermissions($params->userPerms);
            $userCFG->setValue($params->userValues);

            $userCFG->save();
        } else {
            return $response->withStatus(500, gettext("POST to UserRole name, global variable, userPerms and userValues"));
        }
        return $response->withJson(['status' => "success"]);
    }

    public function getUserRole(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->roleID)) {
            $userCFG = UserRoleQuery::Create()->findOneById($params->roleID);

            return $response->withJson([
                'roleID' => $userCFG->getId(),
                'name' => $userCFG->getName(),
                'global' => $userCFG->getGlobal(),
                'usrPerms' => $userCFG->getPermissions(),
                'userValues' => $userCFG->getValue()
            ]);
        } else {
            return $response->withStatus(500, gettext("POST to UserRole name, global variable, userPerms and userValues"));
        }
        return $response->withJson(['status' => "success"]);
    }

    public function renameUserRole(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->roleID) && isset ($params->name)) {
            $userCFG = UserRoleQuery::Create()->findOneById($params->roleID);

            $userCFG->setName($params->name);

            $userCFG->save();

        } else {
            return $response->withStatus(500, gettext("POST to UserRole name, global variable, userPerms and userValues"));
        }
        return $response->withJson(['status' => "success"]);
    }

    public function getAllUserRoles(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $userCFG = UserRoleQuery::Create()->find();

        return $response->withJson($userCFG->toArray());
    }

    public function deleteUserRole(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->roleID)) {
            $userCFG = UserRoleQuery::Create()->findOneById($params->roleID);

            if (!empty($userCFG)) {
                $userCFG->delete();
            }
        } else {
            return $response->withStatus(500, gettext("POST to UserRole name, global variable, userPerms and userValues"));
        }
        return $response->withJson(['status' => "success"]);
    }
}
