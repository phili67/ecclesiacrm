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

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= _('Edrive : File manager') ?></h3>        
    </div>
    <div class="card-body">
        <?php if ($user->isNotesEnabled() || ($user->isEditSelfEnabled())) { ?>
            <form action="#" method="post" id="formId" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-body">
                        <label for="noteInputFile"><?= _("Files input") ?></label>
                        <input type="file" id="noteInputFile" name="noteInputFile[]" multiple>
                        <?= _('Upload your files') ?>
                        <button type="submit" class="btn btn-success" name="Submit"><i class="fas fa-cloud-upload-alt"></i> <?= _("Upload") ?></button>
                    </div>
                </div>
            </form>
        <?php } ?>
        <div class="row">
            <div class="col filmanager-left">
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
            <div class="col filmanager-right" style="display: none;">
                <label class="preview-title-label">
                    <span class="preview-title" style="width: 100%;"></span><button type="button" class="close close-file-preview" data-dismiss="alert" aria-hidden="true">×</button>
                    <hr class="hr-filemanager" />
                    <span class="preview"></span>
                </label>
                <br>
                <div class="share-part">
                    <label><?= _("Internal sharing") ?></label>
                    <span class="shared" width="100%"></span>
                    <div>
                        <div class="row div-title">
                            <div class="col-md-4"></div>
                            <div class="col-md-8 col-center">
                                <button type="button" class="btn btn-sm btn-secondary btn-xs"
                                    id="delete-all-share"
                                    data-toggle="tooltip" data-placement="top" title="<?= _("Delete all shares") ?>"
                                    disabled><i class="fas fa-times"></i> <?= _("Delete") ?></button>
                                &nbsp;
                                <button type="button" class="btn btn-sm btn-secondary btn-xs" id="delete-share"
                                    data-toggle="tooltip" data-placement="top" title="<?= _("Delete shares for the selected users") ?>"
                                    disabled><i class="far fa-stop-circle"></i> <?= _("Stop sharing") ?></button>
                            </div>
                        </div>
                        <div class="row div-title-file-manager">
                            <div class="col-md-4">
                                <span style="color: red">*</span><?= _("With") ?>:
                            </div>
                            <div class="col-md-8">
                                <select size="6" id="select-share-persons-sabre" class="form-control form-control-access-rights" multiple>
                                </select>
                            </div>
                        </div>
                        <div class="row div-title-file-manager">
                            <div class="col-md-4"><span style="color: red">*</span><?= _("Set Rights") ?>:</div>
                            <div class="col-md-8">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle btn-xs" type="button"
                                        id="dropdownMenuButtonRights" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" disabled>
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
                            <div class="col-md-3">
                                <span style="color: red">*</span><?= ("Add user") ?>:
                            </div>
                            <div class="col-md-9">
                                <a data-toggle="popover" title="" data-content="<?= _("Use this method to share files with individuals or teams within your organization. If the recipient already has access to the share, but can't locate it, you can send them the internal link to facilitate access.") ?>" target="_blank" class="blue infoFiles" data-original-title="<?= _("Definition") ?>"><i class="far  fa-question-circle"></i></a>
                                <select name="preview-person-group-sabre-Id" id="preview-person-group-sabre-Id" class="form-control select2" style="width:90%"></select>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-md-6">
                                <span style="color: red">*</span>
                                <input id="sendEmail-sabre" type="checkbox" name="sendEmail-sabre"> <label for="sendEmail-sabre" class="fille-mamager-label-small"><?= _("Send email notification") ?></label>
                            </div>
                            <div class="col-md-3">
                                <label class="fille-mamager-label-small"><?= _("With Right") ?> :</label>
                            </div>
                            <div class="col-md-3">
                                <select name="person-group-Id" id="person-group-rights" class="form-control form-control-sm" style="width:100%" data-placeholder="text to place">
                                    <option value="2">[👀 ] -- [R ]</option>
                                    <option value="3">[👀 ✐] -- [RW]</option>
                                </select>
                            </div>
                        </div>
                        <br />
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
</script>

<!-- Drag and drop -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
<!-- !Drag and Drop -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/filemanager.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>