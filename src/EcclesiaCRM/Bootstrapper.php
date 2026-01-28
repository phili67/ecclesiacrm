<?php

namespace EcclesiaCRM
{
  use EcclesiaCRM\dto\LocaleInfo;
  use EcclesiaCRM\dto\SystemConfig;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\Service\SystemService;
  use Monolog\Handler\StreamHandler;
  use Monolog\Logger;
  use Propel\Runtime\Connection\ConnectionManagerSingle;
  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\Utils\RedirectUtils;
  use EcclesiaCRM\PluginQuery;
  use Propel\Runtime\ServiceContainer\StandardServiceContainer;

  class Bootstrapper
  {
      private static $manager;
      private static $manager2;
      private static $dbClassName;
      private static $databaseServerName;
      private static $databasePort;
      private static $databaseUser;
      private static $databasePassword;
      private static $databaseName;
      private static $rootPath;
      private static $DavServer;
      private static $localeInfo;
      private static $sSoftwareName;
      private static $sSoftwareIcon;

      /**
       *
       * @var Logger
       */
      private static $bootStrapLogger;
      private static StandardServiceContainer $serviceContainer;
      private static StandardServiceContainer $serviceContainer2;

      public static function init($sSERVERNAME, $dbPort, $sUSER, $sPASSWORD, 
            $sDATABASE, $sRootPath, $bLockURL, $URL, $davserver=false,
            $sSoftwareName="EcclesiaCRM", $sSoftwareIcon='icon-small.png')
      {
          global $debugBootstrapper;
          self::$databaseServerName = $sSERVERNAME;
          self::$databaseUser = $sUSER;
          self::$databasePassword = $sPASSWORD;
          self::$databasePort = $dbPort;
          self::$databaseName = $sDATABASE;
          self::$rootPath = $sRootPath;                    
          self::$DavServer = $davserver;
          self::$localeInfo = NULL;
          self::$sSoftwareName = $sSoftwareName;
          self::$sSoftwareIcon = $sSoftwareIcon;

          try {
              SystemURLs::init($sRootPath, $URL, dirname(dirname(__FILE__)));
          } catch (\Exception $e) {
              Bootstrapper::system_failure($e->getMessage());
          }
          if ($debugBootstrapper) {
              self::$bootStrapLogger = LoggerUtils::getAppLogger(Logger::DEBUG);
          } else {
              self::$bootStrapLogger = LoggerUtils::getAppLogger(Logger::INFO);
          }

          self::$bootStrapLogger->debug("Starting EcclesiaCRM");

          if (self::$DavServer == false) {
            SystemURLs::checkAllowedURL($bLockURL, $URL);
          }

          self::initMySQLI();

          self::initPropel();

          if (self::$DavServer == false) {
            if (self::isDatabaseEmpty()) {
                self::installEcclesiaCRMSchema();
            }

            self::initSession();
          }

          SystemConfig::init(ConfigQuery::create()->find());
          self::configureLogging();
          self::configureUserEnvironment();
          self::ConfigureLocale();
          if (!self::isDBCurrent()) {
              if (strpos($_SERVER['REQUEST_URI'], "/v2/system/database/update") === false) {
                  self::$bootStrapLogger->info("Database is not current, redirecting to SystemDBUpdate");
                  RedirectUtils::Redirect('v2/system/database/update');
              } else {
                  self::$bootStrapLogger->debug("Database is not current, not redirecting to SystemDBUpdate since we're already on it");
              }
          }
          LoggerUtils::ResetAppLoggerLevel();
      }

      /***
       * Gets a LocaleInfo object for the currently configured system sLanguage
       *
       * @return EcclesiaCRM\LocaleInfo
       */
      public static function GetCurrentLocale()
      {
          if ( is_null(self::$localeInfo) )
              self::$localeInfo = new LocaleInfo(SystemConfig::getValue('sLanguage'));

          return self::$localeInfo;
      }

      public static function getSoftwareName(): ?string
      {
        return self::$sSoftwareName;
      }

      public static function getSofwareIcon() : ?string
      {
        return SystemURLs::getRootPath()."/".self::$sSoftwareIcon;        
      }

      public static function getSofwareIconDocumentRoot() : ?string
      {
        return SystemURLs::getDocumentRoot()."/".self::$sSoftwareIcon;        
      }

      public static function getRealLocalInfo()
      {
          return Bootstrapper::GetCurrentLocale()->getLocaleInfo();
      }

