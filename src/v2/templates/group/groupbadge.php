<?php

/*******************************************************************************
 *
 *  filename    : groupbadge.php
 *  last change : 2020-06-19
 *  description : form to invoke group reports
 *
 *  Copyright : Philippe Logel all rights reserved
 *
 ******************************************************************************/

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Utils\LabelUtils;

require $sRootDocument . '/Include/Header.php';
?>

<div class="alert alert-info mb-3"><i class="fas fa-info-circle mr-1"></i><?= _("When you add some properties to a person they will be add to the badge.") ?></div>
<?php
if (count($_SESSION['aPeopleCart']) == 0) {
    $useCart = 0;
}

if ($useCart == 1) {
    $allPersons = "";

    foreach ($_SESSION['aPeopleCart'] as $personId) {
        $person = PersonQuery::Create()->findOneById($personId);

        $allPersons .= $person->getFullName() . ",";
    }
?>

    <div class="alert alert-warning mb-3"><i class="fas fa-exclamation-triangle mr-1"></i><?= _("You're about to create babges only for this people") . " : <b>" . $allPersons . "</b> " . _("who are in the cart. If you don't want to do this, empty the cart, and reload the page.") ?></div>
<?php
}

if (isset($_GET['typeProblem'])) {
?>

    <div class="alert alert-danger mb-3">
        <i class="fas fa-ban"></i>
        <?= _("Only PNG and JPEG files are managed actually !!") ?>
    </div>

<?php
}

?>

