<?php
// Include the function library
$bSuppressSessionTests = true;
require $sRootDocument . '/Include/HeaderNotLoggedIn.php';
Header_modals();
Header_body_scripts();
?>

<section class="content pt-3">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-xl-11">

        <div class="card upgrade-shell mb-4">
          <div class="card-header">
            <span class="upgrade-kicker mb-3">
              <i class="fas fa-route mr-2"></i><?= gettext('System upgrade flow') ?>
            </span>
            <h3 class="card-title mb-0">
              <i class="fas fa-rocket mr-2"></i><?= $sPageTitle ?>
            </h3>
          </div>
          <div class="card-body">
            <p class="mb-0">
              <?= gettext('Follow each step in order. Each step unlocks automatically when the previous one is complete.') ?>
            </p>
          </div>
        </div>

        <div class="upgrade-flow">
          <div class="upgrade-flow-header">
            <div>
              <h4><?= gettext('Upgrade workflow') ?></h4>
              <p><?= gettext('Track the four phases and unlock the next action as progress advances.') ?></p>
            </div>
          </div>

          <div class="upgrade-timeline">
            <div class="upgrade-timeline-step is-active" data-step="1">
              <span class="upgrade-timeline-badge">
                <i class="fas fa-database"></i>
              </span>
              <div class="upgrade-timeline-label">
                <strong><?= gettext('Step 1') ?></strong>
                <span><?= gettext('Backup') ?></span>
              </div>
            </div>
            <div class="upgrade-timeline-step is-locked" data-step="2">
              <span class="upgrade-timeline-badge">
                <i class="fas fa-cloud-download-alt"></i>
              </span>
              <div class="upgrade-timeline-label">
                <strong><?= gettext('Step 2') ?></strong>
                <span><?= gettext('Fetch package') ?></span>
              </div>
            </div>
            <div class="upgrade-timeline-step is-locked" data-step="3">
              <span class="upgrade-timeline-badge">
                <i class="fas fa-cogs"></i>
              </span>
              <div class="upgrade-timeline-label">
                <strong><?= gettext('Step 3') ?></strong>
                <span><?= gettext('Apply update') ?></span>
              </div>
            </div>
            <div class="upgrade-timeline-step is-locked" data-step="4">
              <span class="upgrade-timeline-badge">
                <i class="fas fa-check-circle"></i>
              </span>
              <div class="upgrade-timeline-label">
                <strong><?= gettext('Step 4') ?></strong>
                <span><?= gettext('Finalize') ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card upgrade-step-card is-active" data-step-card="1">
              <div class="card-body">
                <div class="step-card-head">
                  <span class="step-icon"><i class="fas fa-database"></i></span>
                  <span class="step-index"><?= gettext('Step 1') ?></span>
                </div>
                <h3>
                  <?= gettext('Backup Database and files') ?>
                  <span id="status1" class="ml-2"></span>
                </h3>
                <p class="step-description"><?= gettext('Secure the current state before changing application files or database structures.') ?></p>
                <div class="step-status" data-step-status="1">
                  <span class="step-status-indicator"></span>
                  <span><?= gettext('Current step') ?></span>
                </div>
                <div class="step-content" id="backupPhase">
                  <p id="status-text" class="mb-3"><?= gettext('Please create a database backup before beginning the upgrade process.') ?></p>
                  <input type="button" class="btn btn-primary" id="doBackup" <?= 'value="' . gettext('Generate Database Backup') . '"' ?>>
                  <span id="backupStatus" class="ml-2"></span>
                  <div id="resultFiles" class="mt-3"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card upgrade-step-card is-locked" data-step-card="2">
              <div class="card-body">
                <div class="step-card-head">
                  <span class="step-icon"><i class="fas fa-cloud-download-alt"></i></span>
                  <span class="step-index"><?= gettext('Step 2') ?></span>
                </div>
                <h3>
                  <?= gettext('Fetch Update Package on Server') ?>
                  <span id="status2" class="ml-2"></span>
                </h3>
                <p class="step-description"><?= gettext('Retrieve the release bundle directly from the server before applying the update.') ?></p>
                <div class="step-status" data-step-status="2">
                  <span class="step-status-indicator"></span>
                  <span><?= gettext('Locked') ?></span>
                </div>
                <div class="step-content" id="fetchPhase" style="display: none">
                  <p class="mb-3"><?= gettext('Fetch the latest files from the CRM GitHub release page') ?></p>
                  <input type="button" class="btn btn-info" id="fetchUpdate" <?= 'value="' . gettext('Fetch Update Files') . '"' ?>>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card upgrade-step-card is-locked" data-step-card="3">
              <div class="card-body">
                <div class="step-card-head">
                  <span class="step-icon"><i class="fas fa-cogs"></i></span>
                  <span class="step-index"><?= gettext('Step 3') ?></span>
                </div>
                <h3>
                  <?= gettext('Apply Update Package on Server') ?>
                  <span id="status3" class="ml-2"></span>
                </h3>
                <p class="step-description"><?= gettext('Validate the downloaded release details, then apply the new application files.') ?></p>
                <div class="step-status" data-step-status="3">
                  <span class="step-status-indicator"></span>
                  <span><?= gettext('Locked') ?></span>
                </div>
                <div class="step-content" id="updatePhase" style="display: none">
                  <p class="mb-3"><?= gettext('Extract the upgrade archive, and apply the new files') ?></p>

                  <div class="card card-outline mb-3">
                    <div class="card-header py-2">
                      <h4 class="card-title mb-0"><?= gettext('Release Notes') ?></h4>
                    </div>
                    <div class="card-body p-0">
                      <pre id="releaseNotes" class="mb-0 p-3" style="max-height:220px; overflow:auto;"></pre>
                    </div>
                  </div>

                  <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                      <tbody>
                        <tr>
                          <th style="width:180px;"><?= gettext('File Name:') ?></th>
                          <td><span id="updateFileName"></span></td>
                        </tr>
                        <tr>
                          <th><?= gettext('Full Path:') ?></th>
                          <td><span id="updateFullPath"></span></td>
                        </tr>
                        <tr>
                          <th><?= gettext('SHA1:') ?></th>
                          <td><code id="updateSHA1"></code></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <input type="button" class="btn btn-warning" id="applyUpdate" value="<?= gettext("Upgrade System") ?>">
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card upgrade-step-card is-locked" data-step-card="4">
              <div class="card-body">
                <div class="step-card-head">
                  <span class="step-icon"><i class="fas fa-check-circle"></i></span>
                  <span class="step-index"><?= gettext('Step 4') ?></span>
                </div>
                <h3><?= gettext("Complete the update") ?></h3>
                <p class="step-description"><?= gettext('Finalize the process with database cleanup and the last manual validation checks.') ?></p>
                <div class="step-status" data-step-status="4">
                  <span class="step-status-indicator"></span>
                  <span><?= gettext('Locked') ?></span>
                </div>
                <div class="step-content" id="finalPhase" style="display: none">
                  <p><?= _("update and clean up the database and files") ?></p>
                  <div class="alert alert-danger mb-3">
                    <strong><?= gettext("IMPORTANT") ?>:</strong>
                    <?= gettext("Before continuing, clear your browser cache without reloading this page so that the software works properly (for the new JavaScript code, fonts, and especially for internal functioning)!!!") ?>
                  </div>
                  <a href="<?= $sRootPath ?>/v2/system/database/update" class="btn btn-success">
                    <i class="fas fa-play mr-1"></i><?= gettext('Start Files/Dadatase Upgrade') ?>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<script nonce="<?= $sCSPNonce ?>">
  window.CRM.isInProgress  = <?= $Backup_In_Progress?"true":"false" ?>;
  window.CRM.BackupDone  = <?= $BackupDone?"true":"false" ?>;
  window.CRM.BackupDatas  = <?= json_encode($Backup_Result_Datas) ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/upgrade/UpgradeCRM.js"></script>

<?php
// Add the page footer
require $sRootDocument . '/Include/FooterNotLoggedIn.php';

// Turn OFF output buffering
ob_end_flush();
?>