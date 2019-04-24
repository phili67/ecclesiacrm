<?php

// Users APIs
use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\User;
use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\Emails\ResetPasswordEmail;
use EcclesiaCRM\Emails\AccountDeletedEmail;
use EcclesiaCRM\Emails\UnlockedEmail;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Person;
use EcclesiaCRM\Family;
use EcclesiaCRM\Note;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Emails\UpdateAccountEmail;
use EcclesiaCRM\Utils\LoggerUtils;


$app->group('/users', function () {
    $this->post('/{userId:[0-9]+}/password/reset', 'passwordReset');
    $this->post('/applyrole' , 'applyRole');
    $this->post('/webdavKey' , 'webDavKey');
    $this->post('/lockunlock', 'lockUnlock');
    $this->post('/showsince', 'showSince');
    $this->post('/showto', 'showTo');
    $this->post('/{userId:[0-9]+}/login/reset', 'loginReset');
    $this->delete('/{userId:[0-9]+}', 'deleteUser');
});

function passwordReset (Request $request, Response $response, array $args ) {
  if (!SessionUser::getUser()->isAdmin()) {
      return $response->withStatus(401);
  }
  $user = UserQuery::create()->findPk($args['userId']);
  if (!is_null($user)) {
      $password = $user->resetPasswordToRandom();
      $user->save();
      $user->createTimeLineNote("password-reset");
      $email = new ResetPasswordEmail($user, $password);
      if ($email->send()) {
          return $response->withStatus(200)->withJson(['status' => "success"]);
      } else {
          $logger = LoggerUtils::getAppLogger();
          $logger->error($email->getError());
          
          throw new \Exception($email->getError());
      }
  } else {
      return $response->withStatus(404);
  }
}


function applyRole (Request $request, Response $response, array $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }
    
    $params = (object)$request->getParsedBody();
      
    if (isset ($params->userID) && isset ($params->roleID)) {
      $user = UserQuery::create()->findPk($params->userID);
       
      if (!is_null($user)) {
         $user->ApplyRole($params->roleID);

         return $response->withJson(['success' => true,'userID' => $params->userID]);
      }
    }
        
    return $response->withJson(['success' => false]);
}

function webDavKey (Request $request, Response $response, array $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }
    
    $params = (object)$request->getParsedBody();
      
    if (isset ($params->userID)) {
    
      $user = UserQuery::create()->findPk($params->userID);
      if (!is_null($user)) {
        return $response->withJson(['status' => "success", "token" => $user->getWebdavkey(),"token2" => $user->getWebdavPublickey()]);
      }
    }
    
    return $response->withJson(['status' => "failed"]);
}

function lockUnlock (Request $request, Response $response, array $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }
    
    $params = (object)$request->getParsedBody();
      
    if (isset ($params->userID)) {
    
      $user = UserQuery::create()->findPk($params->userID);
      
      if (!is_null($user) && $user->getPersonId() != 1) {            
        $newStatus = (empty($user->getIsDeactivated()) ? true : false);

        //update only if the value is different
        if ($newStatus) {
            $user->setIsDeactivated(true);
        } else {
            $user->setIsDeactivated(false);
        }
        
        $user->save();
        
        // a mail is notified
        $email = new UpdateAccountEmail($user, ($newStatus)?gettext("Deactivate account"):gettext("The same as before"));
        $email->send();


        //Create a note to record the status change
        $note = new Note();
        $note->setPerId($user->getPersonId());
        if ($newStatus == 'false') {
            $note->setText(gettext('User Deactivated'));
        } else {
            $note->setText(gettext('User Activated'));
        }
        $note->setType('edit');
        $note->setEntered(SessionUser::getUser()->getPersonId());
        $note->save();

        return $response->withJson(['success' => true]);
      }
    }
    
    return $response->withJson(['success' => false]);
}

function loginReset (Request $request, Response $response, array $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }
    $user = UserQuery::create()->findPk($args['userId']);
    if (!is_null($user)) {
        $user->setFailedLogins(0);
        $user->save();
        $user->createTimeLineNote("login-reset");
        $email = new UnlockedEmail($user);
        if (!$email->send()) {
          $logger = LoggerUtils::getAppLogger();
          $logger->error($email->getError());
        }
        return $response->withStatus(200)->withJson(['status' => "success"]);
    } else {
        return $response->withStatus(404);
    }
}

function deleteUser (Request $request, Response $response, array $args) {
    if (!SessionUser::getUser()->isAdmin()) {
        return $response->withStatus(401);
    }
    $user = UserQuery::create()->findPk($args['userId']);
    if (!is_null($user)) {
        $userConfig =  UserConfigQuery::create()->findPk($user->getId());
        if (!is_null($userConfig)) {
            $userConfig->delete();
        }
        $email = new AccountDeletedEmail($user);
        $user->delete();
        if (!$email->send()) {
          $logger = LoggerUtils::getAppLogger();
          $logger->error($email->getError());
        }
        return $response->withStatus(200)->withJson(['status' => "success"]);
    } else {
        return $response->withStatus(404);
    }
}

function showSince (Request $request, Response $response, array $args) {
    $params = (object)$request->getParsedBody();
      
    if (isset ($params->date)) {
       $user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
       
       $user->setShowSince ($params->date);
       $user->save();
       
       $_SESSION['user'] = $user;

       return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
}

function showTo (Request $request, Response $response, array $args) {
    $params = (object)$request->getParsedBody();
      
    if (isset ($params->date)) {
       $user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
       
       $user->setShowTo ($params->date);
       $user->save();
       
       $_SESSION['user'] = $user;

       return $response->withJson(['success' => true]);
    }
    
    return $response->withJson(['success' => false]);
}