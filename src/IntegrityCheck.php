<?php

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\AppIntegrityService;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

//Set the page title
$sPageTitle = gettext('Integrity Check Results');
if (!SessionUser::getUser()->isAdmin()) {
    RedirectUtils::Redirect('index.php');
    exit;
}
require 'Include/Header.php';
$integrityCheckFile = SystemURLs::getDocumentRoot().'/integrityCheck.json';

if (file_exists($integrityCheckFile)) {
    $IntegrityCheckDetails = json_decode(file_get_contents($integrityCheckFile));
} else {
    $appIntegrity = AppIntegrityService::verifyApplicationIntegrity();
    file_put_contents($integrityCheckFile, json_encode($appIntegrity));

    /*$IntegrityCheckDetails->status = 'failure';
    $IntegrityCheckDetails->message = 'integrityCheck.json file missing';*/

    $IntegrityCheckDetails = json_decode(file_get_contents($integrityCheckFile));
}

if (AppIntegrityService::arePrerequisitesMet()) {
    ?>
  <div class="alert alert-success">
    <h4><?= gettext('All Application Prerequisites Satisfied') ?> </h4>
    <p><?= gettext('All components that EcclesiaCRM relies upon are present and correctly configured on this server') ?></p>
  </div>
  <?php
} else {
        ?>
  <div class="alert alert-danger">
    <h4><?= gettext('Unmet Application Prerequisites') ?> </h4>
    <p><?= gettext('Certain components that EcclesiaCRM relies upon are missing or improperly configured on this server.  The application may continue to function, but may produce unexpected behavior.') ?></p>
    <ul>
      <?php
      foreach (AppIntegrityService::getUnmetPrerequisites() as $prerequisiteName) {
          echo "<li>".$prerequisiteName.": ".gettext("Failed")."</li>";
      } ?>
    </ul>
  </div>
<?php
    }
if ($IntegrityCheckDetails->status == 'failure') {
    ?>
  <div class="alert alert-danger">
    <h4><?= gettext('Integrity Check Failure') ?> </h4>
    <p><?= gettext('The previous integrity check failed') ?></p>
    <p><?= gettext('Details:')?> <?=  $IntegrityCheckDetails->message ?></p>
    <?php
      if (count($IntegrityCheckDetails->files) > 0) {
          ?>
        <p><?= gettext('Files failing integrity check') ?>:
        <ul>
          <?php
          foreach ($IntegrityCheckDetails->files as $key => $file) {
              if ( is_numeric($key) ) {
              ?>
            <li><?= gettext('File Name')?>: <?= $file->filename ?>
              <?php
              if ($file->status == 'File Missing') {
                  ?>
                <ul>
                 <li><?= gettext('File Missing')?></li>
                </ul>
                <?php
              } else {
                  ?>
                <ul>
                 <li><?= gettext('Expected Hash')?>: <?= $file->expectedhash ?></li>
                 <li><?= gettext('Actual Hash') ?>: <?= $file->actualhash ?></li>
                </ul>
                <?php
              } ?>
            </li>
            <?php
              } elseif ( is_string($key) and count($file) > 0) {
                  ?>
                  <li><?= _("Plugin")." : ". $key ?>
                      <ul>
                  <?php
                  foreach ($file as $plugin_file) {
                      ?>
                      <li>
                      <?= gettext('File Name')?>: <?= $plugin_file->filename ?>
                      </li>
                      <?php
                      if ($file->status == 'File Missing') {
                          ?>
                          <ul>
                              <li><?= gettext('File Missing')?></li>
                          </ul>
                          <?php
                      } else {
                          ?>
                          <ul>
                              <li><?= gettext('Expected Hash')?>: <?= $plugin_file->expectedhash ?></li>
                              <li><?= gettext('Actual Hash') ?>: <?= $plugin_file->actualhash ?></li>
                          </ul>
                          <?php
                      }
                  }
                  ?>
                      </ul>
                  </li>
                  <?php
              }
          } ?>
        </ul>
        <?php
      } ?>
  </div>
  <?php
} else {
          ?>
  <div class="alert alert-success">
    <h4><?= gettext('Integrity Check Passed') ?> </h4>
    <p><?= gettext('The previous integrity check passed.  All system file hashes match the expected values.') ?></p>
  </div>
  <?php
      }
?>

<?php
require 'Include/Footer.php';
?>