      private static function ConfigureLocale()
      {
          if (SystemConfig::getValue('sTimeZone')) {
              self::$bootStrapLogger->debug("Setting TimeZone to: " . SystemConfig::getValue('sTimeZone'));
              date_default_timezone_set(SystemConfig::getValue('sTimeZone'));
          }

          $localeInfo = Bootstrapper::GetCurrentLocale();
          self::$bootStrapLogger->debug("Setting locale to: " . $localeInfo->getLocale());

          // this will set the $realLocalInfo unuseffull after
          $realLocaleInfo = Bootstrapper::getRealLocalInfo();

          $domain = 'messages'; // message.mo
          $sLocaleDir = SystemURLs::getDocumentRoot() . '/locale/textdomain';
          self::$bootStrapLogger->debug("Setting local text domain bind to: " . $sLocaleDir);
          bind_textdomain_codeset($domain, 'UTF-8');
          bindtextdomain($domain, $sLocaleDir);

          if (self::isDBCurrent()) {// we avoid plugins in upgrade : only the main translation are used
              $plugins = PluginQuery::create()->findByActiv(true);
              foreach ($plugins as $plugin) {
                  $sLocalePluginDir = SystemURLs::getDocumentRoot() . '/Plugins/' . $plugin->getName() . '/locale/textdomain';
                  if (file_exists($sLocalePluginDir)) {// for each plugins the domain is : 'messages-'.$plugin->getName()
                      $plugin_domain = 'messages-' . $plugin->getName();
                      bind_textdomain_codeset($plugin_domain, 'UTF-8');
                      bindtextdomain($plugin_domain, $sLocalePluginDir);
                  }
              }
          }
          // the default text domain : messages.mo
          textdomain($domain);

          self::$bootStrapLogger->debug("Locale configuration complete");
      }

      private static function initMySQLI()
      {
          global $cnInfoCentral; // need to stop using this everywhere....
          self::$bootStrapLogger->debug("Initializing MySQLi to ". self::$databaseServerName . " as " . self::$databaseUser);
          // Due to mysqli handling connections on 'localhost' via socket only, we need to tease out this case and handle
          // TCP/IP connections separately defaulting self::$databasePort to 3306 for the general case when self::$databasePort is not set.
          if (self::$databaseServerName == "localhost") {
              self::$bootStrapLogger->debug("Connecting to localhost with no port");
              $cnInfoCentral = mysqli_connect(self::$databaseServerName, self::$databaseUser, self::$databasePassword);
          } else {
              if (!isset(self::$databasePort)) {
                  self::$bootStrapLogger->debug("MySQL connection did not specify a port.  Using 3306 as defualt");
                  self::$databasePort=3306;
              }
              // Connect via TCP to specified port and pass a 'null' for database name.
              // We specify the database name in a different call, ie 'mysqli_select_db()' just below here
              self::$bootStrapLogger->debug("Connectiong to ". self::$databaseServerName . " on port " . self::$databasePort . " as " . self::$databaseUser);
              $cnInfoCentral = mysqli_connect(self::$databaseServerName, self::$databaseUser, self::$databasePassword, null, self::$databasePort);
          }
          self::testMYSQLI();
          mysqli_set_charset($cnInfoCentral, 'utf8');
          self::$bootStrapLogger->debug("Selecting database: " . self::$databaseName);
          mysqli_select_db($cnInfoCentral, self::$databaseName)
      or Bootstrapper::system_failure('Could not connect to the MySQL database <strong>'.self::$databaseName.'</strong>. Please check the settings in <strong>Include/Config.php</strong>.<br/>MySQL Error: '.mysqli_error($cnInfoCentral));
          self::$bootStrapLogger->debug("Database selected: " . self::$databaseName);
      }

      private static function testMYSQLI()
      {
          global $cnInfoCentral; // need to stop using this everywhere....
          // Do we have a connection to the database? If not, log it and tell the user
          if (!$cnInfoCentral) {
              // Sanitise the mysqli_connect_error if required.
              $sMYSQLERROR="none captured";
              if (strlen(mysqli_connect_error())>0) {
                  $sMYSQLERROR=mysqli_connect_error();
              }
              // If connecting via a socket, convert self::$databasePort to something sensible.
              if (self::$databaseServerName == "localhost") {
                  self::$databasePort = "Unix socket";
              }
              // Need to initialise otherwise logging etc will fail!
              if (!SystemConfig::isInitialized()) {
                  SystemConfig::init();
              }
              // Log the error to the application log, and show an error page to user.
              LoggerUtils::getAppLogger()->error("ERROR connecting to database at '".self::$databaseServerName."' on port '".self::$databasePort."' as user '".self::$databaseUser."' -  MySQL Error: '".$sMYSQLERROR."'");
              Bootstrapper::system_failure('Could not connect to MySQL on <strong>'.self::$databaseServerName.'</strong> on port <strong>'.self::$databasePort.'</strong> as <strong>'.self::$databaseUser.'</strong>. Please check the settings in <strong>Include/Config.php</strong>.<br/>MySQL Error: '.$sMYSQLERROR, 'Database Connection Failure');
          }
      }

