<?php

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;

// Set the page title and include HTML header
$sPageTitle = gettext("Family Registration");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");

$sessionLogoPath = SystemURLs::getRootPath() . "/icon-large.png";
$headerHTML = Bootstrapper::getSoftwareName();
$sHeader = SystemConfig::getValue("sHeader");
$sEntityName = SystemConfig::getValue("sEntityName");
if (!empty($sHeader)) {
    $headerHTML = html_entity_decode($sHeader, ENT_QUOTES);
} elseif (!empty($sEntityName)) {
    $headerHTML = $sEntityName;
}
?>

    <div class="register-box blur external-auth-box external-auth-box--narrow">
        <div class="card register-box-body external-auth-card">
            <div class="card-header register-logo external-auth-header">
                <div class="external-auth-brand">
                    <img src="<?= $sessionLogoPath ?>" alt="<?= Bootstrapper::getSoftwareName() ?>" class="external-auth-brand__logo">
                    <span class="external-auth-brand__version"><?= SystemService::getDBMainVersion() ?></span>
                </div>
            </div>

            <div class="external-auth-body">
            <p class="external-auth-title text-center"><b><?= $headerHTML ?></b><?= _("A new password was sent to you. Please check your email"); ?></p>

            <p class="mt-3 mb-1 text-center">
                <a class="btn btn-primary" href="<?= SystemURLs::getRootPath() ?>/session/login"><?= _("Login") ?></a>
            </p>
            </div>
        </div>
    </div>
<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
