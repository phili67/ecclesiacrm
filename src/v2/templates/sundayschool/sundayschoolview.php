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
use EcclesiaCRM\Utils\MiscUtils;

require $sRootDocument . '/Include/Header.php';

?>

<?php
if (SessionUser::getUser()->isAddRecords()) {
    ?>
    <div class="alert alert-info info mb-3"><?= _("To add students to this class, simply add them with the select field at the bottom of this page.") ?></div>
    <div class="alert alert-warning edition-mode"
         style="display: none;"><?= _("You're now in edition mode. To see the entire page again, click the button") ?>
        <button type="button" class="btn btn-sm btn-outline-secondary exit-edition-mode" data-widget="collapse"><?= _("Exit") ?></button>
    </div>
    <?php
}
?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2">
        <h3 class="card-title mb-0"><i class="fas fa-tools mr-1"></i><?= _('Class Actions') ?></h3>
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <?php
        if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
            // Display link
            ?>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-primary" id="sEmailLink" href=""><i class="far fa-paper-plane mr-1"></i><?= _('Email') ?></a>
                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only"><?= _('Toggle Dropdown') ?></span>
                </button>
                <div class="dropdown-menu" id="dropDownMail" role="menu"></div>
            </div>

            <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-primary" id="sEmailLinkBCC" href=""><i class="fas fa-paper-plane mr-1"></i><?= _('Email (BCC)') ?></a>
                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only"><?= _('Toggle Dropdown') ?></span>
                </button>
                <div class="dropdown-menu" id="dropDownMailBCC" role="menu"></div>
            </div>
            <?php
        }
        ?>
        <!-- <a class="btn btn-success" data-toggle="modal" data-target="#compose-modal"><i class="fas fa-pencil-alt"></i> Compose Message</a>  This doesn't really work right now...-->
        <?php
        if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)
            or SessionUser::getUser()->isGroupManagerEnabledForId($iGroupId)
            or SessionUser::getUser()->isManageGroups()) {
                    ?>
            <a class="btn btn-sm btn-outline-warning" href="<?= $sRootPath ?>/v2/group/<?= $iGroupId ?>/view"><i
            class="fas fa-info-circle mr-1"></i><?= _('Show More Props') ?> </a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/group/editor/<?= $iGroupId ?>"><i
                class="fas fa-pencil-alt mr-1"></i><?= _("Edit this Class") ?></a>
            <button class="btn btn-sm btn-outline-danger" id="deleteClassButton"><i
                class="fas fa-trash-alt mr-1"></i><?= _("Delete this Class") ?></button>
            <?php
        }
        ?>
        <?php
        if (SessionUser::getUser()->isDeleteRecordsEnabled() or SessionUser::getUser()->isAddRecordsEnabled()
            or SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) or SessionUser::getUser()->isMenuOptionsEnabled()) {
            ?>
            <a class="btn btn-sm btn-warning callRegister disabled" id="callRegister"
               data-callregistergroupid="<?= $iGroupId ?>" data-callregistergroupname="<?= $iGroupName ?>"
               data-toggle="tooltip"  data-placement="bottom" title="<?= _("Be Careful! You are about to create or recreate an event of this Sunday school class to call the register.") ?>"> <i
                    class="fas fa-calendar-check mr-1"></i> <span
                    class="cartActionDescription"><?= _('Create Event & Call the register') ?></span></a>
            <?php
        }
        ?>
        <?php
        if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) or SessionUser::getUser()->isExportSundaySchoolPDFEnabled() or SessionUser::getUser()->isCSVExportEnabled()) {
            ?>
            <div class="btn-group btn-group-sm show">
                    <a class="btn btn-outline-info exportCheckOutPDF" data-groupid="<?= $iGroupId ?>" data-typedesc="<?= _("Export") ?>"><i class="fas fa-file-export mr-1"></i> <?= _("Export") ?></a>
                    <button type="button" class="btn btn-outline-info dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                    <span class="sr-only">Menu déroulant</span>
                </button>
                <div class="dropdown-menu" role="menu">
                    <?php  if ( SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) or SessionUser::getUser()->isCSVExportEnabled() ) {
                    ?>
                        <a class="dropdown-item exportCheckOutCSV" data-groupid="<?= $iGroupId ?>" data-typedesc="<?= _("Export Attendance") ?>"><i class="fas fa-file-excel fas-green"></i> <?= _("Export Attendance") ?></a>
                    <?php } ?>
                    <?php  if ( SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) or SessionUser::getUser()->isCSVExportEnabled() ) { ?>
                        <a class="dropdown-item exportCheckOutPDF" data-groupid="<?= $iGroupId ?>" data-typedesc="<?= _("Export Attendance") ?>"><i class="fas fa-file-pdf fas-red"></i> <?= _("Export Attendance") ?></a>
                        <a class="dropdown-item studentbadge" data-groupid="<?= $iGroupId ?>" data-typedesc="<?= _("Student Badges") ?>"><i class="fas fa-id-badge fas-purple"></i> <?= _("Student Badges") ?></a>
                        <a class="dropdown-item PhotoBook" data-groupid="<?= $iGroupId ?>" data-typedesc="<?= _("PhotoBook") ?>"><i class="fas fa-file-pdf fas-red"></i>  <?= _("PhotoBook") ?></a>
                    <?php } ?>
                </div>
            </div>  
            
            <?php
        }
        ?>
        <?php
        if (Cart::StudentInCart($iGroupId) and SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-sm btn-outline-secondary RemoveStudentsFromGroupCart" id="AddStudentsToGroupCart"
               data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fas fa-times"></i> <span
                    class="cartActionDescription"><?= _("Remove Students from Cart") ?></span></a>
            <?php
        } else if (SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-sm btn-outline-secondary AddStudentsToGroupCart disabled" id="AddStudentsToGroupCart"
               data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fas fa-cart-plus"></i> <span
                    class="cartActionDescription"><?= _("Add Students to Cart") ?></span></a>
            <?php
        }
        ?>
        <?php
        if (Cart::TeacherInCart($iGroupId) and SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-sm btn-outline-secondary RemoveFromTeacherGroupCart" id="AddToTeacherGroupCart"
               data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fas fa-times"></i> <span
                    class="cartActionDescription"><?= _("Remove Teachers from Cart") ?></span></a>
            <?php
        } else if (SessionUser::getUser()->isShowCartEnabled()) {
            ?>
            <a class="btn btn-sm btn-outline-secondary AddToTeacherGroupCart disabled" id="AddToTeacherGroupCart"
               data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fas fa-cart-plus"></i> <span
                    class="cartActionDescription"><?= _("Add Teachers to Cart") ?></span></a>
            <?php
        }

        ?>
        </div>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm teachers">
    <div class="card-header py-2 border-1">
        <h3 class="card-title mb-0"><?= _('Teachers') ?></h3>

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
        <div class="card-footer py-3">
            <div class="row">
                <div class="col-md-2">
                    <label for="personSearchTeachers" class="mb-0 mt-1"><strong><?= _("Add") ?></strong></label>
                </div>
                <div class="col-md-10">
                    <select class="form-control form-control-sm select2" name="addGroupMember" id="personSearchTeachers"
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

    <div class="card card-outline card-info shadow-sm quick-status">
        <div class="card-header py-2 border-1">
            <h3 class="card-title mb-0"><?= _('Quick Status') ?></h3>

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
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header py-2 border-1">
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
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header py-2 border-1">
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

