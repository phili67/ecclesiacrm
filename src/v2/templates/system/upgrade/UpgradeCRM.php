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
      <div class="col-xl-9 col-lg-10">

        <div class="card card-outline card-primary shadow-sm mb-4">
          <div class="card-header">
            <h3 class="card-title mb-0">
              <i class="fas fa-rocket mr-2 text-primary"></i><?= $sPageTitle ?>
            </h3>
          </div>
          <div class="card-body">
            <p class="text-muted mb-0">
              <?= gettext('Follow each step in order. Each step unlocks automatically when the previous one is complete.') ?>
            </p>
          </div>
        </div>

        <div class="timeline">
          <div class="time-label">
            <span class="bg-primary"><?= gettext('Upgrade workflow') ?></span>
          </div>

          <div>
            <i class="fas fa-database bg-primary"></i>
            <div class="timeline-item">
              <h3 class="timeline-header"><?= gettext('Step 1: Backup Database and files') ?> <span id="status1" class="ml-2"></span></h3>
              <div class="timeline-body" id="backupPhase">
                <p id="status-text" class="mb-3"><?= gettext('Please create a database backup before beginning the upgrade process.') ?></p>
                <input type="button" class="btn btn-primary" id="doBackup" <?= 'value="' . gettext('Generate Database Backup') . '"' ?>>
                <span id="backupStatus" class="ml-2"></span>
                <div id="resultFiles" style="margin-top:10px"></div>
              </div>
            </div>
          </div>

          <div>
            <i class="fas fa-cloud-download-alt bg-info"></i>
            <div class="timeline-item">
              <h3 class="timeline-header"><?= gettext('Step 2: Fetch Update Package on Server') ?> <span id="status2" class="ml-2"></span></h3>
              <div class="timeline-body" id="fetchPhase" style="display: none">
                <p class="mb-3"><?= gettext('Fetch the latest files from the CRM GitHub release page') ?></p>
                <input type="button" class="btn btn-info" id="fetchUpdate" <?= 'value="' . gettext('Fetch Update Files') . '"' ?>>
              </div>
            </div>
          </div>

          <div>
            <i class="fas fa-cogs bg-warning"></i>
            <div class="timeline-item">
              <h3 class="timeline-header"><?= gettext('Step 3: Apply Update Package on Server') ?> <span id="status3" class="ml-2"></span></h3>
              <div class="timeline-body" id="updatePhase" style="display: none">
                <p class="mb-3"><?= gettext('Extract the upgrade archive, and apply the new files') ?></p>

                <div class="card card-outline card-secondary mb-3">
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

          <div>
            <i class="fas fa-check-circle bg-success"></i>
            <div class="timeline-item">
              <h3 class="timeline-header"><?= gettext("Step 4: Complete the update") ?></h3>
              <div class="timeline-body" id="finalPhase" style="display: none">
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