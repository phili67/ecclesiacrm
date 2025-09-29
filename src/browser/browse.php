<?php

/* Copyright Philippe Logel not MIT */

// Include the function library
require '../Include/Config.php';

require '../Include/Header-function.php';
require '../Include/Header-Security.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\Utils\InputUtils;

if (!(SessionUser::isActive() && SessionUser::getUser()->isEDrive())) {
    RedirectUtils::Redirect('members/404.php?type=Upload');
    return;
}

$donatedItemID = InputUtils::LegacyFilterInputArr($_GET, 'DonatedItemID');

if ($donatedItemID == NULL) {
    $donatedItemID = 0;
}

// Set the page title and include HTML header
$sPageTitle = _('File Manager');
require '../Include/Header-Short.php';

Header_body_scripts();

$user = SessionUser::getUser();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= _('Edrive : File manager') ?></h3>       

        <div style="float:right">
            <button type="button" class="filemanager-download btn btn-warning btn-sm" data-personid="<?= $user->getPersonId() ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= _("Download") ?>" style="display: none;">
                &nbsp;&nbsp;<i class="fas fa-cloud-download-alt"></i> <?= _("Insert") ?>
            </button>     
        </div>        
    </div>
    <div class="card-body">
        <?php if ($user->isEDriveEnabled()) { ?>
            <form action="#" method="post" id="formId" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-header">
                        <a data-toggle="collapse" href="#collapse-example" aria-expanded="true" aria-controls="collapse-example" id="heading-example" class="d-block">
                            <i class="fa fa-chevron-down pull-right"></i>
                            <?= _("Download files") ?>
                        </a>
                    </div>
                    <div id="collapse-example" class="collapse" aria-labelledby="heading-example">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="noteInputFile"><?= _("Files input") ?></label>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" class="btn btn-primary" id="noteInputFile" name="noteInputFile[]" multiple>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2 download-zone" style="display: none"><label><?= _("Download status") ?></label></div>
                                <div class="col-md-6 download-zone" style="display: none">
                                    <progress id="progress-bar" value="0" max="100"></progress> <label id="progress-bar-label" for="progress-bar">0%</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-2">
                                    <label><?= _('Upload your files') ?></label>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success" name="Submit"><i class="fas fa-cloud-upload-alt"></i> <?= _("Upload") ?></button><br />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php } ?>
        <div class="row">
            <div class="col filmanager-left">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm drag-elements folder-back-drop folder-back-button" data-personid="<?= $user->getPersonId() ?>"
                        data-toggle="tooltip" data-placement="top" title="<?= _("Move up one level, or drag the file(s) to move them up one level.") ?>"
                        <?= (!is_null($user) && $user->getCurrentpath() != "/") ? "" : 'style="display: none;"' ?>>
                        &nbsp;&nbsp;<i class="fas fa-level-up-alt"></i>&nbsp;&nbsp;
                    </button>
                </div>
                <table class="table dataTable table-hover no-footer edrive-table-browse" id="edrive-table" width="100%" style="margin-top:10px!important"></table>
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
                <div class="sticky-top">
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
                    <div class="share-part-another-user" style="display: none; ">
                        <label><?= _("Share By") ?></label>
                        <hr class="hr-filemanager">
                        <span class="shared" width="100%"></span>
                        <div class="share-part-another-user-content"></div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.currentPersonID = <?= $user->getPersonId() ?>;
    window.CRM.browserImage = true;
    window.CRM.donatedItemID = <?= $donatedItemID ?>;
</script>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer-Short.php'; ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/popper/popper.min.js"></script>
<!-- Bootstrap 4.0 -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.js"></script>

<!-- InputMask -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/inputmask/jquery.inputmask.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/pdfmake.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/vfs_fonts.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/jquery.dataTables.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/jszip.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/dataTables.bootstrap4.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/responsive/dataTables.responsive.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/rowGroup.bootstrap4.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/dataTables.buttons.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.colVis.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.html5.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.print.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/dataTables.rowGroup.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/rowGroup.dataTables.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/rowGroup.bootstrap4.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/Select/dataTables.select.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/buttons.bootstrap4.min.js"></script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

<!-- Drag and drop -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>
<!-- !Drag and Drop -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/select2/select2.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootbox/bootbox.all.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/fastclick/fastclick.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/Tooltips.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-toggle/bootstrap-toggle.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/i18next/i18next.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/locale/js/<?= Bootstrapper::getCurrentLocale()->getLocale() ?>.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-validator/validator.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/Footer.js"></script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/js/filemanager.js"></script>