<div class="card card-outline card-success shadow-sm">
    <div class="card-header py-2 border-1 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
        <h4 class="card-title mb-0"><?= _('Students') ?></h4>
        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
        <?php if (SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)
                or SessionUser::getUser()->isGroupManagerEnabledForId($iGroupId)
                or SessionUser::getUser()->isManageGroups()) { ?>
            <button class="btn btn-sm btn-outline-danger" id="remove_all_members"><i class="fas fa-trash-alt mr-1"></i> <?= _("Remove members") ?></button>
        <?php } ?>
        
            <label><?= _("Edition Mode") ?> <input data-width="100"
                                                   data-size="mini" id="editionMode" type="checkbox"
                                                   data-toggle="toggle" data-on="<?= _("On") ?>"
                                                   data-off="<?= _("Off") ?>">
            </label>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="card-body p-2">
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
                <table id="sundayschoolTable" class="table table-striped table-hover table-sm data-table" cellspacing="0"
               width="100%"></table>
            </div>
        </div>
    </div>


<?php
if (SessionUser::getUser()->isAddRecords() 
    or SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)
    or SessionUser::getUser()->isGroupManagerEnabledForId($iGroupId)
    or SessionUser::getUser()->isManageGroups() ) {
    ?>
        <div class="card-footer py-3">
            <div class="row">
                <div class="col-md-2">
                    <label for="personSearch" class="mb-0 mt-1"><strong><?= _("Add Student") ?></strong></label>
                </div>
                <div class="col-md-10">
                    <select class="form-control form-control-sm personSearch select2" name="addGroupMember" id="personSearch"
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
    var iFYID = <?= MiscUtils::CurrentFY() ?>;
    var canSeePrivacyData = <?= (SessionUser::getUser()->isSeePrivacyDataEnabled() or SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId)) ? 1 : 0 ?>;
    var canDeleteMembers = <?= SessionUser::getUser()->isDeleteRecordsEnabled() ? 1 : 0 ?>;
    var sundayGroupName = "<?= $iGroupName ?>";

    window.CRM.currentGroup = <?= $iGroupId ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/sundayschool/SundaySchoolClassView.js"></script>
<script src="<?= $sRootPath ?>/skin/js/groupcommon/group_sundaygroup.js"></script>

<?php
require $sRootDocument . '/Include/Footer.php';
?>


