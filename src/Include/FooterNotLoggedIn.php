<?php
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Bootstrapper;
?>
    <div style="background-color: white; padding-top: 5px; padding-bottom: 5px; text-align: center; position: fixed; bottom: 0; width: 100%">
      <strong><?= gettext('Copyright') ?> &copy; 2017-<?= date('Y') ?> <a href="https://www.ecclesiacrm.com" target="_blank"><?= Bootstrapper::getSoftwareName() ?><?= SystemService::getPackageMainVersion() ?></a>.</strong> <?= gettext('All rights reserved')?>.
    </div>


  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/select2/select2.min.js"></script>

  <!-- Bootstrap 3.3.5 -->
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.js"></script>

  <!-- AdminLTE App -->
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/adminlte/adminlte.min.js"></script>

  <!-- InputMask -->
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/inputmask/jquery.inputmask.min.js"></script>
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/iCheck/icheck.min.js"></script>


  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

  <!-- Bootbox -->
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootbox/bootbox.all.min.js"></script>
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/i18next/i18next.min.js"></script>
  <?php if (!is_null(Bootstrapper::GetCurrentLocale())): ?><script src="<?= SystemURLs::getRootPath() ?>/locale/js/<?= Bootstrapper::GetCurrentLocale()->getLocale() ?>.js"></script><?php endif; ?>

  <!-- Bootbox -->
  <script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootbox/bootbox.all.min.js"></script>

  <script nonce="<?= SystemURLs::getCSPNonce() ?>">
    i18nextOpt = {
        lng:window.CRM.shortLocale,
        nsSeparator: false,
        keySeparator: false,
        pluralSeparator:false,
        contextSeparator:false,
        fallbackLng: false,
        resources: { }
    };

    i18nextOpt.resources[window.CRM.shortLocale] = {
        translation: window.CRM.i18keys
    };
    i18next.init(i18nextOpt);

    /*
      * Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
      * see: https://github.com/select2/select2/issues/5993
      * see: https://github.com/jquery/jquery/issues/4382
      *
      * TODO: Recheck with the select2 GH issue and remove once this is fixed on their side
      */

    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });
  </script>
  <?php

    //If this is a first-run setup, do not include google analytics code.
    if ($_SERVER['SCRIPT_NAME'] != '/setup/index.php') {
        include_once('analyticstracking.php');
    }
 ?>
</body>
</html>
