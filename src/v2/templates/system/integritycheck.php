<?php

/*******************************************************************************
 *
 *  filename    : integritycheck.php
 *  last change : 2023-05-19
 *  website     : http://www.ecclesiacrm.com
 *                          © 2023 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\AppIntegrityService;

require $sRootDocument . '/Include/Header.php';

$integrityCheckFile = SystemURLs::getDocumentRoot().'/integrityCheck.json';

if (file_exists($integrityCheckFile)) {
    $IntegrityCheckDetails = json_decode(file_get_contents($integrityCheckFile));
} else {
    $appIntegrity = AppIntegrityService::verifyApplicationIntegrity();
    file_put_contents($integrityCheckFile, json_encode($appIntegrity));

    $IntegrityCheckDetails->status = 'failure';
    $IntegrityCheckDetails->message = 'integrityCheck.json file missing';

    $IntegrityCheckDetails = json_decode(file_get_contents($integrityCheckFile));
}

if (AppIntegrityService::arePrerequisitesMet()) {
    ?>
  <div class="alert alert-success">
    <h4><?= _('All Application Prerequisites Satisfied') ?> </h4>
    <p><?= _('All components that CRM relies upon are present and correctly configured on this server') ?></p>
  </div>
  <?php
} else {
        ?>
  <div class="alert alert-danger">
    <h4><?= _('Unmet Application Prerequisites') ?> </h4>
    <p><?= _('Certain components that CRM relies upon are missing or improperly configured on this server.  The application may continue to function, but may produce unexpected behavior.') ?></p>
    <ul>
      <?php
      foreach (AppIntegrityService::getUnmetPrerequisites() as $prerequisite) {
        echo "<li>".$prerequisite[0].": "._("Failed")."</li>";
      } ?>
    </ul>
  </div>
<?php
    }
if ($IntegrityCheckDetails->status == 'failure') {
    ?>
  <div class="alert alert-danger">
    <h4><?= _('Integrity Check Failure') ?> </h4>
    <p><?= _('The previous integrity check failed') ?></p>
    <p><?= _('Details:')?> <?=  $IntegrityCheckDetails->message ?></p>
    <?php
      if (!is_null($IntegrityCheckDetails->files)) {
        if (property_exists($IntegrityCheckDetails->files, "CRM")) {
          ?>
        <p>CRM <?= _('Files failing integrity check') ?>:
        <ul>
          <?php
          foreach ($IntegrityCheckDetails->files->CRM as $key => $file) {
              if ( is_numeric($key) ) {
              ?>
            <li><?= _('File Name')?>: <?= $file->filename ?>
              <?php
              if ($file->status == 'File Missing') {
                  ?>
                <ul>
                 <li><?= _('File Missing')?></li>
                </ul>
                <?php
              } else {
                  ?>
                <ul>
                 <li><?= _('Expected Hash')?>: <?= $file->expectedhash ?></li>
                 <li><?= _('Actual Hash') ?>: <?= $file->actualhash ?></li>
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
                      <?= _('File Name')?>: <?= $plugin_file->filename ?>
                      </li>
                      <?php
                      if ($file->status == 'File Missing') {
                          ?>
                          <ul>
                              <li><?= _('File Missing')?></li>
                          </ul>
                          <?php
                      } else {
                          ?>
                          <ul>
                              <li><?= _('Expected Hash')?>: <?= $plugin_file->expectedhash ?></li>
                              <li><?= _('Actual Hash') ?>: <?= $plugin_file->actualhash ?></li>
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
          }// end of CRM entry

          if (property_exists($IntegrityCheckDetails->files, "PLUGINS")) {
        ?>
        <br/>
        <p>PLUGINS <?= _('Files failing integrity check') ?>:
        <ul>
          <?php
          foreach ($IntegrityCheckDetails->files->PLUGINS as $key => $file) {
              if ( is_numeric($key) ) {
              ?>
            <li><?= _('File Name')?>: <?= $file->filename ?>
              <?php
              if ($file->status == 'File Missing') {
                  ?>
                <ul>
                 <li><?= _('File Missing')?></li>
                </ul>
                <?php
              } else {
                  ?>
                <ul>
                 <li><?= _('Expected Hash')?>: <?= $file->expectedhash ?></li>
                 <li><?= _('Actual Hash') ?>: <?= $file->actualhash ?></li>
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
                      <?= _('File Name')?>: <?= $plugin_file->filename ?>
                      </li>
                      <?php
                      if ($file->status == 'File Missing') {
                          ?>
                          <ul>
                              <li><?= _('File Missing')?></li>
                          </ul>
                          <?php
                      } else {
                          ?>
                          <ul>
                              <li><?= _('Expected Hash')?>: <?= $plugin_file->expectedhash ?></li>
                              <li><?= _('Actual Hash') ?>: <?= $plugin_file->actualhash ?></li>
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
          }
      } ?>
  </div>
  <?php
} else {
          ?>
  <div class="alert alert-success">
    <h4><?= _('Integrity Check Passed') ?> </h4>
    <p><?= _('The previous integrity check passed.  All system file hashes match the expected values.') ?></p>
  </div>
  <?php
      }
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
