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
    <div class="col-12 order-lg-1 pb-4">
        <div class="alert alert-light border shadow-sm mb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="pr-md-3">
                <h2 class="h4 mb-1"><i class="fas fa-info-circle text-primary mr-2"></i><?= _("System Information") ?></h2>
                <p class="mb-0 text-muted"><?= _("Review the health of your CRM installation, its technical environment and the services it uses.") ?></p>
            </div>
            <span class="badge badge-primary mt-2 mt-md-0 align-self-md-center"><i class="fas fa-clipboard-list mr-1"></i><?= _("Administration overview") ?></span>
        </div>
    </div>
    <div class="col-12 order-lg-2 pb-4">
        <div class="card card-outline card-primary shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fas fa-clipboard-check mr-2"></i><?= _("Application Prerequisites") ?></h3>
                <p class="small text-muted mb-0 mt-1"><?= _("Start here: these checks identify missing or invalid requirements before troubleshooting the application.") ?></p>
            </div>
            <div class="card-body overflow-auto p-2">
                <div class="row">
                    <?php foreach (AppIntegrityService::getApplicationPrerequisites() as $prerequisite) { ?>
                        <div class="col-lg-4 col-md-6 mb-2">
                            <div class="border rounded p-2 h-100 d-flex justify-content-between align-items-center">
                                <a href="<?=$prerequisite->getLink()?>" class="text-sm pr-2"><?= $prerequisite->getName()?></a>
                                <?= ($prerequisite->getMessage())?'<span class="badge badge-success flex-shrink-0"><i class="fa fa-check mr-1" aria-hidden="true"></i>'._("OK").'</span>':'<span class="badge badge-danger flex-shrink-0"><i class="fa fa-times mr-1" aria-hidden="true"></i>'._("Failed").'</span>' ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 order-lg-3 pb-4">
        <div class="card card-outline card-primary shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fas fa-cubes mr-2"></i><?= _("CRM Installation Information") ?></h3>
                <p class="small text-muted mb-0 mt-1"><?= _("Paths and version details for this CRM installation.") ?></p>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-0 text-sm align-middle">
                    <tr>
                        <td class="text-muted"><?= Bootstrapper::getSoftwareName() ?> <?= _("Software Version") ?></td>
                        <td class="font-weight-bold text-right"><?= SystemService::getInstalledVersion() ?> (Build : <?= SystemService::getBuild() ?>)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">RootPath</td>
                        <td class="text-right text-break"><?= SystemURLs::getRootPath() ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">DocumentRoot</td>
                        <td class="text-right text-break"><?= SystemURLs::getDocumentRoot() ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">ImagesRoot</td>
                        <td class="text-right text-break"><?= SystemURLs::getImagesRoot() ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">URL</td>
                        <td class="text-right text-break"><?= SystemURLs::getURL() ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 order-lg-4 pb-4">
        <div class="card card-outline card-info shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fas fa-server mr-2"></i><?= _("System Information") ?></h3>
                <p class="small text-muted mb-0 mt-1"><?= _("The server environment hosting this application.") ?></p>
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
    <div class="col-lg-4 col-md-6 order-lg-5 pb-4">
        <div class="card card-outline card-secondary shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fab fa-php mr-2"></i>PHP</h3>
                <p class="small text-muted mb-0 mt-1"><?= _("PHP runtime limits and execution environment.") ?></p>
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
    <div class="col-lg-4 col-md-6 order-lg-6 pb-4">
        <div class="card card-outline card-warning shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fas fa-globe mr-2"></i><?= _("Web Server") ?></h3>
                <p class="small text-muted mb-0 mt-1"><?= _("Web server software and available modules.") ?></p>
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
    <div class="col-lg-4 col-md-6 order-lg-7 pb-4">
        <div class="card card-outline card-success shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fas fa-database mr-2"></i><?= _("Database") ?></h3>
                <p class="small text-muted mb-0 mt-1"><?= _("Database connection and server version used by the CRM.") ?></p>
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
    <div class="col-lg-4 col-md-6 order-lg-8 pb-4">
        <div class="card card-outline card-info shadow-sm mb-4 h-100">
            <div class="card-header border-0 py-2">
                <h3 class="card-title d-block float-none mb-0"><i class="fas fa-envelope mr-2"></i><?= _("Email Information") ?></h3>
                <p class="small text-muted mb-0 mt-1"><?= _("SMTP settings used to send messages from the CRM.") ?></p>
            </div>
            <div class="card-body overflow-auto p-2">
                <table class="table table-sm table-striped table-hover mb-3 text-sm">
                    <tr>
                        <td>SMTP Host</td>
                        <td><?= SystemConfig::getValue("sSMTPHost") ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Valid Mail Server Settings") ?></td>
                        <td class="text-right"><span class="badge badge-<?= SystemConfig::hasValidMailServerSettings() ? "success" : "danger" ?>"><i class="fas fa-<?= SystemConfig::hasValidMailServerSettings() ? "check" : "times" ?> mr-1" aria-hidden="true"></i><?= SystemConfig::hasValidMailServerSettings() ? _("Valid") : _("Invalid") ?></span></td>
                    </tr>
                </table>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <label class="text-muted small font-weight-bold text-uppercase mb-0"><?= _("System Infos") ?></label>
                        <div class="small text-muted"><?= _("Open this section to test the mail server connection.") ?></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-info" data-toggle="collapse" data-target="#systemInfosCollapse" aria-expanded="false" aria-controls="systemInfosCollapse" title="<?= _("Show system information") ?>">
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        <span class="sr-only"><?= _("Show system information") ?></span>
                    </button>
                </div>

                <div id="systemInfosCollapse" class="collapse">
                                <p id="mailTest" class="alert alert-light border mb-0" aria-live="polite" data-loading-message="<?= _("Testing connection .....") ?>"><?= _("Testing connection .....") ?></p>
                                </div>
            </div>
        </div>
    </div>
    <div class="col-12 order-lg-9 pb-4">
        <?php
        $integrityCheckStatus = AppIntegrityService::getIntegrityCheckStatus();
        $integrityCheckPassed = ($integrityCheckStatus === _("Passed"));
        ?>
        <div class="card card-outline card-<?= $integrityCheckPassed ? "success" : "danger" ?> shadow-sm mb-4">
            <div class="card-header border-0 py-2">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h3 class="card-title d-block float-none mb-0"><i class="fas fa-shield-alt mr-2"></i><?= _("Application Integrity Check") ?></h3>
                        <p class="small text-muted mb-0 mt-1"><?= _("Compares protected application files with their expected signatures.") ?></p>
                    </div>
                    <div class="mt-2 mt-md-0 d-flex align-items-center">
                        <span class="badge badge-<?= $integrityCheckPassed ? "success" : "danger" ?> mr-2"><i class="fas fa-<?= $integrityCheckPassed ? "check" : "exclamation-triangle" ?> mr-1" aria-hidden="true"></i><?= $integrityCheckStatus ?></span>
                        <button type="button" class="btn btn-sm btn-outline-<?= $integrityCheckPassed ? "success" : "danger" ?>" data-toggle="collapse" data-target="#integrityCheckCollapse" aria-expanded="false" aria-controls="integrityCheckCollapse" title="<?= _("Show integrity check details") ?>">
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            <span class="sr-only"><?= _("Show integrity check details") ?></span>
                        </button>
                    </div>
                </div>
            </div>
            <div id="integrityCheckCollapse" class="collapse">
              <div class="card-body p-2">
                <?php
                $signatureFailures = AppIntegrityService::getFilesFailingIntegrityCheck();
                $crmIntegrityFailures = $signatureFailures['CRM'] ?? [];
                $pluginIntegrityFailures = $signatureFailures['PLUGINS'] ?? [];
                $hasCrmIntegrityFailures = count($crmIntegrityFailures) > 0;
                $hasPluginIntegrityFailures = count($pluginIntegrityFailures) > 0;
                ?>
                <div class="alert alert-<?= $hasCrmIntegrityFailures ? "danger" : "success" ?> d-flex align-items-center py-2 mb-3" role="status">
                    <i class="fas fa-<?= $hasCrmIntegrityFailures ? "exclamation-triangle" : "check-circle" ?> fa-fw mr-2" aria-hidden="true"></i>
                    <span class="small font-weight-bold text-uppercase"><?= _('Details:')?> CRM (<?= $hasCrmIntegrityFailures ? _('One or more files failed the integrity check') : _('All files passed the integrity check') ?>)</span>
                </div>
                <?php
                if ($hasCrmIntegrityFailures) {
                    ?>
                    <p><?= _('Files failing integrity check') ?>:
                                        <table class="table table-sm table-striped table-hover text-sm display responsive no-wrap" width="100%" id="fileIntegrityCheckResultsTable">
                      <thead class="thead-light">
                                            <tr>
                                            <th><?= _("FileName") ?></th>
                                            <th><?= _("Expected Hash") ?></th>
                                            <th><?= _("Actual Hash") ?></th>
                                            </tr>
                    </thead>
                      <?php
                        foreach ($crmIntegrityFailures as $file) {
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
                <div class="alert alert-<?= $hasPluginIntegrityFailures ? "danger" : "success" ?> d-flex align-items-center py-2 mb-3" role="status">
                    <i class="fas fa-<?= $hasPluginIntegrityFailures ? "exclamation-triangle" : "check-circle" ?> fa-fw mr-2" aria-hidden="true"></i>
                    <span class="small font-weight-bold text-uppercase"><?= _('Details:')?> PLUGINS (<?= $hasPluginIntegrityFailures ? _('One or more files failed the integrity check') : _('All files passed the integrity check') ?>)</span>
                </div>
                <?php
                if ($hasPluginIntegrityFailures) {
                    ?>
                    <p><?= _('Files failing integrity check') ?>:
                                        <table class="table table-sm table-striped table-hover text-sm display responsive no-wrap" width="100%" id="pluginfileIntegrityCheckResultsTable">
                      <thead class="thead-light">
                                            <tr>
                                            <th><?= _("Plugin") ?></th>
                                            <th><?= _("FileName") ?></th>
                                            <th><?= _("Expected Hash") ?></th>
                                            <th><?= _("Actual Hash") ?></th>
                                            </tr>
                    </thead>
                      <?php
                        foreach ($pluginIntegrityFailures as $pluginName => $files) {
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