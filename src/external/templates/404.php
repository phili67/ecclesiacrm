<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Bootstrapper;

$sPageTitle = _("Page not fond");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");
?>
  <div class="register-logo" style=" margin: 72px auto 0;">
      <a href="<?= SystemURLs::getRootPath() ?>/"><?= Bootstrapper::getAppName() ?> </a>
  </div>

  <div class="error-page">
    <h2 class="headline text-yellow">404</h2>
    <div class="error-content">
      <h3><i class="fas fa-exclamation-triangle text-yellow"></i><?= _("Oops! Page not found.") ?></h3>
      <p/>
      <h4><?= $message ?></h4>
    </div>
    <!-- /.error-content -->
  </div>
  <!-- /.error-page -->

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