      private static function getDBArrayMaps ()
      {
          require_once(__DIR__ . '/loadDatabase.php');
      }

      private static function initPropel()
      {
          self::$bootStrapLogger->debug("Initializing Propel ORM");
          // ==== ORM
          self::$dbClassName = "\\Propel\\Runtime\\Connection\\ConnectionWrapper";

          // 1. add the first service container
          self::$serviceContainer = Propel::getServiceContainer();
          self::$serviceContainer->checkVersion(2);
          self::$serviceContainer->setAdapterClass('main', 'mysql');

          // load DB array map according to the new propel upgrade
          self::getDBArrayMaps();

          // add the first mamanager
          self::$manager = new ConnectionManagerSingle('main');
          self::$manager->setConfiguration(self::buildConnectionManagerConfig());
          self::$manager->setName('main');
          self::$serviceContainer->setConnectionManager(self::$manager);
          self::$serviceContainer->setDefaultDatasource('main');

          // 2. add the second service container
          self::$serviceContainer2 = Propel::getServiceContainer();
          self::$serviceContainer2->checkVersion(2);
          self::$serviceContainer2->setAdapterClass('pluginstore', 'mysql');

          // add second manager
          self::$manager2 = new ConnectionManagerSingle('pluginstore');
          self::$manager2->setConfiguration(self::buildConnectionManagerConfig());
          self::$manager2->setName('pluginstore');

          self::$serviceContainer2->setConnectionManager( self::$manager2);
          self::$serviceContainer2->setDefaultDatasource('pluginstore');

          // we log everything is ok
          self::$bootStrapLogger->debug("Initialized Propel ORM");

          //LoggerUtils::getAppLogger()->info(json_encode(self::buildConnectionManagerConfig()));
      }
      private static function isDatabaseEmpty()
      {
          self::$bootStrapLogger->debug("Checking for CRM Database tables");
          $connection = Propel::getConnection();
          $query = "SHOW TABLES FROM `".self::$databaseName."`";
          $statement = $connection->prepare($query);
          $resultset = $statement->execute();
          $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
          if (count($results) == 0) {
              self::$bootStrapLogger->debug("No database tables found");
              return true;
          }
          self::$bootStrapLogger->debug("Found " . count($results) . " Database tables");
          return false;
      }

      private static function installEcclesiaCRMSchema()
      {
          self::$bootStrapLogger->info("Installing CRM Schema");
          $connection = Propel::getConnection();
          $version = new Version();
          $version->setVersion(SystemService::getInstalledVersion());
          $version->setUpdateStart(new \DateTime());

          // we install database
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/mysql/install/Install.sql', $connection);

          // we install all the needed plugins
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/BirthdayAnniversaryDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/CurrentUsersDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/FamilyDashboardWidget/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/FamilyInfosDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/FinanceDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/GroupDashboardWidget/mysql/Install.sql', $connection);  
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/MeetingJitsi/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/NewsDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/NoteDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/PastoralCareDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/PersonDashboardWidget/mysql/Install.sql', $connection);  
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/PersonInfosDashboard/mysql/Install.sql', $connection); 
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/SingleDashboardWidget/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/SundaySchoolDashboardWidget/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/ToDoListDashboard/mysql/Install.sql', $connection);
          SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/VolunteerDashboardWidget/mysql/Install.sql', $connection);

          $version->setUpdateEnd(new \DateTime());
          $version->save();
          self::$bootStrapLogger->info("Installed CRM Schema version: " . SystemService::getInstalledVersion());
      }

      private static function initSession()
      {
          // Initialize the session
          $sessionName = 'CRM-'.md5(self::$rootPath);
          session_cache_limiter('private_no_expire:');
          session_name($sessionName);
          session_start();
          self::$bootStrapLogger->debug("Session initialized: " . $sessionName);
      }

