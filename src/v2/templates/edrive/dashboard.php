<?php

/*******************************************************************************
 *
 *  filename    : templates/dashboard.php
 *  last change : 2025-05-24
 *  description : manage the dahboard for EDrive
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2026 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="btn-group">
            <?php
            if ($user->isNotesEnabled() || ($user->isEditSelfEnabled())) {
            ?>
                <button type="button" id="uploadFile" class="btn btn-success btn-sm drag-elements" data-personid="<?= $personId ?>" data-toggle="tooltip" data-placement="top" title="<?= _("Upload a file in EDrive") ?>">
                    &nbsp;&nbsp;<i class="fas fa-cloud-upload-alt"></i>&nbsp;&nbsp;
                </button>
            <?php
            }
            ?>

            <button type="button" class="btn btn-primary btn-sm drag-elements new-folder" data-personid="<?= $personId ?>"
                data-toggle="tooltip" data-placement="top" title="<?= _("Create a Folder") ?>">
                &nbsp;&nbsp;<i class="far fa-folder"></i>&nbsp;&nbsp;
            </button>

            <button type="button" class="btn btn-danger btn-sm drag-elements trash-drop" data-personid="<?= $personId ?>"
                data-toggle="tooltip" data-placement="top" title="<?= _("Delete") ?>">
                &nbsp;&nbsp;<i class="fas fa-trash-alt"></i>&nbsp;&nbsp;
            </button>

            <button type="button" class="btn btn-info btn-sm drag-elements folder-back-drop" data-personid="<?= $personId ?>"
                data-toggle="tooltip" data-placement="top" title="<?= _("Up One Level") ?>"
                <?= (!is_null($user) && $user->getCurrentpath() != "/") ? "" : 'style="display: none;"' ?>>
                &nbsp;&nbsp;<i class="fas fa-level-up-alt"></i>&nbsp;&nbsp;
            </button>


            <button type="button" class="btn btn-default btn-sm drag-elements filemanager-refresh"
                data-toggle="tooltip" data-placement="top" title="<?= _("Actualize files") ?>">
                &nbsp;&nbsp;<i class="fas fa-sync-alt"></i>&nbsp;&nbsp;
            </button>
        </div>
    </div>
</div>

<br>

<div class="row">
    <div class="col filmanager-left">
        <table class="table table-striped table-bordered dataTable no-footer dtr-inline" id="edrive-table"
            width="100%"></table>
    </div>
    <div class="col filmanager-right" style="display: none;">
        <label>
            <span class="preview-title" style="width: 100%;"></span><button type="button" class="close close-file-preview" data-dismiss="alert" aria-hidden="true">×</button>
            <span class="preview"></span>
        </label>
        <br>
        <label><?= _("Internal sharing") ?></label>
        <span class="shared" width="100%"></span>
        <hr />
        <div>
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-8 col-center">
                    <button type="button" class="btn btn-sm btn-warning"><i class="fas fa-times"></i> <?= _("Delete") ?></button>
                    &nbsp;<button type="button" class="btn btn-sm btn-danger"><i class="far fa-stop-circle"></i> <?= _("Stop sharing") ?></button>
                    &nbsp;<button type="button" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> <?= _("Ok") ?></button>
                </div>
            </div>
            <div class="row div-title">
                <div class="col-md-4">
                    <span style="color: red">*</span><?= _("With") ?>:
                </div>
                <div class="col-md-8">
                    <select size="6" style="width:100%" id="select-share-persons-sabre" multiple>
                    </select>
                </div>
            </div>
            <div class="row div-title">
                <div class="col-md-4"><span style="color: red">*</span><?= _("Set Rights") ?>:</div>
                <div class="col-md-8">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= _("Select your rights") . " [👀  ] " . _("or") . " [👀 ✐]" . "--" ?>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" role="button" id="set-right-read" href="#"><?= _("[👀  ]") . ' -- ' . _("[R ]") ?></a>
                            <a class="dropdown-item" role="button" id="set-right-read-write" href="#"><?= _("[👀 ✐]") . ' -- ' . _("[RW]") ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row div-title">
                <div class="col-md-4"><span style="color: red">*</span><?= _("Send email notification") ?>:</div>
                <div class="col-md-8">
                    <input id="sendEmail-sabre" type="checkbox">
                </div>
            </div>
            <div class="row div-title">
                <div class="col-md-3"><span style="color: red">*</span><?= ("Add user") ?>:</div>
                <div class="col-md-9">
                    <a data-toggle="popover" title="" data-content="<?= _("Use this method to share files with individuals or teams within your organization. If the recipient already has access to the share, but can't locate it, you can send them the internal link to facilitate access.") ?>" target="_blank" class="blue infoFiles" data-original-title="<?= _("Definition") ?>"><i class="far  fa-question-circle"></i></a>
                    <select name="preview-person-group-sabre-Id" id="preview-person-group-sabre-Id" class="form-control select2" style="width:90%"></select>
                </div>
            </div>
        </div>
    </div>
</div>
<hr />
<div class="row">
    <div class="col-md-12">
        <span class="float-left" id="currentPath">
            <?= !is_null($user) ? MiscUtils::pathToPathWithIcons($user->getCurrentpath()) : "" ?>
        </span>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.currentPersonID = <?= $personId ?>;
    window.CRM.browserImage = false
</script>

<!-- Drag and drop -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
<!-- !Drag and Drop -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/filemanager.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>