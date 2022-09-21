<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PluginQuery;

?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" type="text/css"
      href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.css">

<!-- Custom EcclesiaCRM styles -->
<link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/ecclesiacrm.min.css">

<!-- custom plugins css files -->
<?php
$plugins = scandir(__DIR__ . "/../Plugins/");

foreach ($plugins as $plugin) {
    if (!in_array($plugin, [".", ".."]) and $plugin != "") {
        if (file_exists(__DIR__ . "/../Plugins/" . $plugin . "/skin/css/")) {
            $files = scandir(__DIR__ . "/../Plugins/" . $plugin . "/skin/css/");

            foreach ($files as $file) {
                if (!in_array($file, [".", ".."])) {
                    ?>
                    <link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/Plugins/<?= $plugin ?>/skin/css/<?= $file ?>">
                    <?php
                }
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
