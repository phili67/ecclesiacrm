<?php

/* copyright 2018 Logel Philippe All right reserved */
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\UserRole;
use EcclesiaCRM\SessionUser;

$app->group('/userrole', function (RouteCollectorProxy $group) {

    $group->post('/add', 'addUserRole' );
    $group->post('/get', 'getUserRole' );
    $group->post('/rename', 'renameUserRole' );
    $group->post('/getall', 'getAllUserRoles' );
    $group->post('/delete', 'deleteUserRole' );

});


function addUserRole (Request $request, Response $response, array $args) {
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
        throw new \Exception(gettext("POST to UserRole name, global variable, userPerms and userValues"),500);
    }
    return $response->withJson(['status' => "success"]);
}

function getUserRole (Request $request, Response $response, array $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $params = (object)$request->getParsedBody();

    if (isset ($params->roleID)) {
      $userCFG = UserRoleQuery::Create()->findOneById($params->roleID);

      return $response->withJson([
        'roleID' => $userCFG->getId(),
        'name' => $userCFG->getName(),
        'global' =>  $userCFG->getGlobal(),
        'usrPerms' =>  $userCFG->getPermissions(),
        'userValues' =>  $userCFG->getValue()
      ]);
    } else {
        throw new \Exception(gettext("POST to UserRole name, global variable, userPerms and userValues"),500);
    }
    return $response->withJson(['status' => "success"]);
}

function renameUserRole (Request $request, Response $response, array $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $params = (object)$request->getParsedBody();

    if (isset ($params->roleID) && isset ($params->name)) {
      $userCFG = UserRoleQuery::Create()->findOneById($params->roleID);

      $userCFG->setName($params->name);

      $userCFG->save();

    } else {
        throw new \Exception(gettext("POST to UserRole name, global variable, userPerms and userValues"),500);
    }
    return $response->withJson(['status' => "success"]);
}

function getAllUserRoles (Request $request, Response $response, array $args) {
    if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroupsEnabled())) {
        return $response->withStatus(401);
    }

    $userCFG = UserRoleQuery::Create()->find();

    return $response->withJson($userCFG->toArray());
}

function deleteUserRole (Request $request, Response $response, array $args) {
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
        throw new \Exception(gettext("POST to UserRole name, global variable, userPerms and userValues"),500);
    }
    return $response->withJson(['status' => "success"]);
}
