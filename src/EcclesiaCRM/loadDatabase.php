<?php
/*
*  This code is under copyright not under MIT Licence
*  copyright   : 2022 Philippe Logel all right reserved not MIT licence
*                Last update 2022-02-08
*                This code cannot be included in another application without authorization
*
*/

$serviceContainer = \Propel\Runtime\Propel::getServiceContainer();

$files = scandir(__DIR__ . "/model/EcclesiaCRM/Map");

$array = ["main" => [], "pluginstore" => []];

foreach ($files as $file) {
    if (!in_array($file, [".", ".."])) {
        $array["main"][] = '\\EcclesiaCRM\\Map\\' . str_replace(".php", "", $file);
    }
}

// we loop all the Plugins directory to find map files
$plugins = scandir(__DIR__ . "/../Plugins/");

foreach ($plugins as $plugin) {
    if (!in_array($plugin, [".", ".."]) and $plugin != "") {
        if (file_exists(__DIR__ . "/../Plugins/" . $plugin . "/model/Map/")) {
            $files = scandir(__DIR__ . "/../Plugins/" . $plugin . "/model/Map/");

            foreach ($files as $file) {
                if (!in_array($file, [".", ".."])) {
                    $array["pluginstore"][] = "\\PluginStore\\Map\\" . str_replace(".php", "", $file);
                }
            }
        }
    }
}

$serviceContainer->initDatabaseMaps($array);
