<?php
declare(strict_types=1);

require_once '../Include/Config.php';


/*if (strpos($_SERVER['REQUEST_URI'], "/v2/users/change/password/" ) === 0) {
    $bNoPasswordRedirect = true; // Subdue UserPasswordChange redirect to prevent looping
}*/
require_once '../Include/Functions.php';

// This file is generated by Composer
require_once dirname(__FILE__).'/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\HttpCache\Cache;
use Slim\Middleware\ContentLengthMiddleware;
use DI\Container;

use EcclesiaCRM\Slim\Middleware\VersionMiddleware;

use EcclesiaCRM\PluginQuery;

use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\VIEWControllers\VIEWUserController;

if (SessionUser::getId() ==  0) RedirectUtils::Redirect('Login.php');

$rootPath = str_replace('/v2/index.php', '', $_SERVER['SCRIPT_NAME']);

$container = new Container();

$settings = require_once __DIR__.'/../Include/slim/settings.php';
$settings($container);

AppFactory::setContainer($container);

$app = AppFactory::create();

$contentLengthMiddleware = new ContentLengthMiddleware();
$app->add($contentLengthMiddleware);

// Register the http cache middleware.
$app->add( new Cache('ApiCache', 0) );

$app->setBasePath($rootPath . "/v2");

$app->add(new VersionMiddleware());

$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secret" => SessionUser::getUser()->getJwtSecretForApi(),
    "secure" => SessionUser::getUser()->isSecure(),
    "path" => "/api",
    "cookie" => SessionUser::getUser()->getUserNameForApi(),
    "ignore" => ["/api/families", "/api/persons/"],
    "algorithm" => "HS256",
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->getBody()
            ->write( json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) );
    }
]));

require_once __DIR__.'/../Include/slim/error-handler.php';

if (SessionUser::getUser()->getNeedPasswordChange()) {// only one route is staying
    $app->get('/users/change/password', VIEWUserController::class . ':renderChangePassword' );
    $app->get('/users/change/password/{PersonID:[0-9]+}', VIEWUserController::class . ':renderChangePassword' );
    $app->post('/users/change/password/{PersonID:[0-9]+}', VIEWUserController::class . ':renderChangePassword' );
} else {// else the password is now changed
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
    require_once __DIR__ . '/routes/sidebar/systemsettings.php';

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

    // system
    require_once __DIR__ . '/routes/system/system.php';

    // deposits
    require_once __DIR__ . '/routes/deposit/deposit.php';

    // queries
    require_once __DIR__ . '/routes/query/query.php';

    // plugins routes
    require_once __DIR__ . '/routes/plugins/plugins.php';

    // we load the plugin
    if (SessionUser::getCurrentPageName() == 'v2/dashboard') {
        // only dashboard plugins are loaded on the maindashboard page
        $plugins = PluginQuery::create()
            ->filterByCategory('Dashboard', Criteria::EQUAL )
            ->findByActiv(true);


    } else {
        $plugins = PluginQuery::create()
            ->filterByCategory('Dashboard', Criteria::NOT_EQUAL )
            ->findByActiv(true);
    }

    foreach ($plugins as $plugin) {
        $path = __DIR__ . '/../Plugins/' .$plugin->getName().'/v2/routes/v2route.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
}

$app->run();
