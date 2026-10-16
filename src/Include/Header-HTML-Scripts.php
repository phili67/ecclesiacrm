<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\PluginDependenciesQuery;

use EcclesiaCRM\SessionUser;

if (isset($template)) {
  $pluginName = SessionUser::getPluginNameForTemplate($template);
}

$pluginName = $pluginName ?? SessionUser::getPluginName();

?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" type="text/css"
      href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.css">

<!-- Custom EcclesiaCRM styles -->
<link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/ecclesiacrm.min.css">

<!-- Additional fonts of the CRM (bMailServiceContentsExternalCssFont, separated by semicolons) -->
<?php
foreach (explode(';', (string) SystemConfig::getValue('bMailServiceContentsExternalCssFont')) as $fontCssUrl) {
    $fontCssUrl = trim($fontCssUrl);
    if ($fontCssUrl === '' || !preg_match('#^(https?:)?//#i', $fontCssUrl)) {
        continue;
    }
    ?>
<link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($fontCssUrl, ENT_QUOTES, 'UTF-8') ?>">
<?php
}
?>

<!-- custom plugins css files -->
<?php
// we load the plugin
if (SessionUser::getCurrentPageName() == 'v2/dashboard') {
    foreach (PluginQuery::getDashboardPluginsForCurrentUser() as $plugin) {
        MiscUtils::renderPluginCssFiles($plugin->getName());
    }
} else {
    $plugin = PluginQuery::getActiveNonDashboardPluginByName($pluginName);

    if (!is_null($plugin)) {
        MiscUtils::renderPluginCssFiles($plugin->getName());
    }
}

// global css dependencies for plugins
$dependencies = PluginDependenciesQuery::create()->filterByExtension('global_css')->find();
foreach ($dependencies as $dependency) {
    $path = $documentRoot . "/" . $dependency->getUrl();
    if (file_exists($path)) {// we write the code directely in the footer.php
        ?>
        <link rel="stylesheet" href="<?= $rootPath ?>/<?= $dependency->getUrl() ?>">
        <?php
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
  } 
?>


<!-- jQuery 2.1.4 -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery/jquery.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/moment/moment-with-locales.min.js"></script>
