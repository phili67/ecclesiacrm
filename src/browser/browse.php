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

<div class="row">
    <div class="col-md-12">
        <div class="btn-group">
            <button type="button" id="uploadFile" class="btn btn-success btn-sm drag-elements" data-personid="1" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= _("Upload a file in EDrive") ?>">
                &nbsp;&nbsp;<i class="fas fa-cloud-upload-alt"></i>&nbsp;&nbsp;
            </button>
            <button type="button" class="filemanager-download btn btn-warning btn-sm" data-personid="1" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= _("Download") ?>" style="display: none;">
                &nbsp;&nbsp;<i class="fas fa-cloud-download-alt"></i>&nbsp;&nbsp;
            </button>
            <button type="button" class="btn btn-primary btn-sm drag-elements new-folder" data-personid="1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Créer un dossier">
                &nbsp;&nbsp;<i class="far fa-folder"></i>&nbsp;&nbsp;
            </button>
            <button type="button" class="btn btn-danger btn-sm drag-elements trash-drop ui-droppable" data-personid="1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Supprimer">
                &nbsp;&nbsp;<i class="fas fa-trash-alt"></i>&nbsp;&nbsp;
            </button>
            <button type="button" class="btn btn-info btn-sm drag-elements folder-back-drop ui-droppable" data-personid="1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Monter d'un niveau">
                &nbsp;&nbsp;<i class="fas fa-level-up-alt"></i>&nbsp;&nbsp;
            </button>
            <button type="button" class="btn btn-default btn-sm drag-elements filemanager-refresh" data-toggle="tooltip" data-placement="top" title="" data-original-title="Actualiser les fichiers">
                &nbsp;&nbsp;<i class="fas fa-sync-alt"></i>&nbsp;&nbsp;
            </button>
        </div>
    </div>
</div>

<br>

<div class="row">
    <div class="col-md-12 filmanager-left">
        <table class="table table-striped table-bordered dataTable no-footer dtr-inline" id="edrive-table"
               width="100%"></table>
    </div>
    <div class="col-md-3 filmanager-right" style="display: none;">
        <h3><?= _("Preview") ?>
            <button type="button" class="close close-file-preview" data-dismiss="alert" aria-hidden="true">×</button>
        </h3>
        <span class="preview"></span>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-12">
    <span class="float-left" id="currentPath">
      <?= !is_null($user) ? MiscUtils::pathToPathWithIcons($user->getCurrentpath()) : "" ?>
    </span>
    </div>
</div>


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.currentPersonID = <?= $user->getPersonId() ?>;
    window.CRM.browserImage = true;
    window.CRM.donatedItemID = <?= $donatedItemID ?>;
</script>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer-Short.php'; ?>

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
