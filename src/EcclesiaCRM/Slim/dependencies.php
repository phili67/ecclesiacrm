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
            if (!isset($_SESSION['Logger'])) {
                    $_SESSION['Logger'] = LoggerUtils::getAppLogger();
            }
            return $_SESSION['Logger'];
        });

        // DIC configuration
        if ($loggeronly == false) {
            $container->set('MailChimpService', function () {
                if (!isset($_SESSION['MailChimpService'])) {
                    $_SESSION['MailChimpService'] = new MailChimpService();
                }
                return $_SESSION['MailChimpService'];
            });

            $container->set('PersonService', function () {
                if (!isset($_SESSION['PersonService'])) {
                    $_SESSION['PersonService'] = new PersonService();
                }
                return $_SESSION['PersonService'];
            });

            $container->set('GroupService', function () {
                if (!isset($_SESSION['GroupService'])) {
                    $_SESSION['GroupService'] = new GroupService();
                }
                return $_SESSION['GroupService'];                
            });

            $container->set('FinancialService', function () {
                if (!isset($_SESSION['FinancialService'])) {
                    $_SESSION['FinancialService'] = new GroupService();
                }
                return $_SESSION['FinancialService'];                   
            });

            $container->set('ReportingService', function () {
                if (!isset($_SESSION['ReportingService'])) {
                    $_SESSION['ReportingService'] = new ReportingService();
                }
                return $_SESSION['ReportingService'];
            });

            $container->set('SystemService', function () {
                if (!isset($_SESSION['SystemService'])) {
                    $_SESSION['SystemService'] = new SystemService();
                }
                return $_SESSION['SystemService'];                
            });
            
            $container->set('CalendarService', function () {
                if (!isset($_SESSION['CalendarService'])) {
                    $_SESSION['CalendarService'] = new CalendarService();
                }
                return $_SESSION['CalendarService'];                
            });

            $container->set('SundaySchoolService', function () {
                if (!isset($_SESSION['SundaySchoolService'])) {
                    $_SESSION['SundaySchoolService'] = new SundaySchoolService();
                }
                return $_SESSION['SundaySchoolService'];                
            });

            $container->set('CacheProvider', function () {
                if (!isset($_SESSION['CacheProvider'])) {
                    $_SESSION['CacheProvider'] = new CacheProvider();
                }
                return $_SESSION['CacheProvider'];
            });

            $container->set('PastoralCareService', function () {
                if (!isset($_SESSION['PastoralCareService'])) {
                    $_SESSION['PastoralCareService'] = new PastoralCareService();
                }
                return $_SESSION['PastoralCareService'];                   
            });
        }
    }
}
