<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\Service\SystemService;

$sPageTitle = _("Page not fond");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");

$sessionLogoPath = SystemURLs::getRootPath() . "/icon-large.png";
?>
  <div class="external-auth-box external-auth-box--narrow">
    <div class="card external-auth-card">
      <div class="card-header external-auth-header">
        <div class="external-auth-brand">
          <img src="<?= $sessionLogoPath ?>" alt="<?= Bootstrapper::getSoftwareName() ?>" class="external-auth-brand__logo">
          <span class="external-auth-brand__version"><?= SystemService::getDBMainVersion() ?></span>
        </div>
      </div>
      <div class="card-body external-auth-body">
        <div class="error-page external-error-page" style="margin-top: 0;">
          <h2 class="headline text-yellow">404</h2>
          <div class="error-content">
            <h3><i class="fas fa-exclamation-triangle text-yellow"></i><?= _("Oops! Page not found.") ?></h3>
            <p/>
            <h4><?= $message ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
