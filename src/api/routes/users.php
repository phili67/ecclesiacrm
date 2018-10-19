<?php

// Users APIs
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


$app->group('/users', function () {

    $this->post('/{userId:[0-9]+}/password/reset', function ($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
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
                $this->Logger->error($email->getError());
                throw new \Exception($email->getError());
            }
        } else {
            return $response->withStatus(404);
        }
    });
        
     $this->post('/applyrole', function ($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
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
    });
    
    
    
    $this->post('/webdavKey', function ($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
            return $response->withStatus(401);
        }
        
        $params = (object)$request->getParsedBody();
          
        if (isset ($params->userID)) {
        
          $user = UserQuery::create()->findPk($params->userID);
          if (!is_null($user)) {
            return $response->withJson(['status' => "success", "token" => $user->getWebdavkey()]);
          }
        }
        
        return $response->withJson(['status' => "failed"]);
    });
    
    $this->post('/lockunlock', function ($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
            return $response->withStatus(401);
        }
        
        $params = (object)$request->getParsedBody();
          
        if (isset ($params->userID)) {
        
          $user = UserQuery::create()->findPk($params->userID);
          
          if (!is_null($user) && $user->getPersonId() != 1) {
            $person = $user->getPerson();
            
            $newStatus = (empty($person->getDateDeactivated()) ? true : false);

            //update only if the value is different
            if ($newStatus) {
                $person->setDateDeactivated(date('YmdHis'));
            } else {
                $person->setDateDeactivated(Null);
            }
            $person->save();
        
            // a one person family is deactivated too
            if ($person->getFamily()->getPeople()->count() == 1) {
              if ($newStatus) {
                  $person->getFamily()->setDateDeactivated(date('YmdHis'));
              } else {
                  $person->getFamily()->setDateDeactivated(Null);
              }
              $person->getFamily()->save();
            }

            //Create a note to record the status change
            $note = new Note();
            $note->setPerId($personId);
            if ($newStatus == 'false') {
                $note->setText(gettext('Person Deactivated'));
            } else {
                $note->setText(gettext('Person Activated'));
            }
            $note->setType('edit');
            $note->setEntered($_SESSION['user']->getPersonId());
            $note->save();

            return $response->withJson(['success' => true]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    

    $this->post('/{userId:[0-9]+}/login/reset', function ($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
            return $response->withStatus(401);
        }
        $user = UserQuery::create()->findPk($args['userId']);
        if (!is_null($user)) {
            $user->setFailedLogins(0);
            $user->save();
            $user->createTimeLineNote("login-reset");
            $email = new UnlockedEmail($user);
            if (!$email->send()) {
                $this->Logger->warn($email->getError());
            }
            return $response->withStatus(200)->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(404);
        }
    });

    $this->delete('/{userId:[0-9]+}', function ($request, $response, $args) {
        if (!$_SESSION['user']->isAdmin()) {
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
                $this->Logger->warn($email->getError());
            }
            return $response->withStatus(200)->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(404);
        }
    });
});
