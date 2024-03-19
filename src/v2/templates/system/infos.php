<?php

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\AppIntegrityService;
use EcclesiaCRM\Service\SystemService;

//Set the page title
include SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4><?= _("EcclesiaCRM Installation Information") ?></h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
                    <tr>
                        <td>EcclesiaCRM <?= _("Software Version") ?></td>
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
        <div class="card">
            <div class="card-header">
                <h4><?= _("System Information") ?></h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
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
        <div class="card">
            <div class="card-header">
                <h4><?= _("Database") ?></h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
                    <tr>
                        <td>EcclesiaCRM <?= _("Database Version") ?></td>
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
        <div class="card">
            <div class="card-header">
                <h4><?= _("Web Server") ?></h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
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
        <div class="card">
            <div class="card-header">
                <h4>PHP</h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
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
        <div class="card">
            <div class="card-header">
                <h4><?= _("Email Information") ?></h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
                    <tr>
                        <td>SMTP Host</td>
                        <td><?= SystemConfig::getValue("sSMTPHost") ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Valid Mail Server Settings") ?></td>
                        <td><?= SystemConfig::hasValidMailServerSettings() ? "true" : "false" ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4><?= _("Application Prerequisites") ?></h4>
            </div>
            <div class="card-body overflow-auto">
                <table class="table table-striped">
                    <?php foreach (AppIntegrityService::getApplicationPrerequisites() as $prerequisite) { ?>
                        <tr>
                          <td><a href='<?=$prerequisite->getLink()?>'><?= $prerequisite->getName()?></a></td>
                          <td><?= ($prerequisite->getMessage())?'<span style="color:green"><i class="fa fa-check" aria-hidden="true"></i></span>':'<span style="color:red"><i class="fa fa-times" aria-hidden="true"></i></span>' ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
              <h4><?= _("Application Integrity Check") . ": " . AppIntegrityService::getIntegrityCheckStatus()?></h4>
            </div>
            <div class="card-body">
              <label><?= _('Details:')?> CRM (<?=  AppIntegrityService::getIntegrityCheckMessage() ?>)</label>
                <?php
                $signatureFailures = AppIntegrityService::getFilesFailingIntegrityCheck();
                if (count($signatureFailures['CRM']) > 0) {
                    ?>
                    <p><?= _('Files failing integrity check') ?>:
                    <table class="display responsive no-wrap" width="100%" id="fileIntegrityCheckResultsTable">
                      <thead>
                      <td><?= _("FileName") ?></td>
                      <td><?= _("Expected Hash") ?></td>
                      <td><?= _("Actual Hash") ?></td>
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
                <br/>
                <label><?= _('Details:')?> PLUGINS (<?=  AppIntegrityService::getIntegrityCheckMessage() ?>)</label>
                <?php
                if (array_key_exists('PLUGINS', $signatureFailures) and count($signatureFailures['PLUGINS']) > 0) {
                    ?>
                    <p><?= _('Files failing integrity check') ?>:
                    <table class="display responsive no-wrap" width="100%" id="pluginfileIntegrityCheckResultsTable">
                      <thead>
                      <td><?= _("Plugin") ?></td>
                      <td><?= _("FileName") ?></td>
                      <td><?= _("Expected Hash") ?></td>
                      <td><?= _("Actual Hash") ?></td>
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
        searching: false
    });

    $("#pluginfileIntegrityCheckResultsTable").DataTable({
        responsive: true,
        paging:false,
        searching: false
    });
  });

</script>

<?php include SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>