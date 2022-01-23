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

<div class="alert alert-info"><?= _("When you add some properties to a person they will be add to the badge.") ?></div>
<?php
if (count($_SESSION['aPeopleCart']) == 0) {
    $useCart = 0;
}

if ($useCart == 1) {
    $allPersons = "";

    foreach ($_SESSION['aPeopleCart'] as $personId) {
        $person = PersonQuery::Create()->findOneById ($personId);

        $allPersons .= $person->getFullName().",";
    }
    ?>

    <div class="alert alert-warning"><?= _("You're about to create babges only for this people")." : <b>".$allPersons."</b> "._("who are in the cart. If you don't want to do this, empty the cart, and reload the page.") ?></div>
    <?php
}

if (isset($_GET['typeProblem'])) {
    ?>

    <div class="alert alert-danger">
        <i class="fas fa-ban"></i>
        <?= _("Only PNG and JPEG files are managed actually !!") ?>
    </div>

    <?php
}

?>


<div class="card card-secondary">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Generate Badges') ?></h3>
    </div>
    <form method="post" action="<?= $sRootPath ?>/Reports/PDFBadgeGroup.php" name="labelform" enctype="multipart/form-data">
        <input id="groupId" name="groupId" type="hidden" value="<?= $iGroupID?>">
        <input id="useCart" name="useCart" type="hidden" value="<?= $useCart?>">
        <div class="card-body">
            <?php if ($isSundaySchool) { ?>
            <div class="row">
                <div class="col-md-6">
                    <?= _("Sunday School Name") ?>
                </div>
                <div class="col-md-6">
                    <input type="text" name="sundaySchoolName" id="sundaySchoolName" maxlength="255" size="3" value="<?= $_COOKIE['sundaySchoolNameSC'] ?>" class="form-control" placeholder="<?= _("Sunday School Name") ?>">
                </div>
            </div><br>
            <?php } ?>
            <div class="row">
                <div class="col-md-6">
                    <?= _('Title color') ?>
                </div>
                <div class="col-md-6">
                    <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                        <input id="checkBox" type="hidden" name="title-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                        <span class="editCalendarName" data-id="38,44"><?= _('Chose your color') ?>:</span>
                        <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                            <i style="background-color: rgb(26, 43, 94);"></i>
                        </div>
                    </div>
                </div>
            </div><br>
            <div class="row">
                <div class="col-md-6">
                    <?= _('BackGround color') ?>
                </div>
                <div class="col-md-6">
                    <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                        <input id="checkBox" type="hidden" name="backgroud-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                        <span class="editCalendarName" data-id="38,44"><?= _('Chose your color') ?>:</span>
                        <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                            <i style="background-color: rgb(26, 43, 94);"></i>
                        </div>
                    </div>
                </div>
            </div><br>

            <div class="row">
                <div class="col-md-6">
                    <?= _("Image") ?>
                </div>
                <div class="col-md-6">
                    <?php
                    $image = (empty($_COOKIE["imageSC"]))?'scleft1.png':$_COOKIE["imageSC"];
                    ?>
                    <input type="text" name="image" id="image" maxlength="255" size="3" value="<?= $image ?>" class="form-control" placeholder="<?= _("Sunday School Name") ?>">
                </div>
            </div>
            <br/>

            <div class="row">
                <div class="col-md-6">
                </div>
                <div class="col-md-6">

                    (<b><?= _("Pictures in the Image folder: ") ?></b>
                    <?php
                    foreach ($imgs as $img) {
                        $name = str_replace("../Images/background/","",$img);
                        echo  '<a href="#" class="add-file" data-name="'. $name .'">'.$name . '</a>  <a class="delete-file" data-name="'. $name .'"><i style="cursor:pointer; color:red;" class="icon far fa-trash-alt"></i></a>, ';
                    }

                    if (count($imgs) == 0) {
                        echo _("None");
                    }
                    ?>
                    )

                </div>
            </div><br>

            <div class="row">
                <div class="col-md-6">
                    <?= _("Upload") ?>
                </div>
                <div class="col-md-3">
                    <input type="file" id="stickerBadgeInputFile" name="stickerBadgeInputFile" style="margin-top:3px">
                </div>
                <div class="col-md-3">
                    <?= _("and")?> &nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-xs btn-success" name="SubmitUpload" value="<?= _("Upload") ?>">
                </div>
            </div><br>

            <div class="row">
                <div class="col-md-6">
                    <i class="fas fa-qrcode fa-lg"></i> <?= _('With QR Code') ?>
                </div>
                <div class="col-md-6">
                    <div class="">
                        <input type="checkbox" name="useQRCode" value="Yes" />
                    </div>
                </div>
            </div><br>



            <div class="row">
                <div class="col-md-6">
                    <?= _("Image Position") ?>
                </div>
                <div class="col-md-6">
                    <select name="imagePosition" class="form-control input-sm">
                        <option value="Left" <?= ($_COOKIE["imagePositionSC"] == 'Left')?'selected':'' ?>><?= _('Left') ?></option>
                        <option value="Center" <?= ($_COOKIE["imagePositionSC"] == 'Center')?'selected':'' ?>><?= _('Center') ?></option>
                        <option value="Right" <?= ($_COOKIE["imagePositionSC"] == 'Right')?'selected':'' ?>><?= _('Right') ?></option>
                    </select>
                </div>
            </div><br>
            <?php
            LabelUtils::LabelSelect('labeltype',_('Badge Type'));
            LabelUtils::FontSelect('labelfont');
            LabelUtils::FontSizeSelect('labelfontsize','('._("default").' 24)');
            LabelUtils::StartRowStartColumn();
            ?>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-5"></div>
                <div class="col-md-4">
                    <input type="submit" class="btn btn-primary" value="<?= _('Generate Badges') ?>" name="Submit">
                </div>
            </div>
        </div>
    </form>
    <!-- /.card-body -->
</div>

<?php
require $sRootDocument . '/Include/Footer.php';
?>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script nonce="<?= $CSPNonce ?>">
    var back = "<?= (empty($_COOKIE["sBackgroudColor"]))?'#F99':$_COOKIE["sBackgroudColor"] ?>";
    var title = "<?= (empty($_COOKIE["sTitleColor"]))?'#3A3':$_COOKIE["sTitleColor"] ?>";
</script>

<script src="<?= $sRootPath ?>/skin/js/BadgeSticker.js"></script>
