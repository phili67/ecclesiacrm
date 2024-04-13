<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;

use Slim\Views\PhpRenderer;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\SessionUser;


class VIEWSessionController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderLoginLock (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/');

        $session = 'Lock';
        $username = '';
        $timeout = '';

        return $renderer->render($response, 'session.php', $this->argumentsLoginArray($session, $username, $timeout));
    }

    public function renderLogin (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/');

        $session = 'none';

        $username = '';

        if (isset($args['usr_name'])) {
            $username = InputUtils::LegacyFilterInput($args['usr_name']);
        }

        $timeout = '';

        if (isset($args['time'])) {
            $timeout = InputUtils::LegacyFilterInput($args['time']);
        }

        return $renderer->render($response, 'session.php', $this->argumentsLoginArray($session, $username, $timeout));
    }

    public function argumentsLoginArray ($session, $username, $timeout)
    {        
        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sCSPNonce'   => SystemURLs::getCSPNonce(),
            'sPageTitle'  => _('Login'),
            'session'     => $session,
            'username'    => $username,
            'timeout'     => $timeout
        ];

        return $paramsArguments;
    }

    public function renderLogout(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        if ( !is_null(SessionUser::getUser()) ) {

            if (!isset($_SESSION['sshowPledges']) || ($_SESSION['sshowPledges'] == '')) {
                $_SESSION['sshowPledges'] = 0;
            }
            if (!isset($_SESSION['sshowPayments']) || ($_SESSION['sshowPayments'] == '')) {
                $_SESSION['sshowPayments'] = 0;
            }
            if (!isset($_SESSION['bSearchFamily']) || ($_SESSION['bSearchFamily'] == '')) {
                $_SESSION['bSearchFamily'] = 0;
            }
        
            $currentUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
        
            // unset jwt token
            $userName = $currentUser->getUserName();
            if (isset($_COOKIE[$userName])) {
                unset($_COOKIE[$userName]);
                setcookie($userName, null, -1, '/');
            }
        
            if (isset($_SESSION['ControllerAdminUserId'])) {
                // in the case the account is in control of an admin
                unset($_SESSION['ControllerAdminUserId']);
                unset($_SESSION['ControllerAdminUserName']);
                unset($_SESSION['ControllerAdminUserSecret']);
                unset($_SESSION['ControllerAdminUserToken']);
            }

            if (isset($_SESSION['photos'])) {
                unset($_SESSION['photos']);
            }
        
            if (!is_null($currentUser)) {
        
              $currentUser->setShowPledges($_SESSION['sshowPledges']);
              $currentUser->setShowPayments($_SESSION['sshowPayments']);
              $currentUser->setDefaultFY($_SESSION['idefaultFY']);
              $currentUser->setCurrentDeposit($_SESSION['iCurrentDeposit']);
              $currentUser->setIsLoggedIn(false);
        
              // we've to leave the old jwt secret and token
              $currentUser->setJwtToken(NULL);
              $currentUser->setJwtSecret(NULL);
        
              $currentUser->save();
            }
        }
        
        $_COOKIE = [];
        $_SESSION = [];
        session_destroy();

        return $response
            ->withHeader('Location', SystemURLs::getRootPath().'/session/login')
            ->withStatus(302);
    }
}