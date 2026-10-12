<?php
declare(strict_types=1);

//
//  Entry point of the WOPI routes (api/wopi/...), called by the Collabora Online server.
//  It has no user session and no JWT : the access is granted by the WOPI access token.
//  It is included by api/index.php, before the session check.
//

use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;
use DI\Container;

use EcclesiaCRM\Slim\dependencies;
use EcclesiaCRM\Slim\Error\handlers;

$container = new Container();

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->add(new ContentLengthMiddleware());

$app->setBasePath($rootPath . "/api");

dependencies::install($container);

$handlers = new handlers($app);
$handlers->installHandlers();

require_once __DIR__.'/routes/documents/documents-wopi.php';

$app->run();
