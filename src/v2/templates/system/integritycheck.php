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

$IntegrityCheckDetails = new stdClass();

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

$prerequisitesOk = AppIntegrityService::arePrerequisitesMet();
$integrityFailed = ($IntegrityCheckDetails->status == 'failure');

?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
      <h3 class="card-title mb-2 mb-sm-0">
        <i class="fas fa-shield-alt mr-2 text-primary"></i><?= _('Application Integrity Center') ?>
      </h3>
      <div class="d-flex align-items-center">
        <span class="badge badge-<?= $integrityFailed ? 'danger' : 'success' ?> px-3 py-2">
          <?= $integrityFailed ? _('Integrity Check Failure') : _('Integrity Check Passed') ?>
        </span>
      </div>
    </div>
    <p class="text-muted small mb-0 mt-2"><?= _('Review prerequisites and file integrity status in one place.') ?></p>
    <div class="mt-2">
      <span class="badge badge-success mr-1"><?= _('OK') ?></span>
      <span class="badge badge-danger mr-1"><?= _('Issue detected') ?></span>
      <span class="badge badge-secondary"><?= _('Click section titles to expand details') ?></span>
    </div>
  </div>
  <div class="card-body">

<?php

if ($prerequisitesOk) {
    ?>
  <div class="alert alert-success mb-4">
    <h4 class="alert-heading h5 mb-2"><i class="fas fa-check-circle mr-2"></i><?= _('All Application Prerequisites Satisfied') ?></h4>
    <p class="mb-0"><?= _('All components that CRM relies upon are present and correctly configured on this server') ?></p>
  </div>
  <?php
} else {
        ?>
  <div class="alert alert-danger mb-4">
    <h4 class="alert-heading h5 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i><?= _('Unmet Application Prerequisites') ?></h4>
    <p><?= _('Certain components that CRM relies upon are missing or improperly configured on this server.  The application may continue to function, but may produce unexpected behavior.') ?></p>
    <ul class="mb-0 pl-3">
      <?php
      foreach (AppIntegrityService::getUnmetPrerequisites() as $prerequisite) {
        echo "<li>".$prerequisite[0].": "._("Failed")."</li>";
      } ?>
    </ul>
  </div>
<?php
    }
