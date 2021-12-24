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

use EcclesiaCRM\SessionUser;

use RobThree\Auth\TwoFactorAuth;

class SystemSettingsIndividualController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get2FA (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // substitute your company or app name here
        $tfa = new TwoFactorAuth('EcclesiaCRM');

        $user = SessionUser::getUser();
        $secret = $user->getTwoFaSecret();

        if ( is_null($secret) ) {
            $secret = $tfa->createSecret();
            $user->setTwoFaSecret($secret);
            $user->save();
        }

        $img = $tfa->getQRCodeImageAsDataUri($user->getPerson()->getFullName()." : EcclesiaCRM", $secret);

        return $response->withJson(['img' => $img]);
    }

    public function verify2FA (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $result = (object)$request->getParsedBody();

        if ( isset ($result->code) ) {
            $code = $result->code;

            $tfa = new TwoFactorAuth('EcclesiaCRM');

            $user = SessionUser::getUser();
            $secret = $user->getTwoFaSecret();

            if ( !is_null($secret) ) {
                if ($tfa->verifyCode($secret, $code)) {
                    $user->setTwoFaSecretConfirm(true);
                    $user->save();
                    return $response->withJson(['status' => 'yes']);
                }
            }
        }

        return $response->withJson(['status' => 'no']);
    }

    public function remove2FA (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = SessionUser::getUser();
        $secret = $user->getTwoFaSecret();

        if ( !is_null($secret) ) {
            $user->setTwoFaSecret(NULL);
            $user->setTwoFaSecretConfirm(false);
            $user->save();
            return $response->withJson(['status' => 'yes']);
        }

        return $response->withJson(['status' => 'no']);
    }
}
