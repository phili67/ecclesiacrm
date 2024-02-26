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
    <div class="card-header border-1">
        <h3 class="card-title"><?= _('Generate Badges') ?></h3>
    </div>
    <form method="post" action="<?= $sRootPath ?>/Reports/PDFBadgeGroup.php" name="labelform" enctype="multipart/form-data">
        <input id="groupId" name="groupId" type="hidden" value="<?= $iGroupID?>">
        <input id="useCart" name="useCart" type="hidden" value="<?= $useCart?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4">
                            <label><?= $group->getName() ?></label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                                <input id="checkBox" type="hidden" name="title-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                                <span class="editCalendarName" data-id="38,44"><?= _('Chose your color') ?>:</span>
                                <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                                    <i style="background-color: rgb(26, 43, 94);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="titlePosition" class="form-control form-control-sm" id="titlePosition">
                                <option value="Left" <?= ($_COOKIE["titlePositionSC"] == 'Left')?'selected':'' ?>><?= _('Left') ?></option>
                                <option value="Center" <?= ($_COOKIE["titlePositionSC"] == 'Center')?'selected':'' ?>><?= _('Center') ?></option>
                                <option value="Right" <?= ($_COOKIE["titlePositionSC"] == 'Right')?'selected':'' ?>><?= _('Right') ?></option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <?php LabelUtils::FontSizeSelect('Titlelabelfontsize','('._("default").' 15)', false); ?>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-4">
                            <label><?= _('BackGround color') ?></label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                                <input id="checkBox" type="hidden" name="backgroud-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                                <span class="editCalendarName" data-id="38,44"><?= _('Chose your color') ?>:</span>
                                <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                                    <i style="background-color: rgb(26, 43, 94);"></i>
                                </div>
                            </div>
                        </div>
                    </div><br>
                    <hr/>
                    <div class="row">
                        <div class="col-md-3">
                            <label><?= ($isSundaySchool)?_("Sunday School Name"):_("Informations") ?></label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="sundaySchoolName" id="sundaySchoolName" maxlength="255" size="3" value="<?= $_COOKIE['sundaySchoolNameSC'] ?>" class= "form-control form-control-sm" placeholder="<?= ($isSundaySchool)?_("Sunday School Name"):_("Informations") ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="sundaySchoolNamePosition" class="form-control form-control-sm" id="sundaySchoolNamePosition">
                                <option value="Left" <?= ($_COOKIE["sundaySchoolNamePositionSC"] == 'Left')?'selected':'' ?>><?= _('Left') ?></option>
                                <option value="Center" <?= ($_COOKIE["sundaySchoolNamePositionSC"] == 'Center')?'selected':'' ?>><?= _('Center') ?></option>
                                <option value="Right" <?= ($_COOKIE["sundaySchoolNamePositionSC"] == 'Right')?'selected':'' ?>><?= _('Right') ?></option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <?php LabelUtils::FontSizeSelect('sundaySchoolNameFontSize','('._("default").' 8)', false); ?>
                        </div>
                    </div><br>
                    <hr/>                

                    <div class="row">
                        <div class="col-md-6">
                            <label><?= _("Image") ?></label>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $image = (empty($_COOKIE["imageSC"]))?'scleft1.png':$_COOKIE["imageSC"];
                            ?>
                            <input type="text" name="image" id="image" maxlength="255" size="3" value="<?= $image ?>" class= "form-control form-control-sm" placeholder="<?= _("Image Name") ?>">
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
                        <div class="col-md-5">
                            <label><?= _("Upload") ?></label>
                        </div>
                        <div class="col-md-5">
                            <input type="file" id="stickerBadgeInputFile" class="btn btn-xs btn-default" name="stickerBadgeInputFile" style="margin-top:3px">
                        </div>
                        <div class="col-md-2">
                            <?= _("and")?> &nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-xs btn-success" name="SubmitUpload" value="<?= _("Upload") ?>">
                        </div>
                    </div><br>

                    <div class="row">
                        <div class="col-md-6">
                            <label><?= _("Image Position") ?></label>
                        </div>
                        <div class="col-md-6">
                            <select name="imagePosition" class="form-control form-control-sm" id="imagePosition">
                                <option value="Left" <?= ($_COOKIE["imagePositionSC"] == 'Left')?'selected':'' ?>><?= _('Left') ?></option>
                                <option value="Center" <?= ($_COOKIE["imagePositionSC"] == 'Center')?'selected':'' ?>><?= _('Center') ?></option>
                                <option value="Right" <?= ($_COOKIE["imagePositionSC"] == 'Right')?'selected':'' ?>><?= _('Right') ?></option>
                            </select>
                        </div>
                    </div><br>

                <?php if ($isSundaySchool) { ?>
                    <hr/>
                <?php } ?>

                    <div class="row">
                        <div class="col-md-6">
                            <i class="fas fa-qrcode fa-lg"></i> <label><?= _('With QR Code') ?></label>
                        </div>
                        <div class="col-md-6">
                            <div class="">
                                <input type="checkbox" name="useQRCode" value="Yes" id="useQRCode" />
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <?php
                    LabelUtils::LabelSelect('labeltype',_('Badge Type'));
                    LabelUtils::FontSelect('labelfont');
                    LabelUtils::FontSizeSelect('labelfontsize','('._("default").' 24)');
                    LabelUtils::StartRowStartColumn();
                    ?>
                </div>
                <div class="col-md-6">
                    <div id="preview">
                        <div class="text-right"><h2><?= _("Preview") ?></h2><br>
                            <img id='myimage'/>
                        </div>
                    </div>
                </div>
            </div>
            
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


