<?php


use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\Service\FinancialService;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\Service\ReportingService;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Service\SundaySchoolService;

use EcclesiaCRM\Utils\LoggerUtils;

// DIC configuration

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

$container->set('Logger', function () {
    return LoggerUtils::getAppLogger();
});

$container->set('CalendarService', function () {
    return new CalendarService();
});

$container->set('SundaySchoolService', function () {
    return new SundaySchoolService();
});


