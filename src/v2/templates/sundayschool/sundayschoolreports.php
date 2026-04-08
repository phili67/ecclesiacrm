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

    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header py-2">
            <h3 class="card-title mb-0"><i class="fas fa-file-export mr-1"></i><?= _('Reports Actions') ?></h3>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                <?php
                if (SessionUser::getUser()->isExportSundaySchoolPDFEnabled()) {
                ?>
                    <button type="submit" class="btn btn-sm btn-outline-primary" name="SubmitClassList">
                        <i class="fas fa-list mr-1"></i> <?= _('Class List') ?>
                    </button>
                    <button type="submit" class="btn btn-sm btn-outline-info" name="SubmitClassAttendance">
                        <i class="fas fa-sheet-plastic mr-1"></i> <?= _('Attendance Sheet') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-info exportCheckOutPDF" id="exportCheckOutPDF" data-makecheckoutgroupid="0">
                        <i class="fas fa-clipboard-check mr-1"></i> <?= _('Real Attendance Sheet') ?>
                    </button>
                    <button type="submit" class="btn btn-sm btn-outline-danger" name="SubmitPhotoBook">
                        <i class="fas fa-book mr-1"></i> <?= _('PhotoBook') ?>
                    </button>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="<?= $sRootPath ?>/v2/sundayschool/dashboard" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> <?= _("Return to sunday school") ?> </a>
    </div>

    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header py-2 border-1">
            <h3 class="card-title mb-0"><i class="far fa-file-pdf mr-1"></i> <?= _('Report Details') ?></h3>
        </div>
        <div class="card-body">
            <table class="table table-sm table-borderless" width="100%">
                <tr>
                    <td class="align-middle" style="width:35%;"><strong><?= _('Select Group') ?>:</strong><br /><?= _('To select multiple hold CTL') ?></td>
                    <td>
                        <!-- Create the group select drop-down -->
                        <select id="GroupID" class="form-control form-control-sm" name="GroupID[]" multiple size="8" style="width:100%"
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
                        <div class="mt-2 text-muted"><?= _('Multiple groups will have a Page Break between Groups') ?></div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="allroles" Name="allroles" value="1" checked>
                            <label class="custom-control-label" for="allroles"><?= _('List all Roles (unchecked will list Teacher/Student roles only)') ?></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="withPicturesForm" Name="withPictures" value="1" checked>
                            <label class="custom-control-label" for="withPicturesForm"><?= _('With Photos') ?></label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('Fiscal Year') ?>:</strong></td>
                    <td>
                        <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
                    </td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('First Sunday') ?>:</strong></td>
                    <td><input type="text" name="FirstSunday" value="<?= $dFirstSunday ?>" maxlength="10" id="FirstSunday" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('Last Sunday') ?>:</strong></td>
                    <td><input type="text" name="LastSunday" value="<?= $dLastSunday ?>" maxlength="10" id="LastSunday" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 1:</strong></td>
                    <td><input type="text" name="NoSchool1" value="<?= $dNoSchool1 ?>" maxlength="10" id="NoSchool1" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 2:</strong></td>
                    <td><input type="text" name="NoSchool2" value="<?= $dNoSchool2 ?>" maxlength="10" id="NoSchool2" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 3:</strong></td>
                    <td><input type="text" name="NoSchool3" value="<?= $dNoSchool3 ?>" maxlength="10" id="NoSchool3" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 4:</strong></td>
                    <td><input type="text" name="NoSchool4" value="<?= $dNoSchool4 ?>" maxlength="10" id="NoSchool4" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 5:</strong></td>
                    <td><input type="text" name="NoSchool5" value="<?= $dNoSchool5 ?>" maxlength="10" id="NoSchool5" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 6:</strong></td>
                    <td><input type="text" name="NoSchool6" value="<?= $dNoSchool6 ?>" maxlength="10" id="NoSchool6" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 7:</strong></td>
                    <td><input type="text" name="NoSchool7" value="<?= $dNoSchool7 ?>" maxlength="10" id="NoSchool7" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('No Sunday School') ?> 8:</strong></td>
                    <td><input type="text" name="NoSchool8" value="<?= $dNoSchool8 ?>" maxlength="10" id="NoSchool8" size="11" class="date-picker form-control form-control-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td class="align-middle"><strong><?= _('Extra Students') ?>:</strong></td>
                    <td><input type="text" name="ExtraStudents" class="form-control form-control-sm" value="<?= $iExtraStudents ?>" id="ExtraStudents" size="11">&nbsp;</td>
                </tr>
                <tr>
                    <td class="align-middle"><strong><?= _('Extra Teachers') ?>:</strong></td>
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