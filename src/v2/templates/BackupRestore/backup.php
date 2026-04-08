<?php

/*******************************************************************************
 *
 *  filename    : templates/backup.php
 *  last change : 2025-07-28
 *  description : manage the backup
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';
?>

<div class="d-flex align-items-center mb-4">
    <div class="mr-3 text-primary" style="font-size:2.5rem;">
        <i class="fas fa-database"></i>
    </div>
    <div>
        <h2 class="mb-0"><?= _('Database Backup') ?></h2>
        <p class="text-muted mb-0 small"><?= _('Create and download a secure backup of your CRM data') ?></p>
    </div>
</div>

<div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-info-circle"></i> <?= _('Backup Recommendations') ?></h5>
    <ul class="mb-0">
        <li><?= _('You should make a manual backup at least once a week unless you already have a regular backup procedure for your systems.') ?></li>
        <li><?= _('After you download the backup file, you should make two copies. Put one of them in a fire-proof safe on-site and the other in a safe location off-site.') ?></li>
        <li><?= _('If you are concerned about confidentiality of data stored in the CRM database, you should encrypt the backup data if it will be stored somewhere potentially accessible to others.') ?></li>
        <li><?= _('For added backup security, you can e-mail the backup to yourself at an e-mail account hosted off-site or to a trusted friend. Be sure to use encryption if you do this, however.') ?></li>
    </ul>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cog mr-2"></i><?= _('Backup Options') ?></h3>
    </div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label class="font-weight-bold d-block"><i class="fas fa-archive mr-1 text-primary"></i> <?= _('Archive Format') ?></label>
            <div class="list-group list-group-flush border rounded-sm mt-2">
                <?php if ($hasGZIP): ?>
                <label class="list-group-item mb-0" style="cursor:pointer;">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="archiveTypeGzip" name="archiveType" value="0" <?= $hasGZIP ? 'checked' : '' ?>>
                        <label class="custom-control-label w-100" for="archiveTypeGzip">
                            <span class="font-weight-bold"><i class="fas fa-compress-alt text-success mr-1"></i>GZip</span>
                            <span class="text-muted small d-block"><?= _("Database only") ?></span>
                        </label>
                    </div>
                </label>
                <?php endif; ?>

                <?php if ($hasZIP): ?>
                <label class="list-group-item mb-0" style="cursor:pointer;">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="archiveTypeZip" name="archiveType" value="1" <?= (!$hasGZIP && $hasZIP) ? 'checked' : '' ?>>
                        <label class="custom-control-label w-100" for="archiveTypeZip">
                            <span class="font-weight-bold"><i class="fas fa-file-archive text-warning mr-1"></i>Zip</span>
                            <span class="text-muted small d-block"><?= _("Database only") ?></span>
                        </label>
                    </div>
                </label>
                <?php endif; ?>

                <label class="list-group-item mb-0" style="cursor:pointer;">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="archiveTypePlain" name="archiveType" value="2" <?= (!$hasGZIP && !$hasZIP) ? 'checked' : '' ?>>
                        <label class="custom-control-label w-100" for="archiveTypePlain">
                            <span class="font-weight-bold"><i class="fas fa-file text-secondary mr-1"></i><?= _('Uncompressed') ?></span>
                            <span class="text-muted small d-block"><?= _("Database only") ?></span>
                        </label>
                    </div>
                </label>

                <label class="list-group-item mb-0" style="cursor:pointer;">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="archiveTypeFull" name="archiveType" value="3">
                        <label class="custom-control-label w-100" for="archiveTypeFull">
                            <span class="font-weight-bold"><i class="fas fa-hdd text-primary mr-1"></i><?= _('Full Backup') ?></span>
                            <span class="text-muted small d-block">tar.gz - <?= _('DB + Photos + Files') ?></span>
                        </label>
                    </div>
                </label>
            </div>
        </div>

        <?php if ($encryptionMethod != "None"): ?>
        <hr>
        <div class="form-group mb-0">
            <label class="font-weight-bold"><i class="fas fa-lock mr-1 text-warning"></i> <?= _('Encryption') ?></label>
            <div class="custom-control custom-switch mb-3">
                <input class="custom-control-input" type="checkbox" name="encryptBackup" id="encryptBackup" value="1">
                <label for="encryptBackup" class="custom-control-label"><?= _('Encrypt backup file with a password?') ?></label>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="pw1" class="small text-muted">(<?= $encryptionMethod ?>) <?= _("encryption") ?> - <?= _('Password') ?></label>
                    <input type="password" name="pw1" id="pw1" class="form-control form-control-sm" placeholder="<?= _('Password') ?>">
                </div>
                <div class="col-md-4">
                    <label for="pw2" class="small text-muted"><?= _('Re-type Password') ?></label>
                    <input type="password" name="pw2" id="pw2" class="form-control form-control-sm" placeholder="<?= _('Confirm password') ?>">
                </div>
            </div>
            <div class="mt-2">
                <span id="passworderror" class="text-danger small"></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary mr-2" type="button" id="doBackup" <?= ($Backup_In_Progress || $BackupDone) ? 'disabled' : '' ?>>
            <i class="fas fa-download mr-1"></i> <?= _('Generate and Download Backup') ?>
        </button>
        <button class="btn btn-outline-info" type="button" id="doRemoteBackup" <?= (!($RemoteBackup && !($Backup_In_Progress || $BackupDone))) ? 'disabled' : '' ?>>
            <i class="fas fa-cloud-upload-alt mr-1"></i> <?= _('Generate and Ship to External Storage') ?>
        </button>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tasks mr-2"></i><?= _('Backup Status') ?></h3>
        <div class="card-tools">
            <span id="backupstatus" class="font-weight-bold" style="color:<?= $BackupDone ? 'green' : 'orange' ?>"><?= $message ?></span>
        </div>
    </div>
    <div class="card-body" id="resultFiles">
        <?php if ($BackupDone): ?>
        <button class="btn btn-success" id="downloadbutton" role="button" data-filename="<?= $Backup_Result_Datas['filename'] ?>">
            <i class="fas fa-download mr-1"></i> <?= $Backup_Result_Datas['filename'] ?>
        </button>
        <?php endif; ?>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isInProgress  = <?= $Backup_In_Progress ? "true" : "false" ?>;
    window.CRM.BackupDone =  <?= $BackupDone ? "true" : "false" ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/backup/backup.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
