<?php

/*******************************************************************************
 *
 *  filename    : templates/restore.php
 *  last change : 2019-11-21
 *  description : manage the restore
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<!-- Page Header -->
<div class="d-flex align-items-center mb-4">
    <div class="mr-3 text-warning" style="font-size:2.5rem;">
        <i class="fas fa-upload"></i>
    </div>
    <div>
        <h2 class="mb-0"><?= _('Database Restore') ?></h2>
        <p class="text-muted mb-0 small"><?= _('Restore a previously saved CRM backup') ?></p>
    </div>
</div>

<!-- Warning -->
<div class="alert alert-warning">
    <h5><i class="icon fas fa-exclamation-triangle"></i> <?= _('Warning') ?></h5>
    <p class="mb-0"><?= _('CAUTION: This will completely erase the existing database, and replace it with the backup.') ?></p>
</div>

<!-- Restore Card -->
<div class="card card-outline card-warning shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-import mr-2"></i><?= _('Select Database Files') ?></h3>
    </div>
    <div class="card-body">

        <div class="alert alert-light border mb-3">
            <i class="fas fa-info-circle text-info mr-2"></i>
            <?= _('If you upload a backup from ChurchInfo, or a previous version of EcclesiaCRM, it will be automatically upgraded to the current database schema.') ?>
        </div>

        <p class="text-muted small mb-3">
            <i class="fas fa-weight-hanging mr-1"></i>
            <?= _("Maximum upload size") ?>: <strong><span class="maxUploadSize"></span></strong>
        </p>

        <form id="restoredatabase" action="<?= $sRootPath ?>/api/database/restore" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="restoreFile" class="font-weight-bold"><?= _('Backup file') ?></label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="restoreFile" id="restoreFile" multiple>
                        <label class="custom-file-label" for="restoreFile"><?= _('Choose file…') ?></label>
                    </div>
                </div>
            </div>

            <?php if ($encryptionMethod != "None"): ?>
            <div class="form-group">
                <label for="restorePassword" class="font-weight-bold">
                    <i class="fas fa-lock mr-1 text-warning"></i>
                    (<?= $encryptionMethod ?>) <?= _("encryption") ?> — <?= _("Password (if any)") ?>
                </label>
                <input type="password" id="restorePassword" name="restorePassword" class="form-control form-control-sm" style="max-width:320px;" placeholder="<?= _('Leave blank if not encrypted') ?>">
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-warning">
                <i class="fas fa-upload mr-1"></i> <?= _('Upload and Restore') ?>
            </button>

        </form>
    </div>
</div>

<!-- Restore Status -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tasks mr-2"></i><?= _('Restore Status') ?></h3>
        <div class="card-tools">
            <span id="restorestatus" class="font-weight-bold" style="color:red"><?= _('No Restore Running') ?></span>
        </div>
    </div>
    <div class="card-body">
        <div id="restoreMessages"></div>
        <div id="restoreNextStep"></div>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/backup/restore.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
