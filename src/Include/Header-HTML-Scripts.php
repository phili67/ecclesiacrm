<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PluginQuery;

use EcclesiaCRM\SessionUser;

use Propel\Runtime\ActiveQuery\Criteria;

?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" type="text/css"
      href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.css">

<!-- Custom EcclesiaCRM styles -->
<link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/ecclesiacrm.min.css">

<!-- custom plugins css files -->
<?php
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
    if (file_exists(__DIR__ . "/../Plugins/" . $plugin->getName() . "/skin/css/")) {
        $files = scandir(__DIR__ . "/../Plugins/" . $plugin->getName() . "/skin/css/");

        foreach ($files as $file) {
            if (!in_array($file, [".", ".."])) {
                ?>
                <link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/Plugins/<?= $plugin->getName() ?>/skin/css/<?= $file ?>">
                <?php
            }
        }
    }

}
?>

<?php
  if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
?>
  <!-- Leaflet -->
  <link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/external/leaflet/leaflet.css">
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/leaflet/leaflet-src.js"></script>
<?php
  } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
  <!-- Bing Maps -->
  <script type='text/javascript' src='https://www.bing.com/api/maps/mapcontrol?callback=GetMap&key=<?= SystemConfig::getValue('sBingMapKey') ?>' async defer></script>
<?php
  }
?>


<!-- jQuery 2.1.4 -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery/jquery.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/moment/moment-with-locales.min.js"></script>
