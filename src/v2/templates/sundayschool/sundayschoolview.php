<?php
/*******************************************************************************
 *
 *  filename    : sundayschoolview.php
 *  last change : 2019-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\Cart;

require $sRootDocument . '/Include/Header.php';

?>

<?php
  if (SessionUser::getUser()->isAddRecords()) {
?>
  <div class="callout callout-info info"><?= _("To add students to this class, simply add them with the select field at the bottom of this page.") ?></div>
  <div class="callout callout-warning edition-mode" style="display: none;"><?= _("You're now in edition mode. To see the entire page again, click the button") ?>   <button type="button" class="btn btn-default exit-edition-mode" data-widget="collapse"><?= _("Exit") ?></button></div>
<?php
  }
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?= _('Sunday School Class Functions') ?></h3>
  </div>
  <div class="box-body">
    <?php
    if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
      // Display link
      ?>
      <div class="btn-group">
        <a class="btn btn-app" id="sEmailLink" href=""><i class="fa fa-send-o"></i><?= _('Email') ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only"><?= _('Toggle Dropdown') ?></span>
        </button>
        <ul class="dropdown-menu" id="dropDownMail" role="menu"></ul>
      </div>

      <div class="btn-group">
        <a class="btn btn-app" id ="sEmailLinkBCC" href=""><i class="fa fa-send"></i><?= _('Email (BCC)') ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only"><?= _('Toggle Dropdown') ?></span>
        </button>
        <ul class="dropdown-menu" id="dropDownMailBCC"  role="menu"></ul>
      </div>
      <?php
    }
    ?>
    <!-- <a class="btn btn-success" data-toggle="modal" data-target="#compose-modal"><i class="fa fa-pencil"></i> Compose Message</a>  This doesn't really work right now...-->
    <a class="btn btn-app bg-yellow" href="<?= $sRootPath ?>/v2/group/<?= $iGroupId ?>/view"><i
        class="fa fa-info-circle"></i><?= _('Show More Props') ?> </a>
  <?php
    if (SessionUser::getUser()->isManageGroupsEnabled()) {
  ?>
  <a class="btn btn-app" href="<?= $sRootPath ?>/GroupEditor.php?GroupID=<?= $iGroupId?>"><i class="fa fa-pencil"></i><?= _("Edit this Class") ?></a>
  <button class="btn btn-app bg-maroon"  id="deleteClassButton"><i class="fa fa-trash"></i><?= _("Delete this Class") ?></button>
  <?php
    }
  ?>
  <?php
  if (SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
      || SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) || SessionUser::getUser()->isMenuOptionsEnabled()) {
  ?>
    <a class="btn btn-app bg-aqua makeCheckOut disabled" id="makeCheckOut" data-makecheckoutgroupid="<?= $iGroupId ?>" data-makecheckoutgroupname="<?= $iGroupName ?>"> <i class="fa fa-calendar-check-o"></i> <span class="cartActionDescription"><?= _('Make Check-out') ?></span></a>
  <?php
    }
  ?>
  <?php
  if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) && (SessionUser::getUser()->isExportSundaySchoolPDFEnabled() || SessionUser::getUser()->isCSVExportEnabled())) {
  ?>
    <a class="btn btn-app bg-green exportCheckOutCSV disabled"  id="exportCheckOutCSV" data-makecheckoutgroupid="<?= $iGroupId ?>" > <i class="fa fa-file-excel-o"></i> <span class="cartActionDescription"><?= _("Export Attendance") ?></span></a>
  <?php
   }
   if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) && SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) {
  ?>
    <a class="btn btn-app bg-red exportCheckOutPDF disabled"  id="exportCheckOutPDF" data-makecheckoutgroupid="<?= $iGroupId ?>" > <i class="fa fa-file-pdf-o"></i> <span class="cartActionDescription"><?= _("Export Attendance") ?></span></a>

    <a class="btn btn-app bg-purple" id="studentbadge" data-groupid="<?= $iGroupId ?>" > <i class="fa fa-file-picture-o"></i> <span class="cartActionDescription"><?= _("Student Badges") ?></span></a>
  <?php
    }
  ?>
  <?php
    if (Cart::StudentInCart($iGroupId) && SessionUser::getUser()->isShowCartEnabled()){
  ?>
    <a class="btn btn-app RemoveStudentsFromGroupCart" id="AddStudentsToGroupCart" data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= _("Remove Students from Cart") ?></span></a>
  <?php
    } else if (SessionUser::getUser()->isShowCartEnabled()) {
   ?>
    <a class="btn btn-app AddStudentsToGroupCart disabled" id="AddStudentsToGroupCart" data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add Students to Cart") ?></span></a>
  <?php
    }
  ?>
  <?php
    if (Cart::TeacherInCart($iGroupId) && SessionUser::getUser()->isShowCartEnabled()) {
  ?>
    <a class="btn btn-app RemoveFromTeacherGroupCart" id="AddToTeacherGroupCart" data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= _("Remove Teachers from Cart") ?></span></a>
  <?php
    } else if (SessionUser::getUser()->isShowCartEnabled()) {
  ?>
    <a class="btn btn-app AddToTeacherGroupCart disabled" id="AddToTeacherGroupCart" data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add Teachers to Cart") ?></span></a>
  <?php
   }

  ?>
  </div>
</div>

<div class="box box-success teachers">
  <div class="box-header with-border">
    <h3 class="box-title"><?= _('Teachers') ?></h3>

    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body row teachers_container"></div>
</div>

<?php
if (SessionUser::getUser()->isAddRecords()) {
    ?>
    <div class="box teachers">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo _("Add Teachers to the Team"); ?>:</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-1">
                    <?= _("Add") ?>
                </div>
                <div class="col-md-3">
                    <select class="form-control personSearchTeachers  select2" name="addGroupMember" style="width:100%"></select>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<?php
   if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)) {
?>

<div class="box box-info quick-status">
  <div class="box-header  with-border">
    <h3 class="box-title"><?= _('Quick Status') ?></h3>

    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body row">
    <div class="col-lg-8">
      <!-- Bar chart -->
      <div class="box box-primary">
        <div class="box-header">
          <i class="fa fa-bar-chart-o"></i>

          <h3 class="box-title"><?= _('Birthdays by Month') ?></h3>
        </div>
        <div class="box-body">
          <div class="disableSelection" id="bar-chart" style="width: 100%; height: 300px;"></div>
        </div>
        <!-- /.box-body-->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-lg-4">
      <!-- Donut chart -->
      <div class="box box-primary">
        <div class="box-header">
          <i class="fa fa-bar-chart-o"></i>

          <h3 class="box-title"><?= _('Gender') ?></h3>
        </div>
        <div class="box-body">
          <div id="donut-chart" style="width: 100%; height: 300px;"></div>
        </div>
        <!-- /.box-body-->
      </div>
      <!-- /.box -->
    </div>
  </div>
  <div class="box-body row">
    <div class="col-lg-12 text-center">
      <small><?= _("Click the chart or Donut parts to interact with the table below.") ?></small>
    </div>
  </div>
</div>

<?php
  }
?>

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><?= _('Students') ?></h3>
    <div style="float:right">
      <label><?= _("Edition Mode") ?> <input data-size="mini" id="editionMode" type="checkbox" data-toggle="toggle" data-on="<?= _("On") ?>" data-off="<?= _("Off") ?>">
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body table-responsive">
    <h4 class="birthday-filter" style="display:none;"><?= _('Showing students with birthdays in') ?> : <span class="month"></span> <i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></h4>
    <h4 class="gender-filter" style="display:none;"><?= _('Showing students with gender') ?> : <span class="type"></span> <i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></h4>
    <table id="sundayschoolTable" class="table table-striped table-bordered data-table" cellspacing="0" width="100%"> </table>
  </div>
</div>

<?php
function implodeUnique($array, $withQuotes)
{
    array_unique($array);
    asort($array);
    if (count($array) > 0) {
        if ($withQuotes) {
            $string = implode("','", $array);

            return "'".$string."'";
        } else {
            return implode(',', $array);
        }
    }

    return '';
}

?>

<?php
  if (SessionUser::getUser()->isAddRecords()) {
?>
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?php echo _("Add Members to Sunday Group"); ?>:</h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-1">
        <?= _("Add") ?>
      </div>
      <div class="col-md-3">
        <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
      </div>
    </div>
  </div>
</div>
<?php
  }
?>

<!-- FLOT CHARTS -->
<script  src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script  src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.resize.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script  src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.pie.js"></script>
<!-- FLOT CATEGORIES PLUGIN - Used to draw bar charts -->
<script  src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.categories.js"></script>

<script nonce="<?= $CSPNonce ?>">
  var birthDayMonthChartJSON = [<?= $birthDayMonthChartJSON ?>];
  var genderChartJSON        = [<?= $genderChartJSON ?>];
  var birthDateColumnText    = '<?= _("Birth Date") ?>';
  var genderColumnText       = '<?= _("Gender") ?>';
  var sundayGroupId          = <?= $iGroupId ?>;
  var canSeePrivacyData      = <?= (SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId))?1:0 ?>;
  var canDeleteMembers       = <?= SessionUser::getUser()->isDeleteRecordsEnabled()?1:0 ?>;
  var sundayGroupName        = "<?= $iGroupName ?>";
</script>

<script src="<?= $sRootPath ?>/skin/js/sundayschool/SundaySchoolClassView.js" ></script>

<?php
require $sRootDocument . '/Include/Footer.php';
?>


