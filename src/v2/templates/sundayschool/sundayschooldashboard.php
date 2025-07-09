<?php

/*******************************************************************************
 *
 *  filename    : sundayschooldashboard.php
 *  last change : 2019-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\Cart;


require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow-gradient"><i class="fab fa-gg"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Classes') ?></span>
                <span class="info-box-number" id="sundaySchoolClassesDasBoard"> <?= $sundaySchoolCountStats['sundaySchoolClasses'] ?> <br /></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-gradient-olive"><i class="fas fa-users"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Teachers') ?></span>
                <span class="info-box-number" id="sundaySchoolTeachersCNTDasBoard"> <?= $sundaySchoolCountStats['teachersCNT'] ?> </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-gradient-orange"><i class="fas fa-child"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= _('Students') ?></span>
                <span class="info-box-number" id="sundaySchoolKidsCNTDasBoard"> <?= $sundaySchoolCountStats['kidsCNT'] ?> </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-gradient-lime"><small><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i></small></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Families') ?></span>
                <span class="info-box-number" id="sundaySchoolFamiliesCNTDasBoard"> <?= $sundaySchoolCountStats['SundaySchoolFamiliesCNT'] ?> </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-gradient-blue"><i class="fas fa-male"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Boys') ?></span>
                <span class="info-box-number" id="sundaySchoolMaleKidsCNTDasBoard"> <?= $sundaySchoolCountStats['maleKidsCNT'] ?> </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-gradient-fuchsia"><i class="fas fa-female"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Girls') ?></span>
                <span class="info-box-number" id="sundaySchoolFemaleKidsCNTDasBoard"> <?= $sundaySchoolCountStats['femaleKidsCNT'] ?> </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
</div><!-- /.row -->
<!-- Small boxes (Stat box) -->
<div class="card">
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('Functions') ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
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

                if (SessionUser::getUser()->isManageGroupsEnabled()) {
                ?>
                    <button class="btn btn-app" data-toggle="modal" data-target="#add-class"><i
                            class="fas fa-plus-square"></i><?= _('Add New Class') ?></button>
                <?php
                }

                if (
                    SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
                    || SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) || SessionUser::getUser()->isMenuOptionsEnabled()
                ) {
                ?>
                    <a class="btn btn-app bg-orange callRegister" id="callRegister"
                        data-callRegistergroupid="<?= $iGroupId ?>" data-callRegistergroupname="<?= $iGroupName ?>"
                        data-toggle="tooltip" data-placement="bottom" title="<?= _("Be Careful! You are about to create or recreate all the events of all the Sunday school classes to call the register.") ?>"> <i
                            class="fas fa-calendar-check"></i> <span
                            class="cartActionDescription"><?= _('Create Events & Call the register') ?></span></a>
                <?php
                }

                if (SessionUser::getUser()->isExportSundaySchoolPDFEnabled() || SessionUser::getUser()->isAdmin()) {
                ?>
                    <a href="<?= $sRootPath ?>/v2/sundayschool/reports" class="btn btn-app bg-red"
                        title="<?= _('Generate class lists and attendance sheets'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= _("To export your attendance, Photobooks, Attendance sheet, and Class list") ?>"><i
                            class="fas fa-file-pdf"></i><?= _('Reports'); ?></a>
                <?php
                }

                if (SessionUser::getUser()->isCSVExportEnabled() || SessionUser::getUser()->isExportSundaySchoolPDFEnabled()) {
                ?>
                    <a href="<?= $sRootPath ?>/sundayschool/SundaySchoolClassListExport.php" class="btn btn-app bg-green"
                        title="<?= _('Export All Classes, Kids, and Parent to CSV file'); ?>"><i
                            class="fas fa-file-excel"></i><?= _('Export to CSV') ?></a>
                <?php
                }
                ?>
                <?php
                if (Cart::GeneralStudentInCart() && SessionUser::getUser()->isShowCartEnabled()) {
                ?>
                    <a class="btn btn-app RemoveAllStudentsFromCart" id="AddAllStudentsToCart"> <i class="fas fa-times"></i> <span
                            class="cartActionDescription"><?= _("Remove Students from Cart") ?></span></a>
                <?php
                } else if (SessionUser::getUser()->isShowCartEnabled()) {
                ?>
                    <a class="btn btn-app AddAllStudentsToCart" id="AddAllStudentsToCart"><i class="fas fa-cart-plus"></i> <span
                            class="cartActionDescription"><?= _("Add Students to Cart") ?></span></a>
                <?php
                }
                ?>
                <?php
                if (Cart::GeneralTeacherInCart() && SessionUser::getUser()->isShowCartEnabled()) {
                ?>
                    <a class="btn btn-app RemoveAllTeachersFromCart" id="AddAllTeachersToCart"><i class="fas fa-times"></i> <span
                            class="cartActionDescription"><?= _("Remove Teachers from Cart") ?></span></a>
                <?php
                } else if (SessionUser::getUser()->isShowCartEnabled()) {
                ?>
                    <a class="btn btn-app AddAllTeachersToCart" id="AddAllTeachersToCart"><i class="fas fa-cart-plus"></i> <span
                            class="cartActionDescription"><?= _("Add Teachers to Cart") ?></span></a>
                <?php
                }

                ?>
            </div>
        </div>
    </div>
</div>
<!-- on continue -->
<div class="card card-info">
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('Sunday School Classes') ?></h3>
        <div class="card-tools pull-right">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <table id="sundayschoolMissing" class="table table-striped table-bordered data-table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th></th>
                    <th><?= _('Class') ?></th>
                    <th><?= _('Teachers') ?></th>
                    <th><?= _('Students') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classStats as $class) {
                ?>
                    <tr>
                        <td style="width:80px">
                            <div class="btn-group" role="group" aria-label="Basic example" >                       
                                <a href="<?= $sRootPath ?>/v2/sundayschool/<?= $class['id'] ?>/view" class="btn btn-default btn-xs">
                                    <i class="fas fa-search-plus fas-blue"></i>
                                </a>
                                <a href="<?= $sRootPath ?>/v2/group/editor/<?= $class['id'] ?>" class="btn btn-default btn-xs">
                                        <i class="fa fas fa-pencil-alt fas-blue"></i>
                                </a>
                            </div>
                        </td>
                        <td><?= $class['name'] ?></td>
                        <td><?= $class['teachers'] ?></td>
                        <td><?= $class['kids'] ?></td>
                    </tr>
                <?php
                } ?>
            </tbody>
        </table>
    </div>
</div>


<div class="card card-danger">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Students not in a Sunday School Class') ?></h3>
        <div class="card-tools pull-right">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="card-body table-responsive">
        <table id="sundayschoolMissing" class="table table-striped table-bordered data-table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th></th>
                    <th><?= _('First Name') ?></th>
                    <th><?= _('Last Name') ?></th>
                    <th><?= _('Birth Date') ?></th>
                    <th><?= _('Age') ?></th>
                    <th><?= _('Home Address') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($kidsWithoutClasses as $child) {
                    extract($child);

                    $hideAge = $flags == 1 || $birthYear == '' || $birthYear == '0';
                    $birthDate = OutputUtils::FormatBirthDate($birthYear, $birthMonth, $birthDay, '-', $flags);
                    $birthDateDate = OutputUtils::BirthDate($birthYear, $birthMonth, $birthDay, $hideAge);
                ?>

                    <tr>
                        <td>
                            <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $kidId ?>" class="btn btn-default btn-xs">
                                <i class="fas fa-search-plus fas-blue"></i>
                            </a>
                        </td>
                        <td><?= $firstName ?></td>
                        <td><?= $LastName ?></td>
                        <?php
                        if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                        ?>
                            <td><?= $birthDate ?></td>
                            <td><?= OutputUtils::FormatAgeFromDate($birthDateDate->format('Y-m-d')) ?></td>
                            <td><?= $Address1 . ' ' . $Address2 . ' ' . $city . ' ' . $state . ' ' . $zip ?></td>
                        <?php
                        } else {
                        ?>
                            <td><?= _("Private Data") ?></td>
                            <td><?= _("Private Data") ?></td>
                            <td><?= _("Private Data") ?></td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (SessionUser::getUser()->isManageGroupsEnabled()) {
?>
    <div class="modal fade" id="add-class" tabindex="-1" role="dialog" aria-labelledby="add-class-label"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title"
                        id="delete-Image-label"><?= _("Add Sunday School Class") ?> </h4>
                    <button type="button" class="close flush-right" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" id="new-class-name" class="form-control form-control-sm" placeholder="<?= _('Enter Name') ?>"
                            maxlength="20" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _('Cancel') ?></button>
                    <button type="button" id="addNewClassBtn" class="btn btn-primary"
                        data-dismiss="modal"><?= _('Add') ?></button>
                </div>
            </div>
        </div>
    </div>
    <script nonce="<?= $CSPNonce ?>">
        $(function() {
            $('.data-table').DataTable({
                "language": window.CRM.plugin.dataTable.language,
                responsive: true
            });

            $("#addNewClassBtn").on('click', function(e) {
                var groupName = $("#new-class-name").val(); // get the name of the from the textbox
                if (groupName) // ensure that the user entered a name
                {
                    window.CRM.APIRequest({
                        method: "POST",
                        path: "groups/",
                        data: JSON.stringify({
                            'groupName': groupName,
                            'isSundaySchool': true
                        })
                    }, function(data) { //yippie, we got something good back from the server
                        window.location.href = window.CRM.root + "/v2/sundayschool/" + data.Id + "/view";
                    });
                } else {

                }
            });

        });
    </script>

<?php
} else {
?>
    <script nonce="<?= $CSPNonce ?>">
        $(function() {
            $('.data-table').DataTable({
                "language": window.CRM.plugin.dataTable.language,
                responsive: true
            });
        });
    </script>
<?php
}
?>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>

<script src="<?= $sRootPath ?>/skin/js/sundayschool/SundaySchoolDashboard.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>