<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        window.CRM.groupID = <?= $iGroupID ?>;

        window.CRM.title = "<?= (empty($_COOKIE["sTitleColorSC"]))?'#3A3':$_COOKIE["sTitleColorSC"] ?>";
        window.CRM.titlePosition = "<?= (empty($_COOKIE["titlePositionSC"]))?"Right":$_COOKIE["titlePositionSC"] ?>"; 
        window.CRM.titleFontSize = "<?= (empty($_COOKIE["TitlelabelfontsizeSC"]))?"8":$_COOKIE["TitlelabelfontsizeSC"] ?>"; 
               
        window.CRM.back = "<?= (empty($_COOKIE["sBackgroudColorSC"]))?'#F99':$_COOKIE["sBackgroudColorSC"] ?>";

        window.CRM.sundaySchoolName = "<?= (empty($_COOKIE["sundaySchoolNameSC"]))?"":$_COOKIE["sundaySchoolNameSC"] ?>";         
        window.CRM.sundaySchoolNamePosition = "<?= (empty($_COOKIE["sundaySchoolNamePositionSC"]))?"Right":$_COOKIE["sundaySchoolNamePositionSC"] ?>"; 
        window.CRM.sundaySchoolNameFontSize = "<?= (empty($_COOKIE["SundaySchoolNameFontSizeSC"]))?"15":$_COOKIE["SundaySchoolNameFontSizeSC"] ?>"; 
        
        window.CRM.image = "<?= (empty($_COOKIE["imageSC"]))?'':$_COOKIE["imageSC"] ?>";
        window.CRM.imagePosition = "<?= (empty($_COOKIE["imagePositionSC"]))?'Left':$_COOKIE["imagePositionSC"] ?>";
               
        window.CRM.labeltype = "<?= (empty($_COOKIE["labeltype"]))?'Tractor':$_COOKIE["labeltype"] ?>";
        window.CRM.labelfont = "<?= (empty($_COOKIE["labelfont"]))?"Courier":$_COOKIE["labelfont"] ?>";
        window.CRM.labelfontsize = "<?= (empty($_COOKIE["labelfontsize"]))?24:$_COOKIE["labelfontsize"] ?>";
        window.CRM.startrow = <?= (empty($_COOKIE["startrow"]))?1:$_COOKIE["startrow"] ?>;
        window.CRM.startcol = <?= (empty($_COOKIE["startcol"]))?1:$_COOKIE["startcol"] ?>;        
        window.CRM.useQRCode = 0;
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/BadgeSticker.js"></script>
<script src="<?= $sRootPath ?>/skin/js/group/groupbadge.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
