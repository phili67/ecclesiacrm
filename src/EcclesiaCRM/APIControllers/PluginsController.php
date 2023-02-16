<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/02/11
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\FileSystemUtils;
use EcclesiaCRM\Plugin;
use EcclesiaCRM\PluginUserRoleQuery;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\SQLUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Version;
use Propel\Runtime\Propel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use EcclesiaCRM\Utils\MiscUtils;
use ZipArchive;

use EcclesiaCRM\PluginQuery;

class PluginsController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function activate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->Id) and SessionUser::isAdmin() )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);
            $plugin->setActiv(true);
            $plugin->save();

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function deactivate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->Id) and SessionUser::isAdmin() )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);
            $plugin->setActiv(false);
            $plugin->save();

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function remove (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->Id) and SessionUser::isAdmin() )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);

            $connection = Propel::getConnection();
            SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/' . $plugin->getName() . '/mysql/Uninstall.sql', $connection);
            LoggerUtils::getAppLogger()->info($plugin->getName()." DB is uninstalled");

            MiscUtils::removeDirectory(SystemURLs::getDocumentRoot(). '/Plugins/' . $plugin->getName() . '/');
            LoggerUtils::getAppLogger()->info($plugin->getName()." directory is removed.");

            exec('cd .. && composer dump-autoload');

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function add (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        if ( SessionUser::isAdmin() ) {
            $file = $_FILES['pluginFile'];

            $uploadedFileDestination = SystemURLs::getDocumentRoot() . "/tmp_attach/" . $file['name'];
            move_uploaded_file($file['tmp_name'], $uploadedFileDestination);

            $backupDir = "../Plugins/";

            $zip = new ZipArchive;
            if ($zip->open($uploadedFileDestination) === TRUE) {
                $res = $zip->extractTo($backupDir);
                $zip->close();

                if ($res) {
                    $connection = Propel::getConnection();

                    $folder = basename($file['name'], '.zip');

                    SQLUtils::sqlImport(SystemURLs::getDocumentRoot() . '/Plugins/' . $folder . '/mysql/Install.sql', $connection);
                    LoggerUtils::getAppLogger()->info($folder . " DB is installed");

                    $string = file_get_contents(SystemURLs::getDocumentRoot() . '/Plugins/' . $folder . '/config.json');
                    $json_a = json_decode($string, true);
                    LoggerUtils::getAppLogger()->info("Plugin  " . $json_a['Name'] . " is installed");

                    exec('cd .. && composer dump-autoload');

                    // we delete the upload zip
                    unlink($uploadedFileDestination);

                    return $response->withHeader('Location', SystemURLs::getRootPath() . '/v2/plugins')->withStatus(302);
                }
            } else {
                throw new \Exception(_("Impossible to open") . $uploadedFileDestination);
            }
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function upgrade (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $logger = LoggerUtils::getAppLogger();

        $folder = $file = $_FILES['pluginFile'];

        $extraPluginName = $_POST['name'];

        $uploadedFileDestination = SystemURLs::getDocumentRoot() . "/tmp_attach/" . $file['name'];

        // move is at the end
        move_uploaded_file($file['tmp_name'], $uploadedFileDestination);

        $backupDir = SystemURLs::getDocumentRoot() . "/tmp_attach/";

        $zip = new ZipArchive;
        if ($zip->open($uploadedFileDestination) === TRUE) {
            $res = $zip->extractTo($backupDir);
            $zip->close();

            $PluginName = pathinfo($file['name'], PATHINFO_FILENAME);

            if ($PluginName == $extraPluginName ) {

                try {

                    $string = file_get_contents($backupDir . '/' . $PluginName . '/config.json');
                    $json_a = json_decode($string, true);

                    $new_version = $json_a['version'];

                    LoggerUtils::getAppLogger()->info("Plugin  " . $new_version . " is installed");

                    //the database isn't at the current version.  Start the upgrade
                    $plugin = PluginQuery::create()->findOneByName($json_a['Name']);
                    $old_version = $plugin->getVersion();

                    $res = version_compare($old_version, $new_version);

                    if ($res == -1) {
                        $dbUpdatesFile = file_get_contents($backupDir . '/' . $PluginName . '/mysql/upgrade.json');
                        $dbUpdates = json_decode($dbUpdatesFile, true);


                        $connection = Propel::getConnection();

                        // first : we apply the pre-scripts
                        foreach ($dbUpdates as $dbUpdate) {
                            foreach ($dbUpdate['prescripts'] as $dbScript) {
                                $scriptName = $backupDir . '/' . $PluginName . '/mysql/' . $dbScript;
                                $logger->info("Upgrade DB - " . $scriptName);
                                if (pathinfo($scriptName, PATHINFO_EXTENSION) == "sql") {
                                    SQLUtils::sqlImport($scriptName, $connection);
                                } else {
                                    require_once($scriptName);
                                }
                            }
                        }

                        // we can copy the code to the new place
                        FileSystemUtils::recursiveCopyDirectory($backupDir . $PluginName . "/", SystemURLs::getDocumentRoot() . '/Plugins/' . $PluginName);

                        // second the post scripts
                        $dbUpdates = json_decode($dbUpdatesFile, true);
                        foreach ($dbUpdates as $dbUpdate) {
                            foreach ($dbUpdate['scripts'] as $dbScript) {
                                $scriptName = $backupDir . '/' . $PluginName . '/mysql/' . $dbScript;
                                $logger->info("Upgrade DB - " . $scriptName);
                                if (pathinfo($scriptName, PATHINFO_EXTENSION) == "sql") {
                                    SQLUtils::sqlImport($scriptName, $connection);
                                } else {
                                    require_once($scriptName);
                                }
                            }
                        }

                        // now we set the new version
                        $plugin->setVersion($new_version);
                        $plugin->save();


                        // now we remove the bases
                        FileSystemUtils::recursiveRemoveDirectory($backupDir . $PluginName);
                        $filename = $backupDir . $PluginName . '.zip';
                        unlink($filename);

                        return $response->withHeader('Location', SystemURLs::getRootPath() . '/v2/plugins')->withStatus(302);
                    }

                } catch (\Exception $exc) {
                    $logger->error(gettext("Databse upgrade failed") . ": " . $exc->getMessage());
                    throw $exc; //allow the method requesting the upgrade to handle this failure also.
                }
            } else {
                return $response->withJson(["status" => "failed : not same name"]);
            }
        }

        return $response->withJson(["status" => "failed : something went wrong (version is the same, zip extraction failed, write access (www-data)...)"]);
    }

    public function addDashboardPlaces (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->dashBoardItems) ) {

            foreach ($pluginPayload->dashBoardItems as $dashBoardItem) {
                $plugin = PluginQuery::create()->findOneByName($dashBoardItem[2]);

                if ( !is_null($plugin) ) {
                    $plgnRole = PluginUserRoleQuery::create()
                        ->filterByPluginId($plugin->getId())
                        ->findOneByUserId(SessionUser::getId());

                    if (!is_null($plgnRole)) {
                        $plgnRole->setDashboardOrientation($dashBoardItem[0]);
                        $plgnRole->setDashboardPlace($dashBoardItem[1]);
                        $plgnRole->save();
                    }
                }
            }

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function removeFromDashboard (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->name) ) {

            $plugin = PluginQuery::create()->findOneByName($pluginPayload->name);

            if ( !is_null($plugin)) {
                $plgnRole = PluginUserRoleQuery::create()
                    ->filterByPluginId($plugin->getId())
                    ->findOneByUserId(SessionUser::getId());

                $plgnRole->setDashboardVisible(false);
                $plgnRole->save();
            }

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function collapseFromDashboard (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $pluginPayload = (object)$request->getParsedBody();

        if ( isset ($pluginPayload->name) ) {

            $plugin = PluginQuery::create()->findOneByName($pluginPayload->name);

            if ( !is_null($plugin)) {
                $plgnRole = PluginUserRoleQuery::create()
                    ->filterByPluginId($plugin->getId())
                    ->findOneByUserId(SessionUser::getId());

                if ($plgnRole->isCollapsed()) {
                    $plgnRole->setCollapsed(false);
                } else {
                    $plgnRole->setCollapsed(true);
                }

                $plgnRole->save();
            }

            return $response->withJson(["status" => "success"]);
        }

        return $response->withJson(["status" => "failed"]);
    }
}
