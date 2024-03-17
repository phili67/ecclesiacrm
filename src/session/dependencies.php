<?php

use EcclesiaCRM\Utils\LoggerUtils;

// DIC configuration
$container->set('Logger', function () {
    return LoggerUtils::getAppLogger();
});