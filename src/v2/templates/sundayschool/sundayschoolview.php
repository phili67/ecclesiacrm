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
    <div
        class="alert alert-info info"><?= _("To add students to this class, simply add them with the select field at the bottom of this page.") ?></div>
    <div class="alert alert-warning edition-mode"
         style="display: none;"><?= _("You're now in edition mode. To see the entire page again, click the button") ?>
        <button type="button" class="btn btn-default exit-edition-mode" data-widget="collapse"><?= _("Exit") ?></button>
    </div>
    <?php
}
?>

<div class="card">
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('Sunday School Class Functions') ?></h3>
    </div>
    <div class="card-body">
        <?php
        if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
            // Display link
            ?>
            <div class="btn-group">
                <a class="btn btn-app" id="sEmailLink" href=""><i class="far fa-paper-plane"></i><?= _('Email') ?></a>
                <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only"><?= _('Toggle Dropdown') ?></span>
                </button>
                <div class="dropdown-menu" id="dropDownMail" role="menu"></div>
            </div>

            <div class="btn-group">
                <a class="btn btn-app" id="sEmailLinkBCC" href=""><i class="fas fa-paper-plane"></i><?= _('Email (BCC)') ?></a>
                <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only"><?= _('Toggle Dropdown') ?></span>
                </button>
                <div class="dropdown-menu" id="dropDownMailBCC" role="menu"></div>
            </div>
            <?php
        }
        ?>
        <!-- <a class="btn btn-success" data-toggle="modal" data-target="#compose-modal"><i class="fas fa-pencil-alt"></i> Compose Message</a>  This doesn't really work right now...-->
        <a class="btn btn-app bg-yellow" href="<?= $sRootPath ?>/v2/group/<?= $iGroupId ?>/view"><i
                class="fas fa-info-circle"></i><?= _('Show More Props') ?> </a>
        <?php
        if (SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isGroupManagerEnabledForId($iGroupId)) {
            ?>
            <a class="btn btn-app" href="<?= $sRootPath ?>/GroupEditor.php?GroupID=<?= $iGroupId ?>"><i
                    class="fas fa-pencil-alt"></i><?= _("Edit this Class") ?></a>
            <button class="btn btn-app bg-maroon" id="deleteClassButton"><i
                    class="fas fa-trash-alt"></i><?= _("Delete this Class") ?></button>
            <?php
        }
        ?>
        <?php
        if (SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
            || SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) || SessionUser::getUser()->isMenuOptionsEnabled()) {
            ?>
            <a class="btn btn-app bg-orange callRegister disabled" id="callRegister"
               data-callregistergroupid="<?= $iGroupId ?>" data-callregistergroupname="<?= $iGroupName ?>"
               data-toggle="tooltip"  data-placement="bottom" title="<?= _("Be Careful! You are about to create or recreate an event of this Sunday school class to call the register.") ?>"> <i
                    class="fas fa-calendar-check"></i> <span
                    class="cartActionDescription"><?= _('Create Event & Call the register') ?></span></a>
            <?php
        }
        ?>
        <?php
        if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) && (SessionUser::getUser()->isExportSundaySchoolPDFEnabled() || SessionUser::getUser()->isCSVExportEnabled())) {
            ?>
            <a class="btn btn-app bg-green exportCheckOutCSV disabled" id="exportCheckOutCSV"
               data-callRegistergroupid="<?= $iGroupId ?>"> <i class="fas fa-file-excel"></i> <span
                    class="cartActionDescription"><?= _("Export Attendance") ?></span></a>
            <?php
        }
        if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) && SessionUser::getUser()->isExportSundaySchoolPDFEnabled()) {
            ?>
            <a class="btn btn-app bg-red exportCheckOutPDF disabled" id="exportCheckOutPDF"
               data-callRegistergroupid="<?= $iGroupId ?>"> <i class="fas fa-file-pdf"></i> <span
                    class="cartActionDescription"><?= _("Export Attendance") ?></span></a>

            <a class="btn btn-app bg-purple" id="studentbadge" data-groupid="<?= $iGroupId ?>"
               data-toggle="tooltip"  data-placement="bottom" title="<?= _("Create here your badges or QR-Code to call the register with them") ?>"> <i
                    class="fas fa-id-badge"></i> <span
                    class="cartActionDescription"><?= _("Student Badges") ?></span></a>
            <?php
        }
        ?>
        <?php
        if (Cart::StudentInCart($iGroupId) && SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-app RemoveStudentsFromGroupCart" id="AddStudentsToGroupCart"
               data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fas fa-times"></i> <span
                    class="cartActionDescription"><?= _("Remove Students from Cart") ?></span></a>
            <?php
        } else if (SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-app AddStudentsToGroupCart disabled" id="AddStudentsToGroupCart"
               data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fas fa-cart-plus"></i> <span
                    class="cartActionDescription"><?= _("Add Students to Cart") ?></span></a>
            <?php
        }
        ?>
        <?php
        if (Cart::TeacherInCart($iGroupId) && SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-app RemoveFromTeacherGroupCart" id="AddToTeacherGroupCart"
               data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fas fa-times"></i> <span
                    class="cartActionDescription"><?= _("Remove Teachers from Cart") ?></span></a>
            <?php
        } else if (SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-app AddToTeacherGroupCart disabled" id="AddToTeacherGroupCart"
               data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fas fa-cart-plus"></i> <span
                    class="cartActionDescription"><?= _("Add Teachers to Cart") ?></span></a>
            <?php
        }

        ?>
    </div>
