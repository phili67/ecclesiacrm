<?php

/* copyright 2018 Logel Philippe All right reserved */

use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\UserProfileQuery;
use EcclesiaCRM\UserProfile;

$app->group('/userprofile', function () {
  
    $this->post('/add',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();
          
        if (isset ($params->name) && isset ($params->global) && isset ($params->userPerms) && isset ($params->userValues)) {
          $userCFGs = UserProfileQuery::Create()
            ->filterByUserProfileName($params->name)
            ->_or()->filterByUserProfileGlobal($params->global)
            ->_and()->filterByUserProfilePermissions($params->userPerms)
            ->_and()->filterByUserProfileValue($params->userValues)
            ->find();
            
          if ($userCFGs->count()) {
            return $response->withJson(['status' => "error"]);
          }

          $userCFG = new UserProfile();
          
          $userCFG->setUserProfileName($params->name);
          $userCFG->setUserProfileGlobal($params->global);
          $userCFG->setUserProfilePermissions($params->userPerms);
          $userCFG->setUserProfileValue($params->userValues);
          
          $userCFG->save();          
        } else {
            throw new \Exception(gettext("POST to userprofile name, global variable, userPerms and userValues"),500);
        }
        return $response->withJson(['status' => "success"]);
    });
    
    $this->post('/get',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();
          
        if (isset ($params->profileID)) {
          $userCFG = UserProfileQuery::Create()->findOneByUserProfileId($params->profileID);
          
          return $response->withJson([
            'profileID' => $userCFG->getUserProfileId(),
            'name' => $userCFG->getUserProfileName(),
            'global' =>  $userCFG->getUserProfileGlobal(),
            'usrPerms' =>  $userCFG->getUserProfilePermissions(),
            'userValues' =>  $userCFG->getUserProfileValue()
          ]);                  
        } else {
            throw new \Exception(gettext("POST to userprofile name, global variable, userPerms and userValues"),500);
        }
        return $response->withJson(['status' => "success"]);
    });
    
    $this->post('/rename',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();
          
        if (isset ($params->profileID) && isset ($params->name)) {
          $userCFG = UserProfileQuery::Create()->findOneByUserProfileId($params->profileID);
          
          $userCFG->setUserProfileName($params->name);
          
          $userCFG->save();
          
        } else {
            throw new \Exception(gettext("POST to userprofile name, global variable, userPerms and userValues"),500);
        }
        return $response->withJson(['status' => "success"]);
    });

    
    $this->post('/getall',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $userCFG = UserProfileQuery::Create()->find();
          
        return $response->withJson($userCFG->toArray());                  
    });
    
    $this->post('/delete',function($request,$response,$args) {
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isManageGroupsEnabled())) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();
          
        if (isset ($params->profileID)) {
          $userCFG = UserProfileQuery::Create()->findOneByUserProfileId($params->profileID);
          
          if (!empty($userCFG)) {
             $userCFG->delete();
          }
        } else {
            throw new \Exception(gettext("POST to userprofile name, global variable, userPerms and userValues"),500);
        }
        return $response->withJson(['status' => "success"]);
    });
});
