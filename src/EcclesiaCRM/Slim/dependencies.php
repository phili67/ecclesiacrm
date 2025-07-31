<?php

namespace EcclesiaCRM\Slim;

use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\Service\FinancialService;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\Service\ReportingService;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\Service\PastoralCareService;

use Slim\HttpCache\CacheProvider;
use EcclesiaCRM\Utils\LoggerUtils;

class dependencies
{
    static public function install(\DI\Container $container, BOOL $loggeronly = false): void
    {
        $container->set('Logger', function () {
            return LoggerUtils::getAppLogger();
        });

        // DIC configuration
        if ($loggeronly == false) {
            $container->set('MailChimpService', function () {
                return new MailChimpService();
            });

            $container->set('PersonService', function () {
                return new PersonService();
            });

            $container->set('GroupService', function () {
                return new GroupService();
            });

            $container->set('FinancialService', function () {
                return new FinancialService();
            });

            $container->set('ReportingService', function () {
                return new ReportingService();
            });

            $container->set('SystemService', function () {
                return new SystemService();
            });
            
            $container->set('CalendarService', function () {
                return new CalendarService();
            });

            $container->set('SundaySchoolService', function () {
                return new SundaySchoolService();
            });

            $container->set('CacheProvider', function () {
                return new CacheProvider();
            });

            $container->set('PastoralCareService', function () {
                return new PastoralCareService();
            });
        }
    }
}
