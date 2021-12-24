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

    /**
     * A PHP function that will generate a secure random password.
     *
     * @param int $length The length that you want your random password to be.
     * @return string The random password.
     */
    public function random_password($length){
        //A list of characters that can be used in our
        //random password.
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
        //Create a blank string.
        $password = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }
        return $password;

    }

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

                    $passwords = "";
                    for ($i = 0;$i< 10;$i++){
                        $passwords .= $this->random_password(10);
                        if ($i < 9) {
                            $passwords .= "<br>";
                        }
                    }

                    $user->setTwoFaRescuePasswords($passwords);
                    $user->save();

                    return $response->withJson(['status' => 'yes', "rescue_passwords" => $passwords]);
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
            $user->setTwoFaRescuePasswords(NULL);
            $user->setTwoFaRescueDateTime('2000-01-01 00:00:00');
            $user->save();

            return $response->withJson(['status' => 'yes']);
        }

        return $response->withJson(['status' => 'no']);
    }
}
