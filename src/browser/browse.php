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

if ( $donatedItemID == NULL ) {
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
        <a href="#" id="uploadFile">
    <span class="fa-stack fa-special-icon drag-elements" data-personid="<?= $user->getPersonId() ?>"
          data-toggle="tooltip" data-placement="top" data-original-title="<?= _("Upload a file in EDrive") ?>">
      <i class="fa fa-square fa-stack-2x" style="color:green"></i>
      <i class="fa fa-cloud-upload fa-stack-1x fa-inverse"></i>
    </span>
        </a>

        <a class="filemanager-download" data-toggle="tooltip" data-placement="top"
           data-original-title="<?= _("Download") ?>" style="display: none;">
  <span class="fa-stack fa-special-icon drag-elements">
    <i class="fa fa-square fa-stack-2x" style="color:orange"></i>
    <i class="fa  fa-cloud-download fa-stack-1x fa-inverse"></i>
  </span>
        </a>

        <a class="new-folder" data-personid="<?= $user->getPersonId() ?>" data-toggle="tooltip" data-placement="top"
           data-original-title="<?= _("Create a Folder") ?>">
<span class="fa-stack fa-special-icon drag-elements">
  <i class="fa fa-square fa-stack-2x" style="color:blue"></i>
  <i class="fa fa-folder-o fa-stack-1x fa-inverse"></i>
</span>
        </a>

        <a class="trash-drop" data-personid="<?= $user->getPersonId() ?>" data-toggle="tooltip" data-placement="top"
           data-original-title="<?= _("Delete") ?>">
<span class="fa-stack fa-special-icon drag-elements">
  <i class="fa fa-square fa-stack-2x" style="color:red"></i>
  <i class="fa fa-trash fa-stack-1x fa-inverse"></i>
</span>
        </a>

        <a class="folder-back-drop" data-personid="<?= $user->getPersonId() ?>" data-toggle="tooltip"
           data-placement="top"
           data-original-title="<?= _("Up One Level") ?>" <?= (!is_null($user) && $user->getCurrentpath() != "/") ? "" : 'style="display: none;"' ?>>
  <span class="fa-stack fa-special-icon drag-elements">
    <i class="fa fa-square fa-stack-2x" style="color:navy"></i>
    <i class="fa fa-level-up fa-stack-1x fa-inverse"></i>
  </span>
        </a>
        <a class="filemanager-refresh" data-toggle="tooltip" data-placement="top"
           data-original-title="<?= _("Actualize files") ?>">
  <span class="fa-stack fa-special-icon drag-elements">
    <i class="fa fa-square fa-stack-2x" style="color:gray"></i>
    <i class="fa  fa-refresh fa-stack-1x fa-inverse"></i>
  </span>
        </a>
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
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/responsive/rowGroup.bootstrap4.min.js"></script>
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
    src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
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
