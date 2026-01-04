<?php

/*******************************************************************************
 *
 *  filename    : sundayschoolreports.php
 *  last change : 2019-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;

require $sRootDocument . '/Include/Header.php';
?>

<form method="post" action="<?= $sRootPath ?>/v2/sundayschool/reports">

    <?php
    if (SessionUser::getUser()->isExportSundaySchoolPDFEnabled()) {
    ?>
        <button type="submit" class="btn btn-app bg-blue" name="SubmitClassList">
            <i class="fas fa-list"></i> <?= _('Class List') ?>
        </button>
        &nbsp;
        <button type="submit" class="btn btn-app bg-info" name="SubmitClassAttendance">
            <i class="fas fa-sheet-plastic"></i> <?= _('Attendance Sheet') ?>
        </button>
        &nbsp;
        <button type="button" class="btn btn-app bg-info exportCheckOutPDF" id="exportCheckOutPDF" data-makecheckoutgroupid="0">
            <i class="fas fa-sheet-plastic"></i> <?= _('Real Attendance Sheet') ?>
        </button>

        &nbsp;

        <button type="submit" class="btn btn-app bg-red" name="SubmitPhotoBook">
            <i class="fas fa-book"></i> <?= _('PhotoBook') ?>
        </button>

        &nbsp;&nbsp;&nbsp;
    <?php
    }
    ?>

    <br>

    <a href="<?= $sRootPath ?>/v2/sundayschool/dashboard" class="btn btn-default"><i class="fas fa-chevron-left"></i> <?= _("Return to sunday school") ?> </a>

    <br>
    <br>

    <div class="card card-secondary">
        <div class="card-header border-1">
            <h3 class="card-title"><i class="far fa-file-pdf"></i> <?= _('Report Details') ?></h3>
        </div>
        <div class="card-body">
            <table class="table table-simple-padding" width="100%">
                <tr>
                    <td><?= _('Select Group') ?>: <br /><?= _('To select multiple hold CTL') ?></td>
                    <td>
                        <!-- Create the group select drop-down -->
                        <select id="GroupID" class="form-control" name="GroupID[]" multiple size="8" style="width:100%"
                            data-toggle="tooltip" data-placement="bottom" title="<?= _("Select one or more groups") ?>">
                            <option value="0"><?= _('None') ?></option>
                            <?php
                            foreach ($groups as $group) {
                            ?>
                                <option value="<?= $group->getID() ?>"><?= $group->getName() ?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <br>
                        <?= _('Multiple groups will have a Page Break between Groups<br>') ?>
                        <input type="checkbox" Name="allroles" value="1" checked>
                        <?= _('List all Roles (unchecked will list Teacher/Student roles only)') ?><br>
                        <input type="checkbox" Name="withPictures" value="1" checked>
                        <?= _('With Photos') ?>
                    </td>
                </tr>

                <tr>
                    <td><?= _('Fiscal Year') ?>:</td>
                    <td class="TextColumnWithBottomBorder">
                        <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
                    </td>
                </tr>

                <tr>
                    <td><?= _('First Sunday') ?>:</td>
                    <td><input type="text" name="FirstSunday" value="<?= $dFirstSunday ?>" maxlength="10" id="FirstSunday" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('Last Sunday') ?>:</td>
                    <td><input type="text" name="LastSunday" value="<?= $dLastSunday ?>" maxlength="10" id="LastSunday" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool1" value="<?= $dNoSchool1 ?>" maxlength="10" id="NoSchool1" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool2" value="<?= $dNoSchool2 ?>" maxlength="10" id="NoSchool2" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool3" value="<?= $dNoSchool3 ?>" maxlength="10" id="NoSchool3" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool4" value="<?= $dNoSchool4 ?>" maxlength="10" id="NoSchool4" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool5" value="<?= $dNoSchool5 ?>" maxlength="10" id="NoSchool5" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool6" value="<?= $dNoSchool6 ?>" maxlength="10" id="NoSchool6" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool7" value="<?= $dNoSchool7 ?>" maxlength="10" id="NoSchool7" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool8" value="<?= $dNoSchool8 ?>" maxlength="10" id="NoSchool8" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('Extra Students') ?>:</td>
                    <td><input type="text" name="ExtraStudents" class="form-control form-control-sm" value="<?= $iExtraStudents ?>" id="ExtraStudents" size="11">&nbsp;</td>
                </tr>
                <tr>
                    <td><?= _('Extra Teachers') ?>:</td>
                    <td><input type="text" name="ExtraTeachers" class="form-control form-control-sm" value="<?= $iExtraTeachers ?>" id="ExtraTeachers" size="11">&nbsp;</td>
                </tr>
            </table>
</form>
</div>
</div>

<?php
require $sRootDocument . '/Include/Footer.php';
?>


<?php
if (SessionUser::getUser()->isExportSundaySchoolPDFEnabled()) {
?>
    <script src="<?= $sRootPath ?>/skin/js/sundayschool/SundaySchoolReports.js"></script>
<?php
}
?>