</div>

<div class="card card-primary teachers">
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('Teachers') ?></h3>

        <div class="card-tools pull-right">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="card-body teachers_container">
        <?php
        if (!SessionUser::getUser()->isAddRecords()) {
        ?>
            <div class="row">
                <div class="col-md-12">
                    <label><?=  _("Private Data") ?></label>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    if (SessionUser::getUser()->isAddRecords()) {
        ?>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-12">
                    <label><?php echo _("Add Teachers to the Team"); ?>:</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-1">
                    <?= _("Add") ?>
                </div>
                <div class="col-md-3">
                    <select class="form-control select2" name="addGroupMember" id="personSearchTeachers"
                            style="width:100%"></select>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php
if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)) {
    ?>

    <div class="card quick-status">
        <div class="card-header border-1">
            <h3 class="card-title"><?= _('Quick Status') ?></h3>

            <div class="card-tools pull-right">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <!-- Bar chart -->
                    <div class="card">
                        <div class="card-header border-1">
                            <h3 class="card-title"><i class="fas fa-birthday-cake"></i> <?= _('Birthdays by Month') ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="disableSelection" id="bar-chart" style="width: 100%; height: 300px;"></div>
                        </div>
                        <!-- /.box-body-->
                    </div>
                    <!-- /.box -->
                </div>
                <div class="col-md-4">
                    <!-- Donut chart -->
                    <div class="card">
                        <div class="card-header border-1">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> <?= _('Gender') ?></h3>
                        </div>
                        <div class="card-body">
                            <div id="donut-chart" style="width: 100%; height: 300px;"></div>
                        </div>
                        <!-- /.box-body-->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </div>
        <div class="card-body row">
            <div class="col-lg-12 text-center">
                <small><?= _("Click the chart or Donut parts to interact with the table below.") ?></small>
            </div>
        </div>
    </div>

    <?php
}
?>

<div class="card">
    <div class="card-header border-1">
        <h4 class="card-title"><?= _('Students') ?></h4>
        <div style="float:right;margin-left: 20px">
        <?php if (SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->isGroupManagerEnabledForId($iGroupId)) { ?>
            <button class="btn btn-danger" id="remove_all_members"><i class="fas fa-trash-alt"></i> <?= _("Remove members") ?></button>
        <?php } ?>
        </div>
        <div style="float:right">
            <label><?= _("Edition Mode") ?> <input data-width="100"
                                                   data-size="mini" id="editionMode" type="checkbox"
                                                   data-toggle="toggle" data-on="<?= _("On") ?>"
                                                   data-off="<?= _("Off") ?>">
            </label>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4 class="birthday-filter text-center" style="display:none;"><?= _('Showing students with birthdays in') ?> : <span
                        class="month"></span> <i style="cursor:pointer; color:red;" class="icon far fa-trash-alt"></i></h4>
            </div>
            <div class="col-md-6">
                <h4 class="gender-filter text-center" style="display:none;"><?= _('Showing students with gender') ?> : <span
                        class="type"></span> <i style="cursor:pointer; color:red;" class="icon far fa-trash-alt"></i></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table id="sundayschoolTable" class="table table-striped table-bordered data-table" cellspacing="0"
               width="100%"></table>
            </div>
        </div>
    </div>


<?php
if (SessionUser::getUser()->isAddRecords()) {
    ?>
        <div class="card-footer">
            <label><?php echo _("Add Members to Sunday Group"); ?>:</label>
            <div class="row">
                <div class="col-md-1">
                    <?= _("Add") ?>
                </div>
                <div class="col-md-3">
                    <select class="form-control personSearch  select2" name="addGroupMember" id="personSearch"
                            style="width:100%"></select>
                </div>
            </div>
        </div>
    <?php
}
?>
</div>

<!-- FLOT CHARTS -->
<script src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.resize.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.pie.js"></script>
<!-- FLOT CATEGORIES PLUGIN - Used to draw bar charts -->
<script src="<?= $sRootPath ?>/skin/external/flot/jquery.flot.categories.js"></script>

<script nonce="<?= $CSPNonce ?>">
    var birthDayMonthChartJSON = [<?= $birthDayMonthChartJSON ?>];
    var genderChartJSON = [<?= $genderChartJSON ?>];
    var birthDateColumnText = '<?= _("Birth Date") ?>';
    var genderColumnText = '<?= _("Gender") ?>';
    var sundayGroupId = <?= $iGroupId ?>;
    var canSeePrivacyData = <?= (SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)) ? 1 : 0 ?>;
    var canDeleteMembers = <?= SessionUser::getUser()->isDeleteRecordsEnabled() ? 1 : 0 ?>;
    var sundayGroupName = "<?= $iGroupName ?>";

    window.CRM.currentGroup = <?= $iGroupId ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sundayschool/SundaySchoolClassView.js"></script>
<script src="<?= $sRootPath ?>/skin/js/groupcommon/group_sundaygroup.js"></script>

<?php
require $sRootDocument . '/Include/Footer.php';
?>


