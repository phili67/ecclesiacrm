<?php

/* copyright 2018 Logel Philippe All right reserved */

use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\UserRole;

$app->group('/userrole', function () {
  
    $this->post('/add',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();
          
        if (isset ($params->name) && isset ($params->global) && isset ($params->userPerms) && isset ($params->userValues)) {
          $userCFGs = UserRoleQuery::Create()
            ->filterByName($params->name)
            ->_or()->filterByGlobal($params->global)
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
    });
    
    $this->post('/get',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
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
    });
    
    $this->post('/rename',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
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
    });

    
    $this->post('/getall',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $userCFG = UserRoleQuery::Create()->find();
          
        return $response->withJson($userCFG->toArray());                  
    });
    
    $this->post('/delete',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
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
    });
});