if ($integrityFailed) {
    ?>
  <div class="alert alert-danger mb-0">
    <h4 class="alert-heading h5 mb-2"><i class="fas fa-times-circle mr-2"></i><?= _('Integrity Check Failure') ?></h4>
    <p class="mb-1"><?= _('The previous integrity check failed') ?></p>
    <p class="mb-3"><strong><?= _('Details:')?></strong> <?=  $IntegrityCheckDetails->message ?></p>
    <?php
      if (!is_null($IntegrityCheckDetails->files)) {
        if (property_exists($IntegrityCheckDetails->files, "CRM")) {
          ?>
        <details open class="mb-3">
          <summary class="font-weight-bold">CRM <?= _('Files failing integrity check') ?></summary>
          <ul class="mb-2 pl-3 mt-2">
          <?php
          foreach ($IntegrityCheckDetails->files->CRM as $key => $file) {
              if ( is_numeric($key) ) {
              ?>
            <li class="mb-2"><strong><?= _('File Name')?>:</strong> <?= $file->filename ?>
              <?php
              if ($file->status == 'File Missing') {
                  ?>
                <ul class="mb-0 pl-3">
                 <li><span class="badge badge-danger"><?= _('File Missing')?></span></li>
                </ul>
                <?php
              } else {
                  ?>
                <ul class="mb-0 pl-3">
                 <li class="mb-1">
                   <div class="row no-gutters align-items-start">
                     <div class="col-12 col-md-3 font-weight-bold"><?= _('Expected Hash')?></div>
                     <div class="col-12 col-md-9">
                       <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small\"><?= $file->expectedhash ?></span>
                     </div>
                   </div>
                 </li>
                 <li class="mb-1">
                   <div class="row no-gutters align-items-start">
                     <div class="col-12 col-md-3 font-weight-bold"><?= _('Actual Hash') ?></div>
                     <div class="col-12 col-md-9">
                       <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small\"><?= $file->actualhash ?></span>
                     </div>
                   </div>
                 </li>
                </ul>
                <?php
              } ?>
            </li>
            <?php
              } elseif ( is_string($key) and count($file) > 0) {
                  ?>
                  <li class="mb-2">
                    <details>
                      <summary><strong><?= _("Plugin")." : ". $key ?></strong></summary>
                      <ul class="mb-0 pl-3 mt-2">
                  <?php
                  foreach ($file as $plugin_file) {
                      ?>
                    <li class="mb-1">
                    <strong><?= _('File Name')?>:</strong> <?= $plugin_file->filename ?>
                      </li>
                      <?php
                      if ($file->status == 'File Missing') {
                          ?>
                      <ul class="mb-1 pl-3">
                            <li><span class="badge badge-danger"><?= _('File Missing')?></span></li>
                          </ul>
                          <?php
                      } else {
                          ?>
                      <ul class="mb-1 pl-3">
                          <li class="mb-1">
                            <div class="row no-gutters align-items-start">
                              <div class="col-12 col-md-3 font-weight-bold"><?= _('Expected Hash')?></div>
                              <div class="col-12 col-md-9">
                                <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small"><?= $plugin_file->expectedhash ?></span>
                              </div>
                            </div>
                          </li>
                          <li class="mb-1">
                            <div class="row no-gutters align-items-start">
                              <div class="col-12 col-md-3 font-weight-bold"><?= _('Actual Hash') ?></div>
                              <div class="col-12 col-md-9">
                                <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small"><?= $plugin_file->actualhash ?></span>
                              </div>
                            </div>
                          </li>
                          </ul>
                          <?php
                      }
                  }
                  ?>
                      </ul>
                    </details>
                  </li>
                  <?php
              }
          } ?>
          </ul>
        </details>
        <?php 
          }// end of CRM entry

          if (property_exists($IntegrityCheckDetails->files, "PLUGINS")) {
        ?>
        <details class="mb-2">
          <summary class="font-weight-bold">PLUGINS <?= _('Files failing integrity check') ?></summary>
          <ul class="mb-0 pl-3 mt-2">
          <?php
          foreach ($IntegrityCheckDetails->files->PLUGINS as $key => $file) {
              if ( is_numeric($key) ) {
              ?>
            <li class="mb-2"><strong><?= _('File Name')?>:</strong> <?= $file->filename ?>
              <?php
              if ($file->status == 'File Missing') {
                  ?>
                <ul class="mb-0 pl-3">
                 <li><span class="badge badge-danger"><?= _('File Missing')?></span></li>
                </ul>
                <?php
              } else {
                  ?>
                <ul class="mb-0 pl-3">
                 <li class="mb-1">
                   <div class="row no-gutters align-items-start">
                     <div class="col-12 col-md-3 font-weight-bold"><?= _('Expected Hash')?></div>
                     <div class="col-12 col-md-9">
                       <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small"><?= $file->expectedhash ?></span>
                     </div>
                   </div>
                 </li>
                 <li class="mb-1">
                   <div class="row no-gutters align-items-start">
                     <div class="col-12 col-md-3 font-weight-bold"><?= _('Actual Hash') ?></div>
                     <div class="col-12 col-md-9">
                       <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small"><?= $file->actualhash ?></span>
                     </div>
                   </div>
                 </li>
                </ul>
                <?php
              } ?>
            </li>
            <?php
              } elseif ( is_string($key) and count($file) > 0) {
                  ?>
                  <li class="mb-2">
                    <details>
                      <summary><strong><?= _("Plugin")." : ". $key ?></strong></summary>
                      <ul class="mb-0 pl-3 mt-2">
                  <?php
                  foreach ($file as $plugin_file) {
                      ?>
                    <li class="mb-1">
                    <strong><?= _('File Name')?>:</strong> <?= $plugin_file->filename ?>
                      </li>
                      <?php
                      if ($file->status == 'File Missing') {
                          ?>
                      <ul class="mb-1 pl-3">
                            <li><span class="badge badge-danger"><?= _('File Missing')?></span></li>
                          </ul>
                          <?php
                      } else {
                          ?>
                      <ul class="mb-1 pl-3">
                        <li class="mb-1">
                          <div class="row no-gutters align-items-start">
                            <div class="col-12 col-md-3 font-weight-bold"><?= _('Expected Hash')?></div>
                            <div class="col-12 col-md-9">
                              <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small\"><?= $plugin_file->expectedhash ?></span>
                            </div>
                          </div>
                        </li>
                        <li class="mb-1">
                          <div class="row no-gutters align-items-start">
                            <div class="col-12 col-md-3 font-weight-bold"><?= _('Actual Hash') ?></div>
                            <div class="col-12 col-md-9">
                              <span class="d-inline-block px-2 py-1 bg-light text-dark rounded border text-monospace text-break small\"><?= $plugin_file->actualhash ?></span>
                            </div>
                          </div>
                        </li>
                          </ul>
                          <?php
                      }
                  }
                  ?>
                      </ul>
                    </details>
                  </li>
                  <?php
              }
          } ?>
          </ul>
        </details>
        <?php
          }
      } else { ?>
      <div class="alert alert-warning mb-0">
        <?= _('No detailed file list is available for this integrity check run.') ?>
      </div>
      <?php } ?>
  </div>
  <?php
} else {
          ?>
  <div class="alert alert-success mb-0">
    <h4 class="alert-heading h5 mb-2"><i class="fas fa-check-circle mr-2"></i><?= _('Integrity Check Passed') ?></h4>
    <p class="mb-0"><?= _('The previous integrity check passed.  All system file hashes match the expected values.') ?></p>
  </div>
  <?php
      }
?>

  </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
