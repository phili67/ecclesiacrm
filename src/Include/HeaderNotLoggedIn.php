<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;

$bSuppressSessionTests = true;
require_once 'Header-function.php';
require_once 'Header-Security.php';

use EcclesiaCRM\PluginQuery;
use Propel\Runtime\ActiveQuery\Criteria;

$localeInfo = Bootstrapper::GetCurrentLocale();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<meta http-equiv="pragma" content="no-cache">-->
    <meta http-equiv="Content-Type" content="text/html">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.css">
    <!-- Custom EcclesiaCRM styles -->
    <link rel="stylesheet" href="<?= SystemURLs::getRootPath() ?>/skin/ecclesiacrm.min.css">

    <!-- custom plugins css files -->
    <?php
        // we load the plugin
        $plugins = PluginQuery::create()
            ->filterByCategory('Dashboard', Criteria::NOT_EQUAL )
            ->findByActiv(true);

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

    <!-- jQuery JS -->
    <script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery/jquery.min.js"></script>

    <title>EcclesiaCRM: <?= $sPageTitle ?></title>

    <?php
    Header_fav_icons();
    ?>

</head>
<body class="hold-transition login-page text-sm">

  <script nonce="<?= SystemURLs::getCSPNonce() ?>"  >
      window.CRM = {
          root: "<?= SystemURLs::getRootPath() ?>",
          lang: "<?= $localeInfo->getLanguageCode() ?>",
          locale: "<?= $localeInfo->getLocale() ?>",
          shortLocale: "<?= $localeInfo->getShortLocale() ?>",
          datePickerformat:"<?= SystemConfig::getValue('sDatePickerPlaceHolder') ?>"
      };
  </script>
