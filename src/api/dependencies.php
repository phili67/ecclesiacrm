<?php


use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\Service\FinancialService;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Service\PersonService;
use EcclesiaCRM\Service\ReportingService;
use EcclesiaCRM\Service\SystemService;

// DIC configuration

$container['PersonService'] = new PersonService();
$container['GroupService'] = new GroupService();

$container['FinancialService'] = new FinancialService();
$container['ReportingService'] = new ReportingService();

$container['SystemService'] = new SystemService();

$container['CalendarService'] = new CalendarService();
$container['Logger'] = $logger;