      private static function configureLogging()
      {
       // PHP Logs
          $phpLogPath = LoggerUtils::buildLogFilePath("php");
          self::$bootStrapLogger->debug("Configuring PHP logs at :" .$phpLogPath);
          ini_set('log_errors', 1);
          ini_set('error_log', $phpLogPath);
          // ORM Logs
          $ormLogPath = LoggerUtils::buildLogFilePath("orm");
          $ormLogger = new Logger('ormLogger');
          self::$bootStrapLogger->debug("Configuring ORM logs at :" .$ormLogPath);

          if (LoggerUtils::getLogLevel() <= 250) {
            self::$dbClassName = "\\Propel\\Runtime\\Connection\\PropelPDO";// no debugging Propel will work at it full speed
          } else {
            self::$dbClassName = "\\Propel\\Runtime\\Connection\\DebugPDO"; // DebugPDO for debugging
          }

          self::$manager->setConfiguration(self::buildConnectionManagerConfig());
          $ormLogger->pushHandler(new StreamHandler($ormLogPath, LoggerUtils::getLogLevel()));
          self::$serviceContainer->setLogger('defaultLogger', $ormLogger);
      }

      public static function GetPDO ()
      {
          return new \PDO(self::GetDSN(), self::GetUser(), self::GetPassword(),array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
      }

      public static function GetDSN()
      {
           return 'mysql:host=' . self::$databaseServerName . ';port='.self::$databasePort.';dbname=' . self::$databaseName;
      }

      public static function GetUser()
      {
        return self::$databaseUser;
      }

      public static function GetPassword()
      {
        return self::$databasePassword;
      }

      private static function buildConnectionManagerConfig()
      {
          if (is_null(self::$databasePort)) {
              self::$databasePort = 3306;
          }
          return [
            'dsn' => Bootstrapper::GetDSN(),
            'user' => self::$databaseUser,
            'password' => self::$databasePassword,
            'settings' => [
                'charset' => 'utf8',
                'queries' => ["SET sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''),'NO_ZERO_DATE',''))"],
            ],
            'classname' => self::$dbClassName,
            'model_paths' => [
                0 => 'src',
                1 => 'vendor',
            ],
        ];
      }

      private static function configureUserEnvironment()  // TODO: This function needs to stop creating global variable-variables.
      {
          global $cnInfoCentral;
          if ( SessionUser::isActive() ) {      // Not set on session/login
              // Load user variables from user config table.
              // **************************************************
              $sSQL = 'SELECT ucfg_name, ucfg_value AS value '
              ."FROM userconfig_ucfg WHERE ucfg_per_ID='".SessionUser::getId()."'";
              $rsConfig = mysqli_query($cnInfoCentral, $sSQL);     // Can't use RunQuery -- not defined yet
              if ($rsConfig) {
                  while (list($ucfg_name, $value) = mysqli_fetch_row($rsConfig)) {
                      //TODO:  THESE Variable-Variables must go awawy
                      // VV's will not work when set here; so all must be refactored away in all use cases throughout the code.
                      $$ucfg_name = $value;
                      $_SESSION[$ucfg_name] = $value;
                  }
              }
          }
      }

      private static function getDBVersion()
      {
          global $cnInfoCentral;

          $sSQL = 'SELECT `ver_version` FROM `version_ver` ORDER BY `ver_version` DESC LIMIT 1;';
          $rsVersion = mysqli_query($cnInfoCentral, $sSQL);     // Can't use RunQuery -- not defined yet
          if ($rsVersion) {
              $version = mysqli_fetch_row($rsVersion)[0];
          }

          return $version;
      }

      private static function system_failure($message, $header = 'Setup failure')
      {
          $sPageTitle = $header;
          if (!SystemConfig::isInitialized()) {
              SystemConfig::init();
          }
          require 'Include/HeaderNotLoggedIn.php'; ?>
          <div class='container'>
              <h3><?= self::$sSoftwareName ?> – <?= _($header) ?></h3>
              <div class='alert alert-danger text-center' style='margin-top: 20px;'>
                  <?= _($message) ?>
              </div>
          </div>
          <?php
          require 'Include/FooterNotLoggedIn.php';
          exit();
      }

      public static function isDBCurrent()
      {
          $dbVersion = self::getDBVersion();
          if ($dbVersion == SystemService::getInstalledVersion()) {
              self::$bootStrapLogger->debug("Database version matches installed version: " . $dbVersion . " == " .SystemService::getInstalledVersion());
              return true;
          } else {
              self::$bootStrapLogger->debug("Database version does not match installed version: " . $dbVersion. " == " .SystemService::getInstalledVersion());
              return false;
          }
      }
  }
}
