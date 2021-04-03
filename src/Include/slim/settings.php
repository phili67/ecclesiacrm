<?php

use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemConfig;

return function (ContainerInterface $container) {
    $container->set('settings', function () {
        if (SystemConfig::getValue('sLogLevel') == 0) {
            return [
                'displayErrorDetails' => false, // set to false in production
                'logErrors' => false,
                'logErrorDetails' => false
            ];
        } else {
            return [
                'displayErrorDetails' => true, // set to false in production
                'logErrors' => true,
                'logErrorDetails' => true
            ];
        }
    });
};
