<?php

require '../Include/Config.php';

// This file is generated by Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$rootPath = str_replace('/session/index.php', '', $_SERVER['SCRIPT_NAME']);

// Instantiate the app
$container = new Container();

$settings = require __DIR__.'/../Include/slim/settings.php';
$settings($container);

AppFactory::setContainer($container);

$app = AppFactory::create();

// Register the http cache middleware.
//$app->add( new Cache('private', 0) );

$app->setBasePath($rootPath . "/session");

// Set up
require __DIR__.'/dependencies.php';
require __DIR__ . '/../Include/slim/error-handler.php';

// routes
require_once __DIR__ . '/routes/session.php';

// Run app
$app->run();