<div class="card card-primary card-outline">
    <div class="card-header border-1 d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-id-badge mr-1"></i><?= _('Generate Group Badges') ?></h3>
        <span class="badge badge-secondary mt-2 mt-sm-0"><?= $group->getName() ?></span>
    </div>

    <form method="post" action="<?= $sRootPath ?>/Reports/PDFBadgeGroup.php" name="labelform" enctype="multipart/form-data">
        <input id="groupId" name="groupId" type="hidden" value="<?= $iGroupID ?>">
        <input id="useCart" name="useCart" type="hidden" value="<?= $useCart ?>">

        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="fas fa-circle-info mr-1"></i>
                <?= _('Configure colors, image and informations, then generate your group badges.') ?>
            </div>

            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="small text-muted mb-1"><?= _('Design flow') ?></div>
                            <span class="badge badge-primary mr-1"><?= _('1. Colors') ?></span>
                            <span class="badge badge-info mr-1"><?= _('2. Image') ?></span>
                            <span class="badge badge-warning mr-1"><?= _('3. Informations') ?></span>
                            <span class="badge badge-success"><?= _('4. Export') ?></span>
                        </div>
                        <div class="col-md-4 text-md-right mt-2 mt-md-0">
                            <button type="button" id="resetGroupBadgeDesigner" class="btn btn-outline-secondary btn-xs">
                                <i class="fas fa-undo mr-1"></i><?= _('Reset Designer') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h4 class="card-title mb-0"><span class="badge badge-primary mr-1">1</span><i class="fas fa-palette mr-1"></i><?= _('Colors') ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"><?= $group->getName() ?></label>
                                <div class="col-md-5">
                                    <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                                        <input id="titleColorInput" type="text" name="title-color" class="form-control form-control-sm" value="<?= (empty($_COOKIE['sTitleColorSC'])) ? '#3A3' : $_COOKIE['sTitleColorSC'] ?>">
                                        <div class="input-group-addon input-group-text">
                                            <i style="background-color: <?= (empty($_COOKIE['sTitleColorSC'])) ? '#3A3' : $_COOKIE['sTitleColorSC'] ?>;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 mt-2 mt-md-0">
                                    <select name="titlePosition" class="form-control form-control-sm" id="titlePosition">
                                        <option value="Left" <?= ($_COOKIE['titlePositionSC'] == 'Left') ? 'selected' : '' ?>><?= _('Left') ?></option>
                                        <option value="Center" <?= ($_COOKIE['titlePositionSC'] == 'Center') ? 'selected' : '' ?>><?= _('Center') ?></option>
                                        <option value="Right" <?= ($_COOKIE['titlePositionSC'] == 'Right') ? 'selected' : '' ?>><?= _('Right') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-2 mt-2 mt-md-0">
                                    <?php LabelUtils::FontSizeSelect('Titlelabelfontsize', '(' . _('default') . ' 15)', false); ?>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <label class="col-md-3 col-form-label"><?= _('BackGround color') ?></label>
                                <div class="col-md-5">
                                    <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                                        <input id="backgroundColorInput" type="text" name="backgroud-color" class="form-control form-control-sm" value="<?= (empty($_COOKIE['sBackgroudColorSC'])) ? '#F99' : $_COOKIE['sBackgroudColorSC'] ?>">
                                        <div class="input-group-addon input-group-text">
                                            <i style="background-color: <?= (empty($_COOKIE['sBackgroudColorSC'])) ? '#F99' : $_COOKIE['sBackgroudColorSC'] ?>;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h4 class="card-title mb-0"><span class="badge badge-info mr-1">2</span><i class="fa-solid fa-image mr-1"></i><?= _('Image') ?></h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $image = (empty($_COOKIE['imageSC'])) ? 'scleft1.png' : $_COOKIE['imageSC'];
                            ?>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="image"><?= _('Name') ?></label>
                                <div class="col-md-9">
                                    <input type="text" name="image" id="image" maxlength="255" value="<?= $image ?>" class="form-control form-control-sm" placeholder="<?= _('Image Name') ?>">
                                    <small class="text-muted"><?= _('Click a file name below to auto-fill this field.') ?></small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"><?= _('Available images') ?></label>
                                <div class="col-md-9">
                                    <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto;">
                                        <?php
                                        if (count($imgs) > 0) {
                                            foreach ($imgs as $img) {
                                                $name = str_replace('../Images/background/', '', $img);
                                                echo '<div class="d-flex align-items-center justify-content-between py-1 border-bottom">';
                                                echo '<a class="add-file" data-name="' . $name . '"><i class="fas fa-file-image mr-1"></i>' . $name . '</a>';
                                                echo '<a class="delete-file text-danger" data-name="' . $name . '" title="' . _('Delete') . '"><i class="fa fa-times"></i></a>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">' . _('None') . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="stickerBadgeInputFile"><?= _('Upload') ?></label>
                                <div class="col-md-6">
                                    <input type="file" id="stickerBadgeInputFile" name="stickerBadgeInputFile" class="form-control-file">
                                </div>
                                <div class="col-md-3 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-success btn-sm btn-block" name="SubmitUpload">
                                        <i class="fas fa-upload mr-1"></i><?= _('Upload') ?>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <label class="col-md-3 col-form-label" for="imagePosition"><?= _('Image Position') ?></label>
                                <div class="col-md-9">
                                    <select name="imagePosition" class="form-control form-control-sm" id="imagePosition">
                                        <option value="Left" <?= ($_COOKIE['imagePositionSC'] == 'Left') ? 'selected' : '' ?>><?= _('Left') ?></option>
                                        <option value="Center" <?= ($_COOKIE['imagePositionSC'] == 'Center') ? 'selected' : '' ?>><?= _('Center') ?></option>
                                        <option value="Right" <?= ($_COOKIE['imagePositionSC'] == 'Right') ? 'selected' : '' ?>><?= _('Right') ?></option>
                                        <option value="Cover" <?= ($_COOKIE['imagePositionSC'] == 'Cover') ? 'selected' : '' ?>><?= _('Cover (Full)') ?></option>
                                    </select>
                                    <div class="btn-group btn-group-sm mt-2" role="group">
                                        <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Left"><?= _('Left') ?></button>
                                        <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Center"><?= _('Center') ?></button>
                                        <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Right"><?= _('Right') ?></button>
                                        <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Cover"><?= _('Cover') ?></button>
                                    </div>
                                </div>
                            </div>

                            <?php if ($isSundaySchool) { ?>
                                <hr>
                            <?php } ?>

                            <div class="form-group row mb-0">
                                <label class="col-md-3 col-form-label" for="useQRCode"><i class="fas fa-qrcode mr-1"></i><?= _('With QR Code') ?></label>
                                <div class="col-md-9 pt-2">
                                    <input type="checkbox" name="useQRCode" value="Yes" id="useQRCode" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h4 class="card-title mb-0"><span class="badge badge-warning mr-1">3</span><i class="fa-solid fa-info mr-1"></i><?= ($isSundaySchool) ? _('Sunday School Name') : _('Informations') ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group row mb-0">
                                <label class="col-md-3 col-form-label" for="sundaySchoolName"><?= ($isSundaySchool) ? _('Sunday School Name') : _('Informations') ?></label>
                                <div class="col-md-5">
                                    <input type="text" name="sundaySchoolName" id="sundaySchoolName" maxlength="255" value="<?= $_COOKIE['sundaySchoolNameSC'] ?>" class="form-control form-control-sm" placeholder="<?= ($isSundaySchool) ? _('Sunday School Name') : _('Informations') ?>">
                                </div>
                                <div class="col-md-2 mt-2 mt-md-0">
                                    <select name="sundaySchoolNamePosition" class="form-control form-control-sm" id="sundaySchoolNamePosition">
                                        <option value="Left" <?= ($_COOKIE['sundaySchoolNamePositionSC'] == 'Left') ? 'selected' : '' ?>><?= _('Left') ?></option>
                                        <option value="Center" <?= ($_COOKIE['sundaySchoolNamePositionSC'] == 'Center') ? 'selected' : '' ?>><?= _('Center') ?></option>
                                        <option value="Right" <?= ($_COOKIE['sundaySchoolNamePositionSC'] == 'Right') ? 'selected' : '' ?>><?= _('Right') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-2 mt-2 mt-md-0">
                                    <?php LabelUtils::FontSizeSelect('sundaySchoolNameFontSize', '(' . _('default') . ' 8)', false); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3 mb-lg-0">
                        <div class="card-header py-2">
                            <h4 class="card-title mb-0"><span class="badge badge-success mr-1">4</span><i class="fas fa-sliders-h mr-1"></i><?= _('Others') ?></h4>
                        </div>
                        <div class="card-body">
                            <?php
                            LabelUtils::LabelSelect('labeltype', _('Badge Type'));
                            LabelUtils::FontSelect('labelfont');
                            LabelUtils::FontSizeSelect('labelfontsize', '(' . _('default') . ' 24)');
                            LabelUtils::StartRowStartColumn();
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div id="preview" class="card card-outline card-info mb-0" style="position: sticky; top: 1rem;">
                        <div class="card-header py-2">
                            <h4 class="card-title mb-0"><i class="fa-solid fa-display mr-1"></i><?= _('Live Preview') ?></h4>
                        </div>
                        <div class="card-body text-center">
                            <div class="small text-muted mb-2"><?= _('The preview updates automatically while you edit.') ?></div>
                            <img id="previewImage" class="img-fluid" alt="<?= _('Badge preview') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex flex-wrap justify-content-end align-items-center">
            <small class="text-muted mr-auto mb-2 mb-md-0">
                <i class="fas fa-print mr-1"></i><?= _('Generate a PDF using current group configuration.') ?>
            </small>
            <button type="submit" class="btn btn-primary" name="Submit"><i class="fa-solid fa-play mr-1"></i><?= _('Generate Badges') ?></button>
        </div>
    </form>
</div>


<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        window.CRM.groupID = <?= $iGroupID ?>;

        window.CRM.title = "<?= (empty($_COOKIE["sTitleColorSC"])) ? '#3A3' : $_COOKIE["sTitleColorSC"] ?>";
        window.CRM.titlePosition = "<?= (empty($_COOKIE["titlePositionSC"])) ? "Right" : $_COOKIE["titlePositionSC"] ?>";
        window.CRM.titleFontSize = "<?= (empty($_COOKIE["TitlelabelfontsizeSC"])) ? "8" : $_COOKIE["TitlelabelfontsizeSC"] ?>";

        window.CRM.back = "<?= (empty($_COOKIE["sBackgroudColorSC"])) ? '#F99' : $_COOKIE["sBackgroudColorSC"] ?>";

        window.CRM.sundaySchoolName = "<?= (empty($_COOKIE["sundaySchoolNameSC"])) ? "" : $_COOKIE["sundaySchoolNameSC"] ?>";
        window.CRM.sundaySchoolNamePosition = "<?= (empty($_COOKIE["sundaySchoolNamePositionSC"])) ? "Right" : $_COOKIE["sundaySchoolNamePositionSC"] ?>";
        window.CRM.sundaySchoolNameFontSize = "<?= (empty($_COOKIE["SundaySchoolNameFontSizeSC"])) ? "15" : $_COOKIE["SundaySchoolNameFontSizeSC"] ?>";

        window.CRM.image = "<?= (empty($_COOKIE["imageSC"])) ? '' : $_COOKIE["imageSC"] ?>";
        window.CRM.imagePosition = "<?= (empty($_COOKIE["imagePositionSC"])) ? 'Left' : $_COOKIE["imagePositionSC"] ?>";

        window.CRM.labeltype = "<?= (empty($_COOKIE["labeltype"])) ? 'Tractor' : $_COOKIE["labeltype"] ?>";
        window.CRM.labelfont = "<?= (empty($_COOKIE["labelfont"])) ? "Courier" : $_COOKIE["labelfont"] ?>";
        window.CRM.labelfontsize = "<?= (empty($_COOKIE["labelfontsize"])) ? 24 : $_COOKIE["labelfontsize"] ?>";
        window.CRM.startrow = <?= (empty($_COOKIE["startrow"])) ? 1 : $_COOKIE["startrow"] ?>;
        window.CRM.startcol = <?= (empty($_COOKIE["startcol"])) ? 1 : $_COOKIE["startcol"] ?>;
        window.CRM.useQRCode = 0;

        $('.set-image-position').on('click', function () {
            var pos = $(this).data('pos');
            $('#imagePosition').val(pos).trigger('change');
        });

        $('#resetGroupBadgeDesigner').on('click', function () {
            $('#titleColorInput').val('#3A3');
            $('#backgroundColorInput').val('#F99');
            $('#titlePosition').val('Right').trigger('change');
            $('#imagePosition').val('Left').trigger('change');
            $('#sundaySchoolName').val('');
            $('#sundaySchoolNamePosition').val('Right').trigger('change');
            $('#useQRCode').prop('checked', false).trigger('input');
            window.CRM.title = '#3A3';
            window.CRM.back = '#F99';
            window.CRM.sundaySchoolName = '';
            window.CRM.useQRCode = 0;
            if (window.CRM && typeof window.CRM.reloadLabel === 'function') {
                window.CRM.reloadLabel();
            }
        });
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/BadgeSticker.js"></script>
<script src="<?= $sRootPath ?>/skin/js/group/groupbadge.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>