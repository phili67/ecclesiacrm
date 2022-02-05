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
<div class="card">
    <div class="card-header  border-0">
        <h3 class="card-title"><?= _('Select Database Files') ?></h3>
    </div>
    <div class="card-body">
        <p><?= _('Select a backup file to restore') ?></p>
        <p><?= _('CAUTION: This will completely erase the existing database, and replace it with the backup') ?></p>
        <p><?= _('If you upload a backup from ChurchInfo, or a previous version of EcclesiaCRM, it will be automatically upgraded to the current database schema') ?></p>
        <p><?= _("Maximum upload size") ?>: <span class="maxUploadSize"></span></p>
        <form id="restoredatabase" action="<?= $sRootPath ?>/api/database/restore" method="POST"
              enctype="multipart/form-data">
            <input type="file" name="restoreFile" id="restoreFile" multiple="">
            <?php
            if ($encryptionMethod != "None") {
                ?>
                <label for="restorePassword">
                    (<?= $encryptionMethod ?>) <?= _("encryption") ?>, <?= _("Password (if any)") ?>:
                </label>
                <input type="text" name="restorePassword"/><br/><br/>
                <button type="submit" class="btn btn-primary btn-small"><?= _('Upload Files') ?></button>
                <?php
            }
            ?>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header  border-0">
        <h3 class="card-title"><?= _('Restore Status:') ?></h3>&nbsp;<h3 class="card-title" id="restorestatus"
                                                                        style="color:red"><?= _('No Restore Running') ?></h3>
        <div id="restoreMessages"></div>
        <span id="restoreNextStep"></span>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/backup/restore.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
