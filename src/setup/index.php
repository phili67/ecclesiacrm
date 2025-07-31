<?php

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Slim\Error\handlers;

use Slim\Factory\AppFactory;
use DI\Container;



error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/external.log');

if (file_exists('../Include/Config.php')) {
    header('Location: ../index.php');
} else {
    require_once dirname(__FILE__) . '/../vendor/autoload.php';

    $rootPath = str_replace('/setup/index.php', '', $_SERVER['SCRIPT_NAME']);
    SystemURLs::init($rootPath, '', dirname(__FILE__)."/../");
    SystemConfig::init(null, 1);

    // Instantiate the app
    $container = new Container();

    AppFactory::setContainer($container);

    $app = AppFactory::create();

    $app->setBasePath($rootPath . "/setup");

    $handlers = new handlers($app);
    $handlers->installHandlers();

    require __DIR__ . '/routes/setup.php';

    $app->run();
}
