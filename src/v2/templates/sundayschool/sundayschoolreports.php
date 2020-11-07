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

<?php
  if (!empty ($message)) {
?>
      <?= $message ?>
<?php
  }
?>

<div class="card card-secondary">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Report Details')?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $sRootPath ?>/v2/sundayschool/reports">

            <table class="table table-simple-padding" align="left">
                <tr>
                    <td><?= _('Select Group')?>: <br/><?=_('To select multiple hold CTL') ?></td>
                    <td>
                        <!-- Create the group select drop-down -->
                        <select id="GroupID" name="GroupID[]" multiple size="8" style="width:100%"
                                data-toggle="tooltip"  data-placement="bottom" title="<?= _("Select one or more groups") ?>">
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
                    <td><input type="text" name="FirstSunday" value="<?= $dFirstSunday ?>" maxlength="10" id="FirstSunday" size="11"  class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('Last Sunday') ?>:</td>
                    <td><input type="text" name="LastSunday" value="<?= $dLastSunday ?>" maxlength="10" id="LastSunday" size="11"  class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool1" value="<?= $dNoSchool1 ?>" maxlength="10" id="NoSchool1" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool2" value="<?= $dNoSchool2 ?>" maxlength="10" id="NoSchool2" size="11"  class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool3" value="<?= $dNoSchool3 ?>" maxlength="10" id="NoSchool3" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool4" value="<?= $dNoSchool4 ?>" maxlength="10" id="NoSchool4" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool5" value="<?= $dNoSchool5 ?>" maxlength="10" id="NoSchool5" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool6" value="<?= $dNoSchool6 ?>" maxlength="10" id="NoSchool6" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool7" value="<?= $dNoSchool7 ?>" maxlength="10" id="NoSchool7" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('No Sunday School') ?>:</td>
                    <td><input type="text" name="NoSchool8" value="<?= $dNoSchool8 ?>" maxlength="10" id="NoSchool8" size="11" class="date-picker form-control" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"></td>
                </tr>

                <tr>
                    <td><?= _('Extra Students') ?>:</td>
                    <td><input type="text" name="ExtraStudents" class="form-control" value="<?= $iExtraStudents ?>" id="ExtraStudents" size="11">&nbsp;</td>
                </tr>
                <tr>
                    <td><?= _('Extra Teachers') ?>:</td>
                    <td><input type="text" name="ExtraTeachers" class="form-control" value="<?= $iExtraTeachers ?>" id="ExtraTeachers" size="11">&nbsp;</td>
                </tr>
                <tr>
                    <td><br/></td>
                </tr>
                <tr>
                    <td width="75%">
                        <?php
                        if ( SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) {
                            ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="submit" class="btn btn-primary" name="SubmitClassList" value="<?= _('Class List') ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="submit" class="btn btn-info" name="SubmitClassAttendance" value="<?= _('Attendance Sheet') ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info exportCheckOutPDF" id="exportCheckOutPDF" data-makecheckoutgroupid="0"><?= _('Real Attendance Sheet') ?></button>
                                </div>
                                <div class="col-md-3">
                                    <input type="submit" class="btn btn-danger" name="SubmitPhotoBook" value="<?= _('PhotoBook') ?>">
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </td>
                    <td width="25%">
                        <div class="col-rd-12">
                            <input type="button" style="align=right" class="btn btn-default" name="Cancel" value="<?= _('Cancel') ?>" onclick="javascript:document.location = '<?= $sRootPath ?>/v2/dashboard';">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php
require $sRootDocument . '/Include/Footer.php';
?>


<?php
if ( SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) {
    ?>
    <script src="<?= $sRootPath ?>/skin/js/sundayschool/SundaySchoolReports.js" ></script>
    <?php
}
?>
