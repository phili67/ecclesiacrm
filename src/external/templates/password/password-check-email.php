<?php

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

// Set the page title and include HTML header
$sPageTitle = gettext("Family Registration");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");
?>

    <div class="register-box" style="width: 600px;">
        <div class="register-logo">
            <?php
            $headerHTML = Bootstrapper::getSoftwareName();
            $sHeader = SystemConfig::getValue("sHeader");
            $sChurchName = SystemConfig::getValue("sChurchName");
            if (!empty($sHeader)) {
                $headerHTML = html_entity_decode($sHeader, ENT_QUOTES);
            } else if (!empty($sChurchName)) {
                $headerHTML = $sChurchName;
            }
            ?>
            <a href="<?= SystemURLs::getRootPath() ?>/"><?= $headerHTML ?></a>
        </div>

        <div class="register-box-body">
            <?= _("A new password was sent to you. Please check your email"); ?>

            <p class="mt-3 mb-1 text-center">
                <a class="btn btn-primary" href="<?= SystemURLs::getRootPath() ?>/session/login"><?= _("Login") ?></a>
            </p>
        </div>
    </div>
<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
