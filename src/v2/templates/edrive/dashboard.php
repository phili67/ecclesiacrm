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

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

require $sRootDocument . '/Include/Header.php';
?>


<div class="alert alert-light border d-flex align-items-start mb-4">
    <i class="fas fa-lightbulb text-primary mt-1 mr-3"></i>
    <div>
        <strong class="d-block mb-1"><?= _('EDrive workspace') ?></strong>
        <span class="text-muted"><?= _('Manage your files, previews and internal sharing from one place.') ?></span>
    </div>
</div>    

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($user->isEDriveEnabled()) { ?>
            <form action="#" method="post" id="formId" enctype="multipart/form-data">
                <div class="card card-outline card-info edrive-upload-card mb-4">
                    <div class="card-body p-3">
                        <label class="edrive-upload-dropzone" id="edrive-upload-dropzone" for="noteInputFile">
                            <span class="edrive-upload-icon" aria-hidden="true"><i class="fas fa-cloud-arrow-up"></i></span>
                            <span class="edrive-upload-copy">
                                <strong><?= _("Upload your files") ?></strong>
                                <span><?= _("Click to choose files or drag and drop them here.") ?></span>
                            </span>
                            <span class="edrive-upload-overlay" aria-hidden="true">
                                <i class="fas fa-arrow-down"></i>
                                <strong><?= _("Drop files to upload") ?></strong>
                            </span>
                            <input type="file" class="edrive-upload-input" id="noteInputFile" name="noteInputFile[]" multiple>
                        </label>
                        <div class="download-zone" style="display: none">
                            <progress id="progress-bar" value="0" max="100"></progress>
                            <label id="progress-bar-label" for="progress-bar" class="mb-0">0%</label>
                        </div>
                    </div>
                </div>
            </form>
        <?php } ?>
        <div class="edrive-panel-backdrop" aria-hidden="true"></div>
        <div class="row">
            <div class="col filmanager-left">
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-folder-tree mr-1"></i> <?= _("Files") ?>

                            </h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-sm drag-elements folder-back-drop folder-back-button" data-personid="<?= $personId ?>"
                            data-toggle="tooltip" data-placement="top" title="<?= _("Move up one level, or drag the file(s) to move them up one level.") ?>"
                            <?= (!is_null($user) && $user->getCurrentpath() != "/") ? "" : 'style="display: none;"' ?>>
                            <i class="fas fa-level-up-alt"></i>
                        </button>
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
            <div class="col filmanager-right" style="display: none;" aria-labelledby="edrive-panel-title">
                <div class="sticky-top">
                    <div class="card card-outline card-primary shadow-sm mb-0">
                        <div class="card-header">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="mr-3">
                                    <div class="preview-title h6 mb-0" id="edrive-panel-title"></div>
                                </div>
                                <button type="button" class="close close-file-preview" aria-label="<?= _("Close") ?>">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="share-part card shadow-sm mb-3">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0"><?= _("Internal sharing") ?></h3>
                                <a data-toggle="popover" title="" data-content="<?= _("Use this method to share files with individuals or teams within your organization. If the recipient already has access to the share, but can't locate it, you can send them the internal link to facilitate access.") ?>" target="_blank" class="text-info infoFiles" data-original-title="<?= _("Definition") ?>"><i class="far fa-question-circle"></i></a>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3"><?= _("Choose people, define their access rights, then manage existing shares from the list below.") ?></p>
                            <span class="shared d-block w-100 mb-3"></span>

                            <div class="form-group mb-3">
                                <label for="preview-person-group-sabre-Id" class="font-weight-bold"><?= _("Add users") ?></label>
                                <select name="preview-person-group-sabre-Id" id="preview-person-group-sabre-Id" class="form-control select2" style="width:100%"></select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="person-group-rights" class="font-weight-bold"><?= _("Set Rights") ?></label>
                                <select name="person-group-Id" id="person-group-rights" class="form-control" style="width:100%">
                                    <option value="2">[R ] -- <?= _("Read only") ?></option>
                                    <option value="3">[RW] -- <?= _("Read and write") ?></option>
                                </select>
                            </div>

                            <div class="custom-control custom-checkbox mb-3">
                                <input id="sendEmail-sabre" type="checkbox" name="sendEmail-sabre" class="custom-control-input">
                                <label for="sendEmail-sabre" class="custom-control-label fille-mamager-label-small"><?= _("Send email notification") ?></label>
                            </div>

                            <div class="edrive-share-list mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label id="edrive-shared-users-label" class="font-weight-bold mb-0"><?= _("Shared with") ?></label>
                                </div>
                                <div id="edrive-shared-users-list" class="edrive-shared-users-list" role="group" aria-labelledby="edrive-shared-users-label" data-empty-label="<?= _("No one has access yet.") ?>"></div>
                                <select id="select-share-persons-sabre" class="edrive-share-select" multiple aria-hidden="true" tabindex="-1"></select>
                                <div class="edrive-share-actions d-flex flex-wrap align-items-center mt-2">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-danger d-none" id="delete-share"
                                            data-toggle="tooltip" data-placement="top" title="<?= _("Delete shares for the selected users") ?>" disabled><?= _("Delete") ?></button>
                                        <button type="button" class="btn btn-outline-secondary d-none" id="delete-all-share"
                                            data-toggle="tooltip" data-placement="top" title="<?= _("Delete All Shares") ?>" disabled><?= _("Delete All Shares") ?></button>
                                    </div>
                                    <div class="edrive-share-rights dropdown d-none">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                            id="dropdownMenuButtonRights" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false" disabled><?= _("Set Rights") ?></button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButtonRights">
                                            <button class="dropdown-item" type="button" id="set-right-read"><?= _("Read only") ?></button>
                                            <button class="dropdown-item" type="button" id="set-right-read-write"><?= _("Read and write") ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="share-part-another-user card shadow-sm" style="display: none; ">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><?= _("Share By") ?></h3>
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
    window.CRM.collaboraEnabled = <?= !empty(SystemConfig::getValue('sCollaboraURL')) ? 'true' : 'false' ?>;
    window.CRM.isCurrentPathPublicFolder = false;
</script>

<!-- Drag and drop -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
<!-- !Drag and Drop -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/edrive/CollaboraEditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/filemanager.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>