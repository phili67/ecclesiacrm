<?php
declare(strict_types=1);

require_once '../Include/Config.php';
require_once '../Include/Functions.php';

// This file is generated by Composer
require_once dirname(__FILE__).'/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\HttpCache\Cache;
use Tuupola\Middleware\JwtAuthentication;
use DI\Container;

use EcclesiaCRM\Slim\Middleware\VersionMiddleware;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\PluginQuery;

use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

if (SessionUser::getId() ==  0) RedirectUtils::Redirect('Login.php');

$rootPath = str_replace('/v2/index.php', '', $_SERVER['SCRIPT_NAME']);

$container = new Container();

$settings = require_once __DIR__.'/../Include/slim/settings.php';
$settings($container);

AppFactory::setContainer($container);

$app = AppFactory::create();

// Register the http cache middleware.
$app->add( new Cache('ApiCache', 0) );

$app->setBasePath($rootPath . "/v2");

$app->add(new VersionMiddleware());

$tokenJWT = null;

if ( !is_null (TokenQuery::Create()->findOneByType("secret")) ) {
    $tokenJWT = TokenQuery::Create()->findOneByType("secret")->getToken();

    $app->add(new JwtAuthentication([
        "secret" => $tokenJWT,
        "path" => "/v2",
        "ignore" => ["/v2/"],
        "algorithm" => ["HS256"],
        "error" => function ($response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) );
        }
    ]));
}

require_once __DIR__.'/../Include/slim/error-handler.php';

// the main dashboard
require_once __DIR__ . '/routes/dashboard.php';

// the routes
require_once __DIR__ . '/routes/user/user.php';
require_once __DIR__ . '/routes/calendar/calendar.php';
require_once __DIR__ . '/routes/gdpr/gdpr.php';
require_once __DIR__ . '/routes/map/map.php';

// the sidebar routes
require_once __DIR__ . '/routes/sidebar/menulinklist.php';
require_once __DIR__ . '/routes/sidebar/pastoralcarelist.php';
require_once __DIR__ . '/routes/sidebar/fundlist.php';
require_once __DIR__ . '/routes/sidebar/volunteeropportunityeditor.php';
require_once __DIR__ . '/routes/sidebar/propertylist.php';
require_once __DIR__ . '/routes/sidebar/propertytypelist.php';
require_once __DIR__ . '/routes/sidebar/kioskmanager.php';

// people
require_once __DIR__ . '/routes/people/people.php';
require_once __DIR__ . '/routes/people/familylist.php';
require_once __DIR__ . '/routes/people/personlist.php';

// pastoralcare
require_once __DIR__ . '/routes/pastoralcare/pastoralcare.php';

require_once __DIR__ . '/routes/group/groups.php';

// backup route
require_once __DIR__ . '/routes/backup/backup.php';
require_once __DIR__ . '/routes/backup/restore.php';

// email routes
// mailchimp
require_once __DIR__ . '/routes/email/mailchimp/mailchimp.php';

// sunday school route
require_once __DIR__ . '/routes/sundayschool/sundayschool.php';

// cart route
require_once __DIR__ . '/routes/cart/cart.php';

// fundraiser
require_once __DIR__ . '/routes/fundraiser/fundraiser.php';

// errors
require_once __DIR__ . '/routes/error/error.php';

$plugins = PluginQuery::create()->findByActiv(true);

foreach ($plugins as $plugin) {
    $path = __DIR__.'/../Plugins/'.$plugin->getName().'/v2/routes/v2route.php';
    if (file_exists($path)) {
        require_once $path;
    }
}

$app->run();
