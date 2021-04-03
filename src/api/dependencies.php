<?php


use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\Service\FinancialService;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\Service\ReportingService;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Utils\LoggerUtils;

// DIC configuration

/*$container['PersonService'] = new PersonService();
$container['GroupService'] = new GroupService();

$container['FinancialService'] = new FinancialService();
$container['ReportingService'] = new ReportingService();*/

$container->set('SystemService', function () {
    return new SystemService();
});

/*$container['CalendarService'] = new CalendarService();
$container['Logger'] = LoggerUtils::getAppLogger();*/
