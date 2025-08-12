<?php
// Include the function library
$bSuppressSessionTests = true;
require $sRootDocument . '/Include/HeaderNotLoggedIn.php';
Header_modals();
Header_body_scripts();
?>
<div class="col-lg-8 col-lg-offset-2" style="margin-top: 10px">
  <div class="timeline">
    <div class="time-label">
      <span class="bg-red">
        <?= $sPageTitle ?>
      </span>
    </div>
    <div>
      <i class="fas fa-database bg-blue"></i>
      <div class="timeline-item">
        <h3 class="timeline-header"><?= gettext('Step 1: Backup Database and files') ?> <span id="status1"></span></h3>
        <div class="timeline-body" id="backupPhase">
          <p id="status-text"><?= gettext('Please create a database backup before beginning the upgrade process.') ?></p>
          <input type="button" class="btn btn-primary" id="doBackup" <?= 'value="' . gettext('Generate Database Backup') . '"' ?>>
          <span id="backupStatus"></span>
          <div id="resultFiles" style="margin-top:10px">
          </div>
        </div>
      </div>
    </div>
    <div>
      <i class="fas fa-cloud-download-alt bg-blue"></i>
      <div class="timeline-item">
        <h3 class="timeline-header"><?= gettext('Step 2: Fetch Update Package on Server') ?> <span id="status2"></span></h3>
        <div class="timeline-body" id="fetchPhase" style="display: none">
          <p><?= gettext('Fetch the latest files from the CRM GitHub release page') ?></p>
          <input type="button" class="btn btn-primary" id="fetchUpdate" <?= 'value="' . gettext('Fetch Update Files') . '"' ?>>
        </div>
      </div>
    </div>
    <div>
      <i class="fas fa-cogs bg-blue"></i>
      <div class="timeline-item">
        <h3 class="timeline-header"><?= gettext('Step 3: Apply Update Package on Server') ?> <span id="status3"></span></h3>
        <div class="timeline-body" id="updatePhase" style="display: none">
          <p><?= gettext('Extract the upgrade archive, and apply the new files') ?></p>
          <h4><?= gettext('Release Notes') ?></h4>
          <pre id="releaseNotes"></pre>
          <ul>
            <li><?= gettext('File Name:') ?> <span id="updateFileName"> </span></li>
            <li><?= gettext('Full Path:') ?> <span id="updateFullPath"> </span></li>
            <li><?= gettext('SHA1:') ?> <span id="updateSHA1"> </span></li>
          </ul>
          <br />
          <input type="button" class="btn btn-warning" id="applyUpdate" value="<?= gettext("Upgrade System") ?>">
        </div>
      </div>
    </div>
    <div>
      <i class="fas fa-circle-play bg-blue"></i>
      <div class="timeline-item">
        <h3 class="timeline-header"><?= gettext("Step 4: Complete the update") ?></h3>
        <div class="timeline-body" id="finalPhase" style="display: none">   
          <p><?= _("update and clean up the database and files") ?></p>
          <p style="color:red"><b><?= gettext("IMPORTANT : Before continuing, clear your browser cache without reloading this page so that the software works properly (for the new JavaScript code, fonts, and especially for internal functioning)!!!") ?></b></p>                  
          <a href="<?= $sRootPath ?>/v2/system/database/update" class="btn btn-primary"><?= gettext('Start Files/Dadatase Upgrade') ?> </a>
        </div>
      </div>
    </div>
  </div>
</div>

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