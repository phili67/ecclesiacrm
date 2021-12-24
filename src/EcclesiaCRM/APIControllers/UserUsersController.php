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

    public function passwordReset(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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


    public function applyRole(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function webDavKey(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function lockUnlock(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function loginReset(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function deleteUser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function showSince(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function showTo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function userstwofaremove(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->userID) ) {

            $user = UserQuery::create()->findOneByPersonId($params->userID);

            $secret = $user->getTwoFaSecret();

            if (!is_null($secret)) {

                $user->setTwoFaSecret(NULL);
                $user->setTwoFaSecretConfirm(false);
                $user->setTwoFaRescuePasswords(NULL);
                $user->save();

                return $response->withJson(['status' => 'yes']);
            }
        }

        return $response->withJson(['status' => 'no']);
    }

    public function userstwofapending(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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
}
