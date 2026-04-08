<?php

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\AppIntegrityService;
use EcclesiaCRM\Service\SystemService;

//Set the page title
include SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>
<div class="row infos-compact">
    <div class="col-lg-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fas fa-cubes mr-2"></i><?= _("CRM Installation Information") ?></h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <tr>
                        <td><?= Bootstrapper::getSoftwareName() ?> <?= _("Software Version") ?></td>
                        <td><?= SystemService::getInstalledVersion() ?></td>
                    </tr>
                    <tr>
                        <td>RootPath</td>
                        <td><?= SystemURLs::getRootPath() ?></td>
                    </tr>
                    <tr>
                        <td>DocumentRoot</td>
                        <td><?= SystemURLs::getDocumentRoot() ?></td>
                    </tr>
                    <tr>
                        <td>ImagesRoot</td>
                        <td><?= SystemURLs::getImagesRoot() ?></td>
                    </tr>
                    <tr>
                        <td>URL</td>
                        <td><?= SystemURLs::getURL() ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fas fa-server mr-2"></i><?= _("System Information") ?></h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <tr>
                        <td>Server Hostname</td>
                        <td><?= gethostname() ?></td>
                    </tr>
                    <tr>
                        <td>Server IP</td>
                        <td><?= $_SERVER['SERVER_ADDR'] ?></td>
                    </tr>
                    <tr>
                        <td>Server Platform</td>
                        <td><?= php_uname() ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fab fa-php mr-2"></i>PHP</h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <tr>
                        <td>PHP Version</td>
                        <td><?= PHP_VERSION ?></td>
                    </tr>
                    <tr>
                        <td>Max file upload size</td>
                        <td><?= ini_get('upload_max_filesize') ?></td>
                    </tr>
                    <tr>
                        <td>Max POST size</td>
                        <td><?= ini_get('post_max_size') ?></td>
                    </tr>
                    <tr>
                        <td>PHP Memory Limit</td>
                        <td><?= ini_get('memory_limit') ?></td>
                    </tr>
                    <tr>
                        <td>PHP Max Exec</td>
                        <td><?= ini_get('max_execution_time') ?></td>
                    </tr>
                    <tr>
                        <td>SAPI Name</td>
                        <td><?= php_sapi_name()  ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fas fa-globe mr-2"></i><?= _("Web Server") ?></h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <tr>
                        <td><?= $_SERVER["SERVER_SOFTWARE"] ?></td>
                    </tr>
