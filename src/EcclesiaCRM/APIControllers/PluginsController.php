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
use EcclesiaCRM\SQLUtils;
use EcclesiaCRM\Utils\LoggerUtils;
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

        if ( isset ($pluginPayload->Id) )
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

        if ( isset ($pluginPayload->Id) )
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

        if ( isset ($pluginPayload->Id) )
        {
            $plugin = PluginQuery::create()->findOneById($pluginPayload->Id);

            $connection = Propel::getConnection();
            SQLUtils::sqlImport(SystemURLs::getDocumentRoot().'/Plugins/' . $plugin->getName() . '/mysql/uninstall.sql', $connection);
            LoggerUtils::getAppLogger()->info($plugin->getName()." DB is uninstalled");

            MiscUtils::removeDirectory(SystemURLs::getDocumentRoot(). '/Plugins/' . $plugin->getName() . '/');
            LoggerUtils::getAppLogger()->info($plugin->getName()." directory is removed.");

            exec('cd ../../.. && composer dump-autoload');
            LoggerUtils::getAppLogger()->info("cd ../.. && composer dump-autoload");

            return $response->withJson(["status" => "success22"]);
        }

        return $response->withJson(["status" => "failed"]);
    }

    public function add (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $file = $_FILES['pluginFile'];

        $uploadedFileDestination = SystemURLs::getDocumentRoot()."/tmp_attach/".$file['name'];
        move_uploaded_file($file['tmp_name'], $uploadedFileDestination);

        $backupDir = "../Plugins/";

        $zip = new ZipArchive;
        if ($zip->open($uploadedFileDestination) === TRUE) {
            $res = $zip->extractTo($backupDir);
            $zip->close();

            if ($res) {
                $connection = Propel::getConnection();

                $folder = basename($file['name'], '.zip');

                SQLUtils::sqlImport(SystemURLs::getDocumentRoot() . '/Plugins/' . $folder . '/mysql/install.sql', $connection);
                LoggerUtils::getAppLogger()->info($folder." DB is installed");

                $string = file_get_contents(SystemURLs::getDocumentRoot() . '/Plugins/' . $folder . '/config.json');
                $json_a = json_decode($string, true);
                LoggerUtils::getAppLogger()->info("Plugin  ".$json_a['Name']. " is installed");

                exec('cd ../../.. && composer dump-autoload');

                // we delete the upload zip
                unlink($uploadedFileDestination);

                return $response->withHeader('Location', '/v2/plugins')->withStatus(302);
            }
        } else {
            throw new \Exception(_("Impossible to open") . $uploadedFileDestination);
        }


        return $response->withJson(["status" => "failed"]);
    }
}
