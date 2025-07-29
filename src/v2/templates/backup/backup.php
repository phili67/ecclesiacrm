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

<div class="card">
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('This tool will assist you in manually backing up the EcclesiaCRM database.') ?></h3>
    </div>
    <div class="card-body">
        <ul>
            <li><?= _('You should make a manual backup at least once a week unless you already have a regular backup procedule for your systems.') ?></li>
            <li><?= _('After you download the backup file, you should make two copies. Put one of them in a fire-proof safe on-site and the other in a safe location off-site.') ?></li>
            <li><?= _('If you are concerned about confidentiality of data stored in the EcclesiaCRM database, you should encrypt the backup data if it will be stored somewhere potentially accessible to others') ?></li>
            <li><?= _('For added backup security, you can e-mail the backup to yourself at an e-mail account hosted off-site or to a trusted friend.  Be sure to use encryption if you do this, however.') ?></li>
        </ul>
        <form method="post" action="<?= $sRootPath ?>/api/database/backup" id="BackupDatabase">
            <div class="row">
                <div class="col-lg-12">
                    <?= _('Select archive type') ?>:&nbsp;
                    <?php
                    if ($hasGZIP) {
                        ?>
                        <input type="radio" name="archiveType" value="0"> GZip
                        <?php
                    }
                    ?>
                    <?php if ($hasZIP) {
                        ?><input type="radio" name="archiveType" value="1"> Zip<?php
                    } ?>
                    &nbsp;&nbsp;&nbsp;<input type="radio" name="archiveType" value="2" checked> <?= _('Uncompressed') ?>
                    &nbsp;&nbsp;&nbsp;<input type="radio" name="archiveType" value="3"
                                             checked> <?= _('tar.gz (Include Photos)') ?>
                </div>
            </div>

            <BR>

            <?php
            if ($encryptionMethod != "None") {
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <input type="checkbox" name="encryptBackup"
                               value="1"><?= _('Encrypt backup file with a password?') ?>
                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-lg-2">
                        (<?= $encryptionMethod ?>) <?= _("encryption") ?>, <?= _('Password') ?>:
                    </div>
                    <div class="col-lg-2">
                        <input type="password" name="pw1" class="form-control form-control-sm">
                    </div>
                    <div class="col-lg-2">
                        <?= _('Re-type Password') ?>:
                    </div>
                    <div class="col-lg-2">
                        <input type="password" name="pw2" class="form-control form-control-sm">
                    </div>
                </div>
                <BR>
                <div class="row">
                    <div class="col-lg-12">
                        <span id="passworderror" style="color: red"></span>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="row">
                <div class="col-lg-3">
                    <button class="btn btn-primary" type="button" id="doBackup" <?= ($Backup_In_Progress or $BackupDone)?'disabled':''?>>
                        <i class="fa-solid fa-hard-drive"></i> <i class="fa-solid fa-play"></i> <?= _('Generate and Download Backup') ?></button>
                </div>
                <div class="col-lg-5">
                    <button class="btn btn-primary" type="button" id="doRemoteBackup" <?= (!($RemoteBackup and !($Backup_In_Progress or $BackupDone)))?'disabled':''?>>
                        <i class="fa-solid fa-cloud"></i> <i class="fa-solid fa-play"></i> <?= _('Generate and Ship Backup to External Storage') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header  border-1">
        <h1 class="card-title"><?= _('Backup Status:') ?> </h1>
            <h1 class="card-title" id="backupstatus"
                style="color:<?= $BackupDone?'green':'orange' ?>"> &nbsp; <?= $message ?></h1>
    </div>
    <div class="card-body" id="resultFiles">
        <?php if ($BackupDone) { ?>
            <button class="btn btn-primary" id="downloadbutton" role="button" data-filename="<?= $Backup_Result_Datas['filename'] ?>">
                <i class='fa-solid fa-upload'></i>  <?= $Backup_Result_Datas['filename'] ?></button>
        <?php } ?>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.isInProgress  = <?= $Backup_In_Progress?"true":"false" ?>;
    window.CRM.BackupDone =  <?= $BackupDone?"true":"false" ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/backup/backup.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
