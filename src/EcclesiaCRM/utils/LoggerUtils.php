<?php

namespace EcclesiaCRM\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

class LoggerUtils
{
    public static function getLogLevel()
    {
        return intval(SystemConfig::getValue("sLogLevel"));
    }

    public static function buildLogFilePath($type)
    {
        return $logFilePrefix = SystemURLs::getDocumentRoot() . '/logs/' . date("Y-m-d") . '-' . $type . '.log';
    }

    /**
     * @return Logger
     */
    public static function getAppLogger()
    {
        $logger = new Logger('defaultLogger');
        $logger->pushHandler(new StreamHandler(self::buildLogFilePath("app"), self::getLogLevel()));
        return $logger;
    }

}