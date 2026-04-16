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

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-1"><i class="fas fa-folder-open mr-2 text-primary"></i><?= _('Edrive : File manager') ?></h3>
        <p class="text-muted mb-0"><?= _('Manage your files, previews and internal sharing from one place.') ?></p>
    </div>
    <div class="text-muted small">
        <i class="fas fa-cloud mr-1"></i><?= _('EDrive workspace') ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($user->isEDriveEnabled()) { ?>
            <form action="#" method="post" id="formId" enctype="multipart/form-data">
                <div class="card card-outline card-info collapsed-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-upload mr-1"></i> <?= _("Download files") ?></h3>

                        <div class="card-tools pull-right">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body" id="edrive-upload-dropzone">
                        <div class="border rounded p-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label for="noteInputFile" class="mb-0"><?= _("Files input") ?></label>
                                </div>
                                <div class="col-md-9">
                                    <input type="file" class="form-control" id="noteInputFile" name="noteInputFile[]" multiple>
                                    <small class="text-muted d-block mt-2"><i class="fas fa-hand-paper mr-1"></i><?= _("You can also drag and drop files into this area.") ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 download-zone" style="display: none">
                            <div class="col-md-3">
                                <label class="mb-0"><?= _("Download status") ?></label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <progress id="progress-bar" value="0" max="100" class="mr-2" style="width:100%"></progress>
                                    <label id="progress-bar-label" for="progress-bar" class="mb-0">0%</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="text-muted mb-2 mb-md-0">
                                <i class="fas fa-cloud-upload-alt mr-1"></i><?= _('Upload your files') ?>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success" name="Submit"><i class="fas fa-cloud-upload-alt mr-1"></i> <?= _("Upload") ?></button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        <?php } ?>
        <div class="row">
            <div class="col filmanager-left">
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-folder-tree mr-1"></i> <?= _("Files") ?>

                            </h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm drag-elements folder-back-drop folder-back-button" data-personid="<?= $personId ?>"
                                data-toggle="tooltip" data-placement="top" title="<?= _("Move up one level, or drag the file(s) to move them up one level.") ?>"
                                <?= (!is_null($user) && $user->getCurrentpath() != "/") ? "" : 'style="display: none;"' ?>>
                                &nbsp;&nbsp;<i class="fas fa-level-up-alt"></i>&nbsp;&nbsp;
                            </button>
                        </div>
                        <table class="table dataTable table-hover no-footer" id="edrive-table" width="100%"></table>
                        <hr />
                        <div class="row">
                            <div class="col-md-12">
                                <span class="float-left" id="currentPath">
                                    <?= !is_null($user) ? MiscUtils::pathToPathWithIcons($user->getCurrentpath()) : "" ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col filmanager-right" style="display: none;">
                <div class="sticky-top">
                    <div class="card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="mr-3">
                                    <div class="preview-title h5 mb-0"></div>
                                </div>
                                <button type="button" class="close close-file-preview" data-dismiss="alert" aria-label="<?= _("Close") ?>">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="share-part card card-outline card-secondary shadow-sm mb-3">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="card-title mb-0"><i class="fas fa-share-alt mr-1 text-secondary"></i> <?= _("Internal sharing") ?></h3>
                                </div>
                                <a data-toggle="popover" title="" data-content="<?= _("Use this method to share files with individuals or teams within your organization. If the recipient already has access to the share, but can't locate it, you can send them the internal link to facilitate access.") ?>" target="_blank" class="text-info infoFiles" data-original-title="<?= _("Definition") ?>"><i class="far fa-question-circle"></i></a>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3"><?= _("Choose people, define their access rights, then manage existing shares from the list below.") ?></p>
                            <span class="shared d-block w-100 mb-3"></span>

                            <div class="form-group mb-3">
                                <label for="preview-person-group-sabre-Id" class="font-weight-bold mb-2"><span class="text-danger">*</span> <?= _("Add users") ?></label>
                                <div class="row">
                                    <div class="col-md-8 mb-2 mb-md-0">
                                        <select name="preview-person-group-sabre-Id" id="preview-person-group-sabre-Id" class="form-control select2" style="width:100%"></select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="person-group-Id" id="person-group-rights" class="form-control form-control-sm" style="width:100%" data-placeholder="text to place">
                                            <option value="2">[👀 ] -- [R ]</option>
                                            <option value="3">[👀 ✐] -- [RW]</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-control custom-checkbox mb-3">
                                <input id="sendEmail-sabre" type="checkbox" name="sendEmail-sabre" class="custom-control-input">
                                <label for="sendEmail-sabre" class="custom-control-label fille-mamager-label-small"><?= _("Send email notification") ?></label>
                            </div>

                            <div class="border rounded p-3 bg-light mb-3">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-2">
                                    <label for="select-share-persons-sabre" class="mb-2 mb-md-0"><span class="text-danger">*</span> <?= _("Shared with") ?></label>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="delete-all-share"
                                            data-toggle="tooltip" data-placement="top" title="<?= _("Delete all shares") ?>"
                                            disabled><i class="fas fa-times mr-1"></i></button>
                                        <button type="button" class="btn btn-outline-secondary" id="delete-share"
                                            data-toggle="tooltip" data-placement="top" title="<?= _("Delete shares for the selected users") ?>"
                                            disabled><i class="far fa-stop-circle mr-1"></i></button>
                                    </div>
                                </div>
                                <select size="" id="select-share-persons-sabre" class="form-control" multiple>
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold d-block mb-2"><span class="text-danger">*</span> <?= _("Set Rights") ?></label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        id="dropdownMenuButtonRights" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" disabled>
                                        <?= _("Select your rights") . " [👀  ] " . _("or") . " [👀 ✐]" . " --" ?>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButtonRights">
                                        <button class="dropdown-item" role="button" id="set-right-read"><?= _("[👀  ]") . ' -- ' . _("[R ]") ?></button>
                                        <button class="dropdown-item" role="button" id="set-right-read-write"><?= _("[👀 ✐]") . ' -- ' . _("[RW]") ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="share-part-another-user card card-outline card-warning shadow-sm" style="display: none; ">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-link mr-1 text-warning"></i> <?= _("Share By") ?></h3>
                        </div>
                        <div class="card-body">
                            <span class="shared d-block w-100 mb-3"></span>
                            <div class="share-part-another-user-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.currentPersonID = <?= $personId ?>;
    window.CRM.browserImage = false,
    window.CRM.currentpath = '<?= $user->getCurrentpath() ?>';
    window.CRM.isPublicFolder = false;
    window.CRM.isCurrentPathPublicFolder = false;
</script>

<!-- Drag and drop -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
<!-- !Drag and Drop -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/filemanager.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>