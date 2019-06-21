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


require $sRootDocument . '/Include/Header.php';

?>

<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-gg"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Classes') ?></span>
                <span class="info-box-number"> <?= $classes ?> <br/></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-olive"><i class="fa fa-group"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Teachers') ?></span>
                <span class="info-box-number"> <?= $teachers ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-orange"><i class="fa fa-child"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= _('Students') ?></span>
                <span class="info-box-number"> <?= $kids ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-gray"><i class="fa fa-user"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Families') ?></span>
                <span class="info-box-number"> <?= count(array_unique($familyIds)) ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-male"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Boys') ?></span>
                <span class="info-box-number"> <?= $maleKids ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-fuchsia"><i class="fa fa-female"></i></span>

            <div class="info-box-content">
                <span class="info-box-text"><?= _('Girls') ?></span>
                <span class="info-box-number"> <?= $femaleKids ?></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
</div><!-- /.row -->
<!-- Small boxes (Stat box) -->
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= _('Functions') ?></h3>
    </div>
    <div class="box-body">
        <?php if (SessionUser::getUser()->isManageGroupsEnabled()) {
            ?>
            <button class="btn btn-app" data-toggle="modal" data-target="#add-class"><i
                    class="fa fa-plus-square"></i><?= _('Add New Class') ?></button>
            <?php
        }
        ?>
        <?php
        if (SessionUser::getUser()->isExportSundaySchoolPDFEnabled() || SessionUser::getUser()->isAdmin()) {
            ?>
            <a href="<?= $sRootPath ?>/sundayschool/SundaySchoolReports.php" class="btn btn-app bg-red"
               title="<?= _('Generate class lists and attendance sheets'); ?>"><i
                    class="fa fa-file-pdf-o"></i><?= _('Reports'); ?></a>
            <?php
        }
        ?>
        <?php
        if (SessionUser::getUser()->isCSVExportEnabled() || SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) {
            ?>
            <a href="<?= $sRootPath ?>/sundayschool/SundaySchoolClassListExport.php" class="btn btn-app bg-green"
               title="<?= _('Export All Classes, Kids, and Parent to CSV file'); ?>"><i
                    class="fa fa-file-excel-o"></i><?= _('Export to CSV') ?></a><br/>
            <?php
        }
        ?>
    </div>
</div>
<!-- on continue -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><?= _('Sunday School Classes') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
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
                        <a href="<?= $sRootPath ?>/v2/sundayschool/<?= $class['id'] ?>/view">
            <span class="fa-stack">
              <i class="fa fa-square fa-stack-2x"></i>
              <i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
            </span>
                        </a>
                        <a href="<?= $sRootPath ?>/GroupEditor.php?GroupID=<?= $class['id'] ?>">
            <span class="fa-stack">
              <i class="fa fa-square fa-stack-2x"></i>
              <i class="fa fa fa-pencil fa-stack-1x fa-inverse"></i>
            </span>
                        </a>
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


<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title"><?= _('Students not in a Sunday School Class') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive">
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
                        <a href="<?= $sRootPath ?>/PersonView.php?PersonID=<?= $kidId ?>">
                <span class="fa-stack">
                  <i class="fa fa-square fa-stack-2x"></i>
                  <i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
                </span>
                        </a>
                    </td>
                    <td><?= $firstName ?></td>
                    <td><?= $LastName ?></td>
                    <?php
                    if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                        ?>
                        <td><?= $birthDate ?></td>
                        <td data-birth-date="<?= ($hideAge ? '' : $birthDateDate->format('Y-m-d')) ?>"></td>
                        <td><?= $Address1.' '.$Address2.' '.$city.' '.$state.' '.$zip ?></td>
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
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"
                        id="delete-Image-label"><?= _("Add Sunday School Class") ?> </h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" id="new-class-name" class="form-control" placeholder="<?= _('Enter Name') ?>"
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
        $(document).ready(function () {
            $('.data-table').DataTable({"language": window.CRM.plugin.dataTable.language,responsive: true});

            $("#addNewClassBtn").click(function (e) {
                var groupName = $("#new-class-name").val(); // get the name of the from the textbox
                if (groupName) // ensure that the user entered a name
                {
                    $.ajax({
                        method: "POST",
                        dataType: "json",
                        contentType: "application/json; charset=utf-8",
                        url: window.CRM.root + "/api/groups/",
                        data: JSON.stringify({
                            'groupName': groupName,
                            'isSundaySchool': true
                        })
                    }).done(function (data) {                               //yippie, we got something good back from the server
                        window.location.href = window.CRM.root + "/v2/sundayschool/" + data.Id + "/view";
                    });
                }
                else {

                }
            });

        });
    </script>

    <?php
} else {
    ?>
    <script nonce="<?= $CSPNonce ?>">
        $(document).ready(function () {
            $('.data-table').DataTable({"language": window.CRM.plugin.dataTable.language,responsive: true});
        });
    </script>
    <?php
}
?>

<?php
require $sRootDocument . '/Include/Footer.php';
?>

