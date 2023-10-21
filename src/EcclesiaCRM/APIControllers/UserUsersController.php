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
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\Emails\ResetPasswordEmail;
use EcclesiaCRM\Emails\AccountDeletedEmail;
use EcclesiaCRM\Emails\UnlockedEmail;
use EcclesiaCRM\Note;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Emails\UpdateAccountEmail;

use RobThree\Auth\TwoFactorAuth;

use DateTime;

class UserUsersController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function passwordReset(ServerRequest $request, Response $response, array $args): Response
    {
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
                $logger = $this->container->get('Logger');
                $logger->error($email->getError());

                throw new \Exception($email->getError());
            }
        } else {
            return $response->withStatus(404);
        }
    }


    public function applyRole(ServerRequest $request, Response $response, array $args): Response
    {
        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->userID) && isset ($params->roleID)) {
            $user = UserQuery::create()->findPk($params->userID);

            if (!is_null($user)) {
                $roleName = $user->ApplyRole($params->roleID);

                $email = new UpdateAccountEmail($user, _("Your user role has changed"));
                $email->send();

                return $response->withJson(['success' => true, 'userID' => $params->userID, 'roleName' => $roleName]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function webDavKey(ServerRequest $request, Response $response, array $args): Response
    {
        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        if (isset ($params->userID)) {

            $user = UserQuery::create()->findPk($params->userID);
            if (!is_null($user)) {
                return $response->withJson(['status' => "success", "token" => $user->getWebdavkey(), "token2" => $user->getWebdavPublickey()]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function lockUnlock(ServerRequest $request, Response $response, array $args): Response
    {
        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(401);
        }

        $params = (object)$request->getParsedBody();

        // note : When a user is deactivated the associated person is deactivated too
        //        but when a person is deactivated the user is deactivated too.
        //        Important : a person re-activated don't reactivate the user

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
                $email = new UpdateAccountEmail($user, ($newStatus) ? _("Account Deactivated") : _("Account Activated"));
                $email->send();

                //Create a note to record the status change
                $note = new Note();
                $note->setPerId($user->getPersonId());
                $note->setText(($newStatus) ? _('Account Deactivated') : _("Account Activated"));
                $note->setType('edit');
                $note->setEntered(SessionUser::getUser()->getPersonId());
                $note->save();

                return $response->withJson(['success' => true]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function loginReset(ServerRequest $request, Response $response, array $args): Response
    {
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
                $logger = $this->container->get('Logger');
                $logger->error($email->getError());
            }
            return $response->withStatus(200)->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteUser(ServerRequest $request, Response $response, array $args): Response
    {
        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(401);
        }
        $user = UserQuery::create()->findPk($args['userId']);
        if (!is_null($user)) {
            $userConfigs = UserConfigQuery::create()->findByPersonId($user->getId());
            foreach ($userConfigs as $userConfig) {
                $userConfig->delete();
            }
            $email = new AccountDeletedEmail($user);
            $user->delete();
            if (!$email->send()) {
                $logger = $this->container->get('Logger');
                $logger->error($email->getError());
            }
            return $response->withStatus(200)->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(404);
        }
    }

    public function showSince(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if (isset ($params->date)) {
            $user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

            $user->setShowSince($params->date);
            $user->save();

            $_SESSION['user'] = $user;

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function showTo(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if (isset ($params->date)) {
            $user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

            $user->setShowTo($params->date);
            $user->save();

            $_SESSION['user'] = $user;

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function userstwofaremove(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->userID) ) {

            $user = UserQuery::create()->findOneByPersonId($params->userID);

            $secret = $user->getTwoFaSecret();

            if (!is_null($secret)) {

                $user->setTwoFaSecret(NULL);
                $user->setTwoFaSecretConfirm(false);
                $user->setTwoFaRescuePasswords(NULL);
                $user->setTwoFaRescueDateTime('2000-01-01 00:00:00');
                $user->save();

                return $response->withJson(['status' => 'yes']);
            }
        }

        return $response->withJson(['status' => 'no']);
    }

    public function userstwofapending(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->userID) ) {

            $user = UserQuery::create()->findOneByPersonId($params->userID);

            $secret = $user->getTwoFaSecret();

            if ( !is_null($secret) ) {

                // we set the date time to be now
                // after we've 60 seconds to use the recovery codes
                $user->setTwoFaRescueDateTime(new DateTime('now'));
                $user->save();

                return $response->withJson(['status' => 'yes']);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function controlAccount(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->userID) and SessionUser::isAdmin() ) {
            $user = UserQuery::create()->findOneByPersonId($params->userID);

            $_SESSION['ControllerAdminUserId'] = SessionUser::getId();
            $_SESSION['ControllerAdminUserName'] = SessionUser::getUser()->getUserName();
            $_SESSION['ControllerAdminUserSecret'] = SessionUser::getUser()->getJwtSecret();
            $_SESSION['ControllerAdminUserToken'] = SessionUser::getUser()->getJwtToken();

            if ( !is_null($user) ) {
                $user->LoginPhaseActivations(true);
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function exitControlAccount(ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->userID) and isset($_SESSION['ControllerAdminUserId']) ) {
            $user = UserQuery::create()->findOneByPersonId($params->userID);

            unset($_SESSION['ControllerAdminUserId']);
            unset($_SESSION['ControllerAdminUserName']);
            unset($_SESSION['ControllerAdminUserSecret']);
            unset($_SESSION['ControllerAdminUserToken']);

            if ( !is_null($user) ) {
                $user->LoginPhaseActivations(true);
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }
}