<?php
if (function_exists('apache_get_modules')) {
    foreach (apache_get_modules() as $item) {
        echo <<<EOD
<tr>
    <td>$item</td>
</tr>
EOD;
    }
} else {
    echo <<<EOD
<tr>
    <td><i>function <pre>apache_get_modules</pre> does not exist!</i></td>
</tr>
EOD;
}
?>
                </table>
            </div>
        </div>        
    </div>
    <div class="col-lg-4">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fas fa-database mr-2"></i><?= _("Database") ?></h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <tr>
                        <td><?= Bootstrapper::getSoftwareName() ?> <?= _("Database Version") ?></td>
                        <td><?= SystemService::getDBVersion() ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Database Server Version") ?></td>
                        <td><?= SystemService::getDBServerVersion() ?></td>
                    </tr>
                    <tr>
                        <td>DSN</td>
                        <td><?= Bootstrapper::getDSN() ?></td>
                    </tr>
                </table>
            </div>
        </div>        
    </div>
    <div class="col-lg-4">
        
    </div>
    <div class="col-lg-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fas fa-clipboard-check mr-2"></i><?= _("Application Prerequisites") ?></h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <?php foreach (AppIntegrityService::getApplicationPrerequisites() as $prerequisite) { ?>
                        <tr>
                          <td><a href='<?=$prerequisite->getLink()?>'><?= $prerequisite->getName()?></a></td>
                          <td><?= ($prerequisite->getMessage())?'<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>':'<span class="badge badge-danger"><i class="fa fa-times" aria-hidden="true"></i></span>' ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header border-0 py-2">
                <h3 class="card-title mb-0"><i class="fas fa-envelope mr-2"></i><?= _("Email Information") ?></h3>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm">
                    <tr>
                        <td>SMTP Host</td>
                        <td><?= SystemConfig::getValue("sSMTPHost") ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Valid Mail Server Settings") ?></td>
                        <td><?= SystemConfig::hasValidMailServerSettings() ? "true" : "false" ?></td>
                    </tr>
                </table>

                                <hr>

                                <label class="text-muted small font-weight-bold text-uppercase mb-2"><?= _("System Infos") ?></label>

                <p id="mailTest"><?= _("Testing connection .....") ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
                <div class="card card-outline card-danger shadow-sm">
                        <div class="card-header border-0 py-2">
                            <h3 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i><?= _("Application Integrity Check") . ": " . AppIntegrityService::getIntegrityCheckStatus()?></h3>
            </div>
            <div class="card-body p-2">
                            <label class="text-muted small font-weight-bold text-uppercase mb-2"><?= _('Details:')?> CRM (<?=  AppIntegrityService::getIntegrityCheckMessage() ?>)</label>
                <?php
                $signatureFailures = AppIntegrityService::getFilesFailingIntegrityCheck();
                if (array_key_exists('CRM', $signatureFailures) and count($signatureFailures['CRM']) > 0) {
                    ?>
                    <p><?= _('Files failing integrity check') ?>:
                                        <table class="table table-sm table-striped table-hover text-sm display responsive no-wrap" width="100%" id="fileIntegrityCheckResultsTable">
                      <thead>
                                            <tr>
                                            <th><?= _("FileName") ?></th>
                                            <th><?= _("Expected Hash") ?></th>
                                            <th><?= _("Actual Hash") ?></th>
                                            </tr>
                    </thead>
                      <?php
                        foreach ($signatureFailures['CRM'] as $file) {
                            ?>
                    <tr>
                      <td><?= $file['filename'] ?></td>
                      <td><?= $file['expectedhash'] ?></td>
                      <td>
                            <?php
                            if ($file->status === 'File Missing') {
                                echo _('File Missing');
                            } else {
                                echo $file['actualhash'];
                            }?>
                      </td>
                    </tr>
                            <?php
                        }
                        ?>
                    </table>
                    <?php
                }
                ?>
                                <br>
                                <label class="text-muted small font-weight-bold text-uppercase mb-2"><?= _('Details:')?> PLUGINS (<?=  AppIntegrityService::getIntegrityCheckMessage() ?>)</label>
                <?php
                if (array_key_exists('PLUGINS', $signatureFailures) and count($signatureFailures['PLUGINS']) > 0) {
                    ?>
                    <p><?= _('Files failing integrity check') ?>:
                                        <table class="table table-sm table-striped table-hover text-sm display responsive no-wrap" width="100%" id="pluginfileIntegrityCheckResultsTable">
                      <thead>
                                            <tr>
                                            <th><?= _("Plugin") ?></th>
                                            <th><?= _("FileName") ?></th>
                                            <th><?= _("Expected Hash") ?></th>
                                            <th><?= _("Actual Hash") ?></th>
                                            </tr>
                    </thead>
                      <?php
                        foreach ($signatureFailures['PLUGINS'] as $pluginName => $files) {
                            foreach ($files as $file) {
                            ?>
                        <tr>
                            <td><?= $pluginName ?></td>
                            <td><?= $file['filename'] ?></td>
                            <td><?= $file['expectedhash'] ?></td>
                            <td>
                                    <?php
                                    if ($file->status === 'File Missing') {
                                        echo _('File Missing');
                                    } else {
                                        echo $file['actualhash'];
                                    }?>
                            </td>
                        </tr>
                                <?php
                            }
                        }
                        ?>
                    </table>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $(function() {
    $("#fileIntegrityCheckResultsTable").DataTable({
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true,
        paging:false,
                searching: false,
                info: false,
                lengthChange: false,
                autoWidth: false
    });

    $("#pluginfileIntegrityCheckResultsTable").DataTable({
        responsive: true,
        paging:false,
        searching: false,
        info: false,
        lengthChange: false,
        autoWidth: false
    });
  });

</script>

<script src="<?= $sRootPath ?>/skin/js/system/EmailDebug.js"></script>

<?php include SